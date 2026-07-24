---
name: programmatic-video-remotion
description: Use when creating programmatic/generative video with Remotion — data-driven video generation, animations defined in React, and rendering pipelines.
---

# Programmatic Video with Remotion

## Core concepts

- A Remotion video is a React component tree rendered frame-by-frame; drive all animation off `useCurrentFrame()` and `fps`, never off wall-clock time or CSS transitions/animations (those don't render deterministically frame-by-frame).
- Compose scenes with `<Sequence>` to control when each part starts/stops within the overall timeline, rather than manually branching on frame ranges inside one giant component.
- Keep compositions data-driven: pass content (text, images, data points) as props so the same composition can render many videos from different inputs.

## Animation

- Use `interpolate()` for value ranges (position, opacity, scale) mapped from frame number, with explicit `extrapolateLeft`/`extrapolateRight` so behavior outside the defined range is intentional, not accidental clamping or overshoot.
- Use `spring()` for natural-feeling motion instead of hand-tuning easing curves, unless a specific non-physical easing is the design intent.
- Keep expensive computation (data fetching, heavy processing) out of the render path — precompute it and pass as props, since the render path runs once per frame.

## Assets & data

- Use `staticFile()` for bundled assets so paths resolve correctly both in the Remotion Studio preview and in the final render.
- For data-driven videos (e.g., a video per user/record), generate a list of input props and render each as a separate composition invocation rather than trying to loop within a single video.

## Rendering

- Render with `@remotion/renderer` (Node API or CLI) rather than screen-recording the preview — it produces frame-accurate output and runs headless in CI.
- Set an explicit `concurrency` appropriate to the machine when rendering many videos or long compositions; the default may not be optimal for batch jobs.
- Preview at a lower resolution/quality during development and only render final quality once the composition is locked, to keep iteration fast.

## Common pitfalls

- Don't use `setTimeout`/`setInterval` or DOM animation APIs inside a composition — they run in real time, not simulation time, and will desync from the rendered frame sequence.
