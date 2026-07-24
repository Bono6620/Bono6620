---
name: terraform-best-practices
description: Use when writing, reviewing, or refactoring Terraform/HCL — new resources, modules, providers, or infrastructure changes. Covers style, module structure, state safety, and testing conventions.
---

# Terraform Best Practices

## Style & structure

- One logical resource group per file; name files by purpose (`network.tf`, `iam.tf`), not by resource type.
- Use variables with explicit `type` and `description`; give every variable a sensible default only when a safe default genuinely exists.
- Prefer data sources over hardcoded IDs/ARNs for anything that already exists.
- Keep modules small and composable — a module should do one thing (e.g., "a VPC", not "the whole environment").

## State safety

- Never hand-edit `.tfstate`. Use `terraform state mv`/`rm` for refactors that change resource addresses.
- Always run `terraform plan` and read the diff before `apply` — pay special attention to any line showing `-/+` (destroy and recreate).
- Use remote state with locking (S3+DynamoDB, Terraform Cloud, etc.) for anything beyond a solo sandbox.
- Treat `terraform apply -auto-approve` as a red flag outside of CI pipelines with their own review gate.

## Change discipline

- For a resource rename, check whether it's actually a rename (`moved` block) vs. a destroy+recreate — the latter can cause downtime for stateful resources (databases, load balancers).
- Tag/label resources consistently (environment, owner, cost-center) so `plan` diffs and cost reports stay legible.
- Run `terraform validate` and `terraform fmt` before proposing a change.

## Testing

- For provider/module development, write acceptance-style tests that apply real infrastructure against a test account and then destroy it — never test against production.
- Prefer small, incremental applies over one giant plan when standing up new infrastructure, so failures are easy to localize.
