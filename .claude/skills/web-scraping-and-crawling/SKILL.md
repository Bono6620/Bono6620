---
name: web-scraping-and-crawling
description: Use when extracting structured data from websites — single-page scraping, multi-step crawls, or search-then-extract workflows. Covers ethical/legal boundaries alongside the technical approach.
---

# Web Scraping & Crawling

## Before scraping anything

- Check the target site's `robots.txt` and terms of service — some explicitly prohibit automated scraping, and ignoring that carries legal risk regardless of technical feasibility.
- Prefer an official API when one exists; scraping is a fallback, not a default, when structured data is already offered another way.
- Identify your scraper with a proper `User-Agent` rather than spoofing a browser to evade detection, unless the task is explicitly an authorized security assessment.

## Single-page extraction

- Inspect whether content is server-rendered (plain HTTP GET + HTML parse suffices) or client-rendered (needs a real browser/headless automation) before choosing a tool — reaching for a full browser when static HTML would do wastes time and resources.
- Extract by stable selectors (data attributes, semantic structure) rather than brittle generated class names that change on every deploy.
- Validate extracted fields against expected types/formats before using them downstream — a scraper that silently returns `None` or malformed data is worse than one that errors loudly.

## Multi-step / interactive flows

- For flows requiring login, pagination, or JS-driven interaction, use a real or headless browser (Playwright) and drive it like a user would — click, wait for network idle or a specific element, then extract.
- Add explicit waits tied to real conditions (element visible, network idle) rather than fixed `sleep` calls, which are both slow and unreliable across page-load variance.

## Crawling at scale

- Respect crawl-delay and rate limits — hammering a site is both hostile and likely to get your IP blocked, defeating the purpose.
- Deduplicate URLs and track visited state explicitly to avoid infinite loops on sites with circular or parameterized links.
- Store raw responses alongside parsed output during development — when a parser breaks on a page structure change, you need the raw HTML to debug against, not just the failed extraction.

## Query-first discovery

- When the goal is "find pages about X" rather than "scrape this known page," search first (via a search API) and only crawl/extract the specific results that match, instead of blindly crawling a whole site.
