<?php
if (!defined('ABSPATH')) {
    exit;
}

function fa_get_member(int $id): ?WP_Post
{
    if (!$id) {
        return null;
    }
    $post = get_post($id);
    return ($post && $post->post_type === 'fa_member') ? $post : null;
}

function fa_member_meta(int $memberId): array
{
    return [
        'gender' => get_post_meta($memberId, '_fa_gender', true) ?: 'male',
        'birth_year' => (int) get_post_meta($memberId, '_fa_birth_year', true) ?: null,
        'death_year' => (int) get_post_meta($memberId, '_fa_death_year', true) ?: null,
        'father_id' => (int) get_post_meta($memberId, '_fa_father_id', true) ?: null,
        'mother_id' => (int) get_post_meta($memberId, '_fa_mother_id', true) ?: null,
        'spouse_id' => (int) get_post_meta($memberId, '_fa_spouse_id', true) ?: null,
    ];
}

function fa_get_all_members(): array
{
    return get_posts([
        'post_type' => 'fa_member',
        'numberposts' => -1,
        'orderby' => 'title',
        'order' => 'ASC',
        'post_status' => 'publish',
    ]);
}

function fa_build_member_index(): array
{
    $byId = [];
    foreach (fa_get_all_members() as $post) {
        $byId[$post->ID] = [
            'post' => $post,
            'meta' => fa_member_meta($post->ID),
        ];
    }

    $childrenOf = [];
    foreach ($byId as $id => $data) {
        $fatherId = $data['meta']['father_id'];
        $motherId = $data['meta']['mother_id'];
        if ($fatherId && isset($byId[$fatherId])) {
            $childrenOf[$fatherId][] = $id;
        } elseif ($motherId && isset($byId[$motherId])) {
            $childrenOf[$motherId][] = $id;
        }
    }

    foreach ($childrenOf as $parentId => $ids) {
        usort($ids, function ($a, $b) use ($byId) {
            $ay = $byId[$a]['meta']['birth_year'] ?? 9999;
            $by = $byId[$b]['meta']['birth_year'] ?? 9999;
            return $ay <=> $by;
        });
        $childrenOf[$parentId] = $ids;
    }

    return ['byId' => $byId, 'childrenOf' => $childrenOf];
}

function fa_get_root_ids(array $index): array
{
    $roots = [];
    foreach ($index['byId'] as $id => $data) {
        if (!empty($data['meta']['father_id']) || !empty($data['meta']['mother_id'])) {
            continue;
        }
        $spouseId = $data['meta']['spouse_id'];
        if ($spouseId && isset($index['byId'][$spouseId])) {
            $spouseMeta = $index['byId'][$spouseId]['meta'];
            if (!empty($spouseMeta['father_id']) || !empty($spouseMeta['mother_id'])) {
                continue;
            }
            if ($spouseId < $id) {
                continue;
            }
        }
        $roots[] = $id;
    }
    usort($roots, function ($a, $b) use ($index) {
        $ay = $index['byId'][$a]['meta']['birth_year'] ?? 9999;
        $by = $index['byId'][$b]['meta']['birth_year'] ?? 9999;
        return $ay <=> $by;
    });
    return $roots;
}

function fa_member_full_name(WP_Post $member): string
{
    return get_the_title($member);
}

function fa_member_years_label(array $meta): string
{
    if (!$meta['birth_year'] && !$meta['death_year']) {
        return '';
    }
    $birth = $meta['birth_year'] ?: '؟';
    if ($meta['death_year']) {
        return $birth . ' - ' . $meta['death_year'];
    }
    return (string) $birth;
}

function fa_member_photo_url(WP_Post $member): string
{
    $thumbId = get_post_thumbnail_id($member);
    if ($thumbId) {
        $url = wp_get_attachment_image_url($thumbId, 'thumbnail');
        if ($url) {
            return $url;
        }
    }
    $meta = fa_member_meta($member->ID);
    $file = $meta['gender'] === 'female' ? 'avatar-female.svg' : 'avatar-male.svg';
    return FA_URL . 'assets/img/' . $file;
}
