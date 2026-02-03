<?php

/**
 * Authentication adapter for OAuth/Session compatibility
 *
 * This adapter provides a unified authentication interface that works with:
 * - OAuth 2.0 (when oce-module-auth is installed)
 * - Legacy PHP sessions (fallback when OAuth is unavailable)
 *
 * Modules should use this adapter instead of checking $_SESSION directly,
 * enabling gradual migration to OAuth without breaking existing functionality.
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\{ModuleName};

use OpenEMR\Common\Logging\SystemLogger;

/**
 * Unified authentication adapter supporting OAuth 2.0 and legacy sessions.
 *
 * Usage:
 *   $auth = new AuthAdapter();
 *   $auth->requireAuth();  // Redirects if not authenticated
 *   $user = $auth->getCurrentUser();
 *
 * The adapter automatically detects whether OAuth (oce-module-auth) is available
 * and uses the appropriate authentication mechanism.
 */
class AuthAdapter
{
    private readonly SystemLogger $logger;
    private readonly GlobalsAccessor $globals;

    /**
     * Cached authentication state
     *
     * @var array<string, mixed>|null
     */
    private ?array $currentUser = null;

    public function __construct(?GlobalsAccessor $globals = null)
    {
        $this->globals = $globals ?? new GlobalsAccessor();
        $this->logger = new SystemLogger();
    }

    /**
     * Check if OAuth module (oce-module-auth) is available
     *
     * This checks if the auth module's SessionBridge class exists,
     * indicating the OAuth infrastructure is installed.
     */
    public function isOAuthAvailable(): bool
    {
        // Check for auth module's SessionBridge class
        return class_exists('OpenCoreEMR\\Modules\\Auth\\SessionBridge');
    }

    /**
     * Get current authenticated user information
     *
     * Returns user data from OAuth token claims or legacy session.
     * Returns null if no user is authenticated.
     *
     * @return array<string, mixed>|null User data with keys: id, username, site_id, etc.
     */
    public function getCurrentUser(): ?array
    {
        if ($this->currentUser !== null) {
            return $this->currentUser;
        }

        // Try OAuth first if available
        if ($this->isOAuthAvailable()) {
            $user = $this->getUserFromOAuth();
            if ($user !== null) {
                $this->currentUser = $user;
                return $user;
            }
        }

        // Fall back to legacy session
        $user = $this->getUserFromSession();
        $this->currentUser = $user;
        return $user;
    }

    /**
     * Require authentication, redirecting if not authenticated
     *
     * Call this at the top of protected pages/endpoints.
     * For API endpoints, throws an exception instead of redirecting.
     *
     * @param bool $isApi Whether this is an API endpoint (throws instead of redirects)
     * @throws {ModuleName}UnauthorizedException When not authenticated and $isApi is true
     */
    public function requireAuth(bool $isApi = false): void
    {
        $user = $this->getCurrentUser();

        if ($user === null) {
            $this->logger->debug('AuthAdapter: Authentication required but no user found');

            if ($isApi) {
                throw new Exception\{ModuleName}UnauthorizedException('Authentication required');
            }

            // Redirect to login page
            $webroot = $this->globals->getString('webroot', '');
            $loginUrl = $webroot . '/interface/login/login.php?site=' . $this->getSiteId();
            header('Location: ' . $loginUrl);
            exit;
        }
    }

    /**
     * Check if user has a specific permission/scope
     *
     * Supports both OAuth scopes and legacy ACL checks.
     *
     * @param string $scope OAuth scope (e.g., 'api:module:example') or ACL section
     * @param string|null $aclValue ACL value for legacy checks (e.g., 'read', 'write')
     */
    public function hasPermission(string $scope, ?string $aclValue = null): bool
    {
        $user = $this->getCurrentUser();
        if ($user === null) {
            return false;
        }

        // Check OAuth scopes if available
        if ($this->isOAuthAvailable() && isset($user['scopes'])) {
            return in_array($scope, $user['scopes'], true);
        }

        // Fall back to legacy ACL check
        if ($aclValue !== null && function_exists('acl_check')) {
            return acl_check($scope, $aclValue);
        }

        return false;
    }

    /**
     * Get the current Bearer token (OAuth only)
     *
     * Returns null if using legacy session authentication.
     */
    public function getApiToken(): ?string
    {
        if (!$this->isOAuthAvailable()) {
            return null;
        }

        // Check Authorization header
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (str_starts_with($authHeader, 'Bearer ')) {
            return substr($authHeader, 7);
        }

        return null;
    }

    /**
     * Get the current site ID
     */
    public function getSiteId(): string
    {
        $user = $this->getCurrentUser();
        return $user['site_id'] ?? $_SESSION['site_id'] ?? 'default';
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    /**
     * Get user from OAuth token
     *
     * @return array<string, mixed>|null
     */
    private function getUserFromOAuth(): ?array
    {
        $token = $this->getApiToken();
        if ($token === null) {
            return null;
        }

        try {
            // Attempt to use SessionBridge if available
            if (class_exists('OpenCoreEMR\\Modules\\Auth\\SessionBridge')) {
                /** @var object $bridge */
                $bridge = new \OpenCoreEMR\Modules\Auth\SessionBridge();
                if (method_exists($bridge, 'validateToken')) {
                    $tokenData = $bridge->validateToken($token);
                    if ($tokenData !== null) {
                        return [
                            'id' => $tokenData['user_id'] ?? null,
                            'username' => $tokenData['username'] ?? null,
                            'site_id' => $tokenData['site_id'] ?? 'default',
                            'scopes' => $tokenData['scopes'] ?? [],
                            'auth_method' => 'oauth',
                        ];
                    }
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error('AuthAdapter: OAuth validation failed: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Get user from legacy PHP session
     *
     * @return array<string, mixed>|null
     */
    private function getUserFromSession(): ?array
    {
        // Ensure session is started
        if (session_status() === PHP_SESSION_NONE) {
            return null;
        }

        // Check for authenticated session
        $authUser = $_SESSION['authUser'] ?? null;
        if ($authUser === null || $authUser === '') {
            return null;
        }

        return [
            'id' => $_SESSION['authUserID'] ?? $_SESSION['authId'] ?? null,
            'username' => $authUser,
            'site_id' => $_SESSION['site_id'] ?? 'default',
            'scopes' => [], // Legacy sessions don't have scopes
            'auth_method' => 'session',
        ];
    }

    /**
     * Clear cached user data
     *
     * Call this after logout or when authentication state changes.
     */
    public function clearCache(): void
    {
        $this->currentUser = null;
    }
}
