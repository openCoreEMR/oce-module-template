<?php

/**
 * Example controller demonstrating the controller pattern
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace {VendorName}\Modules\{ModuleName}\Controller;

use {VendorName}\Modules\{ModuleName}\Exception\{ModuleName}AccessDeniedException;
use {VendorName}\Modules\{ModuleName}\Exception\{ModuleName}NotFoundException;
use {VendorName}\Modules\{ModuleName}\Exception\{ModuleName}ValidationException;
use {VendorName}\Modules\{ModuleName}\GlobalConfig;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\Response;
use Twig\Environment;

/**
 * Example controller showing proper patterns for OpenEMR modules.
 *
 * Key patterns demonstrated:
 * - Constructor dependency injection
 * - Dispatch method routing actions to handlers
 * - Returning Response objects (never void)
 * - CSRF token validation on POST
 * - Throwing custom exceptions (never die/exit)
 * - Using Twig for HTML rendering
 */
class ExampleController
{
    private readonly SystemLogger $logger;

    public function __construct(
        private readonly GlobalConfig $config,
        private readonly Environment $twig
    ) {
        $this->logger = new SystemLogger();
    }

    /**
     * Dispatch action to appropriate method
     */
    public function dispatch(string $action): Response
    {
        return match ($action) {
            'view' => $this->showView(),
            'create' => $this->handleCreate(),
            'list' => $this->showList(),
            default => $this->showList(),
        };
    }

    /**
     * Show the list view
     */
    private function showList(): Response
    {
        $this->logger->debug('Showing list view');

        $content = $this->twig->render('example/list.html.twig', [
            'title' => 'Module Dashboard',
            'items' => [],
            'csrf_token' => CsrfUtils::collectCsrfToken(),
            'webroot' => $this->config->getWebroot(),
        ]);

        return new Response($content);
    }

    /**
     * Show single item view
     */
    private function showView(): Response
    {
        $id = $_GET['id'] ?? null;

        if (empty($id)) {
            throw new {ModuleName}ValidationException('Item ID is required');
        }

        // Example: fetch item from database
        // $item = $this->service->findById((int) $id);
        // if (!$item) {
        //     throw new {ModuleName}NotFoundException("Item not found: {$id}");
        // }

        $content = $this->twig->render('example/view.html.twig', [
            'title' => 'View Item',
            'item' => ['id' => $id, 'name' => 'Example Item'],
            'csrf_token' => CsrfUtils::collectCsrfToken(),
            'webroot' => $this->config->getWebroot(),
        ]);

        return new Response($content);
    }

    /**
     * Handle create action (POST)
     */
    private function handleCreate(): Response
    {
        // Validate CSRF token
        $csrfToken = $_POST['csrf_token'] ?? '';
        if (!CsrfUtils::verifyCsrfToken($csrfToken)) {
            throw new {ModuleName}AccessDeniedException('CSRF token verification failed');
        }

        // Validate input
        $name = $_POST['name'] ?? '';
        if (empty($name)) {
            throw new {ModuleName}ValidationException('Name is required');
        }

        // Process the request
        try {
            // Example: create item via service
            // $this->service->create(['name' => $name]);

            $this->logger->debug("Created item: {$name}");

            // Redirect back to list
            return new Response('', Response::HTTP_FOUND, [
                'Location' => $_SERVER['PHP_SELF']
            ]);
        } catch (\Throwable $e) {
            $this->logger->error("Error creating item: " . $e->getMessage());
            throw $e;
        }
    }
}
