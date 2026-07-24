---
name: huggingface-ml-workflows
description: Use when working with Hugging Face Hub, datasets, model training/fine-tuning, or deploying models via Spaces/Inference. Covers the practical workflow, not ML theory.
---

# Hugging Face ML Workflows

## Hub basics

- Use the `huggingface_hub` Python library or `hf` CLI for programmatic upload/download rather than manual web UI steps when the task is repeatable.
- Pin model/dataset revisions (a commit hash or tag) in production code — the default branch can change under you if the upstream repo updates.
- Check a model's license and intended-use section on its model card before deploying it — permissive hosting doesn't imply permissive commercial use.

## Datasets

- Use `datasets.load_dataset` with streaming (`streaming=True`) for large datasets that don't fit in memory, rather than downloading the full set first.
- Version and document any preprocessing applied before training — a dataset card should say exactly what transforms were applied so results are reproducible.
- Split data before any exploratory analysis that could leak information from the eventual test set.

## Fine-tuning / training

- Start from the smallest model that could plausibly solve the task before reaching for a larger one — iteration speed matters more early on than final accuracy.
- Use `Trainer`/TRL abstractions for standard fine-tuning loops instead of hand-rolling training loops, unless the task genuinely needs custom loss/logging logic.
- Log training runs (loss curves, eval metrics, hyperparameters) somewhere queryable (Trackio, Weights & Biases, or even a structured CSV) — "it trained" isn't a result without the curve.
- Evaluate on a held-out set with the same metric the task will actually be judged on, not just training loss.

## Deployment

- Use Spaces (Gradio/Streamlit) for a quick interactive demo; use Inference Endpoints or a dedicated serving stack (vLLM, TGI) for production-grade throughput/latency needs — Spaces free tier is not meant for production traffic.
- Quantize or distill before deploying to resource-constrained environments; benchmark actual latency/memory on the target hardware, not just parameter count.

## Common pitfalls

- Don't assume a model card's benchmark numbers transfer to your specific domain/data distribution — validate on your own held-out data.
- Watch context-length and tokenizer mismatches when swapping a base model — a fine-tune trained with one tokenizer will silently misbehave with another.
