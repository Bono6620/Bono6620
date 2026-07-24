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
