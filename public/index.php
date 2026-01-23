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

use {VendorName}\Modules\{ModuleName}\Bootstrap;
use {VendorName}\Modules\{ModuleName}\ConfigFactory;
use {VendorName}\Modules\{ModuleName}\Exception\{ModuleName}ExceptionInterface;
use {VendorName}\Modules\{ModuleName}\GlobalsAccessor;
use {VendorName}\Modules\{ModuleName}\ModuleAccessGuard;
use Symfony\Component\HttpFoundation\Response;

// Check if module is installed and enabled - return 404 if not
$guardResponse = ModuleAccessGuard::check(Bootstrap::MODULE_NAME);
if ($guardResponse instanceof Response) {
    $guardResponse->send();
    exit;
}

// Get kernel and bootstrap module
$globalsAccessor = new GlobalsAccessor();
$kernel = $globalsAccessor->get('kernel');
if (!$kernel instanceof \OpenEMR\Core\Kernel) {
    throw new \RuntimeException('OpenEMR Kernel not available');
}
$configAccessor = ConfigFactory::createConfigAccessor();
$bootstrap = new Bootstrap($kernel->getEventDispatcher(), $kernel, $configAccessor);

// Get controller
$controller = $bootstrap->getExampleController();

// Determine action
$actionParam = $_GET['action'] ?? $_POST['action'] ?? 'list';
$action = is_string($actionParam) ? $actionParam : 'list';

// Dispatch to controller and send response
try {
    $response = $controller->dispatch($action);
    $response->send();
} catch ({ModuleName}ExceptionInterface $e) {
    error_log("Module error: " . $e->getMessage());
    $response = new Response(
        "Error: " . htmlspecialchars($e->getMessage()),
        $e->getStatusCode()
    );
    $response->send();
} catch (\Throwable $e) {
    error_log("Unexpected error: " . $e->getMessage());
    $response = new Response(
        "Error: An unexpected error occurred",
        Response::HTTP_INTERNAL_SERVER_ERROR
    );
    $response->send();
}
