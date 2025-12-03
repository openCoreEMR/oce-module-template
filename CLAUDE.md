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
├── public/
│   ├── index.php          # Main entry point (25-35 lines)
│   ├── {feature}.php      # Feature entry points (25-35 lines)
│   └── assets/            # Static assets (CSS, JS, images)
├── src/
│   ├── Bootstrap.php      # Module initialization and DI
│   ├── GlobalsAccessor.php # Globals access wrapper
│   ├── Command/           # Console commands (removed after setup)
│   │   └── SetupCommand.php
│   ├── Controller/        # Request handlers
│   │   ├── {Feature}Controller.php
│   │   └── ...
│   ├── Service/           # Business logic
│   │   ├── {Feature}Service.php
│   │   └── ...
│   ├── Exception/         # Custom exception types
│   │   ├── {ModuleName}ExceptionInterface.php
│   │   ├── {ModuleName}Exception.php
│   │   └── {Specific}Exception.php
│   └── GlobalConfig.php   # Configuration wrapper (you create this)
├── templates/
│   └── {feature}/
│       ├── {view}.html.twig
│       └── partials/
│           └── _{component}.html.twig
├── composer.json
└── openemr.bootstrap.php  # Module loader
```

## Public Entry Point Pattern

Public PHP files should be short! Just dispatch a controller and send a response. Follow this pattern:

```php
<?php
/**
 * [Description of endpoint]
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    [Author Name] <email@example.com>
 * @copyright Copyright (c) 2025 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

require_once __DIR__ . '/../../../../globals.php';

use {VendorName}\Modules\{ModuleName}\Bootstrap;

// Get kernel and bootstrap module
$kernel = $GLOBALS['kernel'];
$bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel);

// Get controller
$controller = $bootstrap->get{Feature}Controller();

// Determine action
$action = $_GET['action'] ?? $_POST['action'] ?? 'default';

// Dispatch to controller and send response
$response = $controller->dispatch($action, $_REQUEST);
$response->send();
```

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

        // Validate input
        if (empty($params['required_field'])) {
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

The `Bootstrap.php` class should provide factory methods for controllers:

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
        private readonly GlobalsAccessor $globals = new GlobalsAccessor()
    ) {
        $this->globalsConfig = new GlobalConfig($this->globals);

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

Update `.composer-require-checker.json` to whitelist OpenEMR symbols:

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
- ✅ Check user authentication before sensitive operations
- ✅ Use `realpath()` and path validation to prevent directory traversal
- ✅ Sanitize all user input in templates (`text`, `attr` filters)
- ✅ Log security events (failed auth, path traversal attempts)
- ✅ Never expose detailed error messages to users

## Summary - Quick Checklist

Before considering work complete:

- [ ] Public entry points are 25-35 lines max
- [ ] Controllers return Response objects (never void)
- [ ] No `header()`, `http_response_code()`, `die()`, or `exit` calls
- [ ] Custom exception hierarchy with interface and getStatusCode()
- [ ] Twig templates for all HTML (no inline HTML in PHP)
- [ ] CSRF validation on all POST requests
- [ ] All pre-commit checks passing
- [ ] PHPDoc comments with proper type hints
- [ ] Symfony HTTP Foundation components used throughout
