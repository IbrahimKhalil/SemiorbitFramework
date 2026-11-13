# Database transactions and isolation

## API

```php
use Semiorbit\Db\DB;
use Semiorbit\Db\TransactionIsolation;

DB::BeginTransaction();

DB::BeginTransaction(TransactionIsolation::READ_COMMITTED);
```

The DB facade forwards the request to the active database driver. The driver interface declares:

```php
public function BeginTransaction(?TransactionIsolation $isolation = null): bool;
```

Passing `null`, including omitting the argument, retains the connection's existing isolation behavior. It does not force a framework-wide default. An explicit level requests isolation for the transaction being started, without permanently changing the setting used by later transactions.

`BeginTransaction()` returns `true` when the transaction starts successfully. It returns `false` when the driver cannot start it or does not support the requested isolation level. Unsupported levels must be rejected before starting a transaction; they must not be silently ignored or replaced by a driver-selected level.

There is no framework requirement to throw an exception for unsupported isolation. Native database exceptions may still occur if the underlying connector is configured to throw; a boolean return signature does not suppress those exceptions automatically.

## Transaction boundaries

A transaction groups database changes into one unit. `Commit()` makes its changes permanent. `Rollback()` abandons its uncommitted changes. All operations belonging to the unit must use the same connection.

Always check the result of beginning the transaction before performing its work. Check write and commit results too; calling `Commit()` does not by itself prove that the commit succeeded.

The following example illustrates boolean failure handling. Its SQL is deliberately generic and must target transactional tables:

```php
use Semiorbit\Db\DB;
use Semiorbit\Db\TransactionIsolation;

if (!DB::BeginTransaction(TransactionIsolation::READ_COMMITTED)) {

    return false;

}

if (!DB::Cmd('UPDATE counters SET value = value + 1 WHERE counter_id = 1')) {

    DB::Rollback();

    return false;

}

if (!DB::Commit()) {

    DB::Rollback();

    return false;

}

return true;
```

The example assumes connector failures are reported as return values. When the connector throws, the caller must also arrange rollback on exceptional exits and propagate or handle the original error. A failed commit caused by a lost connection can have an uncertain outcome; do not blindly repeat a financial operation without checking its identity and recorded state.

Do not start a second transaction inside an active transaction unless the driver explicitly supports the intended nesting or savepoint behavior. Do not mix transaction statements on another connection with this API. DDL and nontransactional storage engines may not obey the same rollback rules as transactional data writes.

## Isolation levels

Isolation determines how concurrent transactions interact. Atomic commit and isolation solve different problems: grouping writes together does not automatically make every business decision safe against concurrent requests.

| Enum case | General behavior | Practical consideration |
| --- | --- | --- |
| `READ_UNCOMMITTED` | May expose changes another transaction has not committed. | A value read may later disappear through rollback. Avoid for financial decisions. |
| `READ_COMMITTED` | Prevents dirty reads; later statements can observe newer committed data. | Two reads in the same transaction can return different values. |
| `REPEATABLE_READ` | Prevents dirty reads and nonrepeatable reads of existing rows. | Snapshot and phantom behavior varies by database; ordinary reads may retain an older view. |
| `SERIALIZABLE` | Successful transactions must have an outcome equivalent to serial execution. | Conflicts can block or abort transactions; callers may need to retry the whole unit. |

These descriptions are a guide to the standard levels, not a promise that every database uses identical locks or snapshots. A serializable transaction does not necessarily run alone: databases can detect conflicting concurrent work and abort one participant.

## Isolation and row locks

A row lock controls concurrent access to particular rows. Isolation also controls the visibility of ordinary reads elsewhere in the transaction. Locking one parent row does not automatically refresh an earlier snapshot used by other queries.

For example, suppose an invoice total is 100 and its payments currently sum to 60. Two requests each want to add a payment of 30. If both independently read 60, both might accept their payment and produce a total of 120.

A suitable workflow is:

