<?php

declare(strict_types=1);

namespace Spryker\VectorDBClient\ChromaDBClient;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Client for interacting with ChromaDB vector database.
 *
 * This class handles all communication with a ChromaDB instance,
 * providing methods to create and manage collections and documents
 * for vector search capabilities.
 */
class ChromaDbClient implements ChromaDbClientInterface
{
    /**
     * @param ClientInterface $httpClient HTTP client for making API requests
     * @param ChromaDbConfig $config Configuration for ChromaDB connection
     */
    public function __construct(
        private ClientInterface $httpClient,
        private ChromaDbConfig $config,
    ) {
    }

    /**
     * Create a new collection in ChromaDB.
     *
     * @param string $collectionName The name of the collection to create
     *
     * @return array<string, mixed> Response from ChromaDB with collection details
     *
     * @throws ChromaDbException If the collection creation fails
     */
    public function createCollection(string $collectionName): array
    {
        try {
            $response = $this->makeApiRequest(
                'POST',
                '/collections',
                [
                    'name' => $collectionName,
                ]
            );

            return $this->parseResponse($response);
        } catch (GuzzleException $exception) {
            if ($exception->getCode() === 409) {
                return ['message' => 'Collection already exists', 'name' => $collectionName];
            }

            throw new ChromaDbException(
                sprintf('Failed to create collection: %s', $exception->getMessage()),
                (int)$exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Get collection details by ID.
     *
     * @param string $collectionId The ID of the collection to retrieve
     *
     * @return array<string, mixed> Collection details
     *
     * @throws ChromaDbException If the collection retrieval fails
     */
    public function getCollection(string $collectionId): array
    {
        try {
            $response = $this->makeApiRequest(
                'GET',
                sprintf('/collections/%s', urlencode($collectionId))
            );

            return $this->parseResponse($response);
        } catch (GuzzleException $exception) {
            throw new ChromaDbException(
                sprintf('Failed to get collection: %s', $exception->getMessage()),
                (int)$exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Find a collection ID by its name.
     *
     * @param string $collectionName The name of the collection to find
     *
     * @return string|null The collection ID if found, null otherwise
     */
    public function getCollectionIdByName(string $collectionName): ?string
    {
        try {
            $response = $this->makeApiRequest('GET', '/collections');
            $collections = $this->parseResponse($response);

            foreach ($collections as $collection) {
                if ($collection['name'] === $collectionName) {
                    return $collection['id'];
                }
            }

            return null;
        } catch (GuzzleException $exception) {
            return null;
        }
    }

    /**
     * Add documents with embeddings to a ChromaDB collection.
     *
     * @param string $collectionId ID or name of the collection
     * @param array<string> $documents Array of text documents
     * @param array<string> $ids Array of unique IDs for each document (must match count of documents)
     * @param array<array<float>> $embeddings Array of embedding vectors for each document
     * @param array<array<string, mixed>> $metadatas Array of metadata objects for each document (optional)
     *
     * @return array<string, mixed> Response from ChromaDB
     *
     * @throws ChromaDbException If adding documents fails
     */
    public function addDocuments(
        string $collectionId,
        array $documents,
        array $ids,
        array $embeddings,
        array $metadatas = []
    ): array {
        try {
            $response = $this->makeApiRequest(
                'POST',
                sprintf('/collections/%s/add', urlencode($collectionId)),
                [
                    'documents' => $documents,
                    'embeddings' => $embeddings,
                    'ids' => $ids,
                    'metadatas' => $metadatas
                ]
            );

            return $this->parseResponse($response);
        } catch (GuzzleException $exception) {
            throw new ChromaDbException(
                sprintf('Failed to add documents: %s', $exception->getMessage()),
                (int)$exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Query the collection using an embedding vector.
     *
     * @param string $collectionId ID or name of the collection
     * @param array<float> $queryEmbedding Embedding vector to query with
     * @param int $numResults = 10,
     * @param int $limit Maximum number of results
     * @param int $offset
     * @param array<string, mixed> $filter Optional filter to apply
     *
     * @return array<string, mixed>|null Query results
     *
     * @throws ChromaDbException If query fails
     */
    public function queryByEmbedding(
        string $collectionId,
        array $queryEmbedding,
        int $numResults = 10,
        int $limit = 10,
        int $offset = 0,
        array $filter = []
    ): ?array {

        try {
            $queryData = [
                'query_embeddings' => [$queryEmbedding],
                'n_results' => $numResults,
                'include' => ['distances', 'metadatas', 'documents']
            ];

            if (!empty($filter)) {
                $queryData['where'] = $filter;
            }

            $response = $this->makeApiRequest(
                'POST',
                sprintf('/collections/%s/query?limit=%s&offset=%s', urlencode($collectionId), $limit, $offset),
                $queryData
            );

            return $this->parseResponse($response);
        } catch (GuzzleException $exception) {
            throw new ChromaDbException(
                sprintf('Failed to query collection: %s', $exception->getMessage()),
                (int)$exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Delete a collection.
     *
     * @param string $collectionId ID or name of the collection
     *
     * @return array<string, mixed> Response from ChromaDB
     *
     * @throws ChromaDbException If deletion fails
     */
    public function deleteCollection(string $collectionId): array
    {
        try {
            $response = $this->makeApiRequest(
                'DELETE',
                sprintf('/collections/%s', urlencode($collectionId))
            );

            return $this->parseResponse($response);
        } catch (GuzzleException $exception) {
            throw new ChromaDbException(
                sprintf('Failed to delete collection: %s', $exception->getMessage()),
                (int)$exception->getCode(),
                $exception
            );
        }
    }

    /**
     * Make an API request to the ChromaDB service.
     *
     * @param string $method The HTTP method to use
     * @param string $endpoint The API endpoint to call
     * @param array<string, mixed> $payload The request payload (optional)
     *
     * @return ResponseInterface The HTTP response
     *
     * @throws GuzzleException If the HTTP request fails
     */
    private function makeApiRequest(
        string $method,
        string $endpoint,
        array $payload = []
    ): ResponseInterface {
        $options = [
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];

        if (!empty($payload)) {
            $options['json'] = $payload;
        }

        return $this->httpClient->request(
            $method,
            $this->config->getBaseUrlWithTenantAndDatabase() . $endpoint,
            $options
        );
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
