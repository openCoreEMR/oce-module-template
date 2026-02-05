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

    $actionParam = $_GET['action'] ?? $_POST['action'] ?? 'list';
    $action = is_string($actionParam) ? $actionParam : 'list';

    $params = array_merge($_GET, $_POST);
    $params['_self'] = $_SERVER['PHP_SELF'] ?? '/';

    try {
        $response = $controller->dispatch($action, $params);
        $response->send();
    } catch ({ModuleName}HttpExceptionInterface $e) {
        error_log("Module error: " . $e->getMessage());
        $response = createErrorResponse($e->getStatusCode(), $kernel, $bootstrap->getWebroot());
        $response->send();
    } catch (\Throwable $e) {
        error_log("Unexpected error: " . $e->getMessage());
        $response = createErrorResponse(Response::HTTP_INTERNAL_SERVER_ERROR, $kernel, $bootstrap->getWebroot());
        $response->send();
    }
}

/**
 * Build a generic error response (Twig) so exception messages are not shown to users.
 */
function createErrorResponse(
    int $statusCode,
    \OpenEMR\Core\Kernel $kernel,
    string $webroot = ''
): Response {
    $templatePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR;
    $twigContainer = new \OpenEMR\Common\Twig\TwigContainer($templatePath, $kernel);
    $twig = $twigContainer->getTwig();
    $content = $twig->render('error.html.twig', [
        'status_code' => $statusCode,
        'title' => $statusCode >= 500 ? 'Server Error' : 'Error',
        'webroot' => $webroot,
    ]);
    return new Response($content, $statusCode);
}
