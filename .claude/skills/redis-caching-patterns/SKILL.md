---
name: redis-caching-patterns
description: Use when adding caching, rate limiting, or in-memory data structures with Redis — key design, expiration strategy, and common data-structure choices.
---

# Redis Caching & Data Patterns

## Key design

- Use a consistent, namespaced key convention (`app:entity:id:field`) so keys are greppable and TTL/eviction policies can target a whole namespace.
- Keep keys short but descriptive — Redis pays a real memory cost per key, and verbose keys add up at scale.

## Caching strategy

- Set an explicit TTL on every cache entry unless there's a deliberate reason for a value to live forever — untracked permanent keys are the most common source of unbounded Redis memory growth.
- Choose cache-aside (read-through on miss, write to cache after DB write) as the default pattern; only reach for write-through or write-behind when the access pattern specifically calls for it.
- Guard against cache stampede on hot keys (many requests missing simultaneously and hammering the DB) with a lock, jittered TTLs, or a "probabilistic early expiration" approach — don't let every client independently regenerate an expensive value at the same instant.

## Data structures

- Use the structure that matches the access pattern: hashes for objects with several fields you'll read/write individually, sorted sets for leaderboards/ranked data, lists for queues, sets for membership checks — not a single JSON blob string for everything.
- Use `SCAN` (cursor-based) instead of `KEYS` in any production code path — `KEYS` blocks the single-threaded server on large keyspaces.

## Rate limiting

- Implement rate limiting with `INCR` + `EXPIRE` (or a sorted-set sliding window for more precision) rather than reading-then-writing a counter, which races under concurrent requests.

## Persistence & reliability

- Understand whether the deployment uses RDB snapshots, AOF, or neither before treating Redis as anything more durable than a cache — data loss on restart is the default unless persistence is explicitly configured.
- Use Redis Cluster or a managed clustered offering only when the dataset or throughput genuinely exceeds a single node; a single well-resourced instance handles most workloads and clustering adds real operational complexity.

## Common pitfalls

- Avoid large single keys (huge hashes/lists) — operations on them block other clients longer; shard large collections across multiple keys when they grow unbounded.
