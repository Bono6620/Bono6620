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
