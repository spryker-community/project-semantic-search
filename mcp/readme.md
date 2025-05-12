# Spryker MCP Server

A **Model Context Protocol (MCP)** server for Spryker Semantic Search. It connects to **Ollama** for generating embeddings and **ChromaDB** for performing vector-based semantic search.

---

## 🚀 Features

- ✅ MCP-compatible HTTP server
- 🧠 Embedding generation using Ollama
- 🔍 Semantic vector search via ChromaDB
- ⚙️ Configurable via `.env` file
- 📋 Structured and configurable logging

---

## 📦 Setup

1. Ensure **Docker** and **Docker Compose** are installed.
2. Start the environment - Docker Compose:

```bash
cd spryker-project-semantic-search
```
```bash
docker-compose up -d
```

## Development

### 🛠 Testing the Server with inspector

Configure `mcp/.env` as in `mcp/.env.example`

```bash
npx @modelcontextprotocol/inspector npx node src/index.js
```
or from any path

```bash
npx @modelcontextprotocol/inspector npx node src/index.js
```

### Settings example

You can add as many servers as projects, just by configuring them properly using the project name.
```json
{
  "mcpServers": {
      "suiteNonsplitSearch": {
          "command": "npx",
          "args": [
              "-y",
              "/Users/vitaliiivanov/Desktop/development/spryker/suite-nonsplit/spryker-project-semantic-search/mcp"
          ],
          "env": {
              "AI_PROVIDER":"ollama",
              "OLLAMA_URL":"http://localhost:11434",
              "OLLAMA_MODEL":"nomic-embed-text",
              "CHROMA_URL":"http://localhost:8000",
              "NUM_RESULTS":"25",
              "PROJECT_NAME":"project-name" // has to match value in php/.env
          }
      }
  }
}
```
```text
```
