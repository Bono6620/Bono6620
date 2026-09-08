<?php
if (!defined('ABSPATH')) {
    exit;
}

function fa_capability_list(): array
{
    $types = [
        'fa_member' => 'fa_members',
        'fa_event' => 'fa_events',
        'fa_document' => 'fa_documents',
        'fa_file' => 'fa_files',
    ];

    $caps = [];
    foreach ($types as $singular => $plural) {
        $caps["edit_{$singular}"] = true;
        $caps["read_{$singular}"] = true;
        $caps["delete_{$singular}"] = true;
        $caps["edit_{$plural}"] = true;
        $caps["edit_others_{$plural}"] = true;
        $caps["publish_{$plural}"] = true;
        $caps["read_private_{$plural}"] = true;
        $caps["delete_{$plural}"] = true;
        $caps["delete_private_{$plural}"] = true;
        $caps["delete_published_{$plural}"] = true;
        $caps["delete_others_{$plural}"] = true;
        $caps["edit_private_{$plural}"] = true;
        $caps["edit_published_{$plural}"] = true;
    }

    return $caps;
}

function fa_register_roles(): void
{
    $editorCaps = fa_capability_list();
    $editorCaps['read'] = true;
    $editorCaps['upload_files'] = true;

    remove_role('family_editor');
    add_role('family_editor', 'محرر الأرشيف', $editorCaps);

    remove_role('family_viewer');
    add_role('family_viewer', 'مشاهدة فقط', ['read' => true]);

    $admin = get_role('administrator');
    if ($admin) {
        foreach (array_keys($editorCaps) as $cap) {
            $admin->add_cap($cap);
        }
    }

    update_option('fa_roles_version', FA_VERSION);
}

add_action('init', 'fa_maybe_upgrade_roles', 1);

function fa_maybe_upgrade_roles(): void
{
    if (get_option('fa_roles_version') !== FA_VERSION) {
        fa_register_roles();
    }
}

function fa_user_can_edit(): bool
{
    return current_user_can('edit_fa_members') || current_user_can('manage_options');
}
