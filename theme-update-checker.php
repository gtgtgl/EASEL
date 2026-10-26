<?php

add_filter('update_themes_easel.gt-gt.org', function (
    $update,
    $theme_data,
    $theme_stylesheet
) {
    if ($theme_stylesheet !== 'easel') {
        return $update;
    }

    $response = wp_remote_get(
        'https://easel.gt-gt.org/downloads/theme-update.json',
        ['timeout' => 10]
    );

    if (is_wp_error($response)) {
        return false;
    }

    if (wp_remote_retrieve_response_code($response) !== 200) {
        return false;
    }

    $data = json_decode(
        wp_remote_retrieve_body($response),
        true
    );

    if (
        !is_array($data) ||
        empty($data['version']) ||
        empty($data['download_url']) ||
        !is_string($data['version']) ||
        !is_string($data['download_url']) ||
        !wp_http_validate_url($data['download_url'])
    ) {
        return false;
    }

return [
    'theme'   => $theme_stylesheet,
    'version' => $data['version'],
    'url'     => !empty($data['details_url'])
        ? esc_url_raw($data['details_url'])
        : 'https://easel.gt-gt.org/',
    'package' => $data['download_url'],
];
}, 10, 3);