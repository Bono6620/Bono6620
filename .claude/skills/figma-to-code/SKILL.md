---
name: figma-to-code
description: Use when converting a Figma design into production frontend code, or connecting existing components back to a Figma design system (Code Connect). Covers reading design specs and translating them faithfully.
---

# Figma to Code

## Reading the design

- Extract the actual values (spacing, colors, font sizes/weights, corner radii) rather than eyeballing — use exact tokens where the file defines them.
- Identify reusable components and variants in the Figma file first; map each to an existing component in the codebase before creating a new one, to avoid duplicate implementations.
- Note responsive behavior (auto-layout resizing, breakpoints) — a static frame doesn't tell you how the component should behave at other widths unless auto-layout constraints are set.

## Implementing

- Match the design system's existing primitives (spacing scale, color tokens, typography scale) instead of hardcoding pixel values pulled straight from Figma — a "17px" gap in the design usually maps to the nearest scale token.
- Preserve semantic HTML structure even when the design is purely visual — headings, buttons, and landmarks matter for accessibility regardless of what the design file shows.
- Implement interactive states (hover, focus, active, disabled, loading) even when the design only shows the default state — infer them from the design system's conventions.

## Code Connect (linking components to Figma)

- Map each Figma component variant to the corresponding code component prop, so designers see the real implementation's prop names in Figma's dev mode.
- Keep the mapping file next to the component it documents, and update it in the same PR that changes the component's public API.

## Verification

- Compare the rendered result against the Figma frame at the same viewport width before calling it done — check spacing and alignment, not just "it looks roughly right."
