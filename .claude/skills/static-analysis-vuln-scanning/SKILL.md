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
