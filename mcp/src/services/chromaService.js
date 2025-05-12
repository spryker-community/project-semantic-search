/**
 * ChromaDB Service for Spryker Semantic Search
 *
 * This service provides an interface to interact with a ChromaDB vector database.
 * It's used to query and retrieve relevant code snippets based on embedding vectors.
 *
 * @module chromaService
 */

import axios from 'axios';
import dotenv from 'dotenv';
import { createLogger } from '../logger.js';

// Load environment variables
dotenv.config();

// Create service logger
const logger = createLogger();

// Configuration constants with defaults
const CHROMA_URL = process.env.CHROMA_URL || 'http://chromadb:8000';
const CHROMA_TENANT = process.env.CHROMA_TENANT || 'default_tenant';
const CHROMA_DATABASE = process.env.CHROMA_DATABASE || 'default_database';
const CHROMA_COLLECTION_NAME = process.env.PROJECT_NAME || 'documents';
const CHROMA_TIMEOUT_MS = parseInt(process.env.CHROMA_TIMEOUT_MS, 10) || 30000;

// Cache for collection ID to avoid redundant API calls
let CHROMA_COLLECTION_ID = null;

/**
 * Initialize the ChromaDB service and get the collection ID
 *
 * @returns {Promise<string>} Collection ID
 * @throws {Error} If initialization fails
 */
export const initChromaDB = async () => {
    if (CHROMA_COLLECTION_ID) {
        return CHROMA_COLLECTION_ID;
    }

    logger.debug(`Initializing ChromaDB and getting collection ID for: ${CHROMA_COLLECTION_NAME}`, {
        tenant: CHROMA_TENANT,
        database: CHROMA_DATABASE
    });

    try {
        // Configure request with timeout
        const config = {
            timeout: CHROMA_TIMEOUT_MS,
            headers: { 'Content-Type': 'application/json' }
        };

        // Get collections list using Chroma API
        const endpoint = `${CHROMA_URL}/api/v2/tenants/${CHROMA_TENANT}/databases/${CHROMA_DATABASE}/collections`;
        const response = await axios.get(endpoint, config);

        // Validate response
        if (!response.data || !Array.isArray(response.data)) {
            throw new Error('Invalid response from ChromaDB: expected array of collections');
        }

        // Find the collection by name and get its ID
        const collection = response.data.find(col => col.name === CHROMA_COLLECTION_NAME);

        if (!collection || !collection.id) {
            throw new Error(`Collection '${CHROMA_COLLECTION_NAME}' not found in ChromaDB`);
        }

        // Cache the collection ID
        CHROMA_COLLECTION_ID = collection.id;
        logger.info(`Found ChromaDB collection ID: ${CHROMA_COLLECTION_ID} for collection: ${CHROMA_COLLECTION_NAME}`);

        return CHROMA_COLLECTION_ID;
    } catch (error) {
        // Enhanced error handling with specific error types
        if (error.code === 'ECONNREFUSED') {
            logger.error(`Connection refused to ChromaDB at ${CHROMA_URL}`, { error });
            throw new Error(`Cannot connect to ChromaDB at ${CHROMA_URL}`);
        }

        if (error.code === 'ETIMEDOUT') {
            logger.error(`Timeout while connecting to ChromaDB`, { error });
            throw new Error(`ChromaDB request timed out after ${CHROMA_TIMEOUT_MS}ms`);
        }

        if (error.response) {
            logger.error(`ChromaDB API error: ${error.response.status} ${error.response.statusText}`, {
                status: error.response.status,
                data: error.response.data
            });
            throw new Error(`ChromaDB returned error: ${error.response.status} ${error.response.statusText}`);
        }

        // Generic error case
        logger.error(`Error initializing ChromaDB: ${error.message}`, { error });
        throw new Error(`Failed to initialize ChromaDB: ${error.message}`);
    }
};

/**
 * Get the ChromaDB collection ID. Initializes if not already done.
 *
 * @returns {Promise<string>} Collection ID
 * @throws {Error} If getting collection ID fails
 */
