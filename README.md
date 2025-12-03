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
git clone https://github.com/opencoreemr/oce-module-template.git oce-module-yourmodulename
cd oce-module-yourmodulename
rm -rf .git
git init
```

### 2. Update Module Metadata

Edit the following files to replace placeholders with your module details:

#### `composer.json`
- Replace `{yourmodulename}` with your module name (e.g., `sinch-fax`, `lab-integration`)
- Update `description`, `keywords`, and `authors`
- Update GitHub URLs in `support` section
- Update namespace in `autoload.psr-4` (e.g., `YourModuleName`)

#### `version.php`
- Update the header comment with your module name and description

#### `phpcs.xml`
- Update the `<ruleset name="">` to match your module

### 3. Install Dependencies

```bash
composer install
```

### 4. Set Up Pre-Commit Hooks

```bash
pip install pre-commit
pre-commit install
```

### 5. Create Your Module Structure

Follow the structure outlined in `CLAUDE.md`:

```
oce-module-yourname/
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

### 6. Read the Documentation

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

use OpenCoreEMR\Modules\{YourModule}\Bootstrap;

$kernel = $GLOBALS['kernel'];
$bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel);
$controller = $bootstrap->getYourController();

$action = $_GET['action'] ?? 'default';
$response = $controller->dispatch($action, $_REQUEST);
$response->send();
```

### Custom Exceptions with Status Codes

```php
interface YourModuleExceptionInterface extends \Throwable
{
    public function getStatusCode(): int;
}

class YourModuleNotFoundException extends YourModuleException
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
composer require opencoreemr/oce-module-yourmodulename
```

### Manual Installation

1. Copy to `interface/modules/custom_modules/oce-module-yourmodulename`
2. Navigate to **Administration > Modules > Manage Modules**
3. Click **Register**, then **Install**, then **Enable**

## Support

- Issues: https://github.com/opencoreemr/oce-module-{yourmodulename}/issues
- Email: support@opencoreemr.com

## License

GNU General Public License v3.0 or later

## Credits

Developed by OpenCoreEMR Inc
