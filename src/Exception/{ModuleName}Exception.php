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

namespace OpenCoreEMR\Modules\{ModuleName}\Exception;

/**
 * Abstract base class for HTTP-aware module exceptions.
 *
 * Extend this class for exceptions that should map to specific HTTP status codes.
 * For exceptions without HTTP semantics, implement {ModuleName}ExceptionInterface directly.
 */
abstract class {ModuleName}Exception extends \RuntimeException implements {ModuleName}HttpExceptionInterface
{
    /**
     * Get the HTTP status code for this exception
     */
    abstract public function getStatusCode(): int;
}
