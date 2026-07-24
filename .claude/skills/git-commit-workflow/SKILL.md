---
name: git-commit-workflow
description: Use when the user asks to stage, commit, push changes, or open a pull request in one go ("commit and push this", "yeet this branch up", "open a PR for these changes"). Covers safe staging, commit-message conventions, and PR creation.
---

# Git Commit & PR Workflow

A safe, repeatable flow for turning working-tree changes into a pushed commit and an open pull request.

## Steps

1. **Inspect before touching anything**
   - `git status` — see untracked and modified files.
   - `git diff` — review unstaged changes; `git diff --staged` for staged ones.
   - `git log --oneline -10` — match the repo's existing commit-message style.

2. **Stage deliberately**
   - Add files by explicit path (`git add path/to/file`), never `git add -A` or `git add .` blindly — it can sweep in `.env`, credentials, or build artifacts.
   - Re-run `git status` after staging to confirm exactly what will be committed.

3. **Write the commit message**
   - Summarize the *why*, not a restatement of the diff.
   - Keep the subject line under ~70 characters; use the body for detail if needed.
   - Match the repo's convention (Conventional Commits, plain imperative, etc.) as seen in `git log`.

4. **Commit and push**
   - Create a new commit rather than amending, unless explicitly asked to amend.
   - `git push -u origin <branch>` on first push of a branch.
   - Never force-push over shared history without explicit confirmation.

5. **Open the pull request**
   - Check for a PR template (`.github/pull_request_template.md` or similar) and mirror its sections.
   - Write a summary of *what changed and why*, plus a short test plan.
   - Open as draft unless told otherwise.

## Guardrails

- Never skip hooks (`--no-verify`) or bypass signing to force a commit through — fix the underlying failure instead.
- Never commit files that look like secrets (`.env`, `*.pem`, `credentials.json`) without explicit confirmation.
- If a pre-commit hook fails after a commit attempt, the commit did not happen — fix the issue and make a fresh commit, don't `--amend`.
