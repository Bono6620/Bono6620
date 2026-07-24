---
name: linear-project-management
description: Use when managing issues, projects, or workflows in Linear — issue structuring, cycle planning, and API/automation usage.
---

# Linear Project Management

## Issue structuring

- Write issue titles as a specific, actionable statement of the change ("Fix pagination off-by-one on orders list") rather than a vague topic ("Pagination bug") — a searchable, specific title pays off every time someone triages later.
- Keep one issue per distinct piece of work; a ticket bundling several unrelated changes can't be prioritized, estimated, or closed cleanly.
- Use sub-issues for genuinely decomposable work (a large feature broken into implementation steps), not as a substitute for a project when the work spans multiple independent issues over time.

## Labels & workflow

- Keep the label taxonomy small and orthogonal (type, area, priority as separate label groups) rather than one long flat list — an overloaded label system stops getting used consistently.
- Match workflow states to how work actually moves (Backlog → Todo → In Progress → In Review → Done) and resist adding custom states that don't represent a genuinely distinct stage — extra states fragment reporting without adding clarity.

## Cycles & planning

- Size cycle scope to the team's actual historical throughput, not to aspirational capacity — a cycle that consistently overflows erodes the planning signal cycles are meant to provide.
- Leave a portion of cycle capacity unplanned for triage/support/bugs unless the team's work is genuinely 100% plannable — an always-fully-packed cycle means every interrupt becomes an overrun.

## Projects

- Use Projects for outcomes with a defined scope and target date spanning multiple issues/cycles; use a simple issue for anything smaller — not every piece of work needs project-level overhead.
- Keep a project's description current with the actual goal and status summary — a stale project description is worse than none, since people trust it as source of truth.

## API & automation

- Use the GraphQL API (or an SDK wrapping it) for programmatic issue creation/updates from other tools (support tickets, error monitoring) rather than manual copy-paste triage.
- Scope API tokens/webhooks to the minimum team/workspace access needed, and validate webhook payloads before acting on them in automation.
