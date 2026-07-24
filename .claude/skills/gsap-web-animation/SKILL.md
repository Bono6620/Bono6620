---
name: gsap-web-animation
description: Use when building web animations with GSAP — timelines, scroll-triggered animation, and performance-conscious motion design.
---

# GSAP Web Animation

## Timelines

- Use `gsap.timeline()` to sequence related animations instead of chaining manual `delay` values on separate tweens — timelines keep sequencing readable and let you scrub/reverse/control the whole sequence as one unit.
- Label key points in a timeline (`tl.addLabel('reveal')`) when other code needs to seek to or sync with a specific point, rather than hardcoding time offsets that break when durations change.
- Use timeline defaults (`{defaults: {ease, duration}}`) to keep a consistent motion feel across a sequence instead of repeating the same easing/duration on every tween.

## Performance

- Animate `transform` and `opacity` wherever possible — GPU-accelerated properties keep animation smooth, while animating layout-triggering properties (`width`, `top`, `left`) forces expensive reflows.
- Use `will-change` sparingly and only on elements actually mid-animation; leaving it set permanently on many elements can hurt performance rather than help it.
- Kill or pause tweens/ScrollTriggers on unmounted components (in a React/Vue/etc. app) — orphaned GSAP instances continue running and leak memory.

## ScrollTrigger

- Set `start`/`end` values relative to meaningful viewport/element positions (`top 80%`, `bottom top`) rather than pixel values that break at other viewport sizes.
- Use `scrub: true` for animations that should track scroll position directly, and plain triggers (`toggleActions`) for animations that should play once when entering view — don't reach for scrub when a one-shot reveal is what's actually wanted.
- Call `ScrollTrigger.refresh()` after any dynamic layout change (content loading, image loading affecting height) so trigger positions stay accurate.

## Accessibility

- Respect `prefers-reduced-motion`: check the media query and skip or simplify non-essential animation for users who've opted out, rather than forcing motion on everyone.
- Ensure animated content remains reachable/readable without JavaScript or with animation disabled — GSAP should enhance content, not gate access to it.

## Common pitfalls

- Don't animate the same property with both CSS transitions and GSAP simultaneously — they fight for control of the property and produce inconsistent results.
