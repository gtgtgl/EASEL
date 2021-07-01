<?php

require_once( 'library/bones.php' );

// テーマ更新の確認
require_once( 'theme-update-checker.php' );
$update_checker = new ThemeUpdateChecker(
    'easel',
    'https://easel.gt-gt.org/downloads/theme-update.json'
);

// ダッシュボードに案内ウィジェットを追加する
function easel_add_custom_widget() {
	echo '<h2>EASELをはじめる手順</h2>
        <p>WordPressのサイト設定が終わったら、ダッシュボード　サイドバーの設定＞EASEL設定を開いてください。<br>
        タイトル画像の設定や、「作品タイプ」の初期設定ができたら、EASELの準備は完了です。<br>
        もっと詳しく知りたい、わからないことがあれば、<a href="https://easel.gt-gt.org/" target="_blank">EASELマニュアルページ</a>をご参照ください。</p>
        <p>また、テーマを利用する中で不具合や要望があれば、お気軽にお申し出ください。</p>
          ';
}
function add_my_widget() {
	wp_add_dashboard_widget( 'custom_widget', 'EASELへようこそ', 'easel_add_custom_widget' );
}
add_action( 'wp_dashboard_setup', 'add_my_widget' );

// カスタム投稿タイプの設定
require_once( 'library/custom-post-type.php' );

// EASEL用のメニューを追加する
add_action('admin_menu', 'register_easel_menu_page');
function register_easel_menu_page() {
    // add_menu_page でカスタムメニューを追加
    add_options_page('EASEL設定', 'EASEL設定', 'administrator', 'easel_settings', 'easel_settings_page');
}
// フィールドの作成
add_action( 'admin_init', 'register_easel_settings' );

function register_easel_settings() {
  $my_options = [
    'title_image_url', // タイトル画像
    'ogp_image', // OGP画像の初期設定
    'ogp_description', // OGP概要の初期設定
    'ogp_twitter_card', // twitterカードの設定
    'set_terms', // 作品タイプの初期設定を行うか
    'base_color', // ベースカラーを変更するか
    'totop', // トップへ戻るボタンを設定するか
    'make_indent', // 作品タイプ「文章」でインデントをつけるか
    'make_main_content_indent', // 作品タイプ「文章」でインデントをつけるか
    'rewrite_permalink', // 作品投稿のパーマリンクをリライトするか
    'footer_text', // フッターテキストの文言変更
    'pass_blur', // パス付きイラストのサムネイルをぼかすか
    'allow_comments_posts', // 「投稿」でコメント機能を許可するかどうか
    'allow_comments_works', // 「作品」でコメント機能を許可するかどうか
    'twitter',  // Twitterアカウント名
    'pixiv' // Pixivアカウント名
  ];
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
      $check_term = term_exists( $easel_term[0], 'custom_cat' ); // タクソノミーが存在すれば配列が返される
      if ( $check_term == 0 ) {
        wp_insert_term(
          $easel_term[1], // ターム名
          'custom_cat', // タクソノミー
          array(
            'description'=> $easel_term[2],
            'slug' => $easel_term[0],
          )
        );
      }
    }
}

// 設定ページでタブを使えるようにする
function custom_theme_add_scripts($loader_src) {
  if (is_admin()) {
    wp_enqueue_script('jquery-ui-tabs', array('jquery-ui-core'));
  }
}
add_action('wp_print_scripts', 'custom_theme_add_scripts');

// EASEL設定ページの中身を読み込み
function easel_settings_page() {
  include 'library/setting.php';
}

// EASEL設定ページ独自のCSSを読み込む
function my_admin_style(){
    wp_enqueue_style( 'my_admin_style', get_template_directory_uri().'/library/css/admin.css' );
}
add_action( 'admin_enqueue_scripts', 'my_admin_style' );

// ページのタイトル表示
add_theme_support( 'title-tag' );

// 作品タイプの初期設定を行う
$check_setup_easel_terms = get_option('easel_set_terms');
if ( $check_setup_easel_terms == 1 ) {
  add_action('admin_init', 'setup_easel_terms');
  update_option('easel_set_terms', '0');
}

