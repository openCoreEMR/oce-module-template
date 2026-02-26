# OpenEMR Module Development Guide for AI Agents

This document describes the architectural patterns and conventions for OpenEMR modules developed by OpenCoreEMR. Follow these patterns when working on **any** OpenEMR module in this organization.

## About This Template

**This is a template repository.** When creating a new module:

### Quick Start (Recommended)
1. Copy this repository
2. Run `composer install`
3. Run `./bin/setup` - Interactive wizard that:
   - Asks if internal (OpenCoreEMR/oce-) or external (community/oe-)
   - Prompts for module name
   - Replaces all placeholders automatically
   - Optionally removes setup files when done
4. Start building your module
5. See `GETTING_STARTED.md` for detailed checklist

### Manual Setup (Alternative)
1. Copy this repository
2. Replace placeholder values throughout the codebase (see Placeholder Reference below)
3. Update `composer.json`, `version.php`, `phpcs.xml`, and documentation files
4. See `GETTING_STARTED.md` for step-by-step checklist

**For AI agents:** When a user asks you to "create a new OpenEMR module" or work on module code, you should follow the patterns documented here. If working on an existing module, verify it follows these patterns and refactor if necessary to maintain consistency.

## Placeholder Reference

This template uses the following placeholders that should be replaced when creating a new module:

| Placeholder | Description | Internal Example | External Example |
|-------------|-------------|------------------|------------------|
| `{VendorName}` | Vendor name (PascalCase) | `OpenCoreEMR` | `MyOrg` or `OpenEMR` |
| `{vendorname}` | Vendor name (lowercase) | `opencoreemr` | `myorg` or `openemr` |
| `{vendor-prefix}` | Module prefix | `oce` | `oe` |
| `{modulename}` | Module name (lowercase-with-hyphens) | `lab-integration` | `lab-integration` |
| `{ModuleName}` | Module name (PascalCase) | `LabIntegration` | `LabIntegration` |

**Usage Examples:**

**For OpenCoreEMR Internal Use:**
- Repository: `oce-module-lab-integration`
- Package: `opencoreemr/oce-module-lab-integration`
- Namespace: `OpenCoreEMR\Modules\LabIntegration`
- Module ID: `oce-module-lab-integration`

**For External/Community Use:**
- Repository: `oe-module-lab-integration`
- Package: `myorg/oe-module-lab-integration` or `openemr/oe-module-lab-integration`
- Namespace: `MyOrg\Modules\LabIntegration` or `OpenEMR\Modules\LabIntegration`
- Module ID: `oe-module-lab-integration`

## Naming Conventions

When creating a new module from this template, use consistent naming:

| Context | Format | Internal Example | External Example |
|---------|--------|------------------|------------------|
| Repository name | `{vendor-prefix}-module-{name}` | `oce-module-lab-integration` | `oe-module-lab-integration` |
| Composer package | `{vendorname}/{vendor-prefix}-module-{name}` | `opencoreemr/oce-module-lab-integration` | `openemr/oe-module-lab-integration` |
| Namespace | `{VendorName}\Modules\{PascalCase}` | `OpenCoreEMR\Modules\LabIntegration` | `OpenEMR\Modules\LabIntegration` |
| Exception prefix | `{PascalCase}Exception` | `LabIntegrationNotFoundException` | `LabIntegrationNotFoundException` |
| Bootstrap constant | `{vendor-prefix}-module-{name}` | `oce-module-lab-integration` | `oe-module-lab-integration` |
| File names | PascalCase for classes | `LabResultController.php` | `LabResultController.php` |
| Directory names | lowercase | `lab-results/` | `lab-results/` |

**Module name rules:**
- Use lowercase with hyphens for repositories and URLs
- Use PascalCase for PHP namespaces and class names
- Keep names concise but descriptive
- Avoid redundant words like "openemr" or "module" in the functional name
- Internal modules use `oce-` prefix, external/community modules use `oe-` prefix

## Module Architecture Overview

OpenEMR modules follow a **Symfony-inspired MVC architecture** with:
- **Controllers** in `src/Controller/` handling business logic
- **Twig templates** in `templates/` for all HTML rendering
- **Services** in `src/Service/` for business operations
- **Minimal public entry points** in `public/` that bootstrap and dispatch

