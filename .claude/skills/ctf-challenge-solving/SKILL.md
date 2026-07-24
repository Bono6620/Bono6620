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
