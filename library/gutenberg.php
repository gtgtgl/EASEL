<?php
if ( !defined( 'ABSPATH' ) ) exit;

function wdl_block_enqueue() {
    //アセットファイルをインクルードして変数に保存
    $asset_file = include( get_theme_file_path('/library/gutenberg/gutenberg.asset.php'));

    //ブロック用のスクリプトを登録
    wp_register_script(
      'easel-shortcode-editor',
      get_theme_file_uri('/library/gutenberg/gutenberg.js'),
      $asset_file['dependencies'], //依存スクリプトの配列
      $asset_file['version'], //バージョン
      true
    );

    //ブロックタイプの登録
    register_block_type(
      'easel/shortcode',
      array(
        //エディター用スクリプトとしてブロック用スクリプトを指定（登録）
        'api_version' => 3,
        'editor_script' => 'easel-shortcode-editor',
      )
    );
  }
  add_action( 'init', 'wdl_block_enqueue' );

function easel_register_link_button_blocks() {
    $script_path = '/library/gutenberg/link-buttons.js';
    $style_path = '/library/css/link-buttons.css';
    wp_register_script(
        'easel-link-buttons-editor',
        get_theme_file_uri($script_path),
        array('wp-blocks', 'wp-block-editor', 'wp-components', 'wp-element', 'wp-i18n'),
        (string) filemtime(get_theme_file_path($script_path)),
        true
    );
    wp_register_style(
        'easel-link-buttons',
        get_theme_file_uri($style_path),
        array(),
        (string) filemtime(get_theme_file_path($style_path))
    );
    foreach (array('blue', 'red', 'yellow', 'green', 'navy', 'gray') as $color) {
        register_block_type('easel/' . $color . '-button', array(
            'api_version' => 3,
            'editor_script' => 'easel-link-buttons-editor',
            'style' => 'easel-link-buttons',
            'attributes' => array(
                'text' => array('type' => 'string', 'source' => 'html', 'selector' => 'a', 'default' => 'リンクボタン'),
                'url' => array('type' => 'string', 'source' => 'attribute', 'selector' => 'a', 'attribute' => 'href', 'default' => '#'),
                'newTab' => array('type' => 'boolean', 'default' => false),
            ),
            'supports' => array('html' => false, 'align' => array('left', 'center', 'right')),
        ));
    }
}
add_action('init', 'easel_register_link_button_blocks');
?>
