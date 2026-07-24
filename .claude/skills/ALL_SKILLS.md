# All Claude Code Skills — Combined

Single-file export of every skill in this collection. Each section below is one skill's full `SKILL.md` content.

Generated from `.claude/skills/` — see `README.md` in that folder for the categorized index.

---


<!-- ============================================================ -->
<!-- Skill: ai-image-video-generation -->
<!-- ============================================================ -->

---
name: ai-image-video-generation
description: Use when generating or editing images/video with AI models (text-to-image, image editing, upscaling, lip-sync, text-to-video) via an API-based generation platform.
---

# AI Image & Video Generation

## Prompting

- Be concrete and specific: describe subject, composition, lighting, style, and camera framing explicitly rather than relying on vague adjectives — specificity is what actually steers these models.
- Iterate in small steps: change one variable (style, composition, subject detail) between generations so you can tell what caused a given change in output.
- For image editing (inpainting/outpainting/style transfer), a tightly scoped mask and instruction produces more predictable results than an open-ended "make it better" prompt.

## Model selection

- Match the model to the task: some models are tuned for photorealism, others for stylized/illustrative output, others specifically for fast iteration vs. final quality — check a platform's model catalog rather than defaulting to whichever model is listed first.
- For video, check whether the model does text-to-video, image-to-video (animating a still), or lip-sync/talking-head specifically — these are different capabilities even when marketed under one "video generation" umbrella.

## Workflow

- Generate at lower resolution/fewer steps first to validate composition and prompt direction, then commit to full-quality generation only once the direction is right — full-res generation is the expensive step.
- Chain specialized models when a single model can't do the whole job (e.g., generate a base image, then a dedicated upscaler, then a dedicated face-restoration pass) rather than expecting one model to excel at every sub-task.
- Save prompts and seeds alongside outputs — reproducibility matters when a client or teammate asks "can we get more like this one."

## Rights & usage

- Check the platform's and underlying model's license/terms for commercial usage rights before using generated output in a commercial product — permissions vary significantly between providers and model licenses.
- Be explicit with clients/stakeholders about which assets are AI-generated when that materially matters for the use case (disclosure requirements vary by platform, jurisdiction, and use case).

## Common pitfalls

- Don't assume a model handles text-in-image (logos, readable signage) reliably — most generation models still render legible text poorly; plan for a manual typography pass if exact text matters.

---


<!-- ============================================================ -->
<!-- Skill: api-security-testing -->
<!-- ============================================================ -->

---
name: api-security-testing
description: Use when testing REST or GraphQL APIs for security issues in an authorized engagement — authentication/authorization flaws, mass assignment, rate limiting, and schema-level exposure.
---

# API Security Testing

Assumes `authorized-security-testing-scope` has been confirmed for this API.

## Mapping the API

- Pull the schema if available (OpenAPI/Swagger spec, GraphQL introspection) — it's the fastest way to enumerate every endpoint/field, including ones not exercised by the frontend.
- If introspection is disabled on GraphQL, note that as a finding-adjacent observation but keep testing via observed queries from the client.
- Diff API versions if multiple are live (`/v1/`, `/v2/`) — older versions often lack fixes applied to the current one.

## Core checks

- **BOLA/IDOR**: for every resource-fetching endpoint, request another user's resource ID with your own token — this is the single most common high-impact API bug.
- **Broken function-level authorization**: try admin-only endpoints/mutations with a low-privilege token.
- **Mass assignment**: send extra fields in a create/update request body (e.g., `"role": "admin"`, `"isVerified": true`) and check if the server accepts them.
- **Rate limiting / resource exhaustion on legitimate endpoints**: confirm limits exist on auth, password-reset, and expensive query endpoints — but only probe within the boundaries authorized in scope, never to the point of actual denial of service.
- **GraphQL-specific**: check for overly permissive query depth/complexity (nested queries as an amplification vector), and whether field-level authorization matches object-level authorization.
- **Excessive data exposure**: compare what the API returns vs. what the frontend actually displays — APIs frequently over-return fields the UI just doesn't render.

## Auth mechanics

- Verify JWTs are validated properly server-side: signature algorithm can't be downgraded (`alg: none`), expiry is enforced, and claims aren't trusted without verification.
- Check API keys/tokens aren't leaking into logs, error messages, or client-side bundles.

## Reporting

- For each finding, include the exact request/response pair (redact real user data) and the minimal set of headers/parameters needed to reproduce it.

---


<!-- ============================================================ -->
<!-- Skill: auth0-identity-integration -->
<!-- ============================================================ -->

---
name: auth0-identity-integration
description: Use when integrating authentication/identity with Auth0 or a similar OAuth2/OIDC identity provider — login flows, token handling, and multi-tenant considerations.
---

# Auth0 / Identity Provider Integration

## Choosing a flow

- Use Authorization Code flow with PKCE for any browser-based app (SPA or server-rendered) and native/mobile apps — it's the current standard and avoids exposing tokens in redirect URLs.
- Use Client Credentials flow only for machine-to-machine communication (service-to-service), never for anything involving an end user's identity.
- Never implement the deprecated Implicit flow or Resource Owner Password flow for new integrations unless a specific legacy constraint genuinely requires it.

## Token handling

- Treat access tokens and refresh tokens as secrets: store them in httpOnly cookies or secure storage, never in `localStorage` where they're exposed to any XSS on the page.
- Validate ID tokens (JWT signature, issuer, audience, expiry) using a library rather than hand-parsing the JWT — signature verification mistakes are a common source of auth bypass bugs.
- Use short-lived access tokens with refresh tokens for renewal, rather than issuing long-lived access tokens to avoid implementing refresh logic.

## Authorization

- Model authorization (roles/permissions) explicitly rather than inferring it from raw claims scattered across the token — use Auth0's Roles/Permissions (or equivalent RBAC feature) and check them server-side on every protected action, never trust client-side role checks as the actual gate.
- For multi-tenant apps, include and verify the tenant/organization claim on every request — a token valid for one tenant must not silently authorize access to another tenant's data.

## Login/logout flow

