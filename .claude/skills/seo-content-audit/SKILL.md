---
name: seo-content-audit
description: Use when the user wants to improve a page or site's search/AI-answer visibility — technical SEO audits, on-page content review, or optimizing for AI-generated answer engines (AEO).
---

# SEO & Content Audit

## Technical SEO checklist

- One `<title>` and one meta description per page, both unique and under ~60/~155 characters respectively.
- Exactly one `<h1>`; a logical, nested heading hierarchy after that (no skipped levels).
- Canonical tags on any page that could be reached via multiple URLs (query params, trailing slash, http/https).
- `robots.txt` and sitemap.xml present and consistent — nothing blocked in `robots.txt` that you actually want indexed.
- Core Web Vitals (LCP, CLS, INP) in the "good" range — check with real-user or lab data, not just a single Lighthouse run.

## On-page content

- Match search intent: informational queries need an actual answer near the top of the page, not just after a long preamble.
- Use descriptive, keyword-relevant anchor text for internal links instead of "click here."
- Add structured data (schema.org markup — Article, Product, FAQ, etc.) matching the actual page content; mismatched schema can trigger manual penalties.

## Optimizing for AI answer engines (AEO)

- Write content that directly answers a specific question in the first 1-2 sentences of a section — this is what gets extracted into AI-generated summaries.
- Use clear headings phrased as the questions users actually ask.
- Keep facts, numbers, and claims verifiable and attributable (cite a source) — answer engines favor content they can confidently ground.

## Audit output

- Prioritize findings by impact × effort, not just by count — a single broken canonical on the homepage outweighs a dozen missing alt tags on low-traffic pages.
