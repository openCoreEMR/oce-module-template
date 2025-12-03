# OpenEMR Module Template

This is a template repository for creating OpenEMR modules using OpenCoreEMR's architectural patterns and best practices.

## What This Template Provides

- **Symfony-inspired MVC architecture** with Controllers, Services, and Twig templates
- **Pre-configured code quality tools** (PHPCS, PHPStan, Rector)
- **Pre-commit hooks** for automated code quality checks
- **Comprehensive documentation** for AI agents (see `CLAUDE.md`)
- **Modern PHP 8.2+** with strict typing and best practices
- **Proper dependency management** with Composer

## Getting Started

### 1. Create Your Module from This Template

Click "Use this template" on GitHub or clone this repository:

```bash
git clone https://github.com/opencoreemr/oce-module-template.git {vendor-prefix}-module-{modulename}
cd {vendor-prefix}-module-{modulename}
rm -rf .git
git init
```

### 2. Install Dependencies

```bash
composer install
```

### 3. Run the Setup Wizard (Recommended)

The automated setup wizard will configure your module:

```bash
./bin/setup
```

This interactive wizard will:
- Ask if this is for internal OpenCoreEMR use (oce- prefix) or external/community use (oe- prefix)
- Prompt for your module name
- Ask for vendor name (if external use)
- Replace all placeholders throughout the codebase
- Optionally remove setup files when complete

**For OpenCoreEMR Internal Use:**
- Namespace: `OpenCoreEMR\Modules\{ModuleName}`
- Package: `opencoreemr/oce-module-{modulename}`

**For External/Community Use:**
- Namespace: `YourVendor\Modules\{ModuleName}` (or `OpenEMR\Modules\{ModuleName}`)
- Package: `yourvendor/oe-module-{modulename}`

### 4. Manual Setup (Alternative)

If you prefer manual setup, edit these files to replace placeholders:

#### `composer.json`
- Replace `{VendorName}` with your vendor name (e.g., `OpenCoreEMR` or `YourOrg`)
- Replace `{vendor-prefix}` with `oce` (internal) or `oe` (external)
- Replace `{modulename}` with your module name (e.g., `lab-integration`)
- Replace `{ModuleName}` with PascalCase version (e.g., `LabIntegration`)
- Update `description`, `keywords`, and `authors`

#### Other files with placeholders:
- `version.php` - Update header comments
- `phpcs.xml` - Update ruleset name
- `src/` files - Update namespaces
- `.composer-require-checker.json` - Update symbol whitelist

### 5. Set Up Pre-Commit Hooks

```bash
pip install pre-commit
pre-commit install
```

### 6. Create Your Module Structure

Follow the structure outlined in `CLAUDE.md`:

```
{vendor-prefix}-module-{modulename}/
├── public/
│   └── index.php          # Main entry point
├── src/
│   ├── Bootstrap.php      # Module initialization
│   ├── GlobalConfig.php   # Configuration management
│   ├── Controller/        # Request handlers
│   ├── Service/           # Business logic
│   └── Exception/         # Custom exceptions
├── templates/             # Twig templates
├── table.sql             # Database schema (optional)
└── openemr.bootstrap.php # Module loader
```

### 7. Read the Documentation

- **`CLAUDE.md`** - Comprehensive architectural patterns and conventions for AI agents
- **`DEVELOPMENT.md`** - Development workflow and guidelines (update this for your module)

## Key Architectural Principles

### Controllers Return Response Objects

```php
public function dispatch(string $action, array $params): Response
{
    return match ($action) {
        'create' => $this->handleCreate($params),
        'list' => $this->showList($params),
        default => $this->showList($params),
    };
}
```

### Public Files Are Minimal

```php
<?php
require_once __DIR__ . '/../../../../globals.php';

use {VendorName}\Modules\{ModuleName}\Bootstrap;

$kernel = $GLOBALS['kernel'];
$bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel);
$controller = $bootstrap->getYourController();

$action = $_GET['action'] ?? 'default';
$response = $controller->dispatch($action, $_REQUEST);
$response->send();
```

### Custom Exceptions with Status Codes

```php
interface {ModuleName}ExceptionInterface extends \Throwable
{
    public function getStatusCode(): int;
}

class {ModuleName}NotFoundException extends {ModuleName}Exception
{
    public function getStatusCode(): int
    {
        return 404;
    }
}
```

## Code Quality Standards

All code must pass these checks before committing:

```bash
pre-commit run -a
```

Or run individual checks:

```bash
composer phpcs      # Code style
composer phpstan    # Static analysis
composer rector     # PHP compatibility
```

**Note:** The `composer-require-checker` hook will fail on the template until you add actual PHP code to `src/` and `public/`. This is expected behavior for an empty template.

## Installation in OpenEMR

### Via Composer (Recommended)

```bash
cd /path/to/openemr
composer require {vendorname}/{vendor-prefix}-module-{modulename}
```

### Manual Installation

1. Copy to `interface/modules/custom_modules/{vendor-prefix}-module-{modulename}`
2. Navigate to **Administration > Modules > Manage Modules**
3. Click **Register**, then **Install**, then **Enable**

## Support

- Issues: https://github.com/{vendorname}/{vendor-prefix}-module-{modulename}/issues
- Email: support@opencoreemr.com

## License

GNU General Public License v3.0 or later

## Credits

Developed by OpenCoreEMR Inc