// パーマリンク設定で作品タイプのスラッグを使えるようにする
// http://elsur.xyz/custompost-rewrite-permalink
// https://blog.entersquare.jp/web-develop/712/
// http://www.560designs.com/memo/861.html

if( get_option('easel_rewrite_permalink') == 'changed' ) {
  add_filter('post_type_link', 'easel_permalink_rewrite', 1, 2);

  function easel_permalink_rewrite($link, $post){
    if($post->post_type === 'works'){
      $terms = get_the_terms($post->ID,'custom_cat');
      if($terms === false) {
        return  home_url('/works/' .$post->ID);
      } else {
        $ancestors = get_ancestors($terms[0]->term_id, 'custom_cat'); // タクソノミースラッグを指定してタームの配列を取得
        $ancestors = array_reverse($ancestors); // 子親の順番で表示されるので、親子の順番に変更
        $ancestor_slugs = array();
        foreach ($ancestors as $ancestor) {
          $ancestor = get_term($ancestor, 'custom_cat'); // タームIDとタクソノミースラッグを指定してターム情報を取得
          $ancestor_slug = $ancestor->slug; // タームスラッグを取得
          array_push( $ancestor_slugs , $ancestor_slug );
        }
        $slug = $terms[0]->slug;
        array_push( $ancestor_slugs , $slug );
        $url = implode( '/' , $ancestor_slugs );
        return home_url('/works/' . $url . '/' .$post->ID);
        return home_url('/works/' . $terms[0]->slug . '/' .$post->ID);
      }
    } else {
      return $link;
    }
  }

  // リダイレクト
  add_filter( 'rewrite_rules_array', 'easel_rewrite_rules_array' );
  function easel_rewrite_rules_array( $rules ) {
  	$new_rules = array(

          //個別記事ページ送り
          'works/(.+?)/([0-9]+)(?:/([0-9]+))?/?$' => 'index.php?post_type=works&p=$matches[2]&page=$matches[3]',

          //アーカイブ
          'works/([^/]+)(?:/([0-9]+))?/?$' => 'index.php?custom_cat=$matches[1]&paged=$matches[2]',

          //アーカイブページ送り
          'works/page/([0-9]{1,})/?$' => 'index.php?post_type=works&paged=$matches[1]',
          // なせか効かないので要研究。

  	);
  	return $new_rules + $rules;
  }
}

// コメント機能を使わない場合はダッシュボードから消す
if(get_option('easel_allow_comments_posts') !== '1' && get_option('easel_allow_comments_works') !== '1' ) {
  function easel_remove_comment(){
    remove_menu_page( 'edit-comments.php' ); //コメントメニュー
  }
  add_action( 'admin_menu', 'easel_remove_comment' );
}

// カスタム投稿画面にターム別ソート機能を追加
function easel_add_worktype_filter() {
    global $post_type;
    if ( 'works' == $post_type ) {
      $args = array(
      	'show_option_all'    => 'すべての作品タイプ',
      	'orderby'            => 'ID',
      	'order'              => 'ASC',
      	'exclude'            => '',
      	'echo'               => 1,
      	'selected'           => 0,
      	'hierarchical'       => 1,
      	'name'               => 'custom_cat',
      	'taxonomy'           => 'custom_cat',
      	'hide_if_empty'      => false,
      	'value_field'	     => 'slug',
      );

      $args2 = array(
      	'show_option_all'    => 'すべての作品タグ',
      	'orderby'            => 'ID',
      	'order'              => 'ASC',
      	'exclude'            => '',
      	'echo'               => 1,
      	'selected'           => 0,
      	'hierarchical'       => 1,
      	'name'               => 'custom_tag',
      	'taxonomy'           => 'custom_tag',
      	'hide_if_empty'      => false,
      	'value_field'	     => 'slug',
      );

      wp_dropdown_categories( $args );
      wp_dropdown_categories( $args2 );
    }
}
add_action( 'restrict_manage_posts', 'easel_add_worktype_filter' );

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

function easel_admin_scripts() {
  //メディアアップローダの javascript API
  wp_enqueue_media();
}
add_action( 'admin_print_scripts', 'easel_admin_scripts' );

