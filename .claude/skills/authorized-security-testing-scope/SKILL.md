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
