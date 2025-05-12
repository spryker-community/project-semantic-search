#!/usr/bin/env node

/**
 * Spryker Project Semantic Search Tool
 *
 * This file initializes a Model Context Protocol server that provides
 * semantic search capabilities for the Spryker codebase.
 * The tool generates embeddings for queries and searches for relevant
 * code snippets in a ChromaDB vector database.
 *
 * @module SprykerProjectSemanticSearch
 */

import {McpServer} from "@modelcontextprotocol/sdk/server/mcp.js";
import {StdioServerTransport} from "@modelcontextprotocol/sdk/server/stdio.js";
import {z} from "zod";
import dotenv from 'dotenv';
import {createLogger} from './logger.js';
import {getEmbedding} from './services/embeddingService.js';
import {queryChromaDB} from './services/chromaService.js';

// Load environment variables from .env file
dotenv.config();

// Create application logger
const logger = createLogger();

// Initialize the Model Context Protocol server
const server = new McpServer({
    name: "SprykerProjectSemanticSearch",
    version: "1.0.0",
    description: "A tool that provides semantic search capabilities for the Spryker project " + (process.env.PROJECT_NAME || '')
        + " codebase among method and class names. "
        + "Limitation: the tool does not search the full file or class content, class or method implementation, just short code references that includes class name, method name and method annotation"
});

logger.info('Initializing MCP server for Spryker Project ' + (process.env.PROJECT_NAME || '') + ' Semantic Search');
/**
 * Define the semantic search tool that allows querying the Spryker codebase
 * using natural language
 */
server.tool(
    "search_in_project",
    'To search classes and methods in ' + (process.env.PROJECT_NAME || ''),
    {
        query: z
            .string()
            .max(120)
            .min(5)
            .describe("The natural language query that contains class or method names"),
        types: z
            .array(z.string())
            .optional()
            .describe("Optional array of types to filter by [\"Plugin\", \"PluginInterface\", \"FacadeInterface\", \"ServiceInterface\", \"ClientInterface\", \"Config\"]." +
                " Apply what is needed, leave empty if not needed")
    },
    async ({query, types}) => {

        try {
            logger.info(`Processing semantic search query: "${query}"`);

            // Step 1: Generate embedding vector for the query
            logger.info('Generating embedding for query');
            const embedding = await getEmbedding(query);
            logger.info('Embedding generated successfully');

            // Step 2: Search for similar code snippets in ChromaDB using the embedding
            logger.info('Querying ChromaDB with embedding');
            const numResults = Number(process.env.NUM_RESULTS) || 15;

            // Check if types are provided and create filters
            const filters = types && types.length > 0 ? {type: {$in: types}} : undefined;
            if (filters) {
                logger.info(`Applying type filters: ${JSON.stringify(filters)}`);
            }

            const results = await queryChromaDB(embedding, numResults, filters);
            logger.info(`Found ${results.length} results from ChromaDB`);


            // Step 3: Format and return the search results
            const formattedResults = {
                content: [
                    {
                        type: "text",
                        text: `Found ${results.length} results matching your query with type filters ${JSON.stringify(types)} that contain code references to method names and their annotations. ` + `\n` +
                            `\`\`\`json\n${JSON.stringify((results.map((result) => result.metadata.code || '')), null, 2)}\n\`\`\``
                    }
                ]
            };

            // Debug output for development purposes
            if (process.env.NODE_ENV === 'development') {
                logger.debug("Search results:", JSON.stringify(formattedResults));
            }

            return formattedResults;
        } catch (error) {
            // Handle errors gracefully
            logger.error(`Error in semantic search: ${error.message}`, {
                error,
                stack: error.stack
            });

            return {
                content: [{
                    type: "text",
                    text: `Error performing semantic search: ${error.message}`
                }]
            };
        }
    }
);

// Initialize transport layer and connect the server
const transport = new StdioServerTransport();

// Start the server
try {
    await server.connect(transport);
    logger.info('Hello! MCP server connected and ready to process requests');
    logger.info(`Server is configured for project: ${process.env.PROJECT_NAME || 'unknown'}`);
} catch (error) {
    logger.error(`Failed to start MCP server: ${error.message}`, {
        error,
        stack: error.stack
    });
    process.exit(1);
}
