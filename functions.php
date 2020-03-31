<?php

require_once( 'library/bones.php' );

// テーマ更新の確認
require_once( 'theme-update-checker.php' );
$update_checker = new ThemeUpdateChecker(
    'easel',
    'https://easel.gt-gt.org/downloads/theme-update.json'
);

// ダッシュボードに案内ウィジェットを追加する
function add_custom_widget() {
	echo '<h2>EASELをはじめる手順</h2>
        <p>WordPressのサイト設定が終わったら、ダッシュボード　サイドバーの設定＞EASEL設定を開いてください。<br>
        タイトル画像の設定や、「作品タイプ」の初期設定ができたら、EASELの準備は完了です。<br>
        もっと詳しく知りたい、わからないことがあれば、<a href="https://easel.gt-gt.org/" target="_blank">EASELマニュアルページ</a>をご参照ください。</p>
        <p>また、テーマを利用する中で不具合や要望があれば、お気軽にお申し出ください。</p>
          ';
}
function add_my_widget() {
	wp_add_dashboard_widget( 'custom_widget', 'EASELへようこそ', 'add_custom_widget' );
}
add_action( 'wp_dashboard_setup', 'add_my_widget' );

// カスタム投稿タイプの設定
require_once( 'library/custom-post-type.php' );

// カスタム投稿画面にターム別ソート機能を追加
function add_post_taxonomy_restrict_filter() {
    global $post_type;
    if ( 'works' == $post_type ) {
        ?>
        <select name="custom_cat">
            <option value="">すべての作品タイプ</option>
            <?php
            $terms = get_terms('custom_cat');
            foreach ($terms as $term) { ?>
                <option value="<?php echo $term->slug; ?>"><?php echo $term->name; ?></option>
            <?php } ?>
        </select>
        <?php
    }
}
add_action( 'restrict_manage_posts', 'add_post_taxonomy_restrict_filter' );

// EASEL用のメニューを追加する
add_action('admin_menu', 'register_easel_menu_page');
function register_easel_menu_page() {
    // add_menu_page でカスタムメニューを追加
    add_options_page('EASEL設定', 'EASEL設定', 'administrator', 'easel_settings', 'easel_settings_page', 'dashicons-edit', 3);
}
// フィールドの作成
add_action( 'admin_init', 'register_easel_settings' );

function register_easel_settings() {
  $my_options = ['title_image_url', 'set_terms', 'twitter', 'pixiv'];
  foreach($my_options as $my_option){
    register_setting( 'easel_settings', 'easel_'.$my_option );
  }
}

// 作品タイプの定義
$easel_terms = array(
  ['illust' , 'イラスト', 'イラスト、漫画など向きの、サムネイル画像の目立つ作品タイプです'],
  ['text' , '文章', '小説やレビューなど、文章メインの作品タイプです'],
  ['update', '更新履歴', '更新情報や簡単なお知らせなどはこちら']
);

// タームを作成する関数
function setup_easel_terms() {
  global $easel_terms;
    foreach ($easel_terms as $easel_term) {
      $check_term = term_exists( $easel_term[0], 'work_type' ); // タクソノミーが存在すれば配列が返される
      if ( $check_term == 0 ) {
        wp_insert_term(
          $easel_term[1], // ターム名
          'work_type', // タクソノミー
          array(
            'description'=> $easel_term[2],
            'slug' => $easel_term[0],
          )
        );
      }
    }
}

// EASEL設定ページの中身を読み込み
function easel_settings_page() {
  include 'library/setting.php';
}

// 作品タイプの初期設定を行う
$check_setup_easel_terms = get_option('easel_set_terms');
if ( $check_setup_easel_terms == 1 ) {
  add_action('admin_init', 'setup_easel_terms');
  update_option('easel_set_terms', '0');
}

