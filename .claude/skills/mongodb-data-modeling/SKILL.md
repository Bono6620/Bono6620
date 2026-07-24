---
name: mongodb-data-modeling
description: Use when designing schemas, writing queries, or reviewing performance for MongoDB — document modeling, indexing, and aggregation pipeline design.
---

# MongoDB Data Modeling & Queries

## Schema design

- Model around access patterns, not around normalized relational instincts: embed data that's always read together and rarely updated independently; reference data that's large, shared across many parents, or updated on its own lifecycle.
- Avoid unbounded arrays embedded in a document (e.g., appending every event to a user document forever) — documents have a 16MB limit and unbounded growth degrades read/write performance long before that.
- Design the `_id` deliberately when query patterns benefit from it (e.g., a compound natural key) instead of always defaulting to an ObjectId, when it removes the need for a separate lookup field.

## Indexing

- Index every field used in a query's filter, sort, or the equality/range fields of a compound query — an un-indexed query pattern that matters in production is a query pattern that needs an index, not an exception.
- Order compound index fields as: equality fields first, then sort fields, then range fields (ESR rule) — the wrong order silently prevents the index from being used efficiently.
- Use `explain()` to confirm a query is actually using the intended index (`IXSCAN`) rather than falling back to a full collection scan (`COLLSCAN`).

## Aggregation pipelines

- Put `$match` and `$sort` stages as early as possible in the pipeline so subsequent stages operate on a smaller, already-filtered set.
- Use `$project`/`$unset` to drop fields you don't need before expensive stages like `$lookup` or `$group`, reducing the data volume flowing through the rest of the pipeline.
- Prefer `$lookup` sparingly and index the foreign field it joins on — it's MongoDB's closest analog to a relational join, and it isn't free.

## Writes & consistency

- Use write concern and read preference settings deliberately based on the operation's durability/consistency needs — the defaults are reasonable for most apps but not for every operation (e.g., financial writes may need `majority` write concern explicitly).
- Wrap multi-document operations that must succeed or fail together in a transaction (available on replica sets) rather than assuming atomicity that only exists at the single-document level.

## Common pitfalls

- Don't reach for MongoDB because "schema-less" sounds easier — a genuinely relational, highly interconnected domain is usually still a better fit for a relational database.
