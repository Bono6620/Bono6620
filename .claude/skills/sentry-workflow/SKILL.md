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