1. Begin the transaction with the isolation behavior the workflow requires.
2. Lock the invoice before reading the payment total used for the decision.
3. Read the current committed payment total and validate the proposed payment.
4. Save the payment and commit.

Every competing payment, refund, item or invoice writer that affects the same invariant must follow the same parent-locking protocol. Merely choosing `READ_COMMITTED` does not prevent both requests from making the same decision if that protocol is missing.

With MySQL InnoDB, an ordinary read under `REPEATABLE_READ` can establish a snapshot before the parent lock is obtained. Later ordinary aggregate reads can then retain that snapshot. `READ_COMMITTED` provides a fresh snapshot for each consistent read; combined with the parent lock and correct operation order, this can support the workflow above. An alternative design can use appropriate current locking reads, but those must be designed and checked for the actual queries involved.

## Driver responsibilities

The shared interface expresses intent. Database-specific transaction commands belong in the driver, not in application controllers.

| Database or access layer | Handling |
| --- | --- |
| MySQL / MySQLi | Supports the four enum levels for InnoDB. Apply an explicit level to the next transaction, then begin it. |
| SQLite | Does not expose the four levels as selectable equivalents. Return `false` for an explicit unsupported level; preserve ordinary behavior when the argument is `null`. |
| PostgreSQL | Accepts all four standard names. The database itself treats `READ_UNCOMMITTED` as `READ_COMMITTED`. |
| Oracle Database | Supports `READ_COMMITTED` and `SERIALIZABLE`. Return `false` for the other two enum requests. |
| SQL Server | Supports the four enum levels. It also has `SNAPSHOT`, which is outside this four-case enum. |
| PDO | An access layer rather than a database engine. Support depends on the underlying database and driver. |

Database capability does not imply that a corresponding Semiorbit driver has already been implemented. Each future driver must implement and verify these semantics before advertising support.

### Isolation support matrix

| Database | READ_UNCOMMITTED | READ_COMMITTED | REPEATABLE_READ | SERIALIZABLE | Additional distinction |
| --- | --- | --- | --- | --- | --- |
| MySQL InnoDB | Yes | Yes | Yes | Yes | Default is normally REPEATABLE_READ; configuration can change it. Other storage engines may not provide transactional guarantees. |
| PostgreSQL | Accepted; behaves as READ_COMMITTED | Yes | Yes | Yes | Default is normally READ_COMMITTED. REPEATABLE_READ also prevents phantom reads. Serialization failures can require a complete retry. |
| Oracle Database | No | Yes | No | Yes | Default is normally READ_COMMITTED. Read-only transactions are a separate facility. |
| SQL Server | Yes | Yes | Yes | Yes | Default is normally READ_COMMITTED. SNAPSHOT is an additional level; READ_COMMITTED_SNAPSHOT changes how READ_COMMITTED is implemented. |
| SQLite | No equivalent selectable level | No equivalent selectable level | No equivalent selectable level | Serializable behavior by default, but no SET TRANSACTION isolation selector | Under this API, explicit enum requests return false, including SERIALIZABLE; no-argument transactions retain SQLite behavior. |

SQLite's shared-cache `read_uncommitted` pragma is a special configuration and is not a portable implementation of this enum. It must not be enabled implicitly to satisfy a request.

### Framework driver coverage

The driver interface provides a common signature; it does not implement isolation itself. The MySQLi implementation handles MySQL, and the SQLite3 implementation preserves SQLite's existing transaction path while rejecting explicit isolation requests. PostgreSQL, Oracle, SQL Server and PDO entries describe the required behavior of drivers for those systems; they are not a claim that those drivers are present in a particular framework installation.

Applications can use no-argument transactions across implemented drivers. An explicit isolation request is portable only across drivers and databases that implement that request. A false result must be handled rather than retried without isolation.

### MySQL / MySQLi

For an explicit request, use:

```sql
SET TRANSACTION ISOLATION LEVEL READ COMMITTED;

START TRANSACTION;
```

