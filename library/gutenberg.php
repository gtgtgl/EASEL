<?php
if ( !defined( 'ABSPATH' ) ) exit;

function wdl_block_01_enqueue() {
    //アセットファイルをインクルードして変数に保存
    $asset_file = include( get_theme_file_path('/library/gutenberg/gutenberg.asset.php'));
   
    //ブロック用のスクリプトを登録
    wp_register_script(
      'gutenberg',
      get_theme_file_uri('/library/gutenberg/gutenberg.js'),
      $asset_file['dependencies'], //依存スクリプトの配列
      $asset_file['version'] //バージョン
    );
   
    //ブロックタイプの登録
    register_block_type(
      'wdl/block-01',
      array(
        //エディター用スクリプトとしてブロック用スクリプトを指定（登録）
        'editor_script' => 'gutenberg',
      )
    );
  }
  add_action( 'init', 'wdl_block_01_enqueue' );
?>
