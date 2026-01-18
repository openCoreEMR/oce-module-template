<?php

/**
 * Exception thrown when access is denied (CSRF failure, insufficient permissions)
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace {VendorName}\Modules\{ModuleName}\Exception;

class {ModuleName}AccessDeniedException extends {ModuleName}Exception
{
    public function getStatusCode(): int
    {
        return 403;
    }
}