`SET TRANSACTION` without `SESSION` or `GLOBAL` changes the characteristics of the next transaction. Do not replace it with a session-wide setting merely to implement this argument. The MySQLi driver uses its native `begin_transaction()` call after the setting succeeds.

If setting the isolation fails, return `false` without beginning the transaction. If beginning fails after the setting succeeded, account for the pending next-transaction setting before reusing that connection; do not assume a failed begin consumed it. Never execute intervening application work between setting the level and beginning the transaction.

### SQLite

SQLite normally serializes writers. In WAL mode, read transactions retain a snapshot; this is not the per-statement fresh-read behavior requested by MySQL `READ_COMMITTED`.

`BEGIN IMMEDIATE` starts a write transaction before subsequent reads and prevents another writer from getting ahead. It is a transaction acquisition mode, not an alias for one of the enum isolation levels. If exposed by the framework, it needs its own explicit design. It can fail when another writer already holds the necessary lock.

Database-specific row locking syntax also needs attention. Wrapping isolation in a shared API does not make MySQL `SELECT ... FOR UPDATE` portable to SQLite.

### PostgreSQL and Oracle

PostgreSQL can specify isolation as part of `BEGIN`, or set transaction characteristics within the new transaction before the relevant queries. Its native handling of `READ_UNCOMMITTED` is documented database behavior, rather than a silent substitution invented by the framework.

Oracle applies supported transaction isolation through its own transaction commands and requires correct placement before transactional work. Do not copy MySQL's begin sequence unchanged into an Oracle driver.

### SQL Server

SQL Server's `SET TRANSACTION ISOLATION LEVEL` can persist on the connection. A driver implementing a transaction-only argument must preserve the previous setting and restore it after commit or rollback, including relevant failure paths. A later no-argument transaction must not unintentionally inherit the previous explicit request.

Its `READ_COMMITTED` implementation can use locks or row versioning according to database configuration. `SNAPSHOT` is a separate option and requires database support/configuration; do not silently map it to `REPEATABLE_READ`.

### PDO

Use PDO's native transaction methods so its transaction tracking stays consistent. Implement isolation through the underlying engine's supported commands at the appropriate time. A PDO wrapper must not infer support merely because another PDO-backed database supports the same enum case.

## Errors and retries

An unsupported explicit level returns `false` and starts no transaction. Applications should treat this as a failed prerequisite, not continue the operation at another isolation level.

Deadlocks, serialization failures and lock contention are distinct from unsupported isolation. A retry, where appropriate, must restart the complete transaction and recalculate its decisions. Do not retry only the final failed statement when its earlier reads may no longer be valid.

Keep external effects such as sending messages outside retryable database work, or use an explicit delivery mechanism that prevents duplication. Keep transactions short and acquire shared resources in a consistent order.



## Official references

- [MySQL: SET TRANSACTION](https://dev.mysql.com/doc/refman/5.7/en/set-transaction.html)
- [MySQL: InnoDB transaction isolation](https://dev.mysql.com/doc/refman/5.7/en/innodb-transaction-isolation-levels.html)
- [SQLite: Isolation](https://www.sqlite.org/isolation.html)
- [SQLite: Transactions](https://www.sqlite.org/lang_transaction.html)
- [PostgreSQL: Transaction isolation](https://www.postgresql.org/docs/current/transaction-iso.html)
- [Oracle: SQL processing and isolation](https://docs.oracle.com/en/database/oracle/oracle-database/21/adfns/sql-processing-for-application-developers.html)
- [SQL Server: SET TRANSACTION ISOLATION LEVEL](https://learn.microsoft.com/en-us/sql/t-sql/statements/set-transaction-isolation-level-transact-sql)
- [PHP: PDO transactions](https://www.php.net/manual/en/pdo.transactions.php)
- [phpDocumentor: @see](https://docs.phpdoc.org/guide/references/phpdoc/tags/see.html)
