<?php

declare(strict_types=1);

namespace Spryker\VectorDBClient\ChromaDBClient;

/**
 * Interface for interacting with ChromaDB vector database.
 *
 * ChromaDB is a vector database for storing and querying embeddings.
 * This interface defines methods for managing collections and documents
 * within the ChromaDB service.
 */
interface ChromaDbClientInterface
{
    /**
     * Create a new collection in ChromaDB.
     *
     * @param string $collectionName The name of the collection to create
     *
     * @return array<string, mixed> Response from ChromaDB with collection details
     *
     * @throws ChromaDbException If the collection creation fails
     */
    public function createCollection(string $collectionName): array;

    /**
     * Get collection details by ID.
     *
     * @param string $collectionId The ID of the collection to retrieve
     *
     * @return array<string, mixed> Collection details
     *
     * @throws ChromaDbException If the collection retrieval fails
     */
    public function getCollection(string $collectionId): array;

    /**
     * Find a collection ID by its name.
     *
     * @param string $collectionName The name of the collection to find
     *
     * @return string|null The collection ID if found, null otherwise
     */
    public function getCollectionIdByName(string $collectionName): ?string;

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
    ): array;

    /**
     * Query the collection using an embedding vector.
     *
     * @param string $collectionId ID or name of the collection
     * @param array<float> $queryEmbedding Embedding vector to query with
     * @param int $numResults
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
    ): ?array;

    /**
     * Delete a collection.
     *
     * @param string $collectionId ID or name of the collection
     *
     * @return array<string, mixed> Response from ChromaDB
     *
     * @throws ChromaDbException If deletion fails
     */
    public function deleteCollection(string $collectionId): array;
}