- Implement logout as a full session termination (clear local session AND call the identity provider's logout endpoint) — clearing only the local cookie leaves an active IdP session that can silently re-authenticate the user.
- Handle the callback route defensively: validate the `state` parameter to prevent CSRF on the auth callback, and handle error query params from the provider gracefully instead of assuming every callback is a success.

## Common pitfalls

- Don't hardcode redirect URIs or client secrets in client-side code; client secrets belong only in server-side environments.
- Test token expiry and refresh behavior explicitly — "login works" during development often hides refresh-flow bugs that only surface after the access token's short lifetime expires in production.

---


<!-- ============================================================ -->
<!-- Skill: authorized-security-testing-scope -->
<!-- ============================================================ -->

---
name: authorized-security-testing-scope
description: Use at the start of ANY security-testing, pentest, or bug-bounty task to confirm authorization and scope before doing anything active. Foundational gate for all other security-testing skills in this collection.
---

# Authorized Security Testing — Scope & Ethics Gate

Every other security-testing skill in this collection assumes this gate has been passed first.

## Before any active testing

1. **Confirm written authorization exists**: a signed pentest engagement letter/rules-of-engagement, or an explicit bug-bounty program scope (on platforms like HackerOne, Bugcrowd, or a vendor's own `security.txt`/VDP page).
2. **Read the scope precisely**: in-scope domains/IPs/apps, explicitly out-of-scope assets, allowed testing types (is active exploitation allowed, or read-only/PoC-level only?), and any rate-limit or blackout-window constraints.
3. **When scope is unclear or the target isn't confirmed to be authorized, stop and ask** rather than guessing — testing an unauthorized target is illegal regardless of intent.

## Hard boundaries (do not cross even inside an engagement)

- No denial-of-service or resource-exhaustion testing unless explicitly authorized in writing.
- No pivoting into or exfiltrating real user data beyond what's needed to prove a finding (use a canary/test account's own data instead).
- No social-engineering or physical-access testing unless it is explicitly part of the signed scope.
- No mass/automated scanning of ranges beyond the authorized scope, even accidentally (double-check CIDR ranges before running a scanner).

## Working within scope

- Prefer the least invasive technique that still proves the vulnerability (e.g., a benign header injection PoC over an actual data exfiltration).
- Log what you tested and when — engagement notes double as your report draft and as evidence you stayed in scope.
- If a test reveals something clearly out of scope or looks like it could affect production availability, pause and flag it to the engagement contact before continuing.

---


<!-- ============================================================ -->
<!-- Skill: bug-bounty-methodology -->
<!-- ============================================================ -->

---
name: bug-bounty-methodology
description: Use when hunting on an authorized bug-bounty program (HackerOne, Bugcrowd, or a vendor's own program) — target selection, reconnaissance workflow, and turning a finding into a submittable report.
---

# Bug Bounty Methodology

Assumes the `authorized-security-testing-scope` gate has already been passed for the specific program being worked.

## Target selection

- Read the program's scope and past disclosed reports (many programs publish resolved reports) to learn what's already been found and what testing style the triager rewards.
- Favor newer or recently-changed assets (new subdomains, recent app releases) — well-trodden endpoints are more likely already picked clean.
- Note the program's reward/severity matrix before investing time — it tells you what classes of bug are actually valued.

## Reconnaissance

- Enumerate subdomains and live hosts within scope (passive sources first: certificate transparency logs, DNS records, search-engine dorking; active scanning only where the scope allows it).
- Fingerprint technology stack (frameworks, CMS, JS libraries with known versions) to prioritize which vulnerability classes are plausible.
- Map the application's functionality by hand (crawl authenticated flows, note every parameter, every state-changing action) — automated crawlers miss most authenticated, multi-step flows.

## Testing

- Work one vulnerability class at a time (e.g., IDOR across all endpoints, then auth/session issues, then injection) rather than randomly poking — it's easier to be thorough and to write up cleanly.
- For each candidate finding, isolate the minimal reproduction — the fewer steps and parameters involved, the more credible and faster-triaged the report.
- Confirm real impact before reporting: a theoretical issue with no demonstrable impact is usually informative-only or gets closed as not-applicable.

## Reporting

- Structure: summary, impact, steps to reproduce (numbered, minimal), proof (screenshot/request-response pair with sensitive data redacted), suggested remediation.
- Report one vulnerability per submission unless they're trivially the same root cause.
- Never test or report on a duplicate without checking the program's existing disclosed reports first.

---


<!-- ============================================================ -->
<!-- Skill: clickhouse-analytics -->
<!-- ============================================================ -->

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

---


<!-- ============================================================ -->
<!-- Skill: cloudflare-workers -->
<!-- ============================================================ -->

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

---


<!-- ============================================================ -->
<!-- Skill: ctf-challenge-solving -->
<!-- ============================================================ -->

---
name: ctf-challenge-solving
description: Use when solving CTF (Capture The Flag) challenges — approaching web, pwn/binary exploitation, crypto, forensics, and reverse-engineering categories systematically.
---

# CTF Challenge Solving

CTFs are designed, sandboxed challenges — the techniques here apply only to CTF/lab environments or explicitly authorized targets, never to systems without permission.

## General approach

- Read the challenge description and hints fully before touching tools — CTF authors often embed the exact technique or a red herring in the wording.
- Identify the category first (web, pwn, crypto, forensics, rev, misc) — each has a different starting toolkit and mindset.
- Start with the obvious/cheap checks (`file`, `strings`, viewing page source, checking HTTP headers) before reaching for heavier tools.

## Web challenges

- Check for the usual suspects fast: source comments, hidden form fields, cookies/JWT contents, robots.txt, exposed `.git`/`.env` files, and predictable admin routes.
- If auth is involved, test both injection (SQLi, template injection) and logic flaws (IDOR, weak session tokens) — CTF web challenges lean heavily on one specific bug class per challenge.

## Binary exploitation (pwn)

- Identify protections first (`checksec`: NX, ASLR, PIE, canary) — this determines which exploitation technique is even viable.
- Work from a disassembler/decompiler (Ghidra, IDA) to understand the vulnerable function before attempting an exploit; don't fuzz blind unless the challenge is explicitly about fuzzing.
- Build the exploit incrementally in a scripting harness (commonly `pwntools`) — confirm each primitive (leak, overwrite) works before chaining the next stage.

## Crypto

- Identify the scheme first (classical cipher, RSA misuse, weak PRNG, hash-length-extension, etc.) — most crypto challenges hinge on a known misuse pattern rather than breaking the underlying primitive.
- Check for textbook implementation mistakes: small/reused RSA exponents, reused nonces, predictable seeds.

## Forensics & reverse engineering

- For forensics: `binwalk`/`exiftool`/`foremost` for embedded data, and don't skip the metadata — flags hide in EXIF, file slack space, and steganography as often as in visible content.
- For rev: start with static analysis (strings, imports, control-flow graph) before dynamic (debugger/tracing); note any anti-debugging checks before running under a debugger.

## Discipline

- Keep notes on what's been tried — CTFs reward not re-testing the same dead end twice under time pressure.

---


<!-- ============================================================ -->
<!-- Skill: duckdb-local-analytics -->
<!-- ============================================================ -->

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

---


<!-- ============================================================ -->
<!-- Skill: expo-react-native -->
<!-- ============================================================ -->

---
name: expo-react-native
description: Use when building, configuring, or deploying a React Native app with Expo — navigation, native modules, EAS builds, and OTA updates.
---

# Expo / React Native

## Project setup

- Use the latest Expo SDK unless the project pins an older one; check `app.json`/`app.config.ts` for the current SDK version before adding native dependencies.
- Prefer Expo Router (file-based routing) for new projects; it maps directly to navigation structure and avoids hand-wired navigator boilerplate.
- Keep native-only code behind `Platform.OS` checks or platform-specific file extensions (`.ios.tsx`/`.android.tsx`) rather than runtime branching scattered through shared components.

## Native modules & config

- Check Expo's module compatibility table before adding a third-party native package — a package without an Expo config plugin usually means ejecting to a bare workflow or using `expo-dev-client`.
- Any native module requiring custom native code needs `expo-dev-client` (a custom dev build) — Expo Go alone can't load unlisted native modules.
- Keep permissions declarations (camera, location, etc.) in `app.json`/`app.config.ts` under the correct platform key; a missing permission string causes silent failures or store rejections, not crashes.

## Builds & deployment

- Use EAS Build for production binaries; configure build profiles (`development`, `preview`, `production`) in `eas.json` rather than one-off local flags.
- Use EAS Update for JS-only OTA updates — but remember native code changes (new native modules, SDK upgrades) always require a fresh store build, OTA can't ship those.
- Test a release build (not just Expo Go / dev client) before submitting — dev-only warnings and Metro's fast refresh can mask real production behavior.

## Common pitfalls

- Don't assume Expo Go supports every package — always verify against the current SDK's compatibility list first.
- Version-lock Expo SDK and React Native together; upgrading one without the other's SDK-matched dependencies via `expo install` causes native-JS mismatches.

---


<!-- ============================================================ -->
<!-- Skill: figma-to-code -->
<!-- ============================================================ -->

---
name: figma-to-code
description: Use when converting a Figma design into production frontend code, or connecting existing components back to a Figma design system (Code Connect). Covers reading design specs and translating them faithfully.
---

# Figma to Code

## Reading the design

- Extract the actual values (spacing, colors, font sizes/weights, corner radii) rather than eyeballing — use exact tokens where the file defines them.
- Identify reusable components and variants in the Figma file first; map each to an existing component in the codebase before creating a new one, to avoid duplicate implementations.
- Note responsive behavior (auto-layout resizing, breakpoints) — a static frame doesn't tell you how the component should behave at other widths unless auto-layout constraints are set.

## Implementing

- Match the design system's existing primitives (spacing scale, color tokens, typography scale) instead of hardcoding pixel values pulled straight from Figma — a "17px" gap in the design usually maps to the nearest scale token.
- Preserve semantic HTML structure even when the design is purely visual — headings, buttons, and landmarks matter for accessibility regardless of what the design file shows.
- Implement interactive states (hover, focus, active, disabled, loading) even when the design only shows the default state — infer them from the design system's conventions.

## Code Connect (linking components to Figma)

- Map each Figma component variant to the corresponding code component prop, so designers see the real implementation's prop names in Figma's dev mode.
- Keep the mapping file next to the component it documents, and update it in the same PR that changes the component's public API.

## Verification

- Compare the rendered result against the Figma frame at the same viewport width before calling it done — check spacing and alignment, not just "it looks roughly right."

---


<!-- ============================================================ -->
<!-- Skill: firebase-backend-integration -->
<!-- ============================================================ -->

---
name: firebase-backend-integration
description: Use when integrating Firebase as a backend — Firestore/Realtime Database data modeling, security rules, Auth, and Cloud Functions.
---

# Firebase Backend Integration

## Firestore data modeling

- Model collections around query patterns, not around a relational mental model — Firestore has no server-side joins, so denormalize data that needs to be read together in one query.
- Keep documents well under the 1MB limit and avoid unbounded arrays/maps that grow forever; use subcollections for one-to-many relationships that can grow large.
- Design collection group queries deliberately when the same subcollection name repeats across parents (e.g., `reviews` under many `products`) — it's the mechanism for querying "all reviews everywhere."

## Security rules

- Write security rules as the actual authorization boundary, not client-side checks — client code can always be bypassed, so every read/write path must be enforced in `firestore.rules`/`database.rules.json`.
- Test rules with the Firebase emulator and the rules unit-testing library before deploying — a rules bug is a data-exposure bug, not just a UX bug.
- Scope rules as tightly as the data model allows (per-document ownership checks via `request.auth.uid`) rather than broad `if true` allowances "to make it work for now."

## Authentication

- Use Firebase Auth's built-in providers where they fit rather than hand-rolling custom auth flows; enable App Check for production apps to reduce abuse from non-genuine clients.
- Verify ID tokens server-side (Cloud Functions/Admin SDK) for any privileged operation — never trust a client-supplied UID without verifying the token that produced it.

## Cloud Functions

- Keep functions small and single-purpose; use Firestore triggers for reactive data processing (e.g., updating a denormalized counter) rather than polling.
- Set explicit memory/timeout configuration matched to the function's actual workload — defaults are not tuned for every use case, and under-provisioning causes silent timeouts on heavier functions.
- Guard against trigger recursion (a function that writes to the same collection/field that triggered it) with a condition check, since an unguarded loop can run indefinitely and rack up cost.

## Common pitfalls

- Don't treat Firestore reads/writes as free — every read/write/delete is billed individually, so a query pattern that re-reads a full collection repeatedly should be reconsidered for indexed, filtered queries or caching.

---


<!-- ============================================================ -->
<!-- Skill: flutter-mobile-development -->
<!-- ============================================================ -->

---
name: flutter-mobile-development
description: Use when building or reviewing a Flutter app — widget structure, state management, and platform-specific considerations for iOS/Android.
---

# Flutter Mobile Development

## Widget structure

- Split large `build()` methods into smaller private widgets rather than one deeply nested tree — it improves readability and lets Flutter's rebuild optimization skip unaffected subtrees.
- Prefer `const` constructors wherever a widget's inputs are compile-time constant — it's a real performance win since Flutter can skip rebuilding const subtrees entirely.
- Separate presentation widgets from logic: keep business logic in a state-management layer (Provider, Riverpod, Bloc) rather than inline in widget build methods.

## State management

- Choose a single state-management approach for the project and use it consistently — mixing several ad hoc (setState here, a singleton there, a stream elsewhere) makes state flow hard to reason about.
- Scope state to the narrowest widget subtree that needs it; a value used by one screen doesn't belong in a global app-wide store.
- Keep async state (loading/error/data) modeled explicitly (e.g., a sealed class or enum with associated data) rather than juggling separate booleans that can drift out of sync.

## Platform considerations

- Test on both iOS and Android throughout development, not just at the end — platform-specific widgets, safe-area insets, and permission flows genuinely differ.
- Use `MediaQuery`/`LayoutBuilder` for responsive layouts instead of hardcoded pixel dimensions, so the UI adapts across phone/tablet form factors.
- Handle platform permission flows (camera, location, notifications) explicitly per platform — the request UX and required manifest/plist entries differ between Android and iOS.

## Performance

- Profile with Flutter DevTools before optimizing blindly — jank is often caused by a specific expensive widget rebuild or an unbounded list without `ListView.builder`'s lazy construction, not by "Flutter being slow" in general.
- Use `ListView.builder`/`GridView.builder` for any list with more than a handful of items instead of building the full list eagerly.

## Common pitfalls

- Don't call `setState` inside a build method or in response to every minor state change on a widget with expensive children — batch related state updates and scope rebuilds narrowly.

---


<!-- ============================================================ -->
<!-- Skill: git-commit-workflow -->
<!-- ============================================================ -->

---
name: git-commit-workflow
description: Use when the user asks to stage, commit, push changes, or open a pull request in one go ("commit and push this", "yeet this branch up", "open a PR for these changes"). Covers safe staging, commit-message conventions, and PR creation.
---

# Git Commit & PR Workflow

A safe, repeatable flow for turning working-tree changes into a pushed commit and an open pull request.

## Steps

1. **Inspect before touching anything**
   - `git status` — see untracked and modified files.
   - `git diff` — review unstaged changes; `git diff --staged` for staged ones.
   - `git log --oneline -10` — match the repo's existing commit-message style.

2. **Stage deliberately**
   - Add files by explicit path (`git add path/to/file`), never `git add -A` or `git add .` blindly — it can sweep in `.env`, credentials, or build artifacts.
   - Re-run `git status` after staging to confirm exactly what will be committed.

3. **Write the commit message**
   - Summarize the *why*, not a restatement of the diff.
   - Keep the subject line under ~70 characters; use the body for detail if needed.
   - Match the repo's convention (Conventional Commits, plain imperative, etc.) as seen in `git log`.

4. **Commit and push**
   - Create a new commit rather than amending, unless explicitly asked to amend.
   - `git push -u origin <branch>` on first push of a branch.
   - Never force-push over shared history without explicit confirmation.

5. **Open the pull request**
   - Check for a PR template (`.github/pull_request_template.md` or similar) and mirror its sections.
   - Write a summary of *what changed and why*, plus a short test plan.
   - Open as draft unless told otherwise.

## Guardrails

- Never skip hooks (`--no-verify`) or bypass signing to force a commit through — fix the underlying failure instead.
- Never commit files that look like secrets (`.env`, `*.pem`, `credentials.json`) without explicit confirmation.
- If a pre-commit hook fails after a commit attempt, the commit did not happen — fix the issue and make a fresh commit, don't `--amend`.

---


<!-- ============================================================ -->
<!-- Skill: graphql-api-design -->
<!-- ============================================================ -->

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

---


<!-- ============================================================ -->
<!-- Skill: gsap-web-animation -->
<!-- ============================================================ -->

---
name: gsap-web-animation
description: Use when building web animations with GSAP — timelines, scroll-triggered animation, and performance-conscious motion design.
---

# GSAP Web Animation

## Timelines

- Use `gsap.timeline()` to sequence related animations instead of chaining manual `delay` values on separate tweens — timelines keep sequencing readable and let you scrub/reverse/control the whole sequence as one unit.
- Label key points in a timeline (`tl.addLabel('reveal')`) when other code needs to seek to or sync with a specific point, rather than hardcoding time offsets that break when durations change.
- Use timeline defaults (`{defaults: {ease, duration}}`) to keep a consistent motion feel across a sequence instead of repeating the same easing/duration on every tween.

## Performance

- Animate `transform` and `opacity` wherever possible — GPU-accelerated properties keep animation smooth, while animating layout-triggering properties (`width`, `top`, `left`) forces expensive reflows.
- Use `will-change` sparingly and only on elements actually mid-animation; leaving it set permanently on many elements can hurt performance rather than help it.
- Kill or pause tweens/ScrollTriggers on unmounted components (in a React/Vue/etc. app) — orphaned GSAP instances continue running and leak memory.

## ScrollTrigger

- Set `start`/`end` values relative to meaningful viewport/element positions (`top 80%`, `bottom top`) rather than pixel values that break at other viewport sizes.
- Use `scrub: true` for animations that should track scroll position directly, and plain triggers (`toggleActions`) for animations that should play once when entering view — don't reach for scrub when a one-shot reveal is what's actually wanted.
- Call `ScrollTrigger.refresh()` after any dynamic layout change (content loading, image loading affecting height) so trigger positions stay accurate.

## Accessibility

- Respect `prefers-reduced-motion`: check the media query and skip or simplify non-essential animation for users who've opted out, rather than forcing motion on everyone.
- Ensure animated content remains reachable/readable without JavaScript or with animation disabled — GSAP should enhance content, not gate access to it.

## Common pitfalls

- Don't animate the same property with both CSS transitions and GSAP simultaneously — they fight for control of the property and produce inconsistent results.

---


<!-- ============================================================ -->
<!-- Skill: huggingface-ml-workflows -->
<!-- ============================================================ -->

---
name: huggingface-ml-workflows
description: Use when working with Hugging Face Hub, datasets, model training/fine-tuning, or deploying models via Spaces/Inference. Covers the practical workflow, not ML theory.
---

# Hugging Face ML Workflows

## Hub basics

- Use the `huggingface_hub` Python library or `hf` CLI for programmatic upload/download rather than manual web UI steps when the task is repeatable.
- Pin model/dataset revisions (a commit hash or tag) in production code — the default branch can change under you if the upstream repo updates.
- Check a model's license and intended-use section on its model card before deploying it — permissive hosting doesn't imply permissive commercial use.

## Datasets

- Use `datasets.load_dataset` with streaming (`streaming=True`) for large datasets that don't fit in memory, rather than downloading the full set first.
- Version and document any preprocessing applied before training — a dataset card should say exactly what transforms were applied so results are reproducible.
- Split data before any exploratory analysis that could leak information from the eventual test set.

## Fine-tuning / training

- Start from the smallest model that could plausibly solve the task before reaching for a larger one — iteration speed matters more early on than final accuracy.
- Use `Trainer`/TRL abstractions for standard fine-tuning loops instead of hand-rolling training loops, unless the task genuinely needs custom loss/logging logic.
- Log training runs (loss curves, eval metrics, hyperparameters) somewhere queryable (Trackio, Weights & Biases, or even a structured CSV) — "it trained" isn't a result without the curve.
- Evaluate on a held-out set with the same metric the task will actually be judged on, not just training loss.

## Deployment

- Use Spaces (Gradio/Streamlit) for a quick interactive demo; use Inference Endpoints or a dedicated serving stack (vLLM, TGI) for production-grade throughput/latency needs — Spaces free tier is not meant for production traffic.
- Quantize or distill before deploying to resource-constrained environments; benchmark actual latency/memory on the target hardware, not just parameter count.

## Common pitfalls

- Don't assume a model card's benchmark numbers transfer to your specific domain/data distribution — validate on your own held-out data.
- Watch context-length and tokenizer mismatches when swapping a base model — a fine-tune trained with one tokenizer will silently misbehave with another.

---


<!-- ============================================================ -->
<!-- Skill: linear-project-management -->
<!-- ============================================================ -->

---
name: linear-project-management
description: Use when managing issues, projects, or workflows in Linear — issue structuring, cycle planning, and API/automation usage.
---

# Linear Project Management

## Issue structuring

- Write issue titles as a specific, actionable statement of the change ("Fix pagination off-by-one on orders list") rather than a vague topic ("Pagination bug") — a searchable, specific title pays off every time someone triages later.
- Keep one issue per distinct piece of work; a ticket bundling several unrelated changes can't be prioritized, estimated, or closed cleanly.
- Use sub-issues for genuinely decomposable work (a large feature broken into implementation steps), not as a substitute for a project when the work spans multiple independent issues over time.

## Labels & workflow

- Keep the label taxonomy small and orthogonal (type, area, priority as separate label groups) rather than one long flat list — an overloaded label system stops getting used consistently.
- Match workflow states to how work actually moves (Backlog → Todo → In Progress → In Review → Done) and resist adding custom states that don't represent a genuinely distinct stage — extra states fragment reporting without adding clarity.

## Cycles & planning

- Size cycle scope to the team's actual historical throughput, not to aspirational capacity — a cycle that consistently overflows erodes the planning signal cycles are meant to provide.
- Leave a portion of cycle capacity unplanned for triage/support/bugs unless the team's work is genuinely 100% plannable — an always-fully-packed cycle means every interrupt becomes an overrun.

## Projects

- Use Projects for outcomes with a defined scope and target date spanning multiple issues/cycles; use a simple issue for anything smaller — not every piece of work needs project-level overhead.
- Keep a project's description current with the actual goal and status summary — a stale project description is worse than none, since people trust it as source of truth.

## API & automation

- Use the GraphQL API (or an SDK wrapping it) for programmatic issue creation/updates from other tools (support tickets, error monitoring) rather than manual copy-paste triage.
- Scope API tokens/webhooks to the minimum team/workspace access needed, and validate webhook payloads before acting on them in automation.

---


<!-- ============================================================ -->
<!-- Skill: marketing-copywriting -->
<!-- ============================================================ -->

---
name: marketing-copywriting
description: Use when writing or reviewing marketing copy — landing pages, ads, emails, and positioning. Covers structure and persuasion principles, not brand voice (which comes from the project).
---

# Marketing Copywriting

## Before writing

- Identify the single audience and the single action the copy should drive — copy trying to speak to everyone and do everything ends up persuading no one.
- Lead with the reader's problem or desired outcome, not the product's feature list — people act on what a thing does for them, not on what it technically contains.
- Know the awareness level of the audience (unaware of the problem, aware of the problem but not the solution, aware of solutions but not this one, ready to buy) — the right opening line differs completely by stage.

## Structure

- Headlines should state a specific, believable benefit or provoke genuine curiosity — vague cleverness reads well internally and converts poorly.
- Use the reader's own language (pulled from reviews, support tickets, sales calls) rather than internal jargon — copy that mirrors how the audience already describes their problem lands as more credible.
- One core message per piece of copy; supporting points should reinforce it, not compete with it for attention.

## Persuasion mechanics

- Make the mechanism believable: explain *why* the product works, not just *that* it works — a vague claim ("boosts productivity") converts worse than a specific, plausible mechanism ("cuts a 3-step approval into 1 click").
- Address the most likely objection directly rather than hoping the reader won't think of it — an unaddressed objection is a silent reason not to convert.
- Use specific numbers and concrete details over generic superlatives ("saves 4 hours a week" beats "saves you time").

## Calls to action

- Make the CTA describe the actual next step and its value ("Start your free 14-day trial" beats "Submit"), and keep friction (form fields, decisions) to the minimum the goal requires.
- Match CTA intensity to awareness level — a cold, unaware audience needs a lower-commitment first step (read more, watch a demo) before a "buy now" ask.

## Editing

- Cut every sentence that doesn't move the reader closer to the action — copy is not an essay; length should be exactly as long as the argument requires, no longer.
- Read it aloud; anywhere you stumble is usually where a real reader will lose interest.

---


<!-- ============================================================ -->
<!-- Skill: mcp-server-builder -->
<!-- ============================================================ -->

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

---


<!-- ============================================================ -->
<!-- Skill: mongodb-data-modeling -->
<!-- ============================================================ -->

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

---


<!-- ============================================================ -->
<!-- Skill: network-pentest-recon -->
<!-- ============================================================ -->

---
name: network-pentest-recon
description: Use when performing authorized network-layer reconnaissance and enumeration — host discovery, port/service scanning, and identifying attack surface on an internal or external network engagement.
---

# Network Penetration Testing — Recon & Enumeration

Assumes `authorized-security-testing-scope` has been confirmed, including exact IP ranges/CIDRs authorized for scanning.

## Scoping the scan

- Double-check every CIDR range against the signed scope before running any scanner — an accidental scan of an out-of-scope range is the single most common way an engagement goes wrong.
- Start passive/light before active: DNS enumeration, WHOIS, certificate transparency logs for external targets; ARP/existing traffic observation for internal segments.

## Host & service discovery

- Use a phased approach: fast host-discovery sweep first (e.g., `nmap -sn`), then a full TCP/UDP port scan on live hosts only, rather than a slow full scan across the whole range up front.
- Service/version detection (`nmap -sV`) and default-script scanning (`-sC`) on open ports to identify likely-vulnerable versions before deeper manual testing.
- Note any rate-limiting or IDS/IPS behavior observed — throttle scans if the engagement's rules of engagement call for stealth or if you see active blocking.

## Prioritizing targets

- Rank discovered services by likely impact: exposed management interfaces (RDP, SSH with weak auth, admin panels), outdated/EOL software with known CVEs, and default credentials are typically the highest-value findings.
- Cross-reference service versions against known CVEs, but always verify a suspected vulnerability manually rather than reporting based on version-banner matching alone (banners lie, and backported patches don't change the version string).

## Documentation

- Keep a running log of every host, port, service, and command run — this becomes both your evidence trail and your report appendix.
- Screenshot or capture raw output for anything you plan to cite as a finding.

---


<!-- ============================================================ -->
<!-- Skill: nextjs-best-practices -->
<!-- ============================================================ -->

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

---


<!-- ============================================================ -->
<!-- Skill: notion-workspace-automation -->
<!-- ============================================================ -->

---
name: notion-workspace-automation
description: Use when working with Notion programmatically or as a knowledge base — converting content into Notion pages/databases, structuring workspace organization, or automating capture from other tools.
---

# Notion Workspace Automation

## Database design

- Model a Notion database around how it will be filtered/sorted/viewed, not just what fields exist — properties that will drive views (status, owner, due date) need to be typed correctly (select, person, date) rather than left as plain text.
- Use relations and rollups to connect databases (e.g., tasks related to projects) instead of duplicating data across databases by hand.
- Keep a database's property set lean; a database with 30 rarely-used properties is harder to maintain than three focused databases linked by relation.

## Structuring content

- Use a consistent page template for recurring content types (meeting notes, specs, research writeups) so information lands in predictable locations and stays scannable across many pages.
- Prefer toggle lists and headings for long pages so readers can navigate/collapse sections, rather than one long undifferentiated scroll.
- Use synced blocks for content that must stay identical across multiple pages (e.g., a team's current OKRs shown on several dashboards) instead of copy-pasting and letting copies drift.

## Programmatic access (API)

- Authenticate via an internal integration token scoped to only the pages/databases it needs — share the integration explicitly with each parent page rather than granting workspace-wide access by default.
- Query databases with filters/sorts server-side via the API rather than pulling everything and filtering client-side, especially for large databases.
- Respect the API's rate limits and paginate through results (`has_more`/`next_cursor`) rather than assuming a single request returns everything.

## Converting content into Notion

- When importing from another source (meeting transcript, research doc, spec), structure it into the receiving database's existing property schema rather than dumping raw text into a single field — the value of a database comes from consistent, filterable properties.
- Tag/categorize imported content immediately (project, type, owner) rather than leaving a backlog of untriaged pages that becomes unsearchable over time.

## Common pitfalls

- Don't nest databases many levels deep inside pages inside databases — flat, relation-linked structures are far easier to query and maintain than deep nesting.

---


<!-- ============================================================ -->
<!-- Skill: observability-monitoring -->
<!-- ============================================================ -->

---
name: observability-monitoring
description: Use when setting up or reviewing application observability — metrics, logs, traces, dashboards, and alerts (Datadog-style platforms or general observability practice).
---

# Observability & Monitoring

## The three pillars

- **Logs**: structured (JSON) over free-text, with consistent field names across services (`service`, `env`, `trace_id`, `level`) so they're queryable and correlatable, not just human-readable.
- **Metrics**: track the RED method for services (Rate, Errors, Duration) and the USE method for resources (Utilization, Saturation, Errors) as the default starting set before adding custom business metrics.
- **Traces**: propagate a trace/correlation ID across every service boundary (HTTP headers, message queue metadata) so a single request can be followed end-to-end across a distributed system.

## Instrumentation

- Instrument at the boundaries first (incoming requests, outgoing calls, database queries, queue consume/publish) — that's where latency and failures actually originate, and where dashboards get the most value fastest.
- Tag metrics/logs/traces with consistent dimensions (service, version, environment, region) so they can be sliced the same way across all three pillars during an incident.
- Avoid high-cardinality tags on metrics (user ID, request ID) — that's what logs and traces are for; metrics tags should have bounded cardinality or the backend's cost and query performance degrade badly.

## Dashboards

- Build dashboards around a specific audience's question ("is checkout healthy right now?") rather than dumping every available metric on one screen — an unfocused dashboard doesn't get looked at during an incident.
- Put the SLO/error-budget-relevant metrics at the top of any service dashboard, not buried among secondary metrics.

## Alerting

- Alert on symptoms users would notice (error rate, latency, availability) as the primary alerts; use cause-level alerts (CPU, queue depth) as supporting context, not as the main page-worthy signal — alerting on every resource metric causes fatigue.
- Set thresholds from actual historical baselines, not round numbers picked without data — an alert that fires constantly gets ignored, and one that never fires provides false confidence.
- Every alert should link to a runbook or have enough context in the alert body that the responder knows the first diagnostic step without hunting for it.

## Common pitfalls

- Don't treat "we added monitoring" as done once dashboards exist — validate that alerts actually fire correctly by testing them against a known failure, not just trusting the configuration.

---


<!-- ============================================================ -->
<!-- Skill: paid-ads-campaign-strategy -->
<!-- ============================================================ -->

---
name: paid-ads-campaign-strategy
description: Use when planning, structuring, or diagnosing paid advertising campaigns (search, social, display) — targeting, creative angles, and performance troubleshooting.
---

# Paid Ads Campaign Strategy

## Before launching

- Define the single primary metric the campaign is optimized for (leads, purchases, installs) before writing a single ad — a campaign optimizing for clicks when the goal is purchases will get cheap, low-intent clicks.
- Build a customer avatar from real data (past customers, support conversations) rather than assumptions — targeting and messaging both flow from who's actually being spoken to.
- Set a realistic test budget and timeframe before judging performance; most platforms need a minimum volume of data (impressions/conversions) before their optimization algorithms stabilize.

## Targeting & structure

- Start narrower and expand, or start broad and let platform optimization narrow — pick one strategy deliberately per platform's current best practice, rather than fighting the algorithm with overly restrictive manual targeting on platforms designed for broad-and-optimize.
- Structure campaigns/ad sets around distinct audiences or objectives, not around internal org structure — an ad set mixing incompatible audiences dilutes the platform's ability to optimize.
- Exclude existing customers from acquisition campaigns (or route them to a separate retention/upsell campaign) so acquisition budget isn't spent re-selling existing buyers.

## Creative

- Test genuinely different angles (different pain point, different mechanism, different proof), not just color/button variations — angle differences move performance far more than surface-level creative tweaks.
- Match ad format to platform-native behavior (vertical video for Reels/TikTok placements, not a repurposed horizontal TV spot) — native-feeling creative consistently outperforms obviously repurposed ads.
- Lead creative with the hook in the first 1-2 seconds/first line — most platforms' engagement drops off sharply if attention isn't captured immediately.

## Diagnosing underperformance

- Separate the funnel stage that's actually broken: low click-through means the ad/targeting isn't compelling; high clicks with low conversion means the landing page or offer is the problem — don't "fix" creative when the real issue is downstream.
- Check for delivery/learning-phase issues (frequency capping, audience overlap between ad sets cannibalizing each other) before concluding the creative itself has failed.
- Compare cost-per-result against a realistic benchmark for the vertical and funnel stage, not an arbitrary internal target with no grounding.

## Scaling

- Scale budget gradually (the platform re-enters a learning phase on large sudden increases) rather than jumping spend dramatically on a single winning ad set.

---


<!-- ============================================================ -->
<!-- Skill: playwright-web-testing -->
<!-- ============================================================ -->

---
name: playwright-web-testing
description: Use when the user wants to test a local or deployed web app end-to-end, automate browser interactions, or verify a UI change actually works ("test this page with Playwright", "click through the signup flow", "take a screenshot of the dashboard").
---

# Playwright Web Testing

Guidance for driving a real browser to verify web UI behavior rather than trusting code alone.

## When to reach for this

- After any frontend change, before declaring it done.
- When a bug report describes a UI interaction that's easier to reproduce than to reason about.
- When the user wants a screenshot or recorded flow as evidence.

## Workflow

1. **Confirm the app is running** — start the dev server if needed, and note the URL/port.
2. **Write the smallest script that exercises the real flow**, e.g.:
   ```js
   const { chromium } = require('playwright');
   const browser = await chromium.launch();
   const page = await browser.newPage();
   await page.goto('http://localhost:3000');
   await page.click('text=Sign up');
   await page.fill('#email', 'test@example.com');
   await page.screenshot({ path: 'signup.png' });
   await browser.close();
   ```
3. **Prefer role/text-based locators** (`getByRole`, `getByText`) over brittle CSS selectors tied to implementation details.
4. **Assert on visible state**, not internal state — text content, element visibility, network responses.
5. **Cover the golden path and one edge case** (empty input, error state, slow network) rather than only the happy path.

## Pitfalls to avoid

- Don't add arbitrary `sleep`/`waitForTimeout` calls — use Playwright's auto-waiting or explicit `waitFor` on a condition.
- Don't claim a UI fix works based on type-checking or unit tests alone — actually drive the browser.
- Clean up: close browser contexts/pages so headless runs don't leak processes.

---


<!-- ============================================================ -->
<!-- Skill: postgres-best-practices -->
<!-- ============================================================ -->

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

---


<!-- ============================================================ -->
<!-- Skill: programmatic-video-remotion -->
<!-- ============================================================ -->

---
name: programmatic-video-remotion
description: Use when creating programmatic/generative video with Remotion — data-driven video generation, animations defined in React, and rendering pipelines.
---

# Programmatic Video with Remotion

## Core concepts

- A Remotion video is a React component tree rendered frame-by-frame; drive all animation off `useCurrentFrame()` and `fps`, never off wall-clock time or CSS transitions/animations (those don't render deterministically frame-by-frame).
- Compose scenes with `<Sequence>` to control when each part starts/stops within the overall timeline, rather than manually branching on frame ranges inside one giant component.
- Keep compositions data-driven: pass content (text, images, data points) as props so the same composition can render many videos from different inputs.

## Animation

- Use `interpolate()` for value ranges (position, opacity, scale) mapped from frame number, with explicit `extrapolateLeft`/`extrapolateRight` so behavior outside the defined range is intentional, not accidental clamping or overshoot.
- Use `spring()` for natural-feeling motion instead of hand-tuning easing curves, unless a specific non-physical easing is the design intent.
- Keep expensive computation (data fetching, heavy processing) out of the render path — precompute it and pass as props, since the render path runs once per frame.

## Assets & data

- Use `staticFile()` for bundled assets so paths resolve correctly both in the Remotion Studio preview and in the final render.
- For data-driven videos (e.g., a video per user/record), generate a list of input props and render each as a separate composition invocation rather than trying to loop within a single video.

## Rendering

- Render with `@remotion/renderer` (Node API or CLI) rather than screen-recording the preview — it produces frame-accurate output and runs headless in CI.
- Set an explicit `concurrency` appropriate to the machine when rendering many videos or long compositions; the default may not be optimal for batch jobs.
- Preview at a lower resolution/quality during development and only render final quality once the composition is locked, to keep iteration fast.

## Common pitfalls

- Don't use `setTimeout`/`setInterval` or DOM animation APIs inside a composition — they run in real time, not simulation time, and will desync from the rendered frame sequence.

---


<!-- ============================================================ -->
<!-- Skill: python-best-practices -->
<!-- ============================================================ -->

---
name: python-best-practices
description: Use when writing, reviewing, or structuring Python code in general — typing, packaging/tooling (uv, ruff), testing, and common idioms. General-purpose, not security-specific.
---

# Python Best Practices

## Tooling

- Use `uv` for environment and dependency management on new projects — it's dramatically faster than `pip`/`venv` and handles lockfiles (`uv.lock`) reproducibly.
- Use `ruff` for both linting and formatting instead of separately running `flake8`/`black`/`isort` — one fast tool covers all three.
- Pin dependencies in a lockfile for applications; keep looser version ranges only for libraries meant to be installed alongside other packages.

## Typing

- Add type hints to function signatures at minimum (params and return type); let local variable types be inferred unless it genuinely aids readability.
- Run `mypy` or `pyright` in CI once a codebase has meaningful type coverage — untyped code doesn't need to retrofit everything at once, but new code should be typed.
- Prefer `dataclasses` or `pydantic` models over loose dicts for structured data that flows between functions/modules.

## Structure & idioms

- Prefer explicit over implicit: avoid `import *`, avoid mutable default arguments (`def f(x=[])` — use `None` and initialize inside).
- Use context managers (`with`) for anything that acquires a resource (files, locks, connections) — don't rely on manual cleanup.
- Prefer composition and small functions over deep inheritance hierarchies; a 5-level class hierarchy is usually a sign the design fought the problem.
- Use `pathlib.Path` instead of raw string path manipulation.

## Testing

- `pytest` with fixtures for setup/teardown; parametrize tests instead of copy-pasting near-identical test functions.
- Keep tests fast and isolated — mock network/filesystem calls at the boundary rather than hitting real external services in unit tests.

## Packaging

- Use `pyproject.toml` as the single source of project metadata (no separate `setup.py`/`setup.cfg` unless a build step genuinely requires it).
- Keep the public API surface of a library deliberate — use `__all__` or a clear `__init__.py` re-export list rather than letting every internal module be importable.

---


<!-- ============================================================ -->
<!-- Skill: python-security-scripting -->
<!-- ============================================================ -->

---
name: python-security-scripting
description: Use when writing Python tooling for authorized security testing — HTTP-based recon/fuzzing scripts, parsing scan output, or automating repetitive pentest/bug-bounty steps. Not for anything targeting a system without confirmed authorization.
---

# Python for Security Tooling

Assumes `authorized-security-testing-scope` has been confirmed for whatever target the script will touch.

## HTTP-based tooling

- Use `requests`/`httpx` with an explicit `Session` so cookies/auth headers persist naturally across a multi-step flow (login, then authenticated requests).
- Set explicit timeouts on every request — an unbounded request against a slow/unresponsive host will hang a scan indefinitely.
- Rate-limit deliberately (`time.sleep` or a token-bucket) when scope requires it; don't let a `for` loop over a wordlist become an unthrottled flood.
- Parse responses defensively — a target returning malformed JSON/HTML shouldn't crash the whole scan; catch and log, then continue.

## Structuring a scan script

- Separate "generate candidate requests" from "send and evaluate" from "report" — this makes it easy to dry-run the candidate list before firing anything, and to re-run just the reporting step over saved output.
- Persist raw request/response pairs for anything flagged as interesting (to a file or SQLite db) rather than only printing to stdout — you'll want the evidence later for the report.
- Use `argparse` with sane defaults so the script is reusable across engagements, not hardcoded to one target.

## Common building blocks

- `dnspython` for programmatic DNS enumeration.
- `python-nmap` or shelling out to `nmap` with `-oX` and parsing the XML output for structured results.
- `BeautifulSoup`/`lxml` for scraping and diffing HTML across app states.

## Safety in the script itself

- Never hardcode credentials or API keys in the script — load them from environment variables or a local, gitignored config file.
- Log what the script actually did (targets hit, timestamps) so it doubles as an audit trail proving you stayed within authorized scope.

---


<!-- ============================================================ -->
<!-- Skill: redis-caching-patterns -->
<!-- ============================================================ -->

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

---


<!-- ============================================================ -->
<!-- Skill: sanity-cms-content-modeling -->
<!-- ============================================================ -->

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

---


<!-- ============================================================ -->
<!-- Skill: sentry-workflow -->
<!-- ============================================================ -->

---
name: sentry-workflow
description: Use when setting up Sentry error monitoring in a project, triaging a Sentry issue, or writing a fix for a reported exception. Covers SDK setup and the find-diagnose-fix loop.
---

# Sentry Workflow

## SDK setup

- Initialize the SDK as early as possible in the app's entry point so it captures startup errors too.
- Set `environment` (production/staging/dev) and `release` on every event — without them, issue grouping and regression tracking are far less useful.
- Scrub PII before it reaches Sentry (`beforeSend`/`sendDefaultPii: false` plus explicit allow-listing) rather than relying on defaults.
- Enable source maps upload for minified frontend bundles, or stack traces become useless.

## Triaging an issue

1. Read the full stack trace and breadcrumbs, not just the top frame — the root cause is often several frames or breadcrumbs away from where the exception surfaced.
2. Check "Events" for frequency and affected users/releases to judge severity and whether it's a regression tied to a specific deploy.
3. Look at tags (browser, OS, release) for a pattern before assuming the bug is universal.
4. Reproduce locally using the captured context (request params, user state) before writing a fix blind.

## Fixing

- Fix the root cause; if you must guard defensively, add a comment explaining *why* the invalid state can occur — don't silently swallow the exception.
- After deploying a fix, mark the issue resolved "in next release" (tied to your release tracking) so Sentry can detect a regression automatically if it recurs.
- For noisy but non-actionable issues (third-party script errors, browser extension noise), use inbound filters or `ignoreErrors` instead of resolving them repeatedly by hand.

---


<!-- ============================================================ -->
<!-- Skill: seo-content-audit -->
<!-- ============================================================ -->

---
name: seo-content-audit
description: Use when the user wants to improve a page or site's search/AI-answer visibility — technical SEO audits, on-page content review, or optimizing for AI-generated answer engines (AEO).
---

# SEO & Content Audit

## Technical SEO checklist

- One `<title>` and one meta description per page, both unique and under ~60/~155 characters respectively.
- Exactly one `<h1>`; a logical, nested heading hierarchy after that (no skipped levels).
- Canonical tags on any page that could be reached via multiple URLs (query params, trailing slash, http/https).
- `robots.txt` and sitemap.xml present and consistent — nothing blocked in `robots.txt` that you actually want indexed.
- Core Web Vitals (LCP, CLS, INP) in the "good" range — check with real-user or lab data, not just a single Lighthouse run.

## On-page content

- Match search intent: informational queries need an actual answer near the top of the page, not just after a long preamble.
- Use descriptive, keyword-relevant anchor text for internal links instead of "click here."
- Add structured data (schema.org markup — Article, Product, FAQ, etc.) matching the actual page content; mismatched schema can trigger manual penalties.

## Optimizing for AI answer engines (AEO)

- Write content that directly answers a specific question in the first 1-2 sentences of a section — this is what gets extracted into AI-generated summaries.
- Use clear headings phrased as the questions users actually ask.
- Keep facts, numbers, and claims verifiable and attributable (cite a source) — answer engines favor content they can confidently ground.

## Audit output

- Prioritize findings by impact × effort, not just by count — a single broken canonical on the homepage outweighs a dozen missing alt tags on low-traffic pages.

---


<!-- ============================================================ -->
<!-- Skill: static-analysis-vuln-scanning -->
<!-- ============================================================ -->

---
name: static-analysis-vuln-scanning
description: Use when running or writing static-analysis security scans over a codebase — Semgrep/CodeQL rule usage, triaging scanner output, and writing custom rules for a recurring vulnerability pattern.
---

# Static Analysis & Vulnerability Scanning

## Running an existing scan

- Start with an established ruleset (Semgrep's `p/security-audit`/`p/owasp-top-ten`, CodeQL's default security queries) rather than writing custom rules from scratch on the first pass.
- Scope the scan to the actual application code — exclude vendored dependencies, generated code, and test fixtures, which otherwise dominate the noise.
- Run incrementally on diffs for PR-time checks (`semgrep --baseline-commit`), and full-repo scans periodically for drift/tech-debt visibility.

## Triaging results

- Sort by severity/confidence first, but don't dismiss "low confidence" findings in genuinely sensitive code paths (auth, crypto, deserialization) without a manual look.
- For each hit, trace the actual data flow: does user-controlled input really reach the sink, or is the value always a hardcoded/trusted constant? Mark true negatives explicitly (`# nosemgrep` with a reason) rather than silently ignoring them, so the next scan doesn't re-flag the same reviewed line.
- Group duplicate findings from the same root cause (e.g., one unsafe helper function called in 40 places) into a single fix rather than 40 individual triage entries.

## Writing a custom rule

- Write the rule from a concrete vulnerable example and a concrete safe example — Semgrep/CodeQL rules are much easier to get right when built test-first against both.
- Keep the pattern as specific as the actual risk requires; an overly broad rule trains reviewers to ignore the scanner.
- Add a short `message` explaining *why* the pattern is dangerous and what the fix looks like, not just what pattern matched.

## Workflow integration

- Fail CI on high-confidence/high-severity findings only at first; treat lower-confidence findings as a dashboard/report item until the team trusts the signal, to avoid scanner fatigue.

---


<!-- ============================================================ -->
<!-- Skill: stripe-integration -->
<!-- ============================================================ -->

---
name: stripe-integration
description: Use when integrating Stripe for payments, subscriptions, or webhooks — setting up checkout, handling webhook events, or upgrading a Stripe SDK/API version.
---

# Stripe Integration

## Core setup

- Never expose the secret key (`sk_...`) to client code — it belongs only in server-side environment variables. Only the publishable key (`pk_...`) goes to the browser.
- Use Stripe Checkout or the Payment Element for card collection whenever possible instead of building custom card forms — it keeps you out of full PCI-DSS scope.
- Pin the API version explicitly (`Stripe-Version` header or SDK config) so a Stripe-side API upgrade can't silently change your integration's behavior.

## Webhooks

- Always verify the webhook signature (`stripe.webhooks.constructEvent`) using the raw request body — a parsed/re-serialized body will fail verification.
- Handle webhooks idempotently: Stripe can and will redeliver the same event, so key any side effect (granting access, sending an email) off the event ID, not just "we received an event."
- Return a 2xx quickly and do slow work (emails, downstream calls) asynchronously — Stripe retries on timeout, which can cause duplicate processing if the handler is slow and non-idempotent.

## Subscriptions

- Treat Stripe's subscription/invoice objects as the source of truth for billing state; don't try to independently compute "is this customer paid" from cached data without reconciling via webhooks.
- Handle the full lifecycle: `checkout.session.completed`, `customer.subscription.updated`, `customer.subscription.deleted`, and `invoice.payment_failed` are the minimum set for a subscription product.

## Upgrading the SDK

- Read the changelog for breaking changes before bumping a major SDK version — Stripe versions its API independently from the SDK, so an SDK bump can surface previously-hidden API version drift.
- Run the integration against Stripe's test mode and a webhook replay before deploying an upgrade.

---


<!-- ============================================================ -->
<!-- Skill: terraform-best-practices -->
<!-- ============================================================ -->

---
name: terraform-best-practices
description: Use when writing, reviewing, or refactoring Terraform/HCL — new resources, modules, providers, or infrastructure changes. Covers style, module structure, state safety, and testing conventions.
---

# Terraform Best Practices

## Style & structure

- One logical resource group per file; name files by purpose (`network.tf`, `iam.tf`), not by resource type.
- Use variables with explicit `type` and `description`; give every variable a sensible default only when a safe default genuinely exists.
- Prefer data sources over hardcoded IDs/ARNs for anything that already exists.
- Keep modules small and composable — a module should do one thing (e.g., "a VPC", not "the whole environment").

## State safety

- Never hand-edit `.tfstate`. Use `terraform state mv`/`rm` for refactors that change resource addresses.
- Always run `terraform plan` and read the diff before `apply` — pay special attention to any line showing `-/+` (destroy and recreate).
- Use remote state with locking (S3+DynamoDB, Terraform Cloud, etc.) for anything beyond a solo sandbox.
- Treat `terraform apply -auto-approve` as a red flag outside of CI pipelines with their own review gate.

## Change discipline

- For a resource rename, check whether it's actually a rename (`moved` block) vs. a destroy+recreate — the latter can cause downtime for stateful resources (databases, load balancers).
- Tag/label resources consistently (environment, owner, cost-center) so `plan` diffs and cost reports stay legible.
- Run `terraform validate` and `terraform fmt` before proposing a change.

## Testing

- For provider/module development, write acceptance-style tests that apply real infrastructure against a test account and then destroy it — never test against production.
- Prefer small, incremental applies over one giant plan when standing up new infrastructure, so failures are easy to localize.

---


<!-- ============================================================ -->
<!-- Skill: vulnerability-report-writing -->
<!-- ============================================================ -->

---
name: vulnerability-report-writing
description: Use when writing up a security finding — pentest report sections, bug-bounty submissions, or a responsible-disclosure notice to a vendor. Covers structure, tone, and what makes a finding actionable.
---

# Vulnerability Report Writing

## Structure that gets acted on

1. **Title** — specific and searchable ("IDOR on `/api/orders/{id}` allows reading other users' orders"), not generic ("Authorization issue").
2. **Summary** — two or three sentences: what the bug is, where, and the core impact.
3. **Severity/impact** — state the realistic worst case (data exposed, privilege gained, systems affected), not the theoretical maximum; back it with the CVSS vector or the program's own severity scale if one is specified.
4. **Steps to reproduce** — numbered, minimal, and literal enough that someone unfamiliar with the app can follow them exactly. Include the exact requests (method, URL, headers, body) or commands used.
5. **Proof** — a screenshot, request/response pair, or short recording; redact any real user data that isn't essential to demonstrating the bug.
6. **Remediation suggestion** — concrete enough to hand to a developer (e.g., "validate that the `order.user_id` matches the authenticated session's user before returning the record" beats "add authorization checks").

## Tone

- Describe what was found and its impact factually — avoid alarmist language ("this destroys the entire company") and avoid minimizing either; let the reproduction steps and impact speak for themselves.
- Assume good faith on the reader's side: they want to fix this, so write for a developer under time pressure, not for a headline.

## Responsible disclosure specifics

- If no bug-bounty program exists, check for a `security.txt` file or a published security contact before reporting; give the vendor a reasonable, clearly-stated window to fix before any public disclosure.
- Never disclose publicly (blog post, social media, conference talk) before the agreed timeline has passed or the vendor has confirmed a fix, unless the vendor is unresponsive well past a reasonable window and the program's own disclosure policy allows it.

## Common mistakes to avoid

- Bundling multiple unrelated bugs into one report — split them so each can be triaged and rewarded independently.
- Omitting the exact request/response that proves the bug, forcing the triager to reconstruct it themselves — this is the single biggest cause of report delays and "unable to reproduce" closures.

---


<!-- ============================================================ -->
<!-- Skill: web-accessibility-a11y -->
<!-- ============================================================ -->

---
name: web-accessibility-a11y
description: Use when building, reviewing, or auditing a web interface for accessibility — semantic HTML, keyboard navigation, screen-reader support, and WCAG conformance checks.
---

# Web Accessibility (A11y)

## Foundations

- Use semantic HTML elements (`<button>`, `<nav>`, `<main>`, `<label>`) instead of styled `<div>`s with click handlers — semantic elements come with keyboard interaction, focus behavior, and screen-reader announcements built in for free.
- Maintain a logical heading hierarchy (one `<h1>`, no skipped levels) so screen-reader users can navigate by heading structure, which is one of the most common ways they scan a page.
- Every interactive element needs an accessible name: visible text, `aria-label`, or an associated `<label>` — an icon-only button with no accessible name is invisible to assistive technology users.

## Keyboard & focus

- Every interactive element must be reachable and operable via keyboard alone (Tab, Enter/Space, arrow keys where appropriate) — test by unplugging the mouse, not just by reading the code.
- Manage focus explicitly on dynamic UI changes: moving focus into a newly opened modal, returning it to the trigger element on close, and never trapping focus in a component that isn't a genuine modal.
- Keep a visible focus indicator; removing `outline` without a replacement focus style makes keyboard navigation unusable, not just less pretty.

## ARIA

- Use ARIA to fill gaps semantic HTML can't cover (custom widgets like comboboxes, tab panels), not as a default layer on top of elements that already have the right semantics — the first rule of ARIA is not to use it when a native element would do.
- Keep ARIA state attributes (`aria-expanded`, `aria-selected`, `aria-hidden`) in sync with actual UI state; stale ARIA state is often worse than none, since it actively misinforms assistive technology.

## Visual & content

- Meet WCAG contrast ratios (4.5:1 for normal text, 3:1 for large text) for all meaningful text/background combinations, checked with an actual contrast tool, not by eye.
- Don't convey information by color alone (error states, required fields, chart series) — pair color with text, icon, or pattern.
- Write meaningful `alt` text for informative images (describing content/function) and empty `alt=""` for purely decorative images, not a filename or "image" as filler.

## Testing

- Combine automated tools (axe, Lighthouse) for catching mechanical issues with manual testing (keyboard-only navigation, a real screen reader like VoiceOver/NVDA) — automated tools catch roughly a third of real accessibility issues at best.

---


<!-- ============================================================ -->
<!-- Skill: web-app-pentest-checklist -->
<!-- ============================================================ -->

---
name: web-app-pentest-checklist
description: Use when performing an authorized web application penetration test — systematic coverage of OWASP-style vulnerability classes, from authentication through business logic.
---

# Web Application Pentest Checklist

Assumes `authorized-security-testing-scope` has been confirmed for this specific application.

## Reconnaissance & mapping

- Enumerate every endpoint and parameter by walking the app manually (and via an intercepting proxy like Burp/ZAP) — don't rely on an automated crawl alone for authenticated, JS-heavy apps.
- Identify all trust boundaries: where does user input cross into a query, a shell, a template, a redirect, or another user's data?

## Core vulnerability classes to cover

- **Injection** (SQL, NoSQL, command, LDAP, template): test every input that reaches a query/interpreter, including headers and JSON body fields, not just visible form fields.
- **Authentication**: password reset flow, credential stuffing resistance/rate limiting, session fixation, MFA bypass paths.
- **Authorization / IDOR**: for every object-referencing endpoint, test access with a second low-privilege account against the first account's objects.
- **Access control on admin/internal functionality**: check whether "hidden" admin routes are actually enforced server-side, not just hidden in the UI.
- **XSS / content injection**: test reflected, stored, and DOM-based paths; check where user content is rendered without appropriate encoding.
- **CSRF**: check state-changing endpoints for CSRF tokens/SameSite cookie protections.
- **SSRF**: test any feature that fetches a URL/webhook/image on the server's behalf.
- **File upload**: type/extension validation, path traversal on the stored filename, whether uploaded content is served with the correct content-type.
- **Business logic**: race conditions on limited-use actions (coupon codes, one-time payments), price/quantity manipulation, workflow-step skipping.

## Tooling notes

- An intercepting proxy (Burp Suite/ZAP) is the backbone of manual testing — use its repeater/intruder for targeted parameter fuzzing rather than blind automated scans across the whole app.
- Automated scanners (Burp Scanner, Nuclei, etc.) are good for breadth/coverage but produce false positives — always manually verify before reporting.

## Reporting

- Rate each finding by real-world impact (data exposed, privilege gained), not just by vulnerability class name.
- Include a concrete remediation suggestion per finding, not just "fix the vulnerability."

---


<!-- ============================================================ -->
<!-- Skill: web-artifact-builder -->
<!-- ============================================================ -->

---
name: web-artifact-builder
description: Use when building a self-contained, polished HTML/React artifact (dashboards, interactive tools, styled reports) meant to be viewed as a standalone page rather than integrated into an existing app.
---

# Web Artifact Builder

## Before writing markup

- Decide the medium first: a static report reads better as clean semantic HTML+CSS; an interactive tool (filters, live calculations, drag-and-drop) justifies React and client-side state.
- Sketch the layout in terms of sections and hierarchy before picking colors — visual polish can't fix a confusing structure.

## Building

- Keep everything self-contained: inline CSS/JS, no external CDN calls, no remote fonts unless embedded — assume the strictest possible execution sandbox.
- Use relative units and flexbox/grid so the layout survives different viewport widths; wide content (tables, code, diagrams) should scroll inside its own container, never force the whole page to scroll horizontally.
- Style for both light and dark viewing — use `prefers-color-scheme` at minimum, and respect an explicit theme override if the host environment provides one.
- Favor a small, deliberate color palette over defaulting to a framework's full color scale; consistency across a dashboard's stat tiles, charts, and text reads as one designed system rather than assembled parts.

## Interactivity

- Every interactive element needs a visible state change on hover/focus/active — a button that looks static until clicked feels broken.
- Debounce expensive recomputation on input changes (search, filters) rather than recalculating on every keystroke.

## Before shipping

- Resize the viewport mentally (or literally, if testing in a browser) from narrow to wide and confirm nothing overflows or clips.
- Check both color-scheme variants render legibly — text contrast against background is the most common miss.

---


<!-- ============================================================ -->
<!-- Skill: web-scraping-and-crawling -->
<!-- ============================================================ -->

---
name: web-scraping-and-crawling
description: Use when extracting structured data from websites — single-page scraping, multi-step crawls, or search-then-extract workflows. Covers ethical/legal boundaries alongside the technical approach.
---

# Web Scraping & Crawling

## Before scraping anything

- Check the target site's `robots.txt` and terms of service — some explicitly prohibit automated scraping, and ignoring that carries legal risk regardless of technical feasibility.
- Prefer an official API when one exists; scraping is a fallback, not a default, when structured data is already offered another way.
- Identify your scraper with a proper `User-Agent` rather than spoofing a browser to evade detection, unless the task is explicitly an authorized security assessment.

## Single-page extraction

- Inspect whether content is server-rendered (plain HTTP GET + HTML parse suffices) or client-rendered (needs a real browser/headless automation) before choosing a tool — reaching for a full browser when static HTML would do wastes time and resources.
- Extract by stable selectors (data attributes, semantic structure) rather than brittle generated class names that change on every deploy.
- Validate extracted fields against expected types/formats before using them downstream — a scraper that silently returns `None` or malformed data is worse than one that errors loudly.

## Multi-step / interactive flows

- For flows requiring login, pagination, or JS-driven interaction, use a real or headless browser (Playwright) and drive it like a user would — click, wait for network idle or a specific element, then extract.
- Add explicit waits tied to real conditions (element visible, network idle) rather than fixed `sleep` calls, which are both slow and unreliable across page-load variance.

## Crawling at scale

- Respect crawl-delay and rate limits — hammering a site is both hostile and likely to get your IP blocked, defeating the purpose.
- Deduplicate URLs and track visited state explicitly to avoid infinite loops on sites with circular or parameterized links.
- Store raw responses alongside parsed output during development — when a parser breaks on a page structure change, you need the raw HTML to debug against, not just the failed extraction.

## Query-first discovery

- When the goal is "find pages about X" rather than "scrape this known page," search first (via a search API) and only crawl/extract the specific results that match, instead of blindly crawling a whole site.

---


<!-- ============================================================ -->
<!-- Skill: web3-crypto-integration -->
<!-- ============================================================ -->

---
name: web3-crypto-integration
description: Use when integrating with blockchain/crypto APIs or building Web3 features — wallet connections, reading on-chain data, and token/market data integration. Not financial advice; technical integration only.
---

# Web3 / Crypto Integration

## Wallet connections

- Use an established wallet-connection library (RainbowKit, Web3Modal, wagmi) rather than hand-rolling provider detection and connection logic — wallet behavior across MetaMask, WalletConnect, Coinbase Wallet, etc. has enough edge cases that a maintained abstraction is worth the dependency.
- Always let the user explicitly initiate the connection (a button click) — never auto-connect to a wallet without user action, both for UX and because some wallets/browsers block silent connection attempts.
- Handle network/chain mismatches explicitly: detect the connected chain and prompt a network switch rather than silently failing transactions built for the wrong chain.

## Reading on-chain / market data

- Use a dedicated RPC provider (Alchemy, Infura, or a public node with documented rate limits) rather than assuming an arbitrary public endpoint has production-grade uptime.
- Cache read-heavy on-chain queries (token balances, prices) with a sensible TTL — blockchain state doesn't need to be re-queried on every render, and public RPC/API endpoints often rate-limit aggressively.
- When surfacing price or market-cap data, cite the data source and timestamp in the UI — crypto prices are volatile enough that unlabeled, stale data misleads users.

## Transactions

- Simulate or estimate gas before submitting a transaction and surface the estimated cost to the user — an unexpected gas fee is one of the most common sources of user complaints in Web3 UX.
- Show clear transaction status (pending, confirmed, failed) tied to the actual transaction hash, and let users track it on a block explorer — don't just show a generic spinner with no way to verify what's happening on-chain.
- Never construct or sign a transaction with a hardcoded private key in application code; production signing belongs in the user's own wallet or a properly secured signing service.

## Security

- Treat any contract-interaction code as security-critical: verify contract addresses against an authoritative source (official docs, verified block-explorer listing) before hardcoding them, since a wrong address can send funds to an attacker's contract.
- If the project handles real user funds, get a professional audit before mainnet deployment — this domain has an unusually high cost for undiscovered bugs.

## Scope note

This skill covers technical integration only — it is not investment guidance, and nothing here should be read as a recommendation to buy, sell, or hold any asset.

---


<!-- ============================================================ -->
<!-- Skill: wordpress-plugin-development -->
<!-- ============================================================ -->

---
name: wordpress-plugin-development
description: Use when building or reviewing a WordPress plugin or theme — hooks, blocks, REST API endpoints, and WP-CLI workflows.
---

# WordPress Plugin & Theme Development

## Plugin structure

- One clear entry file with the plugin header comment (`Plugin Name`, `Version`, etc.); keep logic in included/autoloaded classes rather than one giant file.
- Namespace or prefix every function, class, and hook callback (`myplugin_` / `MyPlugin\`) — WordPress has a single global function/class namespace, and collisions between plugins are a common source of fatal errors.
- Use activation/deactivation hooks for setup/teardown (creating tables, scheduling cron), not plugin load time — code at load time runs on every request.

## Hooks & extensibility

- Prefer filters over direct output when a value might need customizing by another plugin/theme (`apply_filters` around computed values, not just `do_action` for side effects).
- Use the earliest hook that has the data you need, not the latest one that happens to work — hooking too late causes ordering bugs with other plugins.
- Document custom hooks you introduce (name, parameters, when it fires) so themes/other plugins can safely extend the plugin.

## Blocks (Gutenberg)

- Register blocks via `block.json` (metadata-driven) rather than manual `register_block_type` calls with inline arrays — it's the modern, tooling-friendly approach and required for block directory submission.
- Keep block edit/save output in sync: a save function that doesn't match what `edit` produces causes "block validation failed" errors on every affected post.
- Use `useBlockProps` in the edit component so the block wrapper gets the correct classes/attributes the editor expects.

## REST API & data

- Register custom REST routes with explicit `permission_callback` — omitting it (or defaulting to `__return_true` carelessly) is a common vulnerability in WordPress plugins.
- Sanitize on input, escape on output: `sanitize_text_field`/`absint` etc. when saving, `esc_html`/`esc_attr`/`esc_url` when rendering — never trust that sanitized-on-save data is safe to print unescaped.
- Use `$wpdb->prepare()` for any custom SQL touching user input; never concatenate raw input into a query.

## WP-CLI & tooling

- Use WP-CLI for repeatable setup/migration tasks (`wp plugin`, `wp db`, custom commands via `WP_CLI::add_command`) instead of one-off admin-ajax scripts.

---

