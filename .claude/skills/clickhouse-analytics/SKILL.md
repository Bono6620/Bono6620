---
name: clickhouse-analytics
description: Use when designing tables, writing queries, or tuning performance in ClickHouse for analytical/OLAP workloads.
---

# ClickHouse for Analytics

## Table design

- Choose the `ORDER BY` (sorting key) around the columns most queries filter or aggregate on — it's the single biggest lever for query speed in ClickHouse's MergeTree family, far more than indexes are in row-oriented databases.
- Use `PARTITION BY` on a coarse time unit (month, week) for time-series data so old partitions can be dropped/moved cheaply, but avoid partitioning too finely — thousands of tiny partitions hurt performance.
- Pick the narrowest correct column type (`UInt32` vs `UInt64`, `LowCardinality(String)` for repetitive strings) — ClickHouse's columnar compression benefits enormously from tight typing.

## Query patterns

- Filter and aggregate; avoid row-by-row lookups — ClickHouse is built for scanning and aggregating large columnar ranges, not for point lookups by arbitrary ID (use a different store for that access pattern).
- Use `GROUP BY` with `LIMIT` and approximate functions (`uniq`, `quantile`) when exact precision isn't required — they're dramatically faster than exact equivalents at scale.
- Materialized views are the standard way to pre-aggregate on ingest for dashboards that need consistent low-latency reads — don't recompute the same aggregation from raw rows on every dashboard load.

## Ingestion

- Batch inserts (thousands of rows per insert, not one row at a time) — ClickHouse's MergeTree engine is optimized for bulk writes and penalizes high-frequency single-row inserts.
- Use the `Buffer` engine or an ingestion queue in front of ClickHouse when the source produces a high rate of small writes that can't be batched at the source.

## Operations

- Monitor merge and mutation activity — ClickHouse's background merges are core to how it stays fast, and a backlog of unmerged parts degrades query performance over time.
- Use `EXPLAIN` to check whether a query is actually using the primary key/sorting key as expected before assuming a slow query needs a schema change.

## Common pitfalls

- Don't model ClickHouse tables like a normalized OLTP schema with many small joined tables — flatten/denormalize for analytical read patterns; joins are supported but are not ClickHouse's strongest use case at scale.
