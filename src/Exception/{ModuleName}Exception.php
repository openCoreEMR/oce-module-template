<?php

/**
 * Base exception class for module errors
 *
 * @package   OpenCoreEMR
 * @link      http://www.open-emr.org
 * @author    Your Name <your.email@opencoreemr.com>
 * @copyright Copyright (c) 2026 OpenCoreEMR Inc
 * @license   GNU General Public License 3
 */

namespace {VendorName}\Modules\{ModuleName}\Exception;

abstract class {ModuleName}Exception extends \RuntimeException implements {ModuleName}ExceptionInterface
{
    /**
     * Get the HTTP status code for this exception
     */
    abstract public function getStatusCode(): int;
}
