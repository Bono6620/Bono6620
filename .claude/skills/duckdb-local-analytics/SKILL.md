---
name: duckdb-local-analytics
description: Use when analyzing local files (CSV/Parquet/JSON) or embedding fast analytical SQL in an application with DuckDB — a fast in-process alternative to pandas for tabular analysis.
---

# DuckDB for Local Analytics

## When to reach for it

- Use DuckDB instead of pandas when a dataset is larger than comfortably fits in memory as a DataFrame, or when the task is naturally expressed as SQL (joins, window functions, aggregations) rather than imperative row-by-row logic.
- Use it for ad hoc analysis directly over files on disk (`SELECT * FROM 'data.parquet'`) without an ETL step into a database first — DuckDB reads CSV/Parquet/JSON natively and efficiently.
- Embed it in an application (via the Python/Node/etc. client) when the app needs fast local analytical queries without standing up a separate database server.

## Querying files directly

- Query Parquet/CSV files directly with glob patterns (`'data/*.parquet'`) to analyze many files as one logical table, instead of manually concatenating them first.
- Let DuckDB infer schema from the file, but verify inferred types on ambiguous columns (dates, mixed numeric/string) — auto-detection is good but not infallible on messy real-world data.
- Push filters into the file scan (`WHERE` clauses on partition-relevant columns) so DuckDB can skip irrelevant row groups/files rather than reading everything into memory first.

## Performance

- Use `EXPLAIN ANALYZE` to see where time is actually spent in a slow query before assuming a rewrite is needed.
- For repeated queries over the same large dataset, materialize an intermediate DuckDB table or persist to a `.duckdb` file rather than re-scanning raw files on every run.
- DuckDB parallelizes automatically across available cores for most operations — avoid manually chunking work that DuckDB would already parallelize internally.

## Interop

- Query pandas/Polars DataFrames directly by name in SQL (`SELECT * FROM df`) without an explicit conversion step — DuckDB integrates with the Python data ecosystem natively.
- Export results back to Parquet/CSV/Arrow directly from a query (`COPY (SELECT ...) TO 'out.parquet'`) instead of round-tripping through a DataFrame when the destination is just another file.

## Common pitfalls

- Don't assume DuckDB behaves like a full multi-user production OLTP database — it's an embedded analytical engine, single-process by design; use a client-server database when genuine concurrent multi-writer access is required.
