<?php

/**
 * Google Secret Manager configuration accessor
 *
 * @package   OpenCoreEMR
 * @link      https://opencoreemr.com
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\{ModuleName};

use Google\Cloud\SecretManager\V1\Client\SecretManagerServiceClient;
use Google\Cloud\SecretManager\V1\AccessSecretVersionRequest;
use OpenEMR\Core\Kernel;

/**
 * Reads module secrets from Google Cloud Secret Manager.
 *
 * This accessor is used in GKE deployments where secrets are stored in GSM
 * and accessed via Workload Identity. Non-secret configuration is delegated
 * to GlobalsAccessor (database-backed OpenEMR globals).
 *
 * Required environment variables:
 * - OCE_TENANT_GCP_PROJECT_ID: The GCP project containing the secrets
 * - OCE_TENANT_ID: The tenant slug used in secret naming
 *
 * Secret naming convention: {tenant_id}_{modulename}_{SECRET_NAME}
 * Example: cardinal-clinic_cardinal_ui_API_KEY
 */
class SecretManagerAccessor implements ConfigAccessorInterface
{
    /**
     * Maps internal config keys to GSM secret name suffixes.
     *
     * Add your module's secret keys here. Only keys listed here will be
     * fetched from GSM; all other config is delegated to GlobalsAccessor.
     *
     * Example:
     *   GlobalConfig::CONFIG_OPTION_API_KEY => 'API_KEY',
     *   GlobalConfig::CONFIG_OPTION_API_SECRET => 'API_SECRET',
     *
     * @var array<string, string>
     */
    private const SECRET_MAP = [
        // Add your secret mappings here:
        // GlobalConfig::CONFIG_OPTION_API_KEY => 'API_KEY',
    ];

    /**
     * The module name used in GSM secret naming.
     * This should match the module identifier in tfm-oce-tenant module_secrets.
     */
    private const MODULE_NAME = '{modulename}';

    /** @var array<string, string> */
    private array $secretCache = [];

    private ?SecretManagerServiceClient $client = null;

    public function __construct(
        private readonly GlobalsAccessor $globalsAccessor = new GlobalsAccessor()
    ) {
    }

    public function get(string $key, mixed $default = null): mixed
    {
        if ($default === null) {
            return $this->getString($key, '');
        }
        return $this->getString($key, is_string($default) ? $default : '');
    }

    public function getString(string $key, string $default = ''): string
    {
        if (isset(self::SECRET_MAP[$key])) {
            return $this->getSecret($key) ?? $default;
        }

        return $this->globalsAccessor->getString($key, $default);
    }

    public function getBoolean(string $key, bool $default = false): bool
    {
        return $this->globalsAccessor->getBoolean($key, $default);
    }

    public function getInt(string $key, int $default = 0): int
    {
        return $this->globalsAccessor->getInt($key, $default);
    }

    public function has(string $key): bool
    {
        if (isset(self::SECRET_MAP[$key])) {
            return $this->getSecret($key) !== null;
        }

        return $this->globalsAccessor->has($key);
    }

    public function getKernel(): ?Kernel
    {
        return $this->globalsAccessor->getKernel();
    }

    /**
     * Fetch a secret from Google Secret Manager
     */
    private function getSecret(string $configKey): ?string
    {
        if (isset($this->secretCache[$configKey])) {
            return $this->secretCache[$configKey];
        }

        $projectId = getenv('OCE_TENANT_GCP_PROJECT_ID');
        $tenantSlug = getenv('OCE_TENANT_ID');

        if ($projectId === false || $tenantSlug === false) {
            return null;
        }

        $secretSuffix = self::SECRET_MAP[$configKey] ?? null;
        if ($secretSuffix === null) {
            return null;
        }

        $secretName = sprintf(
            'projects/%s/secrets/%s_%s_%s/versions/latest',
            $projectId,
            $tenantSlug,
            self::MODULE_NAME,
            $secretSuffix
        );

        try {
            $client = $this->getClient();
            $request = new AccessSecretVersionRequest();
            $request->setName($secretName);

            $response = $client->accessSecretVersion($request);
            $payload = $response->getPayload();
            if ($payload === null) {
                return null;
            }
            $secretValue = $payload->getData();

            // Skip placeholder values from terraform initialization
            if ($secretValue === 'INITIALIZED') {
                return null;
            }

            $this->secretCache[$configKey] = $secretValue;
            return $secretValue;
        } catch (\Exception $e) {
            error_log("SecretManagerAccessor: Failed to fetch secret {$secretName}: " . $e->getMessage());
            return null;
        }
    }

    private function getClient(): SecretManagerServiceClient
    {
        if (!$this->client instanceof SecretManagerServiceClient) {
            $this->client = new SecretManagerServiceClient();
        }
        return $this->client;
    }
}
