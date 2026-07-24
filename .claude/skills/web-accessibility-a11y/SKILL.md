---
name: web-accessibility-a11y
description: Use when building, reviewing, or auditing a web interface for accessibility — semantic HTML, keyboard navigation, screen-reader support, and WCAG conformance checks.
---

# Web Accessibility (A11y)

## Foundations

- Use semantic HTML elements (`<button>`, `<nav>`, `<main>`, `<label>`) instead of styled `<div>`s with click handlers — semantic elements come with keyboard interaction, focus behavior, and screen-reader announcements built in for free.
- Maintain a logical heading hierarchy (one `<h1>`, no skipped levels) so screen-reader users can navigate by heading structure, which is one of the most common ways they scan a page.
- Every interactive element needs an accessible name: visible text, `aria-label`, or an associated `<label>` — an icon-only button with no accessible name is invisible to assistive technology users.

## Keyboard & focus

- Every interactive element must be reachable and operable via keyboard alone (Tab, Enter/Space, arrow keys where appropriate) — test by unplugging the mouse, not just by reading the code.
- Manage focus explicitly on dynamic UI changes: moving focus into a newly opened modal, returning it to the trigger element on close, and never trapping focus in a component that isn't a genuine modal.
- Keep a visible focus indicator; removing `outline` without a replacement focus style makes keyboard navigation unusable, not just less pretty.

## ARIA

- Use ARIA to fill gaps semantic HTML can't cover (custom widgets like comboboxes, tab panels), not as a default layer on top of elements that already have the right semantics — the first rule of ARIA is not to use it when a native element would do.
- Keep ARIA state attributes (`aria-expanded`, `aria-selected`, `aria-hidden`) in sync with actual UI state; stale ARIA state is often worse than none, since it actively misinforms assistive technology.

## Visual & content

- Meet WCAG contrast ratios (4.5:1 for normal text, 3:1 for large text) for all meaningful text/background combinations, checked with an actual contrast tool, not by eye.
- Don't convey information by color alone (error states, required fields, chart series) — pair color with text, icon, or pattern.
- Write meaningful `alt` text for informative images (describing content/function) and empty `alt=""` for purely decorative images, not a filename or "image" as filler.

## Testing

- Combine automated tools (axe, Lighthouse) for catching mechanical issues with manual testing (keyboard-only navigation, a real screen reader like VoiceOver/NVDA) — automated tools catch roughly a third of real accessibility issues at best.
