---
name: nextjs-best-practices
description: Use when building, reviewing, or upgrading a Next.js application — routing, data fetching, caching, and rendering-mode decisions (App Router, Server Components, Server Actions).
---

# Next.js Best Practices

## Rendering & data fetching

- Default to Server Components; only mark a component `"use client"` when it needs interactivity, browser APIs, or hooks like `useState`/`useEffect`.
- Push `"use client"` as far down the tree as possible — wrap just the interactive leaf, not its whole parent tree, to keep server-rendered HTML and bundle size down.
- Fetch data where it's needed (co-located in the Server Component that renders it) rather than fetching once at the top and prop-drilling.
- Use Server Actions for mutations instead of hand-rolled API routes when the action is only ever called from your own UI.

## Caching

- Understand the four Next.js caches (Request Memoization, Data Cache, Full Route Cache, Router Cache) before reaching for `revalidatePath`/`revalidateTag` — misdiagnosing which cache is stale leads to over-aggressive `no-store` usage that kills performance.
- Prefer `fetch` with explicit `cache`/`next.revalidate` options over disabling caching wholesale.
- Use `revalidateTag` for targeted invalidation after a mutation rather than revalidating whole routes.

## Routing & structure

- Colocate route-specific components under their route segment; keep genuinely shared components in a top-level `components/` directory.
- Use route groups `(group)` to organize without affecting the URL, and parallel/intercepting routes only when the UI genuinely needs them (modals over a route, dashboards with independent panes).

## Upgrades

- Read the codemods list before a major-version upgrade (`npx @next/codemod`) — many breaking changes have an automated migration.
- After upgrading, check middleware, `next.config.js` options, and any custom server code first — these are the most common breakage points.