## File Structure Convention

```
{vendor-prefix}-module-{modulename}/
├── bin/
│   └── setup              # Setup wizard (removed after initial setup)
├── info.txt               # Module display name for OpenEMR UI (REQUIRED)
├── public/
│   ├── index.php          # Main entry point (25-35 lines)
│   ├── {feature}.php      # Feature entry points (25-35 lines)
│   └── assets/            # Static assets (CSS, JS, images)
├── src/
│   ├── Bootstrap.php      # Module initialization and DI
│   ├── ConfigAccessorInterface.php  # Configuration access abstraction
│   ├── ConfigFactory.php            # Factory for config accessor selection
│   ├── EnvironmentConfigAccessor.php # Env var config (for containers)
│   ├── FileConfigAccessor.php       # YAML file config (for K8s)
│   ├── GlobalsAccessor.php          # Database-backed config (OpenEMR globals)
│   ├── GlobalConfig.php             # Centralized configuration wrapper
│   ├── YamlConfigLoader.php         # YAML file parsing and merging
│   ├── ModuleAccessGuard.php        # Entry point access guard
│   ├── Command/           # Console commands (removed after setup)
│   │   └── SetupCommand.php
│   ├── Controller/        # Request handlers
│   │   ├── {Feature}Controller.php
│   │   └── ...
│   ├── Service/           # Business logic
│   │   ├── {Feature}Service.php
│   │   └── ...
│   └── Exception/         # Custom exception types
│       ├── {ModuleName}ExceptionInterface.php
│       ├── {ModuleName}Exception.php
│       ├── {ModuleName}NotFoundException.php
│       ├── {ModuleName}UnauthorizedException.php
│       ├── {ModuleName}AccessDeniedException.php
│       ├── {ModuleName}ValidationException.php
│       ├── {ModuleName}ConfigurationException.php
│       └── {ModuleName}ApiException.php
├── templates/
│   └── {feature}/
│       ├── {view}.html.twig
│       └── partials/
│           └── _{component}.html.twig
├── composer.json
└── openemr.bootstrap.php  # Module loader
```

## Module info.txt (REQUIRED)

**Every module MUST have an `info.txt` file.** OpenEMR reads this file to display the module name in the admin UI.

### Format

Single line containing the display name:
```
{VendorName} {ModuleName} Module
```

**Examples:**
- `OpenCoreEMR Lab Integration Module`
- `OpenCoreEMR Notification Banner`

### Important Notes

1. **Required file** - If missing, OpenEMR falls back to the directory name (ugly)
2. **First line only** - Only the first line is used; keep it simple
3. **No version in name** - Version is tracked separately via `version.php`
4. **Replace placeholders** - Use the same `{VendorName}` and `{ModuleName}` placeholders as elsewhere

## Versioning with Release Please

