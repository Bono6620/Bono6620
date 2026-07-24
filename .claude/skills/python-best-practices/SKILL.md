---
name: python-best-practices
description: Use when writing, reviewing, or structuring Python code in general — typing, packaging/tooling (uv, ruff), testing, and common idioms. General-purpose, not security-specific.
---

# Python Best Practices

## Tooling

- Use `uv` for environment and dependency management on new projects — it's dramatically faster than `pip`/`venv` and handles lockfiles (`uv.lock`) reproducibly.
- Use `ruff` for both linting and formatting instead of separately running `flake8`/`black`/`isort` — one fast tool covers all three.
- Pin dependencies in a lockfile for applications; keep looser version ranges only for libraries meant to be installed alongside other packages.

## Typing

- Add type hints to function signatures at minimum (params and return type); let local variable types be inferred unless it genuinely aids readability.
- Run `mypy` or `pyright` in CI once a codebase has meaningful type coverage — untyped code doesn't need to retrofit everything at once, but new code should be typed.
- Prefer `dataclasses` or `pydantic` models over loose dicts for structured data that flows between functions/modules.

## Structure & idioms

- Prefer explicit over implicit: avoid `import *`, avoid mutable default arguments (`def f(x=[])` — use `None` and initialize inside).
- Use context managers (`with`) for anything that acquires a resource (files, locks, connections) — don't rely on manual cleanup.
- Prefer composition and small functions over deep inheritance hierarchies; a 5-level class hierarchy is usually a sign the design fought the problem.
- Use `pathlib.Path` instead of raw string path manipulation.

## Testing

- `pytest` with fixtures for setup/teardown; parametrize tests instead of copy-pasting near-identical test functions.
- Keep tests fast and isolated — mock network/filesystem calls at the boundary rather than hitting real external services in unit tests.

## Packaging

- Use `pyproject.toml` as the single source of project metadata (no separate `setup.py`/`setup.cfg` unless a build step genuinely requires it).
- Keep the public API surface of a library deliberate — use `__all__` or a clear `__init__.py` re-export list rather than letting every internal module be importable.
