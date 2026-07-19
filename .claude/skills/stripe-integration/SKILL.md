---
name: stripe-integration
description: Use when integrating Stripe for payments, subscriptions, or webhooks — setting up checkout, handling webhook events, or upgrading a Stripe SDK/API version.
---

# Stripe Integration

## Core setup

- Never expose the secret key (`sk_...`) to client code — it belongs only in server-side environment variables. Only the publishable key (`pk_...`) goes to the browser.
- Use Stripe Checkout or the Payment Element for card collection whenever possible instead of building custom card forms — it keeps you out of full PCI-DSS scope.
- Pin the API version explicitly (`Stripe-Version` header or SDK config) so a Stripe-side API upgrade can't silently change your integration's behavior.

## Webhooks

- Always verify the webhook signature (`stripe.webhooks.constructEvent`) using the raw request body — a parsed/re-serialized body will fail verification.
- Handle webhooks idempotently: Stripe can and will redeliver the same event, so key any side effect (granting access, sending an email) off the event ID, not just "we received an event."
- Return a 2xx quickly and do slow work (emails, downstream calls) asynchronously — Stripe retries on timeout, which can cause duplicate processing if the handler is slow and non-idempotent.

## Subscriptions

- Treat Stripe's subscription/invoice objects as the source of truth for billing state; don't try to independently compute "is this customer paid" from cached data without reconciling via webhooks.
- Handle the full lifecycle: `checkout.session.completed`, `customer.subscription.updated`, `customer.subscription.deleted`, and `invoice.payment_failed` are the minimum set for a subscription product.

## Upgrading the SDK

- Read the changelog for breaking changes before bumping a major SDK version — Stripe versions its API independently from the SDK, so an SDK bump can surface previously-hidden API version drift.
- Run the integration against Stripe's test mode and a webhook replay before deploying an upgrade.
