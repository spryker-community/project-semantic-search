<?php

declare(strict_types=1);

namespace Spryker\EmbeddingsClient;

/**
 * Interface for embeddings API clients that convert text to vector representations.
 *
 * Implementations of this interface handle communication with embeddings services
 * to generate vector embeddings for text data that can be used in semantic search,
 * clustering, and other natural language processing applications.
 */
interface EmbeddingsClientInterface
{
    /**
     * Generate embeddings for a single text prompt.
     *
     * @param string $text The text to convert to embeddings
     *
     * @return array<string, mixed> The complete API response including embeddings
     *
     * @throws EmbeddingsException If the API request fails
     */
    public function getEmbeddings(string $text): array;

    /**
     * Extract just the embedding vector from a text prompt.
     *
     * @param string $text The text to convert to an embedding vector
     *
     * @return array<int, float>|null The embedding vector or null if not found
     *
     * @throws EmbeddingsException If the API request fails
     */
    public function getEmbeddingVector(string $text): ?array;

    /**
     * Generate embeddings for multiple text prompts in a single request.
     *
     * @param array<string> $prompts List of text prompts to convert to embeddings
     *
     * @return array<string, mixed> The complete API response including embeddings
     *
     * @throws EmbeddingsException If the API request fails
     */
    public function getMultiEmbeddings(array $prompts): array;

    /**
     * Extract just the embedding vectors from multiple text prompts.
     *
     * @param array<string> $prompts List of text prompts to convert to embedding vectors
     *
     * @return array<int, array<int, float>>|null The embedding vectors or null if not found
     *
     * @throws EmbeddingsException If the API request fails
     */
    public function getMultiEmbeddingVectors(array $prompts): ?array;
}
