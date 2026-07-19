# Curated Claude Code Skills

A hand-picked set of Claude Code skills for this account, inspired by the categories catalogued in
[VoltAgent/awesome-agent-skills](https://github.com/VoltAgent/awesome-agent-skills) — a community index of
1,000+ agent skills contributed by Anthropic, OpenAI, Cloudflare, Vercel, Supabase, HashiCorp, Sentry, Stripe,
Figma, and many other teams.

That index links out to hundreds of separate repositories, each maintained independently by its own team, so
cloning and applying all of them verbatim isn't practical (or, for anything under a non-permissive license,
appropriate) inside a single personal repo. Instead, this directory contains a **curated selection**, written
from scratch to cover the categories that are broadly useful across everyday engineering work: version control,
testing, infrastructure, databases, frontend frameworks, payments, monitoring, SEO, design handoff, building new
agent tools, and — a second, security-focused batch — authorized penetration testing, bug bounty hunting, and
Python.

## What's here

### General engineering

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

### Security, pentesting & Python

All of these assume — and the first one enforces — that testing is authorized (a signed engagement, a
bug-bounty program's published scope, or a CTF/lab environment). None of this is for use against systems
without confirmed permission.

| Skill | Covers |
|---|---|
| `authorized-security-testing-scope` | Authorization/scope gate — read first, every time |
| `bug-bounty-methodology` | Target selection, recon, and report submission on bounty programs |
| `web-app-pentest-checklist` | OWASP-style web vulnerability coverage |
| `api-security-testing` | REST/GraphQL auth, BOLA, mass assignment, rate limiting |
| `network-pentest-recon` | Host/service discovery and enumeration |
| `static-analysis-vuln-scanning` | Semgrep/CodeQL usage, triage, custom rules |
| `python-security-scripting` | Writing Python tooling for authorized testing |
| `python-best-practices` | General Python: typing, `uv`/`ruff`, testing, idioms |
| `ctf-challenge-solving` | Web, pwn, crypto, forensics, and rev categories |
| `vulnerability-report-writing` | Report structure, tone, and responsible disclosure |

## How Claude Code uses these

Each skill is a `SKILL.md` file with YAML frontmatter (`name`, `description`) followed by actionable guidance.
Claude Code surfaces a skill automatically when its `description` matches the task at hand — no manual
invocation needed in most cases.

## Extending this set

To add more skills from the broader awesome-agent-skills catalogue, create a new folder under
`.claude/skills/<skill-name>/SKILL.md` following the same format. Keep content original and practical rather
than copying source material verbatim.
