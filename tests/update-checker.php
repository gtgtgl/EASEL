<?php
// Standalone regression checks; no WordPress installation or network required.
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

function add_action() {}
function add_filter() {}
function get_option($name) { return $GLOBALS['test_state'] ?? false; }
function wp_get_theme($slug) {
    return new class {
        public function get($name) { return '1.6'; }
    };
}
function check($condition, $message) {
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

require dirname(__DIR__) . '/theme-update-checker.php';
check(ThemeUpdate::fromJson('{') === null, 'Invalid JSON must be rejected.');
check(ThemeUpdate::fromJson('{"version":[],"details_url":"https://example.org"}') === null, 'Non-string versions must be rejected.');
$update = ThemeUpdate::fromJson('{"version":"2.0","details_url":"https://example.org","download_url":"https://example.org/theme.zip","extra":"ignored"}');
check($update instanceof ThemeUpdate, 'Valid metadata must load.');
check(!property_exists($update, 'extra'), 'Metadata must not create dynamic properties.');
check($update->toWpFormat()['package'] === 'https://example.org/theme.zip', 'Download URL must survive conversion.');
$checker = new ThemeUpdateChecker('easel', 'https://example.org', false);
check($checker->injectUpdate(false) === false, 'An absent transient must be preserved.');
$GLOBALS['test_state'] = (object) array('update' => $update);
$transient = $checker->injectUpdate((object) array('response' => array()));
check($transient->response['easel']['new_version'] === '2.0', 'A newer version must be offered.');
$update->version = '1.0';
$transient = $checker->injectUpdate((object) array('response' => array()));
check(!isset($transient->response['easel']), 'Stale cached updates must not be offered.');
echo "Update checker regression checks passed on PHP " . PHP_VERSION . "\n";
