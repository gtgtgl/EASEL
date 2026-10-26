<?php
/** Run only against a disposable installation: wp eval-file tests/wordpress.php */
error_reporting(E_ALL);
$_SERVER['SERVER_NAME'] = 'easel.test';
$_SERVER['HTTP_HOST'] = 'easel.test';
$_SERVER['REQUEST_URI'] = '/';
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new ErrorException($message, 0, $severity, $file, $line);
    }
    return false;
});
$check = function ($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
};
wp_set_current_user(1);
require_once ABSPATH . 'wp-admin/includes/class-wp-screen.php';
require_once ABSPATH . 'wp-admin/includes/screen.php';
set_current_screen('settings_page_easel_settings');
$check(post_type_exists('works') && taxonomy_exists('custom_cat'), 'Works and taxonomy registration failed.');
$check(WP_Block_Type_Registry::get_instance()->is_registered('easel/shortcode'), 'Editor block registration failed.');
foreach (array('blue', 'red', 'yellow', 'green', 'navy', 'gray') as $button_color) {
    $button_block = WP_Block_Type_Registry::get_instance()->get_registered('easel/' . $button_color . '-button');
    $check($button_block && $button_block->api_version === 3, 'Link button block registration failed: ' . $button_color);
    $check(in_array('easel-link-buttons', $button_block->style_handles, true), 'Shared link button stylesheet is missing.');
}
$check(current_theme_supports('editor-styles'), 'Block editor theme styles must be enabled.');
$editor_theme_css = implode("\n", array_column(get_block_editor_theme_styles(), 'css'));
$check(strpos($editor_theme_css, '.editor-styles-wrapper') !== false
    && strpos($editor_theme_css, 'Biko_Regular.otf') !== false,
    'Block editor typography stylesheet must be loaded.');
do_action('admin_enqueue_scripts', 'settings_page_easel_settings');
$check(wp_script_is('jquery-ui-tabs', 'enqueued'), 'Settings tabs failed to enqueue.');
$parent = term_exists('text', 'custom_cat') ?: wp_insert_term('Text', 'custom_cat', array('slug' => 'text'));
$child = term_exists('child', 'custom_cat') ?: wp_insert_term('Child', 'custom_cat', array('slug' => 'child', 'parent' => $parent['term_id']));
$id = wp_insert_post(array('post_title' => 'Compatibility fixture', 'post_content' => 'Example content', 'post_type' => 'works', 'post_status' => 'publish'));
wp_set_object_terms($id, array((int) $child['term_id']), 'custom_cat');
$GLOBALS['post'] = get_post($id);
setup_postdata($GLOBALS['post']);
$check(easel_get_work_type_slug($id) === 'text', 'Root taxonomy lookup failed.');
update_option('easel_make_indent', '1');
$check(in_array('make_indent', get_post_class('', $id), true), 'Post classes must use the requested post.');
foreach (array('new_list', 'new_illust', 'new_text') as $tag) {
    $html = do_shortcode('[' . $tag . ' count=1 work_type=child showtype=false showtag=false]');
    $check(strpos($html, 'Compatibility fixture') !== false, 'Shortcode failed: ' . $tag);
    $check(strpos($html, 'class="category"') === false, 'False badge flag must be respected.');
}
$html = do_shortcode('[new_text count=1 work_type=child showtype=true]');
$check(strpos($html, 'class="category"') !== false, 'True badge flag must be respected.');
$check(strpos(get_the_image(), 'default.jpg') !== false, 'Fallback thumbnail failed.');
// A thumbnail may be set while its image data cannot be retrieved.
add_filter('has_post_thumbnail', '__return_true');
add_filter('wp_get_attachment_image_src', '__return_false');
try {
    $check(strpos(get_new_illusts_thum(), 'default.jpg') !== false, 'Failed image lookup must use the fallback without warnings.');
} finally {
    remove_filter('has_post_thumbnail', '__return_true');
    remove_filter('wp_get_attachment_image_src', '__return_false');
}
$GLOBALS['post'] = null;
$check(get_the_image() === '', 'Missing post must be handled.');
$check(easel_make_indent(array('existing'), '', 99999999) === array('existing'), 'Missing post classes must be handled.');
unset($_SERVER['HTTP_USER_AGENT']);
ob_start();
$calendar = get_calendar(true, false);
$check(ob_get_clean() === '', 'Calendar filter must not echo.');
$check(is_string($calendar), 'Calendar must return HTML.');
wp_insert_post(array('post_title' => 'Calendar fixture', 'post_type' => 'post', 'post_status' => 'publish'));
$calendar = get_calendar(true, false);
$check(strpos($calendar, '<tbody>') !== false, 'Calendar table body missing.');
update_option('easel_base_color', 'blue');
easel_change_color();
$check(wp_style_is('change_base_color', 'enqueued'), 'Base color stylesheet failed.');
$widget = new FixedWidgetItem();
$instance = $widget->update(array('setting_title' => '<b>Profile</b>', 'setting_img' => 'javascript:alert(1)', 'setting_textarea' => '<script>alert(1)</script><strong>Text</strong>'), array());
$check($instance['setting_title'] === 'Profile' && $instance['setting_img'] === '', 'Widget input sanitization failed.');
ob_start();
$widget->form(array('setting_textarea' => '</textarea><script>alert(1)</script>'));
$widget_form = ob_get_clean();
$check(strpos($widget_form, '&lt;/textarea&gt;') !== false, 'Widget textarea escaping failed.');
$_POST = array('nonce-custom_css' => wp_create_nonce('action-custom_css'), 'custom_css' => 'body { color: red; }');
save_custom_field($id);
$check(get_post_meta($id, 'custom_css', true) === 'body { color: red; }', 'Custom CSS save failed.');
$_POST['custom_css'] = '';
save_custom_field($id);
$check(get_post_meta($id, 'custom_css', true) === '', 'Custom CSS clearing failed.');
$_POST = array('_wpnonce' => wp_create_nonce('update-tag_' . $child['term_id']), 'easel_term_disc' => '<strong>Term text</strong>');
save_terms((int) $child['term_id']);
$check(get_term_meta((int) $child['term_id'], 'easel_term_disc', true) === '<strong>Term text</strong>', 'Term description save failed.');
$_POST = array();
require __DIR__ . '/settings-security.php';
$query = new WP_Query(array('post_type' => 'works'));
$check($query->have_posts(), 'Works query failed.');
$GLOBALS['current_screen'] = null;
$GLOBALS['wp_query'] = new WP_Query(array('post_type' => 'works', 'p' => $id));
$GLOBALS['post'] = get_post($id);
setup_postdata($GLOBALS['post']);
add_action('wp_body_open', function () { echo '<!-- body-open-check -->'; });
ob_start();
load_template(get_template_directory() . '/single-works.php', false);
$page_html = ob_get_clean();
$check(strpos($page_html, '<html lang=') !== false && strpos($page_html, '<!-- body-open-check -->') !== false, 'Document language and body hooks failed.');
$check(strpos($page_html, 'Compatibility fixture') !== false, 'Works template failed to render.');
echo 'WordPress ' . get_bloginfo('version') . ' integration checks passed on PHP ' . PHP_VERSION . "\n";
