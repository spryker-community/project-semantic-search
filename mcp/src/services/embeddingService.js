/**
 * Embedding Service for Spryker Semantic Search
 *
 * This service is responsible for generating text embeddings using Ollama.
 * It converts natural language text into vector representations that can be
 * used for semantic search operations.
 *
 * @module embeddingService
 */

import axios from 'axios';
import dotenv from 'dotenv';
import { createLogger } from '../logger.js';

// Load environment variables
dotenv.config();

// Create service logger
const logger = createLogger();

// Configuration constants with defaults
const OLLAMA_URL = process.env.OLLAMA_URL || 'http://ollama:11434';
const OLLAMA_MODEL = process.env.OLLAMA_MODEL || 'nomic-embed-text';
const OLLAMA_TIMEOUT_MS = parseInt(process.env.OLLAMA_TIMEOUT_MS, 10) || 30000;

/**
 * Generate embedding for a given text using Ollama API
 *
 * @param {string} text - The text to generate embedding for
 * @returns {Promise<Array<number>>} - A promise that resolves to the embedding vector
 * @throws {Error} If embedding generation fails
 */
export const getEmbedding = async (text) => {
    // Validate input
    if (!text || typeof text !== 'string') {
        throw new Error('Invalid input: text must be a non-empty string');
    }

    const truncatedText = text.length > 50 ? `${text.substring(0, 50)}...` : text;
    logger.debug(`Generating embedding for text: "${truncatedText}"`, {
        modelName: OLLAMA_MODEL,
        textLength: text.length
    });

    try {
        // Configure request with timeout
        const config = {
            timeout: OLLAMA_TIMEOUT_MS,
            headers: { 'Content-Type': 'application/json' }
        };

        // Make API request to Ollama service
        const response = await axios.post(
            `${OLLAMA_URL}/api/embeddings`,
            { model: OLLAMA_MODEL, prompt: text },
            config
        );

        // Validate response
        if (!response.data || !Array.isArray(response.data.embedding)) {
            throw new Error('Invalid response: missing or malformed embedding data');
        }

        const embeddingVector = response.data.embedding;
        logger.debug(`Successfully generated embedding with ${embeddingVector.length} dimensions`);

        return embeddingVector;
    } catch (error) {
        // Enhanced error handling with specific error types
        if (error.code === 'ECONNREFUSED') {
            logger.error(`Connection refused to Ollama service at ${OLLAMA_URL}`, { error });
            throw new Error(`Cannot connect to embedding service at ${OLLAMA_URL}`);
        }

        if (error.code === 'ETIMEDOUT') {
            logger.error(`Timeout while connecting to Ollama service`, { error });
            throw new Error(`Embedding service request timed out after ${OLLAMA_TIMEOUT_MS}ms`);
        }

        if (error.response) {
            logger.error(`Ollama API error: ${error.response.status} ${error.response.statusText}`, {
                status: error.response.status,
                data: error.response.data
            });
            throw new Error(`Embedding service returned error: ${error.response.status} ${error.response.statusText}`);
        }

        // Generic error case
        logger.error(`Error generating embedding: ${error.message}`, { error });
        throw new Error(`Failed to generate embedding: ${error.message}`);
    }
};
