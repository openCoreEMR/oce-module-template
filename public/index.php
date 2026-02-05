<?php

/**
 * Main interface for the module
 *
 * This is the primary entry point for the module's web interface.
 * Entry points should be minimal - just dispatch to a controller and send the response.
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

$sessionAllowWrite = true;

// Load module autoloader before globals.php so our classes are available
// even when OpenEMR hasn't bootstrapped the module (e.g., module not registered)
require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../../../../globals.php';

use OpenCoreEMR\Modules\{ModuleName}\Bootstrap;
use OpenCoreEMR\Modules\{ModuleName}\ConfigFactory;
use OpenCoreEMR\Modules\{ModuleName}\Exception\{ModuleName}HttpExceptionInterface;
use OpenCoreEMR\Modules\{ModuleName}\GlobalsAccessor;
use OpenCoreEMR\Modules\{ModuleName}\ModuleAccessGuard;
use Symfony\Component\HttpFoundation\Response;

// Check if module is installed and enabled - return 404 if not
$guardResponse = ModuleAccessGuard::check(Bootstrap::MODULE_NAME);
if ($guardResponse instanceof Response) {
    $guardResponse->send();
    return;
}

run();

/**
 * Main entry logic. Wrapped in a function so the guard above can use return
 * instead of exit, keeping the template consistent with CLAUDE.md (no exit/die).
 */
function run(): void
{
    $globalsAccessor = new GlobalsAccessor();
    $kernel = $globalsAccessor->get('kernel');
    if (!$kernel instanceof \OpenEMR\Core\Kernel) {
        throw new \RuntimeException('OpenEMR Kernel not available');
    }
    $configAccessor = ConfigFactory::createConfigAccessor();
    $bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel, $configAccessor);

    $controller = $bootstrap->getExampleController();

    $requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';
    $actionParam = $_GET['action'] ?? ($requestMethod === 'POST' ? ($_POST['action'] ?? 'list') : 'list');
    $action = is_string($actionParam) ? $actionParam : 'list';

    // Only include POST params when method is POST so GET ?action=create&... cannot trigger create
    $params = $requestMethod === 'POST' ? array_merge($_GET, $_POST) : array_merge($_GET, []);
    $params['_self'] = $_SERVER['PHP_SELF'] ?? '/';

    // Require POST for create action
    if ($action === 'create' && $requestMethod !== 'POST') {
        $action = 'list';
    }

    try {
        $response = $controller->dispatch($action, $params);
        $response->send();
    } catch ({ModuleName}HttpExceptionInterface $e) {
        error_log("Module error: " . $e->getMessage());
        $response = Bootstrap::createErrorResponse($e->getStatusCode(), $kernel, $bootstrap->getWebroot());
        $response->send();
    } catch (\Throwable $e) {
        error_log("Unexpected error: " . $e->getMessage());
        $response = Bootstrap::createErrorResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $kernel, $bootstrap->getWebroot());
        $response->send();
    }
}
