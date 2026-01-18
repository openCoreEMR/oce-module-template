<?php

/**
 * Exception thrown when user is not authenticated
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace {VendorName}\Modules\{ModuleName}\Exception;

class {ModuleName}UnauthorizedException extends {ModuleName}Exception
{
    public function getStatusCode(): int
    {
        return 401;
    }
}
