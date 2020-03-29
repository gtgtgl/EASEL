<?php
// ショートコードの実装
/* 最新記事リスト */
function getNewItems($atts) {
    extract(shortcode_atts(array(
        "count" => '5', //最新記事リストの取得数
        "work_type" => 'update', //表示する記事のカテゴリー指定
        "type" => 'default', //表示タイプ
        "post_type" => 'works', //投稿タイプ
        "orderby" => 'post_date', //表示の順番は何によるか
        "order" => 'DESC', //表示順は順か逆か
        "class" => null, //クラス
    ), $atts));
    global $post;
    $oldpost = $post;
    $myposts = get_posts('numberposts='.$count.'&post_type='.$post_type.'&order='.$order.'&orderby='.$orderby.'&custom_cat='.$work_type);
    $retHtml='<ul class="news_list '.$type.' '.$class.'">';
    foreach($myposts as $post) :
      setup_postdata($post);
      $retHtml.='<li><a href="'.get_permalink().'">';
      $retHtml.='<span class="news_date">'.get_post_time( 'Y-m-d' ).'</span>';
      $retHtml.='<span class="news_title">'.the_title("","",false).'</span>';
      $retHtml.='</a></li>';
    endforeach;
    $retHtml.='</ul>';
    $post = $oldpost;
    wp_reset_postdata();
    return $retHtml;
}
add_shortcode("new_list", "getNewItems");

/* 最新イラストリスト */
// 参考　https://github.com/yhira/cocoon/blob/master/tmp/eye-catch.php
function get_new_illusts_thum() {
  if ( has_post_thumbnail() ) {
    $thumbnail_id = get_post_thumbnail_id();
    $eye_img = wp_get_attachment_image_src( $thumbnail_id , 'full' );
    $url = $eye_img[0];
    $width = $eye_img[1];
    $height = $eye_img[2];
    $size = $width.'x'.$height.' size-'.$width.'x'.$height;
    $attr = array(
      'class' => "attachment-$size eye-catch-image",
    );
    //アイキャッチの表示
    if ($width && $height) {
      return get_the_post_thumbnail(null,array($width, $height), $attr);
    } else {
      return get_the_post_thumbnail(null,'full', $attr);
    }
  } else {
    return get_the_image();
  }
}
function getNewItems2($atts) {
  extract(shortcode_atts(array(
      "count" => '-1', //最新記事リストの取得数
      "work_type" => 'illust', //表示する記事のカテゴリー指定
      "type" => 'default', //表示タイプ
      "post_type" => 'works', //投稿タイプ
      "orderby" => 'post_date', //表示の順番は何によるか
      "order" => 'DESC', //表示順は順か逆か
      "class" => null, //クラス
  ), $atts));
  global $post;
  $oldpost = $post;
  $myposts = get_posts('numberposts='.$count.'&post_type='.$post_type.'&order='.$order.'&orderby='.$orderby.'&custom_cat='.$work_type);
  $retHtml='<ul class="shortcode-illust '.$type.' '.$class.'">';
  foreach($myposts as $post) :
    setup_postdata($post);
    $new_illusts_thum = get_new_illusts_thum();
    $retHtml.='<li><a href="'.get_permalink().'">';
    $retHtml.='<figure>'.$new_illusts_thum.'</figure>';
    $retHtml.= '<section><div><h3 class="h2">'.the_title("","",false).'</h3>';
    $retHtml.= '<p class="byline vcard"><time class="updated" itemprop="datePublished">'.get_the_time('Y-m-j').'</time></p>';
    $retHtml.= '</div></section></a></li>';
  endforeach;
  $retHtml.='</ul>';
  $post = $oldpost;
  wp_reset_postdata();
  return $retHtml;
}
add_shortcode("new_illust", "getNewItems2");

/* 最新記事リスト */
function getNewItems3($atts) {
  extract(shortcode_atts(array(
      "count" => '-1', //最新記事リストの取得数
      "work_type" => 'text', //表示する記事のカテゴリー指定
      "type" => 'default', //表示タイプ
      "post_type" => 'works', //投稿タイプ
      "orderby" => 'post_date', //表示の順番は何によるか
      "order" => 'ASC', //表示順は順か逆か
      "class" => null, //クラス
  ), $atts));
  global $post;
  $oldpost = $post;
  $myposts = get_posts('numberposts='.$count.'&post_type='.$post_type.'&order='.$order.'&orderby='.$orderby.'&custom_cat='.$work_type);
  $retHtml='<ul class="shortcode-text '.$type.' '.$class.'">';
  foreach($myposts as $post) :
    setup_postdata($post);
    $retHtml.='<li><a href="'.get_permalink().'"><h4>'.the_title("","",false).'</h4></a>';
    $retHtml.='<p>'.get_the_excerpt().'</p>';
  endforeach;
  $retHtml.='</ul>';
  $post = $oldpost;
  wp_reset_postdata();
  return $retHtml;
}
add_shortcode("new_text", "getNewItems3");

//テキストエディタにクイックタグボタン追加
if ( !function_exists( 'add_quicktags_to_text_editor' ) ):
function add_quicktags_to_text_editor() {
  //スクリプトキューにquicktagsが保存されているかチェック
  if (wp_script_is('quicktags')){?>
    <script>
      QTags.addButton('new_list','更新履歴一覧','[new_list]');
      QTags.addButton('new_illust','イラスト一覧','[new_illust]');
    </script>
  <?php
  }
}
endif;
add_action( 'admin_print_footer_scripts', 'add_quicktags_to_text_editor' );

//テーマテンプレートのfunctions.phpに追記
//ビジュアルテキストエディタにクイックタグを追加する

function my_add_mce_button() {
	// ユーザー権限を確認
	if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
		return;
	}
	// リッチテキストエディタを使えるか
	if ( 'true' == get_user_option( 'rich_editing' ) ) {
		add_filter( 'mce_external_plugins', 'my_add_tinymce_plugin' );
		add_filter( 'mce_buttons', 'my_register_mce_button' );
	}
}
add_action('admin_head', 'my_add_mce_button');

// プラグインを追加
function my_add_tinymce_plugin( $plugin_array ) {
	$plugin_array['my_mce_button'] = get_template_directory_uri() .'/library/js/quicktag.js'; //子テーマの場合は get_stylesheet_directory_uri()を使う
	return $plugin_array;
}

// リッチテキストエディタにボタンを追加する
function my_register_mce_button( $buttons ) {
	array_push( $buttons, 'my_mce_button' );
	return $buttons;
}

 ?>
