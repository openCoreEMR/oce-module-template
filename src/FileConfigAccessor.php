<?php

/**
 * File-based configuration accessor (YAML config files)
 *
 * @package   OpenCoreEMR
 * @link      https://opencoreemr.com
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\{ModuleName};

use OpenEMR\Core\Kernel;
use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Read module configuration from YAML files, with env var overrides.
 *
 * Precedence: environment variables > YAML files > defaults.
 * OpenEMR system values (OE_SITE_DIR, webroot, etc.) delegate to GlobalsAccessor.
 *
 * @internal Use ConfigFactory::createConfigAccessor() instead of instantiating directly
 */
class FileConfigAccessor implements ConfigAccessorInterface
{
    /**
     * Map short YAML keys to internal config keys ({vendor_prefix}_{modulename}_*)
     *
     * Add your config option mappings here. Example:
     *   'api_key' => GlobalConfig::CONFIG_OPTION_API_KEY,
     *   'api_secret' => GlobalConfig::CONFIG_OPTION_API_SECRET,
     *
     * @var array<string, string>
     */
    private const KEY_MAP = [
        'enabled' => GlobalConfig::CONFIG_OPTION_ENABLED,
        // Add your config option mappings here
        // 'api_key' => GlobalConfig::CONFIG_OPTION_API_KEY,
        // 'api_secret' => GlobalConfig::CONFIG_OPTION_API_SECRET,
    ];

    /**
     * Map internal config keys to environment variable names for override support.
     * Same mapping as EnvironmentConfigAccessor::KEY_MAP.
     *
     * @var array<string, string>
     */
    private const ENV_OVERRIDE_MAP = [
        GlobalConfig::CONFIG_OPTION_ENABLED => '{VENDOR_PREFIX}_{MODULENAME}_ENABLED',
        // Add your env var override mappings here (must match EnvironmentConfigAccessor::KEY_MAP)
        // GlobalConfig::CONFIG_OPTION_API_KEY => '{VENDOR_PREFIX}_{MODULENAME}_API_KEY',
        // GlobalConfig::CONFIG_OPTION_API_SECRET => '{VENDOR_PREFIX}_{MODULENAME}_API_SECRET',
    ];

    /**
     * Reverse map: internal config key => short YAML key
     *
     * @var array<string, string>
     */
    private const REVERSE_KEY_MAP = [
        GlobalConfig::CONFIG_OPTION_ENABLED => 'enabled',
        // Add reverse mappings for each KEY_MAP entry
        // GlobalConfig::CONFIG_OPTION_API_KEY => 'api_key',
        // GlobalConfig::CONFIG_OPTION_API_SECRET => 'api_secret',
    ];

    /** @var ParameterBag<string, mixed> */
    private readonly ParameterBag $bag;
    private readonly GlobalsAccessor $globalsAccessor;

    /**
     * @param array<string, mixed> $yamlData merged data from YamlConfigLoader::load()
     */
    public function __construct(array $yamlData)
    {
        $this->globalsAccessor = new GlobalsAccessor();
        $this->bag = $this->buildBag($yamlData);
    }

    /**
     * Build a ParameterBag from YAML data with env var overrides
     *
     * Start with YAML values (mapped to internal keys), then override with
     * any set environment variables.
     *
     * @param array<string, mixed> $yamlData
     * @return ParameterBag<string, mixed>
     */
    private function buildBag(array $yamlData): ParameterBag
    {
        $params = [];

        // Map short YAML keys to internal config keys
        foreach (self::KEY_MAP as $yamlKey => $configKey) {
            if (array_key_exists($yamlKey, $yamlData)) {
                $params[$configKey] = $yamlData[$yamlKey];
            }
        }

        // Override with environment variables where set
        foreach (self::ENV_OVERRIDE_MAP as $configKey => $envVar) {
            $value = getenv($envVar);
            if ($value !== false) {
                $params[$configKey] = $value;
            }
        }

        return new ParameterBag($params);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if (isset(self::REVERSE_KEY_MAP[$key])) {
            return $this->bag->get($key, $default);
        }

        return $this->globalsAccessor->get($key, $default);
    }

    public function getString(string $key, string $default = ''): string
    {
        if (isset(self::REVERSE_KEY_MAP[$key])) {
            return $this->bag->getString($key, $default);
        }

        return $this->globalsAccessor->getString($key, $default);
    }

    public function getBoolean(string $key, bool $default = false): bool
    {
        if (isset(self::REVERSE_KEY_MAP[$key])) {
            return $this->bag->getBoolean($key, $default);
        }

        return $this->globalsAccessor->getBoolean($key, $default);
    }

    public function getInt(string $key, int $default = 0): int
    {
        if (isset(self::REVERSE_KEY_MAP[$key])) {
            return $this->bag->getInt($key, $default);
        }

        return $this->globalsAccessor->getInt($key, $default);
    }

    public function has(string $key): bool
    {
        if (isset(self::REVERSE_KEY_MAP[$key])) {
            return $this->bag->has($key);
        }

        return $this->globalsAccessor->has($key);
    }

    /**
     * Get the OpenEMR Kernel instance
     *
     * Delegates to GlobalsAccessor since Kernel is always from OpenEMR globals.
     */
    public function getKernel(): ?Kernel
    {
        return $this->globalsAccessor->getKernel();
    }
}
