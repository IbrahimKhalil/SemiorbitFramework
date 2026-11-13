<?php

namespace Semiorbit\Db;

enum TransactionIsolation: string
{

    case READ_UNCOMMITTED = 'READ UNCOMMITTED';

    case READ_COMMITTED = 'READ COMMITTED';

    case REPEATABLE_READ = 'REPEATABLE READ';

    case SERIALIZABLE = 'SERIALIZABLE';

}