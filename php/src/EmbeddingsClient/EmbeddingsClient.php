<?php

declare(strict_types=1);

namespace Spryker\EmbeddingsClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;

/**
 * Client for interacting with embeddings API to generate vector representations of text.
 *
 * This class handles communication with the embeddings service to convert text
 * into vector embeddings that can be used for semantic search, clustering,
 * and other natural language processing tasks.
 */
class EmbeddingsClient implements EmbeddingsClientInterface
{
    /**
     * @var ClientInterface HTTP client for making API requests
     */
    private ClientInterface $httpClient;

    /**
     * @var string The model identifier used for generating embeddings
     */
    private string $model;

    /**
     * @param ClientInterface $httpClient HTTP client for making API requests
     * @param string $model The model identifier to use for generating embeddings
     */
    public function __construct(
        ClientInterface $httpClient,
        string $model
    ) {
        $this->httpClient = $httpClient;
        $this->model = $model;
    }

    /**
     * Generate embeddings for a single text prompt.
     *
     * @param string $text The text to convert to embeddings
     *
     * @return array<string, mixed> The complete API response including embeddings
     *
     * @throws EmbeddingsException If the API request fails
     */
    public function getEmbeddings(string $text): array
    {
        try {
            $response = $this->makeApiRequest('/api/embeddings', [
                'model' => $this->model,
                'prompt' => $text,
            ]);

            return $this->parseResponse($response);
        } catch (GuzzleException $exception) {
            throw new EmbeddingsException(
                sprintf('Failed to get embeddings: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Extract just the embedding vector from a text prompt.
     *
     * @param string $text The text to convert to an embedding vector
     *
     * @return array<int, float>|null The embedding vector or null if not found
     *
     * @throws EmbeddingsException If the API request fails
     */
    public function getEmbeddingVector(string $text): ?array
    {
        $result = $this->getEmbeddings($text);

        return $result['embedding'] ?? null;
    }

    /**
     * Generate embeddings for multiple text prompts in a single request.
     *
     * @param array<string> $prompts List of text prompts to convert to embeddings
     *
     * @return array<string, mixed> The complete API response including embeddings
     *
     * @throws EmbeddingsException If the API request fails
     */
    public function getMultiEmbeddings(array $prompts): array
    {
        try {
            $response = $this->makeApiRequest('/api/embed', [
                'model' => $this->model,
                'input' => $prompts,
            ]);

            return $this->parseResponse($response);
        } catch (GuzzleException $exception) {
            throw new EmbeddingsException(
                sprintf('Failed to get multiple embeddings: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Extract just the embedding vectors from multiple text prompts.
     *
     * @param array<string> $prompts List of text prompts to convert to embedding vectors
     *
     * @return array<int, array<int, float>>|null The embedding vectors or null if not found
     *
     * @throws EmbeddingsException If the API request fails
     */
    public function getMultiEmbeddingVectors(array $prompts): ?array
    {
        $results = $this->getMultiEmbeddings($prompts);

        return $results['embeddings'] ?? null;
    }

    /**
     * Make an API request to the embeddings service.
     *
     * @param string $endpoint The API endpoint to call
     * @param array<string, mixed> $payload The request payload
     *
     * @return ResponseInterface The HTTP response
     *
     * @throws GuzzleException If the HTTP request fails
     */
    private function makeApiRequest(string $endpoint, array $payload): ResponseInterface
    {
        return $this->httpClient->request('POST', $endpoint, [
            RequestOptions::JSON => $payload,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ]);
    }

    /**
     * Parse the API response into an array.
     *
     * @param ResponseInterface $response The HTTP response
     *
     * @return array<string, mixed> The parsed response data
     */
    private function parseResponse(ResponseInterface $response): array
    {
        $contents = $response->getBody()->getContents();

        return json_decode($contents, true);
    }
}
