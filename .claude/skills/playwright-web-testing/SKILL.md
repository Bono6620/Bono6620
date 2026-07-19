---
name: playwright-web-testing
description: Use when the user wants to test a local or deployed web app end-to-end, automate browser interactions, or verify a UI change actually works ("test this page with Playwright", "click through the signup flow", "take a screenshot of the dashboard").
---

# Playwright Web Testing

Guidance for driving a real browser to verify web UI behavior rather than trusting code alone.

## When to reach for this

- After any frontend change, before declaring it done.
- When a bug report describes a UI interaction that's easier to reproduce than to reason about.
- When the user wants a screenshot or recorded flow as evidence.

## Workflow

1. **Confirm the app is running** — start the dev server if needed, and note the URL/port.
2. **Write the smallest script that exercises the real flow**, e.g.:
   ```js
   const { chromium } = require('playwright');
   const browser = await chromium.launch();
   const page = await browser.newPage();
   await page.goto('http://localhost:3000');
   await page.click('text=Sign up');
   await page.fill('#email', 'test@example.com');
   await page.screenshot({ path: 'signup.png' });
   await browser.close();
   ```
3. **Prefer role/text-based locators** (`getByRole`, `getByText`) over brittle CSS selectors tied to implementation details.
4. **Assert on visible state**, not internal state — text content, element visibility, network responses.
5. **Cover the golden path and one edge case** (empty input, error state, slow network) rather than only the happy path.

## Pitfalls to avoid

- Don't add arbitrary `sleep`/`waitForTimeout` calls — use Playwright's auto-waiting or explicit `waitFor` on a condition.
- Don't claim a UI fix works based on type-checking or unit tests alone — actually drive the browser.
- Clean up: close browser contexts/pages so headless runs don't leak processes.
