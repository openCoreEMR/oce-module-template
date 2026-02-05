<?php

/**
 * Manages the configuration options for the module.
 *
 * This class provides a centralized location for all configuration access,
 * with support for both database-backed and environment variable configuration.
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\{ModuleName};

use OpenEMR\Services\Globals\GlobalSetting;

class GlobalConfig
{
    private readonly bool $isEnvConfigMode;

    public function __construct(
        private readonly ConfigAccessorInterface $configAccessor = new GlobalsAccessor()
    ) {
        $this->isEnvConfigMode = $configAccessor instanceof EnvironmentConfigAccessor;
    }

    /**
     * Configuration option constants.
     * Define your module's configuration keys here.
     */
    public const CONFIG_OPTION_ENABLED = '{vendor_prefix}_{modulename}_enabled';

    // Add your configuration option constants here, for example:
    // public const CONFIG_OPTION_API_KEY = '{vendor_prefix}_{modulename}_api_key';
    // public const CONFIG_OPTION_API_SECRET = '{vendor_prefix}_{modulename}_api_secret';

    /**
     * Check if configuration is managed via environment variables
     */
    public function isEnvConfigMode(): bool
    {
        return $this->isEnvConfigMode;
    }

    /**
     * Check if the module is enabled
     */
    public function isEnabled(): bool
    {
        return $this->configAccessor->getBoolean(self::CONFIG_OPTION_ENABLED, false);
    }

    /**
     * Check if the module is properly configured
     *
     * Override this method to add your module's configuration validation.
     * Return true if all required configuration is present and valid.
     */
    public function isConfigured(): bool
    {
        // Add your configuration validation logic here
        // Example:
        // return !empty($this->getApiKey()) && !empty($this->getApiSecret());
        return true;
    }

    // Add your configuration getter methods here, for example:
    //
    // public function getApiKey(): string
    // {
    //     return $this->configAccessor->getString(self::CONFIG_OPTION_API_KEY, '');
    // }
    //
    // /**
    //  * Get API secret with decryption support for database mode
    //  */
    // public function getApiSecret(): string
    // {
    //     $value = $this->configAccessor->getString(self::CONFIG_OPTION_API_SECRET, '');
    //     if ($value !== '' && $value !== '0') {
    //         // In env config mode, secrets are stored as plaintext (no encryption)
    //         if ($this->isEnvConfigMode) {
    //             return $value;
    //         }
    //         // In database mode, decrypt the value
    //         $cryptoGen = new \OpenEMR\Common\Crypto\CryptoGen();
    //         $decrypted = $cryptoGen->decryptStandard($value);
    //         return $decrypted !== false ? $decrypted : '';
    //     }
    //     return '';
    // }

    /**
     * Get OpenEMR webroot path
     */
    public function getWebroot(): string
    {
        return $this->configAccessor->getString('webroot', '');
    }

    /**
     * Get assets static relative path
     */
    public function getAssetsStaticRelative(): string
    {
        return $this->configAccessor->getString('assets_static_relative', '');
    }

    /**
     * Get the global settings section configuration for the admin UI
     *
     * This method returns an array of configuration options that will be
     * displayed in the OpenEMR admin settings page.
     *
     * @return array<string, array<string, string|bool|int|array<string, string>>>
     */
    public function getGlobalSettingSectionConfiguration(): array
    {
        return [
            self::CONFIG_OPTION_ENABLED => [
                'title' => 'Enable Module',
                'description' => 'Enable this module',
                'type' => GlobalSetting::DATA_TYPE_BOOL,
                'default' => false
            ],
            // Add your configuration options here, for example:
            //
            // self::CONFIG_OPTION_API_KEY => [
            //     'title' => 'API Key',
            //     'description' => 'Your API key',
            //     'type' => GlobalSetting::DATA_TYPE_TEXT,
            //     'default' => ''
            // ],
            // self::CONFIG_OPTION_API_SECRET => [
            //     'title' => 'API Secret',
            //     'description' => 'Your API secret (stored encrypted)',
            //     'type' => GlobalSetting::DATA_TYPE_ENCRYPTED,
            //     'default' => ''
            // ],
        ];
    }
}