//画像アップロード用のタグを出力する
function generate_upload_image_tag($name, $value){?>
  <input name="<?php echo $name; ?>" type="text" value="<?php echo $value; ?>" />
  <input type="button" name="<?php echo $name; ?>_select" value="選択" />
  <input type="button" name="<?php echo $name; ?>_clear" value="クリア" />
  <div id="<?php echo $name; ?>_thumbnail" class="uploded-thumbnail">
    <?php if ($value): ?>
      <img src="<?php echo $value; ?>" alt="選択中の画像">
    <?php endif ?>
  </div>


  <script type="text/javascript">
  (function ($) {

      var custom_uploader;

      $("input:button[name=<?php echo $name; ?>_select]").click(function(e) {

          e.preventDefault();

          if (custom_uploader) {

              custom_uploader.open();
              return;

          }

          custom_uploader = wp.media({

              title: "画像を選択してください",

              /* ライブラリの一覧は画像のみにする */
              library: {
                  type: "image"
              },

              button: {
                  text: "画像の選択"
              },

              /* 選択できる画像は 1 つだけにする */
              multiple: false

          });

          custom_uploader.on("select", function() {

              var images = custom_uploader.state().get("selection");

              /* file の中に選択された画像の各種情報が入っている */
              images.each(function(file){

                  /* テキストフォームと表示されたサムネイル画像があればクリア */
                  $("input:text[name=<?php echo $name; ?>]").val("");
                  $("#<?php echo $name; ?>_thumbnail").empty();

                  /* テキストフォームに画像の URL を表示 */
                  $("input:text[name=<?php echo $name; ?>]").val(file.attributes.sizes.full.url);

                  /* プレビュー用に選択されたサムネイル画像を表示 */
                  $("#<?php echo $name; ?>_thumbnail").append('<img src="'+file.attributes.sizes.full.url+'" />');

              });
          });

          custom_uploader.open();

      });

      /* クリアボタンを押した時の処理 */
      $("input:button[name=<?php echo $name; ?>_clear]").click(function() {
          $("input:text[name=<?php echo $name; ?>]").val("");
          $("#<?php echo $name; ?>_thumbnail").empty();
      });

  })(jQuery);
  </script>
  <?php
}

function my_admin_scripts() {
  //メディアアップローダの javascript API
  wp_enqueue_media();
}
add_action( 'admin_print_scripts', 'my_admin_scripts' );

// 抜粋をリッチエディタに変更
add_action( 'add_meta_boxes', array ( 'VisualEditorExcerptDemo', 'switch_boxes' ) );
class VisualEditorExcerptDemo{
  public static function switch_boxes()
  {
    if ( ! post_type_supports( $GLOBALS['post']->post_type, 'excerpt' ) )    {
      return;
    }

    remove_meta_box(
      'postexcerpt' // ID
    ,   ''      // スクリーン、空だと全ての投稿タイプをサポート
    ,   'normal'    // コンテキスト
    );

    add_meta_box(
      'postexcerpt2'   // Reusing just 'postexcerpt' doesn't work.
    ,   __( 'Excerpt' )  // タイトル
    ,   array ( __CLASS__, 'show' ) // 表示関数
    ,   null          // スクリーン
    ,   'normal'      // コンテキスト
    ,   'core'        // 優先度
    );
  }

  //メタボックスの出力
  public static function show( $post )  {
  ?>
    <label class="screen-reader-text" for="excerpt"><?php
    _e( 'Excerpt' )
    ?></label>
    <?php
    //デフォルト名の'excerpt'を使用
    wp_editor(
      self::unescape( $post->post_excerpt ),
      'excerpt',
      array (
      'textarea_rows' => 15
    ,   'media_buttons' => false
    ,   'teeny'     => true
    ,   'tinymce'     => true
      )
    );
  }

  //エスケープ解除
  public static function unescape( $str )  {
    return str_replace(
      array ( '&lt;', '&gt;', '&quot;', '&amp;', '&nbsp;', '&amp;nbsp;' )
    ,   array ( '<',  '>',  '"',    '&',   ' ', ' ' )
    ,   $str
    );
  }
}