//本文抜粋を取得する関数
//使用方法：http://nelog.jp/get_the_custom_excerpt
function easel_get_the_custom_excerpt($content, $length) {
  $length = ($length ? $length : 70);//デフォルトの長さを指定する
  $content =  preg_replace('/<!--more-->.+/is',"",$content); //moreタグ以降削除
	$content = preg_replace('#\[[^\]]+\]#', '',$content);
  $content =  strip_tags($content);//タグの除去
  $content =  str_replace(array("\r\n","\n","\r"),"",$content);//特殊文字の削除（今回はスペースのみ）
  $content =  mb_substr($content,0,$length);//文字列を指定した長さで切り取る
  return $content;
}

// OGP設定
function easel_setting_ogp() {

  if (is_front_page() || is_home() || is_singular()) {
    $ogp_image = get_option('easel_ogp_image');
    // Twitterカードの種類（summary_large_image または summary を指定）
    $twitter_card = get_option('easel_ogp_twitter_card');
    // Facebook APP ID
    $facebook_app_id = '';

    global $post;
    $ogp_title = '';
    $ogp_description = get_option('easel_ogp_description');
    $ogp_url = '';
    $html = '';
    if (is_singular()) {
      // 記事＆固定ページ
      setup_postdata($post);
      $ogp_title = $post->post_title;
      if(has_excerpt()) {
        $ogp_description = easel_get_the_custom_excerpt( get_the_excerpt(), 120 );
      }
      $ogp_url = get_permalink();
      wp_reset_postdata();
    } elseif (is_front_page() || is_home()) {
      // トップページ
      $ogp_title = get_bloginfo('name');
      $ogp_url = home_url();
      if (get_option('easel_ogp_description') !== '') {
        $ogp_description = get_option('easel_ogp_description');
      }
    }

    // og:type
    $ogp_type = (is_front_page() || is_home()) ? 'website' : 'article';

    // og:image
    if (is_singular() && has_post_thumbnail()) {
      $ps_thumb = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full');
      $ogp_image = $ps_thumb[0];
    }

    // 出力するOGPタグをまとめる
    $html = "\n";
    $html .= '<meta property="og:title" content="' . esc_attr($ogp_title) . '">' . "\n";
    $html .= '<meta property="og:description" content="' . esc_attr($ogp_description) . '">' . "\n";
    $html .= '<meta property="og:type" content="' . $ogp_type . '">' . "\n";
    $html .= '<meta property="og:url" content="' . esc_url($ogp_url) . '">' . "\n";
    $html .= '<meta property="og:image" content="' . esc_url($ogp_image) . '">' . "\n";
    $html .= '<meta property="og:site_name" content="' . esc_attr(get_bloginfo('name')) . '">' . "\n";
    $html .= '<meta name="twitter:card" content="' . $twitter_card . '">' . "\n";
    $html .= '<meta property="twitter:title" content="' . esc_attr($ogp_title) . '">' . "\n";
    $html .= '<meta property="twitter:url" content="' . esc_url($ogp_url) . '">' . "\n";
    $html .= '<meta property="twitter:description" content="' . esc_attr($ogp_description) . '">' . "\n";
    $html .= '<meta name="twitter:image" content="' . esc_url($ogp_image) . '">' . "\n";
    $html .= '<meta property="og:locale" content="ja_JP">' . "\n";

    if ($facebook_app_id != "") {
      $html .= '<meta property="fb:app_id" content="' . $facebook_app_id . '">' . "\n";
    }

    echo $html;
  }
}
// headタグ内にOGPを出力する
add_action('wp_head', 'easel_setting_ogp');

