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

namespace OpenCoreEMR\Modules\{ModuleName}\Controller;

use OpenCoreEMR\Modules\{ModuleName}\Exception\{ModuleName}AccessDeniedException;
use OpenCoreEMR\Modules\{ModuleName}\Exception\{ModuleName}ValidationException;
use OpenCoreEMR\Modules\{ModuleName}\GlobalConfig;
use OpenEMR\Common\Csrf\CsrfUtils;
use OpenEMR\Common\Logging\SystemLogger;
use Symfony\Component\HttpFoundation\RedirectResponse;
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
     *
     * @param array<string, mixed> $params Request parameters (typically from $_GET + $_POST)
     */
    public function dispatch(string $action, array $params = []): Response
    {
        return match ($action) {
            'view' => $this->showView($params),
            'create' => $this->handleCreate($params),
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
     *
     * @param array<string, mixed> $params
     */
    private function showView(array $params): Response
    {
        $id = $params['id'] ?? null;

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
     *
     * @param array<string, mixed> $params
     */
    private function handleCreate(array $params): Response
    {
        // Validate CSRF token
        $csrfToken = $params['csrf_token'] ?? '';
        if (!CsrfUtils::verifyCsrfToken($csrfToken)) {
            throw new {ModuleName}AccessDeniedException('CSRF token verification failed');
        }

        // Validate input
        $name = $params['name'] ?? '';
        if (empty($name)) {
            throw new {ModuleName}ValidationException('Name is required');
        }

        // Process the request
        try {
            // Example: create item via service
            // $this->service->create(['name' => $name]);

            $this->logger->debug("Created item: {$name}");

            // Redirect back to list - use PHP_SELF from params for testability
            $redirectUrl = $params['_self'] ?? '/';
            return new RedirectResponse($redirectUrl);
        } catch (\Throwable $e) {
            $this->logger->error("Error creating item: " . $e->getMessage());
            throw $e;
        }
    }
}
