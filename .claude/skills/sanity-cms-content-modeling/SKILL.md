---
name: sanity-cms-content-modeling
description: Use when modeling content, writing GROQ queries, or structuring a Sanity Studio schema for a headless CMS project.
---

# Sanity CMS Content Modeling

## Schema design

- Model content types around how editors think about content, not around how the frontend happens to render it today — a schema tightly coupled to one frontend layout breaks the moment design changes.
- Use references for reusable/shared content (authors, categories, product SKUs) instead of duplicating the data inline on every document that needs it.
- Use `portable text` for rich content bodies rather than plain markdown/HTML strings — it stays structured, supports custom block types, and renders safely without an HTML-injection surface.

## GROQ queries

- Project only the fields the frontend actually needs (`{title, slug, "author": author->name}`) rather than fetching whole documents — it keeps payloads small and avoids leaking internal-only fields.
- Use `->` dereferencing directly in the query to resolve references server-side instead of round-tripping multiple queries client-side.
- Test complex GROQ queries in the Vision tool before wiring them into application code — a malformed projection silently returns `null` fields rather than erroring.

## Studio configuration

- Keep field-level validation (`Rule.required()`, custom validators) in the schema itself so bad data is caught at authoring time, not discovered downstream in the frontend.
- Use structure builder customization to group content logically for editors (by section, by content type) rather than leaving the default flat document list for a large schema.

## Content experimentation & SEO

- Model SEO-relevant fields (meta title/description, canonical, structured data hints) as a reusable object type included on every page-level document, rather than ad hoc per-type fields.
- Version and preview draft content via Sanity's draft/published states before an editor publishes — validate the preview matches the intended live rendering, especially for portable-text custom blocks.

## Common pitfalls

- Don't over-nest objects many levels deep in the schema — it makes both the Studio editing UI and GROQ projections harder to work with; prefer references once nesting gets deep.
