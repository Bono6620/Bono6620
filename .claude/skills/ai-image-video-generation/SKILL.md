---
name: ai-image-video-generation
description: Use when generating or editing images/video with AI models (text-to-image, image editing, upscaling, lip-sync, text-to-video) via an API-based generation platform.
---

# AI Image & Video Generation

## Prompting

- Be concrete and specific: describe subject, composition, lighting, style, and camera framing explicitly rather than relying on vague adjectives — specificity is what actually steers these models.
- Iterate in small steps: change one variable (style, composition, subject detail) between generations so you can tell what caused a given change in output.
- For image editing (inpainting/outpainting/style transfer), a tightly scoped mask and instruction produces more predictable results than an open-ended "make it better" prompt.

## Model selection

- Match the model to the task: some models are tuned for photorealism, others for stylized/illustrative output, others specifically for fast iteration vs. final quality — check a platform's model catalog rather than defaulting to whichever model is listed first.
- For video, check whether the model does text-to-video, image-to-video (animating a still), or lip-sync/talking-head specifically — these are different capabilities even when marketed under one "video generation" umbrella.

## Workflow

- Generate at lower resolution/fewer steps first to validate composition and prompt direction, then commit to full-quality generation only once the direction is right — full-res generation is the expensive step.
- Chain specialized models when a single model can't do the whole job (e.g., generate a base image, then a dedicated upscaler, then a dedicated face-restoration pass) rather than expecting one model to excel at every sub-task.
- Save prompts and seeds alongside outputs — reproducibility matters when a client or teammate asks "can we get more like this one."

## Rights & usage

- Check the platform's and underlying model's license/terms for commercial usage rights before using generated output in a commercial product — permissions vary significantly between providers and model licenses.
- Be explicit with clients/stakeholders about which assets are AI-generated when that materially matters for the use case (disclosure requirements vary by platform, jurisdiction, and use case).

## Common pitfalls

- Don't assume a model handles text-in-image (logos, readable signage) reliably — most generation models still render legible text poorly; plan for a manual typography pass if exact text matters.
