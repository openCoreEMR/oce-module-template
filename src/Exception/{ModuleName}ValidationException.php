<?php

/**
 * Exception thrown when input validation fails
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace OpenCoreEMR\Modules\{ModuleName}\Exception;

class {ModuleName}ValidationException extends {ModuleName}Exception
{
    public function getStatusCode(): int
    {
        return 400;
    }
}
