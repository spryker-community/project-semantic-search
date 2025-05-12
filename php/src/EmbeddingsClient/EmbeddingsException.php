<?php

declare(strict_types=1);

namespace Spryker\EmbeddingsClient;

use Exception;

/**
 * Exception thrown when there is an error communicating with the embeddings service.
 *
 * This exception is used for all errors related to generating text embeddings,
 * including API communication errors, invalid responses, and service failures.
 */
class EmbeddingsException extends Exception
{
}