// カスタムフィールドの追加
function myplugin_add_meta_box() {
	$screens = array( 'post', 'page' );
	foreach ( $screens as $screen ) {
		add_meta_box(
      'custom_css',
			'カスタムCSS',
			'create_custom_css',
			$screen
		);
	}
}
add_action( 'add_meta_boxes', 'myplugin_add_meta_box' );

// 記事・固定ページにカスタムCSSが使えるようにする
function create_custom_css() {
    $keyname = 'custom_css';
    global $post;
    // 保存されているカスタムフィールドの値を取得
    $get_value = get_post_meta( $post->ID, $keyname, true );

    // nonceの追加
    wp_nonce_field( 'action-' . $keyname, 'nonce-' . $keyname );

    // HTMLの出力
    echo '<textarea style="margin-top: 0px;margin-bottom: 0px;height: 82px;width: 100%;height: 150px;" placeholder="ここにCSSを書くと個別ページに反映されます。styleタグは不要です。" name="' . $keyname . '">' . $get_value . '</textarea>';
}

// カスタムフィールドの保存
add_action( 'save_post', 'save_custom_field' );
function save_custom_field( $post_id ) {
    $custom_fields = ['custom_css'];

    foreach( $custom_fields as $d ) {
        if ( isset( $_POST['nonce-' . $d] ) && $_POST['nonce-' . $d] ) {
            if( check_admin_referer( 'action-' . $d, 'nonce-' . $d ) ) {

                if( isset( $_POST[$d] ) && $_POST[$d] ) {
                    update_post_meta( $post_id, $d, $_POST[$d] );
                } else {
                    delete_post_meta( $post_id, $d, get_post_meta( $post_id, $d, true ) );
                }
            }
        }

    }
}

// キャプション付き画像が記事外にはみ出るのを修正する
add_filter( 'img_caption_shortcode_width', function(){ return 0; } );

/*********************
LAUNCH BONES
Let's get everything up and running.
*********************/

function bones_ahoy() {

  //Allow editor style.
  add_editor_style( get_stylesheet_directory_uri() . '/library/css/editor-style.css' );

  // let's get language support going, if you need it
  load_theme_textdomain( 'bonestheme', get_template_directory() . '/library/translation' );

  // launching operation cleanup
  add_action( 'init', 'bones_head_cleanup' );
  // A better title
  add_filter( 'wp_title', 'rw_title', 10, 3 );
  // remove WP version from RSS
  add_filter( 'the_generator', 'bones_rss_version' );
  // remove pesky injected css for recent comments widget
  add_filter( 'wp_head', 'bones_remove_wp_widget_recent_comments_style', 1 );
  // clean up comment styles in the head
  add_action( 'wp_head', 'bones_remove_recent_comments_style', 1 );
  // clean up gallery output in wp
  add_filter( 'gallery_style', 'bones_gallery_style' );

  // enqueue base scripts and styles
  add_action( 'wp_enqueue_scripts', 'bones_scripts_and_styles', 999 );
  // ie conditional wrapper

  // launching this stuff after theme setup
  bones_theme_support();

  // adding sidebars to Wordpress (these are created in functions.php)
  add_action( 'widgets_init', 'bones_register_sidebars' );

  // cleaning up random code around images
  add_filter( 'the_content', 'bones_filter_ptags_on_images' );
  // cleaning up excerpt
  add_filter( 'excerpt_more', 'bones_excerpt_more' );

} /* end bones ahoy */

// let's get this party started
add_action( 'after_setup_theme', 'bones_ahoy' );


/************* OEMBED SIZE OPTIONS *************/

if ( ! isset( $content_width ) ) {
	$content_width = 680;
}

/************* THUMBNAIL SIZE OPTIONS *************/

// Thumbnail sizes
add_image_size( 'bones-thumb-600', 600, 150, true );
add_image_size( 'bones-thumb-300', 300, 100, true );

