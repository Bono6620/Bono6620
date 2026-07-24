---
name: wordpress-plugin-development
description: Use when building or reviewing a WordPress plugin or theme — hooks, blocks, REST API endpoints, and WP-CLI workflows.
---

# WordPress Plugin & Theme Development

## Plugin structure

- One clear entry file with the plugin header comment (`Plugin Name`, `Version`, etc.); keep logic in included/autoloaded classes rather than one giant file.
- Namespace or prefix every function, class, and hook callback (`myplugin_` / `MyPlugin\`) — WordPress has a single global function/class namespace, and collisions between plugins are a common source of fatal errors.
- Use activation/deactivation hooks for setup/teardown (creating tables, scheduling cron), not plugin load time — code at load time runs on every request.

## Hooks & extensibility

- Prefer filters over direct output when a value might need customizing by another plugin/theme (`apply_filters` around computed values, not just `do_action` for side effects).
- Use the earliest hook that has the data you need, not the latest one that happens to work — hooking too late causes ordering bugs with other plugins.
- Document custom hooks you introduce (name, parameters, when it fires) so themes/other plugins can safely extend the plugin.

## Blocks (Gutenberg)

- Register blocks via `block.json` (metadata-driven) rather than manual `register_block_type` calls with inline arrays — it's the modern, tooling-friendly approach and required for block directory submission.
- Keep block edit/save output in sync: a save function that doesn't match what `edit` produces causes "block validation failed" errors on every affected post.
- Use `useBlockProps` in the edit component so the block wrapper gets the correct classes/attributes the editor expects.

## REST API & data

- Register custom REST routes with explicit `permission_callback` — omitting it (or defaulting to `__return_true` carelessly) is a common vulnerability in WordPress plugins.
- Sanitize on input, escape on output: `sanitize_text_field`/`absint` etc. when saving, `esc_html`/`esc_attr`/`esc_url` when rendering — never trust that sanitized-on-save data is safe to print unescaped.
- Use `$wpdb->prepare()` for any custom SQL touching user input; never concatenate raw input into a query.

## WP-CLI & tooling

- Use WP-CLI for repeatable setup/migration tasks (`wp plugin`, `wp db`, custom commands via `WP_CLI::add_command`) instead of one-off admin-ajax scripts.