// 固定ページでも「抜粋」を使えるように
add_post_type_support( 'page', 'excerpt' );

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
    <p style="font-size:12px;">ここに入力した文章は、OGPの概要文として設定されます。<br>作品投稿の場合は、作品の抜粋として作品一覧に表示されます。</p>
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
function easel_custom_css() {
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
add_action( 'add_meta_boxes', 'easel_custom_css' );

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

// カレンダーをカッコよくする
add_filter('get_calendar', 'easel_calendar');

function easel_calendar($initial = true, $echo = true) {

    global $wpdb, $m, $monthnum, $year, $wp_locale, $posts;

    // week_begins = 0 stands for Sunday
    $week_begins = intval(get_option('start_of_week'));

    // Let's figure out when we are
    if ( !empty($monthnum) && !empty($year) ) {
        $thismonth = ''.zeroise(intval($monthnum), 2);
        $thisyear = ''.intval($year);
    } elseif ( !empty($w) ) {
        // We need to get the month from MySQL
        $thisyear = ''.intval(substr($m, 0, 4));
        $d = (($w - 1) * 7) + 6; //it seems MySQL's weeks disagree with PHP's
        $thismonth = $wpdb->get_var("SELECT DATE_FORMAT((DATE_ADD('{$thisyear}0101', INTERVAL $d DAY) ), '%m')");
    } elseif ( !empty($m) ) {
        $thisyear = ''.intval(substr($m, 0, 4));
        if ( strlen($m) < 6 )
                $thismonth = '01';
        else
                $thismonth = ''.zeroise(intval(substr($m, 4, 2)), 2);
    } else {
        $thisyear = gmdate('Y', current_time('timestamp'));
        $thismonth = gmdate('m', current_time('timestamp'));
    }

    $unixmonth = mktime(0, 0 , 0, $thismonth, 1, $thisyear);
    $last_day = date('t', $unixmonth);

    // Get the next and previous month and year with at least one post
    $previous = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
        FROM $wpdb->posts
        WHERE post_date < '$thisyear-$thismonth-01'
        AND post_type = 'post' AND post_status = 'publish'
            ORDER BY post_date DESC
            LIMIT 1");
    $next = $wpdb->get_row("SELECT MONTH(post_date) AS month, YEAR(post_date) AS year
        FROM $wpdb->posts
        WHERE post_date > '$thisyear-$thismonth-{$last_day} 23:59:59'
        AND post_type = 'post' AND post_status = 'publish'
            ORDER BY post_date ASC
            LIMIT 1");

    /* translators: Calendar caption: 1: month name, 2: 4-digit year */
    //$calendar_caption = _x('%1$s %2$s', 'calendar caption');
    $calendar_caption = '%2$s'.$thismonth;
    $calendar_output = '<table id="wp-calendar">
    <thead>
    <tr><th id="calendar-caption" colspan="7">' . sprintf($calendar_caption, $wp_locale->get_month($thismonth), date('Y.', $unixmonth)) . '</th></tr>
    <tr>';

    $myweek = array();

    for ( $wdcount=0; $wdcount<=6; $wdcount++ ) {
        $myweek[] = $wp_locale->get_weekday(($wdcount+$week_begins)%7);
    }

    foreach ( $myweek as $wd ) {
        $day_name = (true == $initial) ? $wp_locale->get_weekday_initial($wd) : $wp_locale->get_weekday_abbrev($wd);
        if($day_name=='日')$day_name='Sun';
        if($day_name=='月')$day_name='Mon';
        if($day_name=='火')$day_name='Tue';
        if($day_name=='水')$day_name='Wed';
        if($day_name=='木')$day_name='Thu';
        if($day_name=='金')$day_name='Fri';
        if($day_name=='土')$day_name='Sat';
        $wd = esc_attr($wd);
        $calendar_output .= "\n\t\t<th scope=\"col\" title=\"$wd\">$day_name</th>";
    }

    $calendar_output .= '
    </tr>
    </thead>

    <tfoot>
    <tr>';
    //////////////////ここから変更した
    if( $previous || $next)$calendar_output2 = "\n\t".'<div id="wp-calendar-pn">';
    if ( $previous ) {
        $calendar_output2 .= "\n\t\t".'<a id="prev" href="' . get_month_link($previous->year, $previous->month) . '" title="' . esc_attr( sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month($previous->month), date('Y', mktime(0, 0 , 0, $previous->month, 1, $previous->year)))) . '">&laquo;</a>';
    }

    if ( $next ) {
        $calendar_output2 .= "\n\t\t".'<a id="next" href="' . get_month_link($next->year, $next->month) . '" title="' . esc_attr( sprintf(__('View posts for %1$s %2$s'), $wp_locale->get_month($next->month), date('Y', mktime(0, 0 , 0, $next->month, 1, $next->year))) ) . '">&raquo;</a>';
    }
    if( $previous || $next)$calendar_output2 .= "\n\t".'</div>'."\n";
    //////////////////ここまで変更した

    // Get days with posts
    $dayswithposts = $wpdb->get_results("SELECT DISTINCT DAYOFMONTH(post_date)
        FROM $wpdb->posts WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:00'
        AND post_type = 'post' AND post_status = 'publish'
        AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59'", ARRAY_N);
    if ( $dayswithposts ) {
        foreach ( (array) $dayswithposts as $daywith ) {
            $daywithpost[] = $daywith[0];
        }
    } else {
        $daywithpost = array();
    }

    if (strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'camino') !== false || stripos($_SERVER['HTTP_USER_AGENT'], 'safari') !== false)
        $ak_title_separator = "\n";
    else
        $ak_title_separator = ', ';

    $ak_titles_for_day = array();
    $ak_post_titles = $wpdb->get_results("SELECT ID, post_title, DAYOFMONTH(post_date) as dom "
        ."FROM $wpdb->posts "
        ."WHERE post_date >= '{$thisyear}-{$thismonth}-01 00:00:00' "
        ."AND post_date <= '{$thisyear}-{$thismonth}-{$last_day} 23:59:59' "
        ."AND post_type = 'post' AND post_status = 'publish'"
    );
    if ( $ak_post_titles ) {
        foreach ( (array) $ak_post_titles as $ak_post_title ) {

                /** This filter is documented in wp-includes/post-template.php */
                $post_title = esc_attr( apply_filters( 'the_title', $ak_post_title->post_title, $ak_post_title->ID ) );

                if ( empty($ak_titles_for_day['day_'.$ak_post_title->dom]) )
                    $ak_titles_for_day['day_'.$ak_post_title->dom] = '';
                if ( empty($ak_titles_for_day["$ak_post_title->dom"]) ) // first one
                    $ak_titles_for_day["$ak_post_title->dom"] = $post_title;
                else
                    $ak_titles_for_day["$ak_post_title->dom"] .= $ak_title_separator . $post_title;
        }
    }

    // See how much we should pad in the beginning
    $pad = calendar_week_mod(date('w', $unixmonth)-$week_begins);
    if ( 0 != $pad )
        $calendar_output .= "\n\t\t".'<td colspan="'. esc_attr($pad) .'" class="pad">&nbsp;</td>';
    $daysinmonth = intval(date('t', $unixmonth));
    for ( $day = 1; $day <= $daysinmonth; ++$day ) {
        if ( isset($newrow) && $newrow )
            $calendar_output .= "\n\t</tr>\n\t<tr>\n\t\t";
        $newrow = false;

        if ( $day == gmdate('j', current_time('timestamp')) && $thismonth == gmdate('m', current_time('timestamp')) && $thisyear == gmdate('Y', current_time('timestamp')) )
            $calendar_output .= '<td id="today">';
        else
            $calendar_output .= '<td>';

        if ( in_array($day, $daywithpost) ) // any posts today?
                $calendar_output .= '<a href="' . get_day_link( $thisyear, $thismonth, $day ) . '" title="' . esc_attr( $ak_titles_for_day[ $day ] ) . "\">$day</a>";
        else
            $calendar_output .= $day;
        $calendar_output .= '</td>';

        if ( 6 == calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins) )
            $newrow = true;
    }

    $pad = 7 - calendar_week_mod(date('w', mktime(0, 0 , 0, $thismonth, $day, $thisyear))-$week_begins);

    if ( $pad != 0 && $pad != 7 )
        $calendar_output .= "\n\t\t".'<td class="pad" colspan="'. esc_attr($pad) .'">&nbsp;</td>';
    $calendar_output .= "\n\t</tr>\n\t</tbody>\n\t</table>";

    //ここで足す
    $calendar_output .= $calendar_output2;

    echo $calendar_output;
}