export const getChromaCollectionId = async () => {
    if (!CHROMA_COLLECTION_ID) {
        return await initChromaDB();
    }
    return CHROMA_COLLECTION_ID;
};

/**
 * Query ChromaDB with an embedding vector to find similar documents
 *
 * @param {Array<number>} embedding - The embedding vector to query with
 * @param {number} limit - Maximum number of results to return
 * @param {Object} filters - Optional metadata filters to apply to the query
 * @returns {Promise<Array<Object>>} Array of matching documents with their metadata
 * @throws {Error} If querying fails
 */
export const queryChromaDB = async (embedding, limit = 5, filters = undefined) => {
    // Validate input
    if (!embedding || !Array.isArray(embedding) || embedding.length === 0) {
        throw new Error('Invalid embedding: must be a non-empty array of numbers');
    }

    if (typeof limit !== 'number' || limit <= 0) {
        throw new Error('Invalid limit: must be a positive number');
    }

    logger.debug(`Querying ChromaDB collection: ${CHROMA_COLLECTION_NAME}`, {
        tenant: CHROMA_TENANT,
        database: CHROMA_DATABASE,
        limit,
        embeddingDimensions: embedding.length
    });

    try {
        // Ensure we have the collection ID
        const collectionId = await getChromaCollectionId();

        // Configure request with timeout
        const config = {
            timeout: CHROMA_TIMEOUT_MS,
            headers: { 'Content-Type': 'application/json' }
        };

        // Using ChromaDB v2 API with collection ID
        const endpoint = `${CHROMA_URL}/api/v2/tenants/${CHROMA_TENANT}/databases/${CHROMA_DATABASE}/collections/${collectionId}/query`;

        // Prepare query payload
        const queryPayload = {
            query_embeddings: [embedding],
            n_results: limit,
            include: ["metadatas", "documents", "distances"]
        };

        // Add filters if provided
        if (filters) {
            queryPayload.where = filters;
            logger.debug(`Applying metadata filters to query: ${JSON.stringify(filters)}`);
        }

        // Execute query
        const response = await axios.post(endpoint, queryPayload, config);

        // Handle empty results
        if (!response.data ||
            !response.data.metadatas ||
            !Array.isArray(response.data.metadatas[0])) {
            logger.warn('No results returned from ChromaDB');
            return [];
        }

        // Transform the response to a more usable format with complete information
        const results = response.data.metadatas[0].map((metadata, index) => {
            // Extract document content and distance if available
            const document = response.data.documents &&
                response.data.documents[0] &&
                response.data.documents[0][index];

            // Create a formatted result object
            return {
                document: document,
                metadata: { ...metadata } // Include all original metadata
            };
        });

        logger.debug(`ChromaDB returned ${results.length} results`);
        return results;
    } catch (error) {
        // Enhanced error handling with specific error types
        if (error.code === 'ECONNREFUSED') {
            logger.error(`Connection refused to ChromaDB at ${CHROMA_URL}`, { error });
            throw new Error(`Cannot connect to ChromaDB at ${CHROMA_URL}`);
        }

        if (error.code === 'ETIMEDOUT') {
            logger.error(`Timeout while querying ChromaDB`, { error });
            throw new Error(`ChromaDB query timed out after ${CHROMA_TIMEOUT_MS}ms`);
        }

        if (error.response) {
            logger.error(`ChromaDB API error: ${error.response.status} ${error.response.statusText}`, {
                status: error.response.status,
                data: error.response.data
            });
            throw new Error(`ChromaDB returned error: ${error.response.status} ${error.response.statusText}`);
        }

        // Generic error case
        logger.error(`Error querying ChromaDB: ${error.message}`, { error });
        throw new Error(`Failed to query ChromaDB: ${error.message}`);
    }
};

/**
 * Reset the cached collection ID
 * Useful for testing or when the collection has been recreated
 */
export const resetCollectionIdCache = () => {
    CHROMA_COLLECTION_ID = null;
    logger.debug('ChromaDB collection ID cache has been reset');
};
