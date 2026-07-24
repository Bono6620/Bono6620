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
