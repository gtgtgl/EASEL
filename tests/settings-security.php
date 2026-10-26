<?php
// Included by wordpress.php in a disposable WordPress installation.
$description = '<strong>C:\\works\\draft</strong> \\d+ "quoted"';
for ($attempt = 0; $attempt < 2; $attempt++) {
    $_POST = wp_slash(array(
        '_wpnonce' => wp_create_nonce('update-tag_' . $child['term_id']),
        'easel_term_disc' => $attempt ? get_term_meta((int) $child['term_id'], 'easel_term_disc', true) : $description,
    ));
    do_action('edited_custom_cat', (int) $child['term_id']);
    $check(get_term_meta((int) $child['term_id'], 'easel_term_disc', true) === $description,
        'Term backslashes and quotes must survive saving and resaving.');
}
$_POST['easel_term_disc'] = wp_slash('<strong>Allowed</strong><img src="x" onerror="alert(98765)"><script>alert(98765)</script>');
do_action('edited_custom_cat', (int) $child['term_id']);
$saved_description = get_term_meta((int) $child['term_id'], 'easel_term_disc', true);
$check(strpos($saved_description, '<strong>Allowed</strong>') !== false
    && strpos($saved_description, 'onerror') === false && strpos($saved_description, '<script>') === false,
    'Term saves must preserve allowed HTML without executable markup.');
$_POST['_wpnonce'] = 'invalid';
$_POST['easel_term_disc'] = 'Must not overwrite';
do_action('edited_custom_cat', (int) $child['term_id']);
$check(get_term_meta((int) $child['term_id'], 'easel_term_disc', true) === $saved_description, 'Invalid nonce must not change a term.');
$_POST['_wpnonce'] = wp_create_nonce('update-tag_' . $child['term_id']);
wp_set_current_user(0);
do_action('edited_custom_cat', (int) $child['term_id']);
$check(get_term_meta((int) $child['term_id'], 'easel_term_disc', true) === $saved_description, 'Unauthorized edits must not change a term.');
wp_set_current_user(1);
$_POST = array();

register_easel_settings();
foreach (array('title_image_url', 'ogp_image', 'twitter', 'pixiv') as $setting) {
    $check(sanitize_option('easel_' . $setting, 'javascript:alert(98765)') === '', 'Unsafe URL must be rejected: ' . $setting);
    $check(sanitize_option('easel_' . $setting, 'https://example.org/image?a=1&b=2') === 'https://example.org/image?a=1&b=2', 'Valid URL must survive: ' . $setting);
    $check(sanitize_option('easel_' . $setting, array('bad')) === '', 'Array must be rejected: ' . $setting);
}
$check(sanitize_option('easel_footer_text', '<b>Copyright</b>') === 'Copyright', 'Copyright must be plain text.');
$check(sanitize_option('easel_ogp_description', "Line one\n<strong>Line two</strong>") === "Line one\nLine two", 'Description must retain line breaks without HTML.');
foreach (array(
    'base_color' => array('piano', 'basic_color'),
    'totop' => array('visible', 'hidden'),
    'rewrite_permalink' => array('changed', 'default'),
    'ogp_twitter_card' => array('summary_large_image', 'summary'),
) as $setting => $values) {
    $check(sanitize_option('easel_' . $setting, $values[0]) === $values[0], 'Valid selection must survive: ' . $setting);
    $check(sanitize_option('easel_' . $setting, 'invalid') === $values[1], 'Invalid selection must use a safe fallback: ' . $setting);
}
foreach (array('set_terms', 'make_indent', 'make_main_content_indent', 'text_work_max_width_narrow', 'pass_blur', 'allow_comments_posts', 'allow_comments_works') as $setting) {
    $check(sanitize_option('easel_' . $setting, '1') === '1'
        && sanitize_option('easel_' . $setting, null) === '0'
        && sanitize_option('easel_' . $setting, array('1')) === '0', 'Checkbox validation failed: ' . $setting);
}

// Bypass saving to simulate legacy unsafe values still present in a database.
$legacy_value = 'javascript:alert(98765)" onerror="alert(98765)"><script>alert(98765)</script></textarea>';
$legacy_filter = function () use ($legacy_value) { return $legacy_value; };
$legacy_options = array('title_image_url', 'ogp_image', 'footer_text', 'ogp_description', 'twitter', 'pixiv');
foreach ($legacy_options as $setting) {
    add_filter('pre_option_easel_' . $setting, $legacy_filter);
}
$original_query = $GLOBALS['wp_query'];
$original_post = $GLOBALS['post'];
try {
    ob_start();
    easel_settings_page();
    $settings_html = ob_get_clean();
    $check(strpos($settings_html, '<script>alert(98765)</script>') === false
        && strpos($settings_html, '" onerror="alert(98765)') === false,
        'Legacy settings must not escape input, textarea, or image attributes.');
    $GLOBALS['wp_query'] = new WP_Query(array('post_type' => 'works', 'p' => $id));
    $GLOBALS['post'] = get_post($id);
    setup_postdata($GLOBALS['post']);
    ob_start();
    load_template(get_template_directory() . '/single-works.php', false);
    $public_html = ob_get_clean();
    $check(strpos($public_html, '<script>alert(98765)</script>') === false
        && strpos($public_html, '" onerror="alert(98765)') === false
        && strpos($public_html, 'src="javascript:') === false,
        'Legacy logo and copyright must be safe on public pages.');
} finally {
    foreach ($legacy_options as $setting) {
        remove_filter('pre_option_easel_' . $setting, $legacy_filter);
    }
    $GLOBALS['wp_query'] = $original_query;
    $GLOBALS['post'] = $original_post;
}
$legacy_description = '<strong>legacy-term-safe</strong><img src="x" onerror="alert(98765)"><script>alert(98765)</script>';
update_term_meta((int) $child['term_id'], 'easel_term_disc', wp_slash($legacy_description));
try {
    $GLOBALS['wp_query'] = new WP_Query(array('custom_cat' => 'child'));
    foreach (array('works-text', 'works-illust', 'works-update') as $template) {
        $GLOBALS['wp_query']->rewind_posts();
        ob_start();
        load_template(get_template_directory() . '/library/parts/' . $template . '.php', false);
        $term_html = ob_get_clean();
        $check(strpos($term_html, '<strong>legacy-term-safe</strong>') !== false
            && strpos($term_html, '<script>alert(98765)</script>') === false
            && strpos($term_html, 'onerror="alert(98765)') === false,
            'Legacy term HTML must be safe when rendering: ' . $template);
    }
    $check(get_term_meta((int) $child['term_id'], 'easel_term_disc', true) === $legacy_description, 'Rendering must not rewrite stored descriptions.');
} finally {
    $GLOBALS['wp_query'] = $original_query;
    $GLOBALS['post'] = $original_post;
}
