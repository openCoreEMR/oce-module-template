<?php

/**
 * Environment-based configuration accessor
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace {VendorName}\Modules\{ModuleName};

use Symfony\Component\HttpFoundation\ParameterBag;

/**
 * Reads module configuration from environment variables.
 *
 * This accessor is used when {VENDOR_PREFIX}_{MODULE_NAME}_ENV_CONFIG=1 is set,
 * bypassing the database-backed globals system entirely for module config.
 * OpenEMR system values (OE_SITE_DIR, webroot, etc.) are still delegated
 * to GlobalsAccessor since they are not module configuration.
 *
 * @internal Use ConfigFactory::createConfigAccessor() instead of instantiating directly
 */
class EnvironmentConfigAccessor implements ConfigAccessorInterface
{
    /**
     * Maps internal config keys ({vendor_prefix}_{modulename}_*) to env var names ({VENDOR_PREFIX}_{MODULENAME}_*)
     *
     * Override this in your implementation to map your specific config keys.
     * Example:
     *   GlobalConfig::CONFIG_OPTION_ENABLED => '{VENDOR_PREFIX}_{MODULENAME}_ENABLED',
     *   GlobalConfig::CONFIG_OPTION_API_KEY => '{VENDOR_PREFIX}_{MODULENAME}_API_KEY',
     *
     * @var array<string, string>
     */
    private const KEY_MAP = [
        GlobalConfig::CONFIG_OPTION_ENABLED => '{VENDOR_PREFIX}_{MODULENAME}_ENABLED',
        // Add your config option mappings here
    ];

    /** @var ParameterBag<string, mixed> */
    private readonly ParameterBag $envBag;
    private readonly GlobalsAccessor $globalsAccessor;

    public function __construct()
    {
        $this->globalsAccessor = new GlobalsAccessor();
        $this->envBag = $this->buildEnvBag();
    }

    /**
     * Build a ParameterBag from environment variables
     *
     * @return ParameterBag<string, mixed>
     */
    private function buildEnvBag(): ParameterBag
    {
        $params = [];
        foreach (self::KEY_MAP as $configKey => $envVar) {
            $value = getenv($envVar);
            if ($value !== false) {
                $params[$configKey] = $value;
            }
        }
        return new ParameterBag($params);
    }

    public function get(string $key, mixed $default = null): mixed
    {
        // Check if this is a module config key
        if (isset(self::KEY_MAP[$key])) {
            return $this->envBag->get($key, $default);
        }

        // For OpenEMR system values, delegate to GlobalsAccessor
        return $this->globalsAccessor->get($key, $default);
    }

    public function getString(string $key, string $default = ''): string
    {
        if (isset(self::KEY_MAP[$key])) {
            return $this->envBag->getString($key, $default);
        }

        return $this->globalsAccessor->getString($key, $default);
    }

    public function getBoolean(string $key, bool $default = false): bool
    {
        if (isset(self::KEY_MAP[$key])) {
            return $this->envBag->getBoolean($key, $default);
        }

        return $this->globalsAccessor->getBoolean($key, $default);
    }

    public function getInt(string $key, int $default = 0): int
    {
        if (isset(self::KEY_MAP[$key])) {
            return $this->envBag->getInt($key, $default);
        }

        return $this->globalsAccessor->getInt($key, $default);
    }

    public function has(string $key): bool
    {
        if (isset(self::KEY_MAP[$key])) {
            return $this->envBag->has($key);
        }

        return $this->globalsAccessor->has($key);
    }
}
