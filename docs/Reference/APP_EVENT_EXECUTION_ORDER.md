# App Events Execution Order

This reference describes the order in which Semiorbit invokes app lifecycle events, from app creation through request execution and output handling.

**The key order is: `onStartup()` runs before controller initialization; `onLoad()` runs after controller initialization and before the action.**

Location: `docs/Reference/APP_EVENT_EXECUTION_ORDER.md`.

Verified against Semiorbit Framework **8.6.8**, public `master` source, on **2026-10-05**. The tables describe the implementation, including hooks that are declared but are not automatically invoked.

## Contents

- [Main request: complete execution order](#main-request-complete-execution-order)
- [Internal HMVC request: execution order](#internal-hmvc-request-execution-order)
- [All app events at a glance](#all-app-events-at-a-glance)
- [What is available in each app hook](#what-is-available-in-each-app-hook)
- [Choosing the correct hook](#choosing-the-correct-hook)
- [Controller events and callable routes](#controller-events-and-callable-routes)
- [Output, early exits, and shutdown](#output-early-exits-and-shutdown)
- [Source reference](#source-reference)

## Main request: complete execution order

The main request is the startup request handled by the first `Run()` call on an app service. App creation happens first, either through an explicit `Create()` call or lazily through `Service()`.

| Order | Event or framework step | What happens |
| --- | --- | --- |
| 1 | `App::Create(...)` | Register the app class, apply supplied path overrides, and construct its `AppService`. |
| 2 | **`App::onInit($appService)`** | Run before the service assigns its paths, loads configuration, or marks the app active. |
| 3 | Configure the app service | Assign paths, load configuration, and select the routes path. |
| 4 | Activate the app and register services | Mark the app active and register its services. Activating the main app also defines framework globals and starts the session. |
| 5 | **`App::onCreate()`** | Run after activation and service registration, before request execution. |
| 6 | Complete `Create()` | Assign the constructed service to the app class's service property. |
| 7 | `App::Run(...)` | Begin output buffering and select the startup-request branch. |
| 8 | **`App::onStart()`** | Run before loading the startup request. Its language, routing, controller, and action have not yet been resolved by `Request::Load()`. |
| 9 | Begin `Request::Load()` | Read the URI and request input, select the language, load routes, and resolve the controller or callable route. |
| 10 | **`App::onStartup($request)`** | Run as middleware before constructing the controller. |
| 11 | Construct and initialize the controller | Create its action collection, view, optional dataset, and response; call controller `Initialize()`, then controller `onStart()`. |
| 12 | Complete `Request::Load()` | Select the action, merge route/path parameters into the request parameters, and resolve the request ID. |
| 13 | **`App::onLoad($request)`** | Run after the startup request is loaded, before executing its action. |
| 14 | Update the previous-page URL | Call `Url::setPreviousPage()`. |
| 15 | Execute the action | Call `Request::CallAction()`, which dispatches the controller action or the route callable. |
| 16 | Collect output | Update `Request::LastRun()`, collect buffered output, close the buffer, and prepend that output to the action result. |
| 17 | **`App::onRun(&$output)`** | Run after the action. The hook may modify the combined output by reference. |
| 18 | Finalize output | Sanitize output if configured, echo it if `$flush_output` is true, and return the output. |

This is the normal successful path for a controller route. Exceptions, explicit exits, and nested requests can interrupt or interleave these steps. Callable routes skip controller construction and action detection; see [Controller events and callable routes](#controller-events-and-callable-routes).

## Internal HMVC request: execution order

HMVC means an internal request executed within the same PHP execution, usually by calling `run($uri)` or the app's `Run($uri)` after its initial run has started. A new browser request starts a new PHP execution and follows the main-request sequence again.

Subsequent `Run()` calls on the same service use the HMVC branch. The service switches out of startup mode before calling app `onStart()`, so a nested `Run()` from that hook is already an internal request.

| Order | Event or framework step | What happens |
| --- | --- | --- |
| 1 | Internal `Run($uri, $flush_output)` | Begin a separate output buffer. |
| 2 | **`App::onHmvcRequestStart(&$uri)`** | Run before constructing/loading the internal request. The URI can be changed by reference. |
| 3 | Check the URI | If it is empty, close the buffer and return `null`; later hooks for this internal request do not run. |
| 4 | Create and begin loading the request | Construct a new `Request($uri)`, parse its query string, select its language, load routes, and resolve its controller or callable. |
| 5 | **`App::onRequest($request)`** | Run as middleware before constructing the internal controller. This replaces `onStartup()` for internal requests. |
| 6 | Construct and initialize the controller | Call controller `Initialize()`, then controller `onStart()`, as on the main request. |
| 7 | Complete request loading | Resolve the action, route/path parameters, and request ID. |
| 8 | **`App::onHmvcRequestLoaded($request)`** | Run after loading the internal request and before its action. This replaces app `onLoad()`. |
| 9 | Execute the action and collect output | Dispatch the action/callable, update `Request::LastRun()`, and combine buffered output with its result. |
| 10 | **`App::onRun(&$output)`** | Process this internal request's output by reference. |
| 11 | Finalize output | Sanitize if configured, optionally echo, and return the output. |

Internal requests do not automatically repeat `onInit()`, `onCreate()`, app `onStart()`, `onStartup()`, or app `onLoad()`.

An internal request executes at the point where it is called. For example, if the main action starts an internal request, the internal hooks and its `onRun()` finish before the main action continues. The main request later invokes its own `onRun()`.

## All app events at a glance

All app hooks below are public static methods. Stage names describe their purpose; the order column shows the execution sequence. Main and internal requests follow separate sequences.

### Main request events

Orders 1 and 2 run during app creation. Orders 3 through 6 run during the main request.

| Stage | Order | App hook signature | When it runs |
| --- | --- | --- | --- |
| Initialize | 1 | `onInit(AppService $appService)` | Before the service assigns paths, loads configuration, or activates the app. Receives the service being constructed. |
| Create | 2 | `onCreate()` | After configuration, activation, and service registration; before request execution. |
| Start | 3 | `onStart()` | Before loading the main request. Its language, routing, controller, and action are not yet resolved by request loading. |
| Middleware | 4 | `onStartup(Request $request)` | After selecting the language and resolving the route/controller, before constructing the controller. |
| Load | 5 | `onLoad(Request $request)` | After controller initialization, action detection, and parameter loading; before executing the action. |
| Output | 6 | `onRun(&$output)` | After successful action execution and output collection; before sanitization, echoing, and returning the output. Can modify output by reference. |

### Internal HMVC request events

The numbering restarts for each internal request. These events execute at the point where the internal request is called; they do not follow main-request order 6 automatically.

| Stage | Order | App hook signature | When it runs |
| --- | --- | --- | --- |
| Start | 1 | `onHmvcRequestStart(&$uri)` | Before constructing/loading the internal request. Can change its URI by reference. |
| Middleware | 2 | `onRequest(Request $request)` | After resolving the internal request's language and route/controller, before constructing its controller. |
| Load | 3 | `onHmvcRequestLoaded(Request $request)` | After internal controller initialization, action detection, and parameter loading; before executing the action. |
| Output | 4 | `onRun(&$output)` | After successful internal action execution and output collection; before sanitization and delivery. Can modify output by reference. |

### Declared termination events

These hooks have no execution-order number because the current framework does not automatically invoke them.

| Stage | Order | App hook signature | Current behavior |
| --- | --- | --- | --- |
| Stop | — | `onStop()` | Declared to represent termination, but no framework lifecycle dispatcher call or shutdown registration invokes it. |
| Finish | — | `onFinish()` | Declared to represent completion, but no framework lifecycle dispatcher call or shutdown registration invokes it. |

There is also an `onRegister` comment in `AppManager::Register()`. The current code neither declares a corresponding app hook nor dispatches one; it is not an executable lifecycle event.

## What is available in each app hook

The columns follow the lifecycle from initialization to request loading. They describe the normal first app creation and request, at the point where each hook begins. Custom code or earlier nested requests may have performed additional work.

| State | App `onInit()` | App `onCreate()` | App `onStart()` | `onStartup()` / `onRequest()` | `onLoad()` / `onHmvcRequestLoaded()` |
| --- | --- | --- | --- | --- | --- |
| Service paths | Not yet assigned to the service. | Assigned. | Assigned. | Assigned. | Assigned. |
| Configuration | Not yet loaded by this service. | Loaded. | Loaded. | Loaded. | Loaded. |
| Services | Not yet registered by this service. | Registered. | Registered. | Registered. | Registered. |
| Main app session | Not yet started by activation. | Started during activation. | Started. | Started. | Started. |
| Language for this request | Not yet selected. | Not yet selected. | Not yet selected by this request's `Load()`. | Selected. | Selected. |
| Routes for this request | Not yet loaded. | Not yet loaded. | Not yet loaded by this request. | Loaded. | Loaded. |
| Controller information | Not yet resolved. | Not yet resolved. | Not yet resolved by this request. | Resolved for controller routes. | Resolved for controller routes. |
| Controller instance (`$request->Class`) | Not yet constructed. | Not yet constructed. | Not yet constructed. | Not yet constructed. | Constructed for controller routes. |
| Selected action (`$request->Action`) | Not yet resolved. | Not yet resolved. | Not yet resolved. | Not yet resolved. | Resolved for controller routes. |
| Request parameters (`$request->Params`) | Not yet populated. | Not yet populated. | Not yet populated by this request's `Load()`. | Initial input populated; route/path parameters not yet merged. | Route/path parameters merged. |
| Request ID (`$request->ID`) | Not yet resolved. | Not yet resolved. | Not yet resolved. | Not yet resolved by the final parameter step. | Resolved. |
| Authentication / permissions | Auth configuration and main-app session not yet initialized by this service. | Configuration/session ready; account validation depends on the auth implementation and explicit checks. | Depend on the auth implementation and where checks are called. | Can be checked before controller initialization. | Reflect earlier checks; can also be checked before the action. |

`onInit()` receives the service being constructed. `onCreate()` receives no request argument and runs before request loading. Session entries above refer to activation of the main app: activating another app does not independently start another session through that same main-app setup path.

## Choosing the correct hook

| Requirement | Suitable hook | Reason |
| --- | --- | --- |
| Configure an app before any request runs. | `onCreate()` | Configuration and services have been loaded. |
| Inspect the selected language before constructing the main controller. | `onStartup()` | Request language selection precedes middleware. |
| Authenticate or authorize before controller initialization. | `onStartup()` for the main request; `onRequest()` for internal requests. | Both middleware hooks precede controller construction. |
| Inspect the selected action or final route parameters. | `onLoad()` or `onHmvcRequestLoaded()`. | Those values are resolved after middleware. |
| Change the URI of an internal request. | `onHmvcRequestStart(&$uri)` | The request has not yet been constructed. |
| Change the final action output. | `onRun(&$output)` | Buffered output and the action result have been combined. |

Authentication timing matters when creating role-dependent values. Calculate them after the authentication operation that establishes the current account. If token authentication happens in `onLoad()`, a role flag calculated in `onStartup()` predates that operation.

A PHP constant is a snapshot: it does not update when the authenticated account changes later in the execution. Use a current auth query when the value must remain dynamic across internal requests.

Checks in `onLoad()` can protect the action, but controller construction and controller `onStart()` have already run. Put checks in middleware when they must also protect controller initialization.

In `onInit()`, the passed service is still being initialized: its paths and configuration are not yet assigned by `StartApplication()`. Also, `Application::Create()` assigns the app class's service property only after service construction returns. Avoid recursively calling `App::Service()` from creation hooks to obtain the service being constructed.

## Controller events and callable routes

App `onStart()` and controller `onStart()` are different methods on different objects.

| Hook | Owner | When it runs |
| --- | --- | --- |
| `App::onStart()` | App class, static. | Before loading the main request; it is not repeated for each internal request. |
| `$controller->Initialize()` | Controller instance. | Near the end of its constructor, after basic controller setup. |
| `$controller->onStart()` | Controller instance. | Immediately after `Initialize()`, still inside the constructor, before it returns. |

Models, service providers, and commands also have methods named `onStart()`. They belong to their own lifecycles and are not additional app lifecycle steps.

For a route resolved to a callable, `Request::Load()` still invokes the appropriate app middleware, then completes parameter loading. It does not construct a controller or detect a controller action. The appropriate loaded-request hook still runs, and `Request::CallAction()` executes the callable. Code supporting callable routes should not assume that `$request->Class` or `$request->Action` exists.

## Output, early exits, and shutdown

- `onRun(&$output)` runs before optional output sanitization and before `Run()` echoes or returns the output. Changing the referenced variable changes the output processed afterward.
- `$flush_output = false` prevents the final echo; it does not skip the normal hooks. `Run()` still returns the output.
- `AppService::Run()` catches `\Exception`. An exception before `onRun()` skips that hook and enters exception-response handling; this catch does not cover every PHP `\Throwable`.
- An explicit `exit` or `die` prevents later normal lifecycle steps from executing.
- `onStop()` and `onFinish()` are declared in `Application` and `AppInterface`, but the current framework does not dispatch them or register them as shutdown callbacks. Do not depend on their names or comments as a guarantee of execution. Project code may explicitly invoke or wire them separately.

## Source reference

Execution order is established by the dispatcher calls, not just the hook comments.

| Source | Relevant implementation |
| --- | --- |
| [Application.php](https://github.com/IbrahimKhalil/SemiorbitFramework/blob/master/src/core/Base/Application.php) | `Create()`, lazy `Service()`, and all app hook declarations. |
| [AppService.php](https://github.com/IbrahimKhalil/SemiorbitFramework/blob/master/src/core/Base/AppService.php) | `StartApplication()` dispatches creation hooks; `Run()` dispatches main/HMVC hooks, runs actions, and processes output. |
| [Request.php](https://github.com/IbrahimKhalil/SemiorbitFramework/blob/master/src/core/Http/Request.php) | `Load()` calls middleware before `LoadController()`; `CallMiddleware()` selects `onStartup()` or `onRequest()`. |
| [Controller.php](https://github.com/IbrahimKhalil/SemiorbitFramework/blob/master/src/core/Http/Controller.php) | The constructor calls controller `Initialize()` and controller `onStart()`. |
| [AppManager.php](https://github.com/IbrahimKhalil/SemiorbitFramework/blob/master/src/core/Base/AppManager.php) | App registration, activation, main-app globals/session setup, and hook dispatch through `Call()`. |
| [AppInterface.php](https://github.com/IbrahimKhalil/SemiorbitFramework/blob/master/src/core/Base/AppInterface.php) | Interface declarations, including the currently undispatched `onStop()` and `onFinish()`. |

The links follow `master`; lifecycle changes in later framework versions should be reflected in this reference.
