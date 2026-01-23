<?php

/**
 * Bootstrap class for initializing the OpenEMR module
 *
 * This class handles:
 * - Dependency injection setup
 * - Event subscription
 * - Global settings registration
 * - Menu items
 * - Controller factory methods
 *
 * @package   {VendorName}
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@example.com>
 * @copyright Copyright (c) 2026 {VendorName}
 * @license   GNU General Public License 3
 */

namespace {VendorName}\Modules\{ModuleName};

use {VendorName}\Modules\{ModuleName}\Controller\ExampleController;
use OpenEMR\Common\Logging\SystemLogger;
use OpenEMR\Common\Twig\TwigContainer;
use OpenEMR\Core\Kernel;
use OpenEMR\Events\Globals\GlobalsInitializedEvent;
use OpenEMR\Menu\MenuEvent;
use OpenEMR\Services\Globals\GlobalSetting;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class Bootstrap
{
    public const MODULE_NAME = "{vendor-prefix}-module-{modulename}";

    private readonly GlobalConfig $globalsConfig;
    private readonly \Twig\Environment $twig;
    private readonly SystemLogger $logger;

    public function __construct(
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Kernel $kernel = new Kernel(),
        ?ConfigAccessorInterface $configAccessor = null
    ) {
        $configAccessor ??= ConfigFactory::createConfigAccessor();
        $this->globalsConfig = new GlobalConfig($configAccessor);

        $templatePath = \dirname(__DIR__) . DIRECTORY_SEPARATOR . "templates" . DIRECTORY_SEPARATOR;
        $twig = new TwigContainer($templatePath, $this->kernel);
        $this->twig = $twig->getTwig();

        $this->logger = new SystemLogger();
        $this->logger->debug('Module Bootstrap constructed');
    }

    /**
     * Subscribe to OpenEMR events
     *
     * This is called by openemr.bootstrap.php when the module is loaded.
     * Register all event listeners here.
     */
    public function subscribeToEvents(): void
    {
        $this->addGlobalSettings();
        $this->addMenuItems();

        // Only proceed with additional subscriptions if module is configured and enabled
        if (!$this->globalsConfig->isConfigured()) {
            $this->logger->debug('Module is not configured. Skipping additional event subscriptions.');
            return;
        }

        if (!$this->globalsConfig->isEnabled()) {
            $this->logger->debug('Module is disabled. Skipping additional event subscriptions.');
            return;
        }

        $this->logger->debug('Module is enabled and configured');

        // Add additional event listeners here
        // Example: $this->eventDispatcher->addListener(SomeEvent::class, $this->handleSomeEvent(...));
    }

    /**
     * Register global settings for the module
     */
    public function addGlobalSettings(): void
    {
        $this->eventDispatcher->addListener(
            GlobalsInitializedEvent::EVENT_HANDLE,
            $this->addGlobalSettingsSection(...)
        );
    }

    /**
     * Add global settings section to OpenEMR administration
     */
    public function addGlobalSettingsSection(GlobalsInitializedEvent $event): void
    {
        $service = $event->getGlobalsService();
        $section = xlt("Your Module Name");
        $service->createSection($section, 'Module');

        // In env config mode, show informational message instead of editable fields
        if ($this->globalsConfig->isEnvConfigMode()) {
            $setting = new GlobalSetting(
                xlt('Configuration Managed Externally'),
                GlobalSetting::DATA_TYPE_HTML_DISPLAY_SECTION,
                '',
                '',
                false
            );
            $setting->addFieldOption(
                GlobalSetting::DATA_TYPE_OPTION_RENDER_CALLBACK,
                static fn() => xlt(
                    'This module is configured via environment variables by deployment administrators.'
                )
            );
            $service->appendToSection($section, '{vendor_prefix}_{modulename}_env_config_notice', $setting);
            return;
        }

        $settings = $this->globalsConfig->getGlobalSettingSectionConfiguration();

        foreach ($settings as $key => $config) {
            $service->appendToSection(
                $section,
                $key,
                new GlobalSetting(
                    xlt($config['title']),
                    $config['type'],
                    $config['default'],
                    xlt($config['description']),
                    true
                )
            );
        }
    }

    /**
     * Register menu items for the module
     */
    public function addMenuItems(): void
    {
        $this->eventDispatcher->addListener(
            MenuEvent::MENU_UPDATE,
            $this->addModuleMenuItem(...)
        );
    }

    /**
     * Add module menu item to OpenEMR menu
     */
    public function addModuleMenuItem(MenuEvent $event): void
    {
        if (!$this->globalsConfig->isEnabled()) {
            return;
        }

        $menu = $event->getMenu();

        $menuItem = new \stdClass();
        $menuItem->requirement = 0;
        $menuItem->target = 'yourmodule';
        $menuItem->menu_id = 'yourmodule';
        $menuItem->label = xlt('Your Module Name');
        $menuItem->url = '/interface/modules/custom_modules/' . self::MODULE_NAME . '/public/index.php';
        $menuItem->icon = 'fa-star'; // Change to appropriate FontAwesome icon
        $menuItem->children = [];
        $menuItem->acl_req = ["patients", "demo"]; // Adjust ACL requirements as needed

        foreach ($menu as $item) {
            if ($item->menu_id == 'modimg') {
                $item->children[] = $menuItem;
                break;
            }
        }
    }

    /**
     * Get ExampleController instance
     *
     * Factory method for creating the controller with all dependencies.
     */
    public function getExampleController(): ExampleController
    {
        return new ExampleController(
            $this->globalsConfig,
            $this->twig
        );
    }

    /**
     * Factory method template for your controllers
     *
     * Add factory methods here to create controller instances with proper dependencies.
     * Example:
     *
     * public function getYourFeatureController(): YourFeatureController
     * {
     *     return new YourFeatureController(
     *         $this->globalsConfig,
     *         new YourFeatureService($this->globalsConfig),
     *         $this->twig
     *     );
     * }
     */
}
