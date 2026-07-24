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