Module versions are managed automatically by [Release Please](https://github.com/googleapis/release-please). **Never edit version numbers manually.**

### How It Works

1. Merge PRs with [conventional commit](https://www.conventionalcommits.org/) titles
2. Release Please creates a release PR with version bumps
3. When merged, it updates:
   - `.release-please-manifest.json` - Source of truth for version
   - `version.php` - PHP version constants for runtime
   - `CHANGELOG.md` - Generated from commit messages

### Configuration Files

| File | Purpose |
|------|---------|
| `release-please-config.json` | Release Please settings and extra-files list |
| `.release-please-manifest.json` | Current version (updated automatically) |
| `version.php` | PHP version constants (updated via `extra-files`) |

### Adding Version-Dependent Files

If you add files that need version updates (rare), add them to `release-please-config.json`:

```json
{
  "packages": {
    ".": {
      "extra-files": [
        "version.php",
        "some-other-file.txt"
      ]
    }
  }
}
```

Release Please uses the [generic updater](https://github.com/googleapis/release-please/blob/main/docs/customizing.md#updating-arbitrary-files) to find and replace version patterns like `x]x.y.z` in extra files.

## Configuration Abstraction Layer

The template includes a flexible configuration system that supports database-backed globals, environment variables, and YAML file-based configuration:

### Key Components

| File | Purpose |
|------|---------|
| `ConfigAccessorInterface` | Common interface for all config accessors |
| `GlobalsAccessor` | Reads config from OpenEMR database globals |
| `EnvironmentConfigAccessor` | Reads config from environment variables |
| `FileConfigAccessor` | Reads config from YAML files with env var overrides |
| `YamlConfigLoader` | Parses YAML config files, processes imports, merges |
| `ConfigFactory` | Selects the appropriate accessor based on environment |
| `GlobalConfig` | Centralized wrapper providing typed access to all module config |

### Usage Pattern

```php
// In Bootstrap or entry points - factory determines config source
$configAccessor = ConfigFactory::createConfigAccessor();
$config = new GlobalConfig($configAccessor);

// Use typed getters
$isEnabled = $config->isEnabled();      // bool
$apiKey = $config->getApiKey();         // string (decrypted in DB mode)
```

### Environment Variable Mode

Set `{VENDOR_PREFIX}_{MODULENAME}_ENV_CONFIG=1` to use environment variables instead of database:

```bash
# Enable env config mode
export {VENDOR_PREFIX}_{MODULENAME}_ENV_CONFIG=1

# Module configuration
export {VENDOR_PREFIX}_{MODULENAME}_ENABLED=true
export {VENDOR_PREFIX}_{MODULENAME}_API_KEY=your-api-key
```

Benefits:
- Container-friendly deployments (no database config needed)
- Secrets can be injected via environment
- Config is immutable (no admin UI editing)

### YAML File-Based Configuration Mode

For Kubernetes-style deployments, modules support YAML config files mounted via ConfigMap and Secret volumes. This is the preferred approach for K8s because it maps directly to volume mounts.

**Config files (conventional paths):**
- `/etc/oce/{modulename}/config.yaml` — non-sensitive settings (from ConfigMap)
- `/etc/oce/{modulename}/secrets.yaml` — sensitive settings (from Secret)

**Override paths via env vars:**
- `{VENDOR_PREFIX}_{MODULENAME}_CONFIG_FILE` — custom path to config file
- `{VENDOR_PREFIX}_{MODULENAME}_SECRETS_FILE` — custom path to secrets file

**Example config.yaml:**
```yaml
enabled: true
api_key: "your-api-key"
region: us
```

**Example secrets.yaml:**
```yaml
api_secret: "your-api-secret"
```

**Precedence:** env vars > YAML files > database globals

The module auto-detects file presence — no activation flag needed. When config files are present, the admin UI shows "Configuration Managed Externally" instead of editable fields.

**Imports:** Config files support Symfony-style imports for splitting across files:
```yaml
imports:
  - { resource: secrets.yaml }
enabled: true
```

### Adding New Config Options

**IMPORTANT:** All module config settings MUST be accessible via all three config modes (YAML files, environment variables, AND OpenEMR globals). When you add a new config option, update all the relevant files:

**Never check settings directly** (e.g., `getenv()` or `$GLOBALS[]`). Always use the config abstraction:

1. Add constant in `GlobalConfig`:
```php
public const CONFIG_OPTION_API_KEY = '{vendor_prefix}_{modulename}_api_key';
```

2. Add env var mapping in `EnvironmentConfigAccessor::KEY_MAP`:
```php
private const KEY_MAP = [
    GlobalConfig::CONFIG_OPTION_API_KEY => '{VENDOR_PREFIX}_{MODULENAME}_API_KEY',
];
```

3. Add YAML key mapping in `FileConfigAccessor::KEY_MAP`, `ENV_OVERRIDE_MAP`, and `REVERSE_KEY_MAP`:
```php
// KEY_MAP: short YAML key => internal config key
'api_key' => GlobalConfig::CONFIG_OPTION_API_KEY,

// ENV_OVERRIDE_MAP: internal key => env var name
GlobalConfig::CONFIG_OPTION_API_KEY => '{VENDOR_PREFIX}_{MODULENAME}_API_KEY',

// REVERSE_KEY_MAP: internal key => short YAML key
GlobalConfig::CONFIG_OPTION_API_KEY => 'api_key',
```

4. Add typed getter in `GlobalConfig`:
```php
public function getApiKey(): string
{
    return $this->configAccessor->getString(self::CONFIG_OPTION_API_KEY, '');
}
```

5. Register the global setting in `Bootstrap::addGlobalSettingsSection()` for admin UI.

6. Use the getter in your code:
```php
// Correct - uses abstraction
$apiKey = $this->config->getApiKey();

// WRONG - bypasses abstraction
$apiKey = getenv('MYMODULE_API_KEY') ?: $GLOBALS['mymodule_api_key'];
```

The config accessors handle precedence automatically: `FileConfigAccessor` checks env vars then YAML data, `EnvironmentConfigAccessor` checks env vars then falls back to globals.

### External Config Mode in Admin UI

When env config mode is enabled (`{VENDOR_PREFIX}_{MODULENAME}_ENV_CONFIG=1`), the global settings section must display an informational message instead of editable fields:

```php
public function registerGlobalSettings(GlobalsInitializedEvent $event): void
{
    $service = $event->getGlobalsService();
    $section = xlt('My Module');
    $service->createSection($section);

    // In env config mode, show informational message instead of editable fields
    if ($this->globalsConfig->isExternalConfigMode()) {
        $setting = new GlobalSetting(
            xlt('Configuration Managed Externally'),
            GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION,
            '',
            '',
            false
        );
        $setting->addFieldOption(
            GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK,
            static fn() => xlt('This module is managed by deployment administrators.')
        );
        $service->appendToSection($section, '{vendor_prefix}_{modulename}_env_config_notice', $setting);
        return;
    }

    // Normal mode: register editable settings
    // ...
}
```

This prevents confusion when admins see config fields that have no effect because the module reads from environment variables instead.

## Module Access Guard

The `ModuleAccessGuard` prevents access to module endpoints when:
1. Module is not registered in OpenEMR
2. Module is disabled in module management
3. Module's own 'enabled' setting is off

```php
// At top of public entry points. Use return (not exit) to stay consistent with "no exit/die" rules.
$guardResponse = ModuleAccessGuard::check(Bootstrap::MODULE_NAME);
if ($guardResponse instanceof Response) {
    $guardResponse->send();
    return;
}
run();  // Rest of entry logic in a function so the guard can return instead of exit
```

Returns 404 (not 403) to avoid leaking module presence. Wrapping the rest of the entry point in a function (e.g. `run()`) allows the guard to use `return` instead of `exit`, keeps the template testable, and avoids any exception for "exit in guard only" in the coding standards.

### Authentication and ACL

Entry points load `globals.php`, which typically starts the OpenEMR session. For sensitive operations (create, update, delete, or viewing sensitive data), call OpenEMR's ACL (e.g. `AclMain::aclCheckCore('section', 'action')`) and throw `{ModuleName}UnauthorizedException` or `{ModuleName}AccessDeniedException` if the check fails. The menu item's `acl_req` controls visibility; controllers must enforce the same (or stricter) ACL before performing the action.

## Public Entry Point Pattern

Public PHP files should be short! Just dispatch a controller and send a response. Follow this pattern:

```php
<?php
/**
 * [Description of endpoint]
 *
 * @package   {VendorName}
 * @link      http://www.open-emr.org
 * @author    [Author Name] <email@example.com>
 * @copyright Copyright (c) 2026 {VendorName}
 * @license   GNU General Public License 3
 */

$sessionAllowWrite = true;

// Load module autoloader before globals.php
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../../../../globals.php';

use {VendorName}\Modules\{ModuleName}\Bootstrap;
use {VendorName}\Modules\{ModuleName}\ConfigFactory;
use {VendorName}\Modules\{ModuleName}\Exception\{ModuleName}ExceptionInterface;
use {VendorName}\Modules\{ModuleName}\GlobalsAccessor;
use {VendorName}\Modules\{ModuleName}\ModuleAccessGuard;
use Symfony\Component\HttpFoundation\Response;

// Check if module is installed and enabled - return 404 if not
$guardResponse = ModuleAccessGuard::check(Bootstrap::MODULE_NAME);
if ($guardResponse instanceof Response) {
    $guardResponse->send();
    return;
}
run();

function run(): void {
    // Get kernel and bootstrap module
    $globalsAccessor = new GlobalsAccessor();
    $kernel = $globalsAccessor->get('kernel');
    // ... bootstrap, get controller, dispatch ...
    try {
        $response = $controller->dispatch($action);
        $response->send();
    } catch ({ModuleName}ExceptionInterface $e) {
        error_log("Module error: " . $e->getMessage());
        // Use a generic Twig error page; do not show exception messages to users
        $response = createErrorResponse($e->getStatusCode(), $kernel, $bootstrap->getWebroot());
        $response->send();
    } catch (\Throwable $e) {
        error_log("Unexpected error: " . $e->getMessage());
        $response = createErrorResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $kernel, $bootstrap->getWebroot());
        $response->send();
    }
}
```

In exception handlers, render a generic error template (e.g. `templates/error.html.twig`) instead of echoing exception messages, to avoid leaking implementation details. Log the real message with `error_log()`.

## Controller Pattern

Controllers should:
- Be in `src/Controller/`
- Use **constructor dependency injection**
- Return **Symfony Response objects** (never void)
- Have a `dispatch()` method that routes actions
- Throw **custom exceptions** (never die/exit)

```php
<?php

namespace {VendorName}\Modules\{ModuleName}\Controller;

use {VendorName}\Modules\{ModuleName}\Exception\{ModuleName}AccessDeniedException;
use {VendorName}\Modules\{ModuleName}\Exception\{ModuleName}NotFoundException;
use {VendorName}\Modules\{ModuleName}\Exception\{ModuleName}ValidationException;
use {VendorName}\Modules\{ModuleName}\GlobalConfig;
use {VendorName}\Modules\{ModuleName}\Service\{Feature}Service;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

class {Feature}Controller
{
    private readonly SystemLogger $logger;

    public function __construct(
        private readonly GlobalConfig $config,
        private readonly {Feature}Service $service,
        private readonly Environment $twig
    ) {
        $this->logger = new SystemLogger();
    }

    /**
     * Dispatch action to appropriate method
     *
     * @param array<string, mixed> $params
     */
    public function dispatch(string $action, array $params): Response
    {
        return match ($action) {
            'create' => $this->handleCreate($params),
            'view' => $this->showView($params),
            'list' => $this->showList($params),
            default => $this->showList($params),
        };
    }

    /**
     * @param array<string, mixed> $params
     */
    private function showList(array $params): Response
    {
        // Business logic here

        $content = $this->twig->render('{feature}/list.html.twig', [
            'items' => $items,
            'csrf_token' => CsrfUtils::collectCsrfToken(),
        ]);

        return new Response($content);
    }

    /**
     * @param array<string, mixed> $params
     */
    private function handleCreate(array $params): Response
    {
        // Validate CSRF
        if (!CsrfUtils::verifyCsrfToken($params['csrf_token'] ?? '')) {
            throw new {ModuleName}AccessDeniedException("CSRF token verification failed");
        }

        // Validate input (use explicit check; avoid empty() which treats "0" as missing)
        $required = trim($params['required_field'] ?? '');
        if ($required === '') {
            throw new {ModuleName}ValidationException("Required field is missing");
        }

        // Process request
        try {
            $this->service->create($params);
            return new RedirectResponse($_SERVER['PHP_SELF']);
        } catch (\Exception $e) {
            $this->logger->error("Error creating item: " . $e->getMessage());
            throw new {ModuleName}Exception("Error creating item: " . $e->getMessage());
        }
    }
}
```

## Exception Handling Pattern

### Define Custom Exception Hierarchy

All modules should have their own exception types in `src/Exception/`:

```php
<?php
// src/Exception/{ModuleName}ExceptionInterface.php

namespace {VendorName}\Modules\{ModuleName}\Exception;

interface {ModuleName}ExceptionInterface extends \Throwable
{
    /**
     * Get the HTTP status code for this exception
     */
    public function getStatusCode(): int;
}
```

```php
<?php
// src/Exception/{ModuleName}Exception.php

namespace {VendorName}\Modules\{ModuleName}\Exception;

abstract class {ModuleName}Exception extends \RuntimeException implements {ModuleName}ExceptionInterface
{
    abstract public function getStatusCode(): int;
}
```

```php
<?php
// src/Exception/{ModuleName}NotFoundException.php

namespace {VendorName}\Modules\{ModuleName}\Exception;

class {ModuleName}NotFoundException extends {ModuleName}Exception
{
    public function getStatusCode(): int
    {
        return 404;
    }
}
```

### Common Exception Types to Implement

- `{ModuleName}NotFoundException` (404) - Resource not found
- `{ModuleName}UnauthorizedException` (401) - User not authenticated
- `{ModuleName}AccessDeniedException` (403) - CSRF failed, insufficient permissions
- `{ModuleName}ValidationException` (400) - Invalid input data
- `{ModuleName}ConfigurationException` (500) - Configuration errors

### Exception Handling in Public Files

```php
try {
    $response = $controller->dispatch($action, $_REQUEST);
    $response->send();
} catch ({ModuleName}ExceptionInterface $e) {
    error_log("Error: " . $e->getMessage());

    $response = new Response(
        "Error: " . htmlspecialchars($e->getMessage()),
        $e->getStatusCode()
    );
    $response->send();
} catch (\Exception $e) {
    error_log("Unexpected error: " . $e->getMessage());

    $response = new Response(
        "Error: An unexpected error occurred",
        500
    );
    $response->send();
}
```

## Response Handling - CRITICAL RULES

### ✅ ALWAYS DO:
- Controllers return `Response`, `JsonResponse`, `RedirectResponse`, or `BinaryFileResponse`
- Use Symfony HTTP Foundation components
- Call `$response->send()` in public entry points
- Use `Response` constants: `Response::HTTP_OK`, `Response::HTTP_NOT_FOUND`, etc.
- Throw exceptions with proper types (never with status codes in constructor)

### ❌ NEVER DO:
- ~~`header('Location: ...')`~~ → Use `RedirectResponse`
- ~~`http_response_code(404)`~~ → Use `new Response($content, 404)` or exceptions
- ~~`echo json_encode($data)`~~ → Use `JsonResponse`
- ~~`readfile($path)`~~ → Use `BinaryFileResponse`
- ~~`die()` or `exit`~~ → Throw exceptions
- ~~Controllers returning `void`~~ → Return `Response` objects

### Example: Correct Response Handling

```php
// JSON Response
return new JsonResponse(['status' => 'success'], Response::HTTP_OK);

// Redirect
return new RedirectResponse('/path/to/redirect');

// File Download
$response = new BinaryFileResponse($filePath);
$response->setContentDisposition(
    ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    'filename.pdf'
);
return $response;

// HTML Response
$content = $this->twig->render('template.html.twig', $data);
return new Response($content);
```

## Bootstrap Pattern

The `Bootstrap.php` class should provide factory methods for controllers and accept an optional `ConfigAccessorInterface`:

```php
<?php

namespace {VendorName}\Modules\{ModuleName};

use {VendorName}\Modules\{ModuleName}\Controller\{Feature}Controller;
use {VendorName}\Modules\{ModuleName}\Service\{Feature}Service;
use OpenEMR\Core\Kernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    public const MODULE_NAME = "{vendor-prefix}-module-{modulename}";

    private readonly GlobalConfig $globalsConfig;
    private readonly \Twig\Environment $twig;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Kernel $kernel = new Kernel(),
        ?ConfigAccessorInterface $configAccessor = null
    ) {
        // Use factory to determine config source if not provided
        $configAccessor ??= ConfigFactory::createConfigAccessor();
        $this->globalsConfig = new GlobalConfig($configAccessor);

        $templatePath = \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
        $twig = new TwigContainer($templatePath, $this->kernel);
        $this->twig = $twig->getTwig();
    }

    /**
     * Get {Feature}Controller instance
     */
    public function get{Feature}Controller(): {Feature}Controller
    {
        return new {Feature}Controller(
            $this->globalsConfig,
            new {Feature}Service($this->globalsConfig),
            $this->twig
        );
    }
}
```

### External Config Mode in Admin UI

When env config mode is enabled, the global settings section displays an informational message instead of editable fields:

```php
// In addGlobalSettingsSection()
if ($this->globalsConfig->isExternalConfigMode()) {
    $setting = new GlobalSetting(
        xlt('Configuration Managed Externally'),
        GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION,
        '', '', false
    );
    $setting->addFieldOption(
        GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK,
        static fn() => xlt('This module is configured via environment variables.')
    );
    $service->appendToSection($section, '{vendor_prefix}_{modulename}_env_config_notice', $setting);
    return;
}
```

## Twig Template Pattern

Templates should use OpenEMR's translation and sanitization filters:

```twig
{# templates/{feature}/view.html.twig #}

{% extends "base.html.twig" %}

{% block content %}
<div class="container">
    <h1>{{ 'Page Title'|xlt }}</h1>

    {% if error_message %}
        <div class="alert alert-danger">
            {{ error_message|text }}
        </div>
    {% endif %}

    <form method="post" action="{{ action_url|attr }}">
        <input type="hidden" name="csrf_token" value="{{ csrf_token|attr }}">

        <div class="form-group">
            <label>{{ 'Field Label'|xlt }}</label>
            <input type="text" name="field_name" class="form-control">
        </div>

        <button type="submit" class="btn btn-primary">
            {{ 'Submit'|xlt }}
        </button>
    </form>
</div>
{% endblock %}
```

### Twig Filter Reference
- `xlt` - Translate text
- `text` - Sanitize text for HTML output
- `attr` - Sanitize for HTML attributes
- `xlj` - Translate and JSON-encode for JavaScript

## Development Workflow

Use the **Taskfile** for Docker and module operations. Install: `brew install go-task`

### Starting Development

```bash
task dev:start          # Start Docker environment
task module:install     # Install and enable module in OpenEMR
task dev:browse         # Open OpenEMR in browser
```

### Common Commands

```bash
task dev:port           # Show assigned ports
task dev:logs           # View OpenEMR logs
task dev:shell          # Shell into OpenEMR container
task dev:stop           # Stop Docker (keeps data)
task dev:reset          # Reset all data (fresh start)
```

### Module Management

```bash
task module:list        # List all modules
task module:disable     # Disable module
task module:reinstall   # Unregister and reinstall
```

### Code Quality

```bash
pre-commit run -a       # Run all checks
composer test           # Run PHPUnit tests
```

## CI Checks

GitHub Actions runs these checks on every PR.

### Conventional Commit Titles (IMPORTANT)

PR titles **must** follow conventional commits with **lowercase subject**:

```
type: lowercase description
```

**Correct:**
- `fix: resolve phpstan errors`
- `feat: add user authentication`
- `docs: update readme`

**Wrong (will fail CI):**
- `fix: Resolve PHPStan errors` ← uppercase
- `Fix: resolve errors` ← uppercase type
- `resolve phpstan errors` ← missing type

Valid types: `feat`, `fix`, `docs`, `style`, `refactor`, `perf`, `test`, `build`, `ci`, `chore`, `revert`, `deps`

### Composer Require Checker

CI verifies all symbols are declared as dependencies.

**When using new OpenEMR classes:** The `symbol-whitelist` in `.composer-require-checker.json` contains OpenEMR classes that are provided at runtime but can't be declared as composer dependencies. **Ask the user before adding new entries** - they may know a better solution.

**When using PHP extensions** (e.g., `ctype_digit`, `curl_*`), add to `composer.json`:

```json
{
  "require": {
    "ext-ctype": "*",
    "ext-curl": "*"
  }
}
```

## Code Quality Standards

All code must pass these checks:

```bash
pre-commit run -a
```

This runs:
- ✅ PHP Syntax Check
- ✅ PHP_CodeSniffer (PHPCS)
- ✅ PHPStan Static Analysis
- ✅ Rector
- ✅ Composer Require Checker

### CRITICAL: Handling Errors and Warnings

**NEVER ignore errors or warnings from any check.** Make every effort to fix them properly.

**Forbidden shortcuts (require explicit user approval):**
- Adding entries to `symbol-whitelist` in `.composer-require-checker.json`
- Adding entries to a PHPStan baseline file
- Using `@phpstan-ignore-*` annotations
- Using `// phpcs:ignore` comments
- Suppressing warnings with `@SuppressWarnings`

If you believe a suppression is genuinely necessary, **ask the user first** and explain why the error cannot be fixed properly. The user may know a better solution or may approve the exception.

**The right approach:**
1. Understand what the error is telling you
2. Fix the root cause (add missing types, fix logic, add dependencies)
3. If stuck, ask the user for guidance
4. Only suppress with explicit user approval and a comment explaining why

### Common Quality Issues to Avoid

**Line Length:**
- Maximum 120 characters per line
- Split long constructors across multiple lines

**Type Hints:**
- Add PHPDoc for array parameters: `@param array<string, mixed> $params`
- Use proper return types on all methods

**Unused Code:**
- Never suppress warnings with `@SuppressWarnings`
- If a parameter is unused, either use it or remove it
- Remove commented-out code

## Dependencies

Always include these in `composer.json`:

```json
{
  "require": {
    "php": ">=8.2",
    "symfony/event-dispatcher": "^6.0 || ^7.0",
    "symfony/http-foundation": "^6.0 || ^7.0",
    "twig/twig": "^3.0"
  }
}
```

## Composer Require Checker Configuration

The template includes a base `.composer-require-checker.json` with common OpenEMR symbols. **Do not add new entries without user approval** - see "Handling Errors and Warnings" above.

```json
{
  "symbol-whitelist": [
    "OpenEMR\\Common\\Csrf\\CsrfUtils",
    "OpenEMR\\Common\\Database\\QueryUtils",
    "OpenEMR\\Common\\Logging\\SystemLogger",
    "OpenEMR\\Core\\Kernel",
    "RuntimeException",
    "session_start",
    "session_status",
    "PHP_SESSION_NONE",
    "sqlStatement",
    "sqlQuery",
    "xlt",
    "text",
    "attr"
  ],
  "php-core-extensions": [
    "Core",
    "standard",
    "curl",
    "json",
    "session",
    "SPL"
  ]
}
```

## Security Checklist

- ✅ Always validate CSRF tokens on POST requests
- ✅ Require POST (or correct method) for write actions; only merge POST into params when method is POST
- ✅ Check user authentication and ACL before sensitive operations (e.g. `AclMain::aclCheckCore()`); throw UnauthorizedException or AccessDeniedException if denied
- ✅ Use `realpath()` and path validation to prevent directory traversal
- ✅ Sanitize all user input in templates (`text`, `attr` filters)
- ✅ Log security events (failed auth, path traversal attempts); pass user/sensitive data as structured context (e.g. `['name' => $name]`), not interpolated into the message, to avoid log injection and parsing issues
- ✅ Never expose detailed error messages to users
- ✅ Use explicit checks (e.g. `=== ''`, `=== null`) instead of `empty()` for string/ID validation so values like `"0"` are not rejected
- ✅ Do not redirect to user-supplied URLs; use a server-derived value (e.g. `$_SERVER['PHP_SELF']`) or an allowlist of allowed targets

## Summary - Quick Checklist

Before considering work complete:

- [ ] Public entry points are 25-35 lines max
- [ ] Controllers return Response objects (never void)
- [ ] No `header()`, `http_response_code()`, `die()`, or `exit` calls
- [ ] Custom exception hierarchy with interface and getStatusCode()
- [ ] Twig templates for all HTML (no inline HTML in PHP)
- [ ] CSRF validation on all POST requests
- [ ] Write actions require POST; entry point only merges POST params when method is POST
- [ ] ACL enforced in controller (or entry point) for sensitive actions; no redirect to user-supplied URLs
- [ ] All pre-commit checks passing
- [ ] PHPDoc comments with proper type hints
- [ ] Symfony HTTP Foundation components used throughout