/*********************
LAUNCH BONES
Let's get everything up and running.
*********************/

function bones_ahoy() {

  //Allow editor style.
  add_editor_style( get_template_directory_uri() . '/library/css/editor-style.css' );

  // let's get language support going, if you need it
  load_theme_textdomain( 'bonestheme', get_template_directory_uri() . '/library/translation' );

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
  add_action( 'wp_enqueue_scripts', 'bones_scripts_and_styles', 8 );
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

add_filter( 'image_size_names_choose', 'bones_custom_image_sizes' );

function bones_custom_image_sizes( $sizes ) {
    return array_merge( $sizes, array(
        'bones-thumb-600' => __('600px by 150px'),
        'bones-thumb-300' => __('300px by 100px'),
    ) );
}

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
        $alt = $post->post_title;
        $img = '<img src="'.$get_img.'" alt="'.$alt.'" class="attachment-post-thumbnail size-post-thumbnail wp-post-image">';
        return $img;
    }
}

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

	register_sidebar(array(
		'id' => 'footer_left',
		'name' => __( 'フッター左', 'bonestheme' ),
		'description' => __( 'フッターエリアの左側です', 'bonestheme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));

	register_sidebar(array(
		'id' => 'footer_center',
		'name' => __( 'フッター中央', 'bonestheme' ),
		'description' => __( 'フッターエリアの中央です', 'bonestheme' ),
		'before_widget' => '<div id="%1$s" class="widget %2$s">',
		'after_widget' => '</div>',
		'before_title' => '<h4 class="widgettitle">',
		'after_title' => '</h4>',
	));

	register_sidebar(array(
		'id' => 'footer_right',
		'name' => __( 'フッター右', 'bonestheme' ),
		'description' => __( 'フッターエリアの右側です', 'bonestheme' ),
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


// パスワード付き記事のタイトル調整
function easel_edit_protected_word () {
return '%s <i class="fas fa-lock locked-icon"></i>';
}
add_filter('protected_title_format','easel_edit_protected_word');

// パスワード付き記事の抜粋調整
function easel_protected_excerpt( $excerpt ) {
  if ( post_password_required() ) {
    if ( 'works' === get_post_type() ) {
      return 'この作品を見るにはパスワードの入力が必要です。';
    } else {
      return 'この記事を見るにはパスワードの入力が必要です。';
    }
  }
  return $excerpt;
}
add_filter('the_excerpt', 'easel_protected_excerpt' );

// パスワード付き記事の本文調整
function easel_password_form() {
    $text = the_excerpt();
    $text = '<h5>パスワードを入力してください</h5>
    <form class="post_password" action="' . wp_login_url() . '?action=postpass" method="post">
    <input name="post_password" type="password" size="24" />
    <input type="submit" name="Submit" value="' . esc_attr__("送信") . '" />
    </form>';
    return $text;
}
add_filter('the_password_form', 'easel_password_form');

get_template_part( 'library/shortcode' );
get_template_part( 'library/widget' );

// ベースカラーを変更する
function easel_change_color() {
  $changebasecolor = get_option('easel_base_color');

  if($changebasecolor === 'basic_color' || empty($changebasecolor)) {
    return;
  } else {
    wp_register_style( 'change_base_color', get_template_directory_uri() . '/library/css/basecolors/style-'. $changebasecolor .'.css', array(), $theme->Version, 'all' );
    wp_enqueue_style( 'change_base_color' );
  }
}
add_action( 'wp_enqueue_scripts', 'easel_change_color', 9 );

// post_class関数のオーバーロード
function easel_make_indent( $classes ) {
	global $post;
  $terms = get_the_terms($post->ID,'custom_cat'); // タームのIDを取得
if (is_array($terms)) {
	foreach ($terms as $term) {
		// code...
		$term_id = $term->term_id;
		$ancestors = get_ancestors($term_id, 'custom_cat'); // タクソノミースラッグを指定してタームの配列を取得
		if(!empty($ancestors)) {
			$ancestors = array_reverse($ancestors); // 子親の順番で表示されるので、親子の順番に変更
			$ancestor = $ancestors[0]; // 階層が最も上の祖先タームIDを取り出す
			$parent_term = get_term($ancestor, 'custom_cat'); // タームIDとタクソノミースラッグを指定してターム情報を取得
			$slug = $parent_term->slug; // タームスラッグを取得
		}
	}
}
  global $my_options;
  if ( get_option('easel_make_indent') === '1' ) {
    if (has_term( 'text', 'custom_cat') || $slug == 'text') {
      $classes[] = 'make_indent';
    }
    return $classes;
  } else {
    return $classes;
  }
}
add_filter( 'post_class', 'easel_make_indent' );

// 改ページのページ送りを改修
// https://try-m.co.jp/blog/cms/883/
function custom_wp_link_pages($args = '')
{
  $defaults = array(
    'before'      => '<div class="page-links">',
    'after'       => '</div>',
    'next_or_number'       => 'number',
    'nextpagelink'     => __( '<i class="fas fa-angle-right"></i>' ),
    'previouspagelink' => __( '<i class="fas fa-angle-left"></i>' ),
    'separator'        => '',
    'pagelink'         => '%',
    'echo'             => 1,
    'text_before'      => '',
    'text_after'      => '',
  );

  $r = wp_parse_args( $args, $defaults );
  $r = apply_filters( 'wp_link_pages_args', $r );
  extract( $r, EXTR_SKIP );

  global $page, $numpages, $multipage, $more, $pagenow;

  $output = '';
  if ( $multipage ) {
    if ( 'number' == $next_or_number ) {
      $output .= $before;

      // 前に戻るリンク
      if($page > 1){
        $output .= _wp_link_page($page-1) . $defaults['previouspagelink'] . '</a>';
      }

      // 1ページ目から現在ページまでのリンク
      if($page > 5) {
        $output .= '' . _wp_link_page( 1 ) . '1</a><span style="font-size:80%;">　…　</span>';
        for ( $i = 2; $i >= 0; $i-- ) {
          $t = $page-$i;
          $j = str_replace( '%', $t, $pagelink );
          $output .= ' ';
          if ( $t != $page || ( ( ! $more ) && ( $page == 1 ) ) )
          $output .= '' . _wp_link_page( $t );
          else
          $output .= '<span class="post-page-numbers current">';

          $output .= $text_before . $j . $text_after;
          if ( $t != $page || ( ( ! $more ) && ( $page == 1 ) ) )
          $output .= '</a>';
          else
          $output .= '</span>';
        }
      } else {
        for ( $i = 1; $i <= $page; $i = $i + 1 ) {
          $j = str_replace( '%', $i, $pagelink );
          $output .= ' ';
          if ( $i != $page || ( ( ! $more ) && ( $page == 1 ) ) )
          $output .= '' . _wp_link_page( $i );
          else
          $output .= '<span class="post-page-numbers current">';

          $output .= $text_before . $j . $text_after;
          if ( $i != $page || ( ( ! $more ) && ( $page == 1 ) ) )
          $output .= '</a>';
          else
          $output .= '</span>';
        }
      }

      // 次のページから最後ページまでのリンク
      if(($numpages - $page) > 4) {
        for ( $i = 1; $i < 3; $i++ ) {
          $t = $page + $i;
          $j = str_replace( '%', $t, $pagelink );
          $output .= ' ';
          $output .= '' . _wp_link_page( $t );
          $output .= $text_before . $j . $text_after;
          $output .= '</a>';
        }
        $output .= '<span style="font-size:80%;">　…　</span>' . _wp_link_page($numpages) . $numpages .'</a>';
      } else {
        for ( $i = $page+1; $i <= $numpages; $i = $i + 1 ) {
          $j = str_replace( '%', $i, $pagelink );
          $output .= ' ';
          $output .= '' . _wp_link_page( $i );

          $output .= $text_before . $j . $text_after;
          $output .= '</a>';
        }
      }

      // 次へすすむリンク
      if($page != $numpages) {
          $output .= _wp_link_page($page+1) . $defaults['nextpagelink'] . '</a>';
      }
      $output .= $after;
    } else {
      if ( $more ) {
        $output .= $before;
        $i = $page - 1;
        if ( $i && $more ) {
          $output .= _wp_link_page( $i );
          $output .= $text_before . $previouspagelink . $text_after . '</a>';
        }
        $i = $page + 1;
        if ( $i <= $numpages && $more ) {
          $output .= _wp_link_page( $i );
          $output .=  $text_before . $nextpagelink . $text_after . '</a>';
        }
        $output .= $after;
      }
    }
  }

  if ( $echo )
    echo $output;

  return $output;
}

/* DON'T DELETE THIS CLOSING TAG */ ?>
