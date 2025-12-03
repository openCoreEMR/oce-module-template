# Getting Started with Your OpenEMR Module

This quick start guide will help you create a new OpenEMR module from this template.

## Step 1: Copy the Template

```bash
git clone https://github.com/opencoreemr/oce-module-template.git oce-module-yourname
cd oce-module-yourname
rm -rf .git
git init
git add .
git commit -m "Initial commit from template"
```

## Step 2: Search and Replace Placeholders

Use your text editor's find-and-replace feature to replace these placeholders throughout the project:

| Find | Replace With | Example |
|------|--------------|---------|
| `{yourmodulename}` | Your module name (lowercase, hyphenated) | `lab-integration` |
| `YourModuleName` | Your module name (PascalCase) | `LabIntegration` |
| `Your Module` | Your module display name | `Lab Integration` |
| `Your Name` | Your actual name | `Jane Smith` |
| `your.email@opencoreemr.com` | Your email | `jane@opencoreemr.com` |

Files to update:
- `composer.json` - Replace package name, description, keywords, author
- `version.php` - Update header comment and author
- `phpcs.xml` - Update ruleset name
- `openemr.bootstrap.php` - Replace `YourModuleName` namespace
- `README.md` - Update module-specific content
- `DEVELOPMENT.md` - Update module-specific content

## Step 3: Install Dependencies

```bash
composer install
```

## Step 4: Set Up Pre-Commit Hooks

```bash
pip install pre-commit
pre-commit install
```

## Step 5: Create Your Module Structure

### Create Bootstrap Class

Create `src/Bootstrap.php`:

```php
<?php

namespace OpenCoreEMR\Modules\YourModuleName;

use OpenEMR\Core\Kernel;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    public const MODULE_NAME = "oce-module-yourmodulename";

    private readonly GlobalConfig $globalsConfig;
    private readonly \Twig\Environment $twig;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Kernel $kernel = new Kernel()
    ) {
        $this->globalsConfig = new GlobalConfig();

        $templatePath = \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
        $twig = new TwigContainer($templatePath, $this->kernel);
        $this->twig = $twig->getTwig();
    }

    // Add factory methods for your controllers here
}
```

### Create GlobalConfig

Create `src/GlobalConfig.php` - see `DEVELOPMENT.md` for examples.

### Create Exception Hierarchy

Create:
- `src/Exception/YourModuleExceptionInterface.php`
- `src/Exception/YourModuleException.php`
- `src/Exception/YourModuleNotFoundException.php`
- `src/Exception/YourModuleAccessDeniedException.php`
- `src/Exception/YourModuleValidationException.php`

### Create Your First Controller

Create `src/Controller/YourFeatureController.php` - see `DEVELOPMENT.md` for examples.

### Create Your First Service

Create `src/Service/YourFeatureService.php` - see `DEVELOPMENT.md` for examples.

### Create Public Entry Point

Create `public/index.php` - see `DEVELOPMENT.md` for the minimal 25-35 line pattern.

### Create Twig Templates

Create `templates/feature/list.html.twig` - see `DEVELOPMENT.md` for examples.

## Step 6: Create Module Registration File

Create `openemr.bootstrap.php` in the root directory - see `DEVELOPMENT.md` for examples.

## Step 7: Test Your Module

```bash
# Run code quality checks
composer code-quality

# Copy to OpenEMR for testing
cp -r . /path/to/openemr/interface/modules/custom_modules/oce-module-yourname/
```

Then in OpenEMR:
1. Navigate to **Administration > Modules > Manage Modules**
2. Click **Register**
3. Click **Install**
4. Click **Enable**

## Step 8: Version Control

```bash
git add .
git commit -m "Initial module structure"
```

## Need Help?

- Read `CLAUDE.md` for comprehensive architectural patterns
- Read `DEVELOPMENT.md` for detailed development workflow
- Check existing modules for examples
- Ask on OpenEMR forums or create an issue

## Checklist

- [ ] Replaced all placeholders in files
  - [ ] `composer.json`
  - [ ] `version.php`
  - [ ] `phpcs.xml`
  - [ ] `openemr.bootstrap.php`
  - [ ] `README.md`
  - [ ] `DEVELOPMENT.md`
- [ ] Created Bootstrap class
- [ ] Created GlobalConfig class
- [ ] Created exception hierarchy
- [ ] Created at least one controller
- [ ] Created at least one service
- [ ] Created public entry point
- [ ] Created Twig templates
- [ ] All pre-commit checks pass (except composer-require-checker until you have code)
- [ ] Module registers and enables in OpenEMR
- [ ] Deleted `.gitkeep` files
- [ ] Deleted this `GETTING_STARTED.md` file (once you're done)

Happy coding!