/*
to add more sizes, simply copy a line from above
and change the dimensions & name. As long as you
upload a "featured image" as large as the biggest
set width or height, all the other sizes will be
auto-cropped.

To call a different size, simply change the text
inside the thumbnail function.

For example, to call the 300 x 100 sized image,
we would use the function:
<?php the_post_thumbnail( 'bones-thumb-300' ); ?>
for the 600 x 150 image:
<?php the_post_thumbnail( 'bones-thumb-600' ); ?>

You can change the names and dimensions to whatever
you like. Enjoy!
*/

add_filter( 'image_size_names_choose', 'bones_custom_image_sizes' );

function bones_custom_image_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'bones-thumb-600' => __('600px by 150px'),
        'bones-thumb-300' => __('300px by 100px'),
    ) );
}

/*
The function above adds the ability to use the dropdown menu to select
the new images sizes you have just created from within the media manager
when you add media to your content blocks. If you add more image sizes,
duplicate one of the lines in the array and name it according to your
new image size.
*/

// アイキャッチのない場合に記事内の最初の画像を拾ってくる

function get_the_image() {
    global $post;
    $get_img = '';
    // 取得したい画像の位置
    $number = 1;
    $pattern1 ='/<a href=[\'"]([^\'"]+.(jpg|jpeg|gif|png|svg))[\'"]>/i';
    $pattern2 ='/<img.*src=[\'"]([^\'"]+)[\'"].*>/i';
    // リンク先画像があるかどうか
    $match_num = preg_match_all($pattern1, $post->post_content, $matches);
    if($match_num === 0){
    // リンク先画像がない場合は最初の画像を取得
        preg_match_all($pattern2, $post->post_content, $matches);
    }
     $get_img = $matches[1][$number - 1];

    if(empty($get_img)){
        // 記事内に画像がない場合のデフォルト画像を設定
        // デフォルト画像を非表示にしたい場合は下にある$get_img = false;の左側の//を消したらよいです。
        $get_img  = get_template_directory_uri()."/img/default.jpg";
        // $get_img  = false;
    }
    if($get_img === false){
        return false;
    } else{
        // 画像表示設定
        $alt = get_the_title();
        $img = '<img src="'.$get_img.'" alt="'.$alt.'" class="attachment-post-thumbnail size-post-thumbnail wp-post-image">';
        return $img;
    }
}

function bones_theme_customizer($wp_customize) {
  // $wp_customize calls go here.
  //
  // Uncomment the below lines to remove the default customize sections

  // $wp_customize->remove_section('title_tagline');
  // $wp_customize->remove_section('colors');
  // $wp_customize->remove_section('background_image');
  // $wp_customize->remove_section('static_front_page');
  // $wp_customize->remove_section('nav');

  // Uncomment the below lines to remove the default controls
  // $wp_customize->remove_control('blogdescription');

  // Uncomment the following to change the default section titles
  // $wp_customize->get_section('colors')->title = __( 'Theme Colors' );
  // $wp_customize->get_section('background_image')->title = __( 'Images' );
}

add_action( 'customize_register', 'bones_theme_customizer' );

// サイドバー他ウィジェットエリアの設定
function bones_register_sidebars() {
	register_sidebar(array(
		'id' => 'sidebar1',
		'name' => __( 'サイドバー１', 'bonestheme' ),
		'description' => __( '通常のサイドバーです', 'bonestheme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));

	register_sidebar(array(
		'id' => 'sidebar2',
		'name' => __( 'サイドバー２', 'bonestheme' ),
		'description' => __( '固定ページ用のサイドバーです', 'bonestheme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));
}

function bones_fonts() {
  wp_enqueue_style('googleFonts', '//fonts.googleapis.com/css?family=Lato:400,700,400italic,700italic');
}

add_action('wp_enqueue_scripts', 'bones_fonts');

get_template_part( 'library/shortcode' );
get_template_part( 'library/widget' );


/* DON'T DELETE THIS CLOSING TAG */ ?>
