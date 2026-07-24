---
name: graphql-api-design
description: Use when designing, implementing, or reviewing a GraphQL schema/server (Apollo Server, Apollo Client, or federated schemas) — schema design, resolvers, and performance pitfalls.
---

# GraphQL API Design

## Schema design

- Design the schema around what clients need to ask, not around the database's table structure — a GraphQL schema is a client-facing contract, not a 1:1 mirror of storage.
- Use non-null (`!`) deliberately: a field should be non-null only when it's genuinely guaranteed by the resolver, since a null violation on a non-null field nulls out the entire parent object up the tree.
- Prefer specific, well-named types and enums over generic `String`/`JSON` fields — it's what makes GraphQL's introspection and tooling valuable.
- Version by evolving the schema additively (new fields, deprecating old ones with `@deprecated`) rather than breaking changes; GraphQL has no URL versioning escape hatch.

## Resolvers & performance

- Batch and cache resolver-level data fetching with a per-request loader (DataLoader pattern) — without it, a nested query naturally produces N+1 database calls.
- Keep resolvers thin: business logic belongs in a service layer the resolver calls, not inline in the resolver function, so it's testable independent of the GraphQL layer.
- Guard query depth/complexity (`graphql-depth-limit`, cost analysis) on any publicly exposed schema — an unbounded nested query is a denial-of-service vector.

## Federation (Apollo Federation / subgraphs)

- Keep each subgraph owning a clear entity boundary; use `@key` directives to define how entities are referenced across subgraphs rather than duplicating full entity data in each service.
- Avoid circular entity references between subgraphs — they complicate the query planner and often signal the domain boundary is drawn wrong.

## Client-side (Apollo Client)

- Normalize the cache correctly by ensuring every type with an `id`/`_id` field is fetched consistently — inconsistent field selection across queries causes stale or duplicated cache entries.
- Use fragments to keep the fields a component needs colocated with that component, instead of one large query at the page root.

## Errors

- Return partial data with an `errors` array for field-level failures rather than failing the whole request, when the client can still use the successful fields.
- Use distinguishable error codes/extensions (not just message strings) so clients can branch on error type reliably.
