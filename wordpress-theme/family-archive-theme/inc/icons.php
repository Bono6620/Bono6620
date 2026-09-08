<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Small hand-built line-icon set (circle/rect/line/polyline/polygon only,
 * no freehand path data) so every glyph renders correctly everywhere -
 * used instead of emoji for a consistent, professional look.
 */
function fa_icon(string $name, string $class = ''): string
{
    $inner = match ($name) {
        'tree' => '<circle cx="12" cy="5" r="2.6"/><line x1="12" y1="7.6" x2="12" y2="11"/><line x1="6" y1="15" x2="12" y2="11"/><line x1="18" y1="15" x2="12" y2="11"/><circle cx="6" cy="17.4" r="2.6"/><circle cx="18" cy="17.4" r="2.6"/>',
        'folder' => '<rect x="3" y="5" width="7" height="3" rx="1"/><rect x="3" y="7" width="18" height="12" rx="2"/>',
        'clock' => '<circle cx="12" cy="12" r="8.5"/><line x1="12" y1="12" x2="12" y2="7.5"/><line x1="12" y1="12" x2="15.5" y2="13.5"/>',
        'scroll' => '<rect x="5" y="3" width="14" height="18" rx="2"/><line x1="8.5" y1="8" x2="15.5" y2="8"/><line x1="8.5" y1="12" x2="15.5" y2="12"/><line x1="8.5" y1="16" x2="13" y2="16"/>',
        'user' => '<circle cx="12" cy="8" r="3.6"/><polygon points="4.5,20 8,13.5 16,13.5 19.5,20"/>',
        'sliders' => '<line x1="4" y1="7" x2="20" y2="7"/><circle cx="9" cy="7" r="2"/><line x1="4" y1="13" x2="20" y2="13"/><circle cx="16" cy="13" r="2"/><line x1="4" y1="19" x2="20" y2="19"/><circle cx="11" cy="19" r="2"/>',
        'logout' => '<rect x="4" y="4" width="9" height="16" rx="1.5"/><polyline points="13,8 18,12 13,16"/><line x1="18" y1="12" x2="9.5" y2="12"/>',
        'search' => '<circle cx="10.5" cy="10.5" r="6.5"/><line x1="15.3" y1="15.3" x2="20" y2="20"/>',
        'plus' => '<line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>',
        'edit' => '<polyline points="5,19 5,15 15.5,4.5 19,8 8.5,18.5 4.5,19.5"/><line x1="13" y1="6.5" x2="17" y2="10.5"/>',
        'trash' => '<line x1="4" y1="7" x2="20" y2="7"/><rect x="6" y="7" width="12" height="13" rx="1.5"/><line x1="10" y1="10.5" x2="10" y2="16.5"/><line x1="14" y1="10.5" x2="14" y2="16.5"/><polyline points="9,7 9,4 15,4 15,7"/>',
        'mosque' => '<circle cx="12" cy="7" r="3"/><line x1="12" y1="4" x2="12" y2="1.5"/><rect x="5" y="10" width="14" height="10" rx="1"/><polygon points="9,20 9,15 12,12 15,15 15,20"/>',
        'scale' => '<line x1="12" y1="4" x2="12" y2="19"/><line x1="5" y1="7" x2="19" y2="7"/><circle cx="5" cy="11" r="3.2"/><circle cx="19" cy="11" r="3.2"/><line x1="8" y1="20" x2="16" y2="20"/>',
        'leaf' => '<path d="M5 19c0-8 4-14 14-14 0 10-6 14-14 14z" fill="currentColor" stroke="none"/><line x1="6" y1="18" x2="16" y2="7"/>',
        'lock' => '<rect x="5" y="10.5" width="14" height="10" rx="1.5"/><path d="M8 10.5v-3a4 4 0 0 1 8 0v3" fill="none"/>',
        'photo' => '<rect x="3.5" y="5" width="17" height="14" rx="2"/><circle cx="9" cy="10.5" r="1.8"/><polyline points="4,18 9.5,13 13,16 16,13.5 20,17"/>',
        'chevron-down' => '<polyline points="5,8.5 12,15.5 19,8.5"/>',
        'users' => '<circle cx="8.5" cy="8" r="3"/><polygon points="2.5,19 5,13.5 12,13.5 14.5,19"/><circle cx="17" cy="9" r="2.4"/><path d="M15 13.5h5l2 5.5h-5.2" fill="none"/>',
        'calendar' => '<rect x="4" y="5.5" width="16" height="14.5" rx="2"/><line x1="4" y1="10" x2="20" y2="10"/><line x1="8" y1="3.5" x2="8" y2="7"/><line x1="16" y1="3.5" x2="16" y2="7"/>',
        'shield' => '<path d="M12 3.5 19 6.5v5.3c0 4.6-3 7.8-7 8.7-4-0.9-7-4.1-7-8.7V6.5z" fill="none"/><polyline points="9,12 11,14 15.5,9.2"/>',
        'download' => '<line x1="12" y1="4" x2="12" y2="14.5"/><polyline points="7.5,11 12,15.5 16.5,11"/><line x1="5" y1="19" x2="19" y2="19"/>',
        'key' => '<circle cx="7.5" cy="14.5" r="3.5"/><line x1="10" y1="12" x2="19" y2="3"/><line x1="15" y1="7" x2="18" y2="10"/><line x1="17.5" y1="4.5" x2="20" y2="7"/>',
        'arrow-back' => '<line x1="4" y1="12" x2="20" y2="12"/><polyline points="12,5 19,12 12,19"/>',
        default => '<circle cx="12" cy="12" r="8.5"/>',
    };

    $cls = $class ? ' ' . esc_attr($class) : '';
    return '<svg class="fa-icon' . $cls . '" viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . $inner . '</svg>';
}
