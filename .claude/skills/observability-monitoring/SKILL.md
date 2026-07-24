---
name: observability-monitoring
description: Use when setting up or reviewing application observability — metrics, logs, traces, dashboards, and alerts (Datadog-style platforms or general observability practice).
---

# Observability & Monitoring

## The three pillars

- **Logs**: structured (JSON) over free-text, with consistent field names across services (`service`, `env`, `trace_id`, `level`) so they're queryable and correlatable, not just human-readable.
- **Metrics**: track the RED method for services (Rate, Errors, Duration) and the USE method for resources (Utilization, Saturation, Errors) as the default starting set before adding custom business metrics.
- **Traces**: propagate a trace/correlation ID across every service boundary (HTTP headers, message queue metadata) so a single request can be followed end-to-end across a distributed system.

## Instrumentation

- Instrument at the boundaries first (incoming requests, outgoing calls, database queries, queue consume/publish) — that's where latency and failures actually originate, and where dashboards get the most value fastest.
- Tag metrics/logs/traces with consistent dimensions (service, version, environment, region) so they can be sliced the same way across all three pillars during an incident.
- Avoid high-cardinality tags on metrics (user ID, request ID) — that's what logs and traces are for; metrics tags should have bounded cardinality or the backend's cost and query performance degrade badly.

## Dashboards

- Build dashboards around a specific audience's question ("is checkout healthy right now?") rather than dumping every available metric on one screen — an unfocused dashboard doesn't get looked at during an incident.
- Put the SLO/error-budget-relevant metrics at the top of any service dashboard, not buried among secondary metrics.

## Alerting

- Alert on symptoms users would notice (error rate, latency, availability) as the primary alerts; use cause-level alerts (CPU, queue depth) as supporting context, not as the main page-worthy signal — alerting on every resource metric causes fatigue.
- Set thresholds from actual historical baselines, not round numbers picked without data — an alert that fires constantly gets ignored, and one that never fires provides false confidence.
- Every alert should link to a runbook or have enough context in the alert body that the responder knows the first diagnostic step without hunting for it.

## Common pitfalls

- Don't treat "we added monitoring" as done once dashboards exist — validate that alerts actually fire correctly by testing them against a known failure, not just trusting the configuration.
