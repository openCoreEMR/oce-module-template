<?php

/**
 * Factory for creating configuration accessors
 *
 * @package   OpenCoreEMR
 * @link      https://opencoreemr.com
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\{ModuleName};

/**
 * Factory for creating the appropriate configuration accessor.
 *
 * Supports four modes (checked in order of precedence):
 * 1. GSM config: OCE_TENANT_GCP_PROJECT_ID is set (GKE with Secret Manager)
 * 2. File config: YAML files at conventional or overridden paths
 * 3. Env config: {VENDOR_PREFIX}_{MODULENAME}_ENV_CONFIG=1
 * 4. Database globals (default)
 *
 * This pattern allows modules to be configured via Google Secret Manager (GKE),
 * YAML files (Kubernetes), environment variables (containers), or database
 * (traditional OpenEMR).
 */
class ConfigFactory
{
    /**
     * Environment variable indicating GKE deployment with GSM secrets.
     * When set, secrets are fetched from Google Secret Manager.
     */
    public const GSM_PROJECT_VAR = 'OCE_TENANT_GCP_PROJECT_ID';

    /**
     * Environment variable that toggles environment-based configuration.
     * Set to "1" or "true" to enable environment variable configuration mode.
     */
    public const ENV_CONFIG_VAR = '{VENDOR_PREFIX}_{MODULENAME}_ENV_CONFIG';

    /**
     * Environment variable to override the config file path.
     * When set, this path is checked instead of the conventional path.
     */
    public const CONFIG_FILE_ENV_VAR = '{VENDOR_PREFIX}_{MODULENAME}_CONFIG_FILE';

    /**
     * Environment variable to override the secrets file path.
     * When set, this path is checked instead of the conventional path.
     */
    public const SECRETS_FILE_ENV_VAR = '{VENDOR_PREFIX}_{MODULENAME}_SECRETS_FILE';

    /**
     * Conventional path for the config file (mounted via ConfigMap in K8s).
     * Update to match your module's name: /etc/oce/{modulename}/config.yaml
     */
    public const CONVENTIONAL_CONFIG_PATH = '/etc/oce/{modulename}/config.yaml';

    /**
     * Conventional path for the secrets file (mounted via Secret in K8s).
     * Update to match your module's name: /etc/oce/{modulename}/secrets.yaml
     */
    public const CONVENTIONAL_SECRETS_PATH = '/etc/oce/{modulename}/secrets.yaml';

    /**
     * Check if environment-only config mode is enabled
     */
    public static function isEnvConfigMode(): bool
    {
        $value = getenv(self::ENV_CONFIG_VAR);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Check if file-based config mode is active
     *
     * File config mode is active when any config files exist at
     * conventional or overridden paths.
     */
    public static function isFileConfigMode(): bool
    {
        $loader = new YamlConfigLoader();
        return $loader->hasConfigFiles(self::getConfigFileCandidates());
    }

    /**
     * Check if Google Secret Manager mode is enabled (GKE deployment)
     */
    public static function isSecretManagerMode(): bool
    {
        return getenv(self::GSM_PROJECT_VAR) !== false;
    }

    /**
     * Check if any external config mode is active (GSM, file, or env)
     */
    public static function isExternalConfigMode(): bool
    {
        return self::isSecretManagerMode() || self::isFileConfigMode() || self::isEnvConfigMode();
    }

    /**
     * Create the appropriate config accessor based on environment
     *
     * Precedence: GSM > file config > env config > database globals
     */
    public static function createConfigAccessor(): ConfigAccessorInterface
    {
        if (self::isSecretManagerMode()) {
            return new SecretManagerAccessor();
        }

        if (self::isFileConfigMode()) {
            $loader = new YamlConfigLoader();
            $paths = $loader->resolveFilePaths(self::getConfigFileCandidates());
            $yamlData = $loader->load($paths);
            return new FileConfigAccessor($yamlData);
        }

        if (self::isEnvConfigMode()) {
            return new EnvironmentConfigAccessor();
        }

        return new GlobalsAccessor();
    }

    /**
     * Get candidate config file paths (overridden + conventional)
     *
     * @return list<string>
     */
    private static function getConfigFileCandidates(): array
    {
        $candidates = [];

        $configOverride = getenv(self::CONFIG_FILE_ENV_VAR);
        if ($configOverride !== false && $configOverride !== '') {
            $candidates[] = $configOverride;
        }

        $secretsOverride = getenv(self::SECRETS_FILE_ENV_VAR);
        if ($secretsOverride !== false && $secretsOverride !== '') {
            $candidates[] = $secretsOverride;
        }

        $candidates[] = self::CONVENTIONAL_CONFIG_PATH;
        $candidates[] = self::CONVENTIONAL_SECRETS_PATH;

        return $candidates;
    }
}
