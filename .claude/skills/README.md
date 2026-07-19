# Curated Claude Code Skills

A hand-picked set of Claude Code skills for this account, inspired by the categories catalogued in
[VoltAgent/awesome-agent-skills](https://github.com/VoltAgent/awesome-agent-skills) — a community index of
1,000+ agent skills contributed by Anthropic, OpenAI, Cloudflare, Vercel, Supabase, HashiCorp, Sentry, Stripe,
Figma, and many other teams.

That index links out to hundreds of separate repositories, each maintained independently by its own team, so
cloning and applying all of them verbatim isn't practical (or, for anything under a non-permissive license,
appropriate) inside a single personal repo. Instead, this directory contains a **curated selection of ~12
skills**, written from scratch to cover the categories that are broadly useful across everyday engineering work:
version control, testing, infrastructure, databases, frontend frameworks, payments, monitoring, SEO, design
handoff, and building new agent tools.

## What's here

| Skill | Covers |
|---|---|
| `git-commit-workflow` | Safe staging, commit conventions, PR creation |
| `playwright-web-testing` | End-to-end browser testing and verification |
| `terraform-best-practices` | HCL style, state safety, module structure |
| `postgres-best-practices` | Schema design, migrations, query hygiene |
| `nextjs-best-practices` | App Router, Server Components, caching |
| `cloudflare-workers` | Workers, Durable Objects, Wrangler deploys |
| `stripe-integration` | Checkout, webhooks, subscription lifecycle |
| `sentry-workflow` | SDK setup and the triage-fix loop |
| `seo-content-audit` | Technical SEO and AI-answer-engine optimization |
| `figma-to-code` | Translating Figma designs into production code |
| `mcp-server-builder` | Designing and shipping new MCP tools |
| `web-artifact-builder` | Self-contained HTML/React artifacts and dashboards |

## How Claude Code uses these

Each skill is a `SKILL.md` file with YAML frontmatter (`name`, `description`) followed by actionable guidance.
Claude Code surfaces a skill automatically when its `description` matches the task at hand — no manual
invocation needed in most cases.

## Extending this set

To add more skills from the broader awesome-agent-skills catalogue, create a new folder under
`.claude/skills/<skill-name>/SKILL.md` following the same format. Keep content original and practical rather
than copying source material verbatim.
