---
name: mcp-server-builder
description: Use when building a new MCP (Model Context Protocol) server to expose an external API or tool to an agent. Covers tool design, schema definitions, and common pitfalls.
---

# MCP Server Builder

## Designing tools

- Each tool should map to one clear capability with a name and description an LLM can act on without extra context — "search_issues" not "doThing".
- Write the tool's `description` as documentation aimed at the model: state what it does, when to use it, what it returns, and any preconditions (auth, required fields).
- Keep input schemas minimal and typed; avoid free-form "options" blobs that force the model to guess valid values — use enums where the API has a fixed set of choices.
- Prefer several focused tools over one mega-tool with a `mode` parameter that branches into unrelated behavior.

## Implementation

- Validate and sanitize all tool inputs server-side — never trust that the model produced well-formed arguments, especially for anything that touches a filesystem, shell, or database.
- Return structured, LLM-readable errors ("rate limited, retry after 30s" beats a bare stack trace) so the calling agent can react sensibly.
- Paginate list-style responses by default; an unbounded "list all records" tool will blow out context on a large account.
- Keep tool calls idempotent where possible, or clearly document side effects (creates, deletes) in the description so an agent doesn't call them speculatively.

## Testing

- Exercise each tool directly (not just through a model) to confirm the schema round-trips correctly.
- Test with an actual agent loop before shipping — descriptions that read fine to a human sometimes fail to trigger correct tool selection from a model.

## Security

- Scope credentials to the minimum needed; never let a generic "run arbitrary query" tool exist without strict allow-listing if the server has any external-facing exposure.
