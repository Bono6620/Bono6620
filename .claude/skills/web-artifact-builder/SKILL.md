---
name: web-artifact-builder
description: Use when building a self-contained, polished HTML/React artifact (dashboards, interactive tools, styled reports) meant to be viewed as a standalone page rather than integrated into an existing app.
---

# Web Artifact Builder

## Before writing markup

- Decide the medium first: a static report reads better as clean semantic HTML+CSS; an interactive tool (filters, live calculations, drag-and-drop) justifies React and client-side state.
- Sketch the layout in terms of sections and hierarchy before picking colors — visual polish can't fix a confusing structure.

## Building

- Keep everything self-contained: inline CSS/JS, no external CDN calls, no remote fonts unless embedded — assume the strictest possible execution sandbox.
- Use relative units and flexbox/grid so the layout survives different viewport widths; wide content (tables, code, diagrams) should scroll inside its own container, never force the whole page to scroll horizontally.
- Style for both light and dark viewing — use `prefers-color-scheme` at minimum, and respect an explicit theme override if the host environment provides one.
- Favor a small, deliberate color palette over defaulting to a framework's full color scale; consistency across a dashboard's stat tiles, charts, and text reads as one designed system rather than assembled parts.

## Interactivity

- Every interactive element needs a visible state change on hover/focus/active — a button that looks static until clicked feels broken.
- Debounce expensive recomputation on input changes (search, filters) rather than recalculating on every keystroke.

## Before shipping

- Resize the viewport mentally (or literally, if testing in a browser) from narrow to wide and confirm nothing overflows or clips.
- Check both color-scheme variants render legibly — text contrast against background is the most common miss.
