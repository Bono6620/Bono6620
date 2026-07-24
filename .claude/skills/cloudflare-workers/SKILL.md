---
name: cloudflare-workers
description: Use when building, deploying, or debugging Cloudflare Workers, Durable Objects, or Wrangler-based projects — edge functions, KV/D1/R2 bindings, and Workers deployment configuration.
---

# Cloudflare Workers

## Project setup

- `wrangler.toml`/`wrangler.jsonc` is the source of truth for bindings (KV namespaces, D1 databases, R2 buckets, environment variables, secrets) — keep it in sync with what the code actually references.
- Use `wrangler dev` for local iteration; it emulates the Workers runtime far more accurately than a generic Node dev server.
- Secrets go through `wrangler secret put`, never committed into `wrangler.toml` or source.

## Durable Objects

- Use a Durable Object when you need strongly consistent, single-threaded state (a chat room, a rate limiter, a game session) — not as a general-purpose database replacement.
- Keep Durable Object storage operations small and batched; the built-in SQLite-backed storage (`this.ctx.storage.sql`) is preferred over the legacy KV-style storage API for new code.
- Route requests to a Durable Object by a stable, meaningful ID (e.g., `idFromName(userId)`), not a random ID, so the same logical entity always lands on the same instance.

## Performance & limits

- Workers have CPU-time limits per request — avoid heavy synchronous computation; offload to Durable Objects, Queues, or external services for long-running work.
- Use `ctx.waitUntil()` for background work (logging, analytics) that shouldn't block the response.
- Cache aggressively at the edge with the Cache API where content is cacheable — it's the main lever for latency and cost on Workers.

## Deployment

- `wrangler deploy` targets whatever environment is configured; double-check `--env` when a project has staging/production environments defined.
- Review bindings and routes in the Cloudflare dashboard (or `wrangler.toml`) before deploying — a missing binding fails at runtime, not at build time.
