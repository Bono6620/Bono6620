---
name: postgres-best-practices
description: Use when designing schemas, writing queries, adding migrations, or diagnosing performance issues on a Postgres database (including managed variants like Supabase, Neon, RDS). Covers indexing, migrations, and query hygiene.
---

# PostgreSQL Best Practices

## Schema design

- Use the narrowest correct type (`int` vs `bigint`, `text` vs `varchar(n)` — prefer `text` unless a hard length limit is a real business rule).
- Add `NOT NULL` and foreign key constraints wherever the domain guarantees them — don't rely on application code alone.
- Use `timestamptz`, never bare `timestamp`, for anything that crosses time zones.

## Migrations

- Every migration should be reversible or explicitly documented as one-way.
- Adding a `NOT NULL` column to a large, live table: add it nullable, backfill in batches, then add the constraint — a single `ALTER TABLE ... NOT NULL` on a huge table can hold a long lock.
- Wrap DDL changes in a transaction where Postgres allows it (most DDL does, with the notable exception of some concurrent index operations).
- Use `CREATE INDEX CONCURRENTLY` on production tables to avoid blocking writes.

## Query hygiene

- Reach for `EXPLAIN (ANALYZE, BUFFERS)` before guessing at a slow-query fix.
- Index foreign key columns explicitly — Postgres does not do this automatically.
- Avoid `SELECT *` in application code paths that matter for performance; fetch only needed columns.
- Watch for N+1 query patterns from ORMs; prefer a single joined/batched query.

## Managed-Postgres specifics (Supabase/Neon/RDS)

- Respect connection limits — use a pooler (PgBouncer, Supabase pooler, Neon's built-in pooling) for serverless/edge functions that open many short-lived connections.
- Row-Level Security (RLS) policies, where used (e.g., Supabase), should be tested with a non-superuser role — RLS is bypassed for superusers/`service_role`.
- Be deliberate about branching/preview databases (Neon branches, Supabase preview) — verify migrations against a branch before promoting to the primary.
