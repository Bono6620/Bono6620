---
name: firebase-backend-integration
description: Use when integrating Firebase as a backend — Firestore/Realtime Database data modeling, security rules, Auth, and Cloud Functions.
---

# Firebase Backend Integration

## Firestore data modeling

- Model collections around query patterns, not around a relational mental model — Firestore has no server-side joins, so denormalize data that needs to be read together in one query.
- Keep documents well under the 1MB limit and avoid unbounded arrays/maps that grow forever; use subcollections for one-to-many relationships that can grow large.
- Design collection group queries deliberately when the same subcollection name repeats across parents (e.g., `reviews` under many `products`) — it's the mechanism for querying "all reviews everywhere."

## Security rules

- Write security rules as the actual authorization boundary, not client-side checks — client code can always be bypassed, so every read/write path must be enforced in `firestore.rules`/`database.rules.json`.
- Test rules with the Firebase emulator and the rules unit-testing library before deploying — a rules bug is a data-exposure bug, not just a UX bug.
- Scope rules as tightly as the data model allows (per-document ownership checks via `request.auth.uid`) rather than broad `if true` allowances "to make it work for now."

## Authentication

- Use Firebase Auth's built-in providers where they fit rather than hand-rolling custom auth flows; enable App Check for production apps to reduce abuse from non-genuine clients.
- Verify ID tokens server-side (Cloud Functions/Admin SDK) for any privileged operation — never trust a client-supplied UID without verifying the token that produced it.

## Cloud Functions

- Keep functions small and single-purpose; use Firestore triggers for reactive data processing (e.g., updating a denormalized counter) rather than polling.
- Set explicit memory/timeout configuration matched to the function's actual workload — defaults are not tuned for every use case, and under-provisioning causes silent timeouts on heavier functions.
- Guard against trigger recursion (a function that writes to the same collection/field that triggered it) with a condition check, since an unguarded loop can run indefinitely and rack up cost.

## Common pitfalls

- Don't treat Firestore reads/writes as free — every read/write/delete is billed individually, so a query pattern that re-reads a full collection repeatedly should be reconsidered for indexed, filtered queries or caching.
