<?php

declare(strict_types=1);

namespace Spryker\VectorDBClient\ChromaDBClient;

/**
 * Exception thrown when there is an error communicating with the ChromaDB service.
 *
 * This exception is used for all errors related to ChromaDB operations,
 * including API communication errors, invalid responses, and service failures.
 */
class ChromaDbException extends \Exception
{
}
