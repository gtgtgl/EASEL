<?php

if ( !defined( 'ABSPATH' ) ) exit;

// Flush rewrite rules for custom post types
add_action( 'after_switch_theme', 'bones_flush_rewrite_rules' );

// Flush your rewrite rules
function bones_flush_rewrite_rules() {
	flush_rewrite_rules();
}

// let's create the function for the custom type
function custom_post_example() {
	// creating (registering) the custom type
	register_post_type( 'works', /* (http://codex.wordpress.org/Function_Reference/register_post_type) */
		// let's now add all the options for this post type
		array( 'labels' => array(
			'name' => __( '作品', 'bonestheme' ), /* This is the Title of the Group */
			'singular_name' => __( '作品', 'bonestheme' ), /* This is the individual type */
			'all_items' => __( '作品一覧', 'bonestheme' ), /* the all items menu item */
			'add_new' => __( '作品を投稿する', 'bonestheme' ), /* The add new menu item */
			'add_new_item' => __( '新しい作品を投稿する', 'bonestheme' ), /* Add New Display Title */
			'edit' => __( '編集', 'bonestheme' ), /* Edit Dialog */
			'edit_item' => __( '編集', 'bonestheme' ), /* Edit Display Title */
			'new_item' => __( '新しい作品', 'bonestheme' ), /* New Display Title */
			'view_item' => __( '作品を見る', 'bonestheme' ), /* View Display Title */
			'search_items' => __( '作品を検索', 'bonestheme' ), /* Search Custom Type Title */
			'not_found' =>  __( 'まだ作品がありません', 'bonestheme' ), /* This displays if there are no entries yet */
			'parent_item_colon' => ''
			), /* end of arrays */
			'public' => true,
			'publicly_queryable' => true,
			'exclude_from_search' => false,
			'show_ui' => true,
			'query_var' => true,
			'menu_position' => 8, /* this is what order you want it to appear in on the left hand side menu */
			'menu_icon' => 'dashicons-art', /* the icon for the custom post type menu */
			'rewrite'	=> array( 'slug' => 'works', 'with_front' => false ), /* you can specify its url slug */
			'has_archive' => 'works', /* you can rename the slug here */
			'capability_type' => 'post',
			'hierarchical' => true,
			/* the next one is important, it tells what's enabled in the post editor */
			'supports' => array( 'title', 'editor', 'thumbnail', 'author', 'excerpt', 'revisions', 'comments', 'sticky')
		) /* end of options */
	); /* end of register post type */
}
	add_action( 'init', 'custom_post_example');

	register_taxonomy( 'custom_cat',
		array('works'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
		array('hierarchical' => true,     /* if this is true, it acts like categories */
			'labels' => array(
				'name' => __( '作品タイプ', 'bonestheme' ), /* name of the custom taxonomy */
				'singular_name' => __( '作品タイプ', 'bonestheme' ), /* single taxonomy name */
				'all_items' => __( '作品タイプ一覧', 'bonestheme' ), /* all title for taxonomies */
				'parent_item' => __( '親作品タイプ', 'bonestheme' ), /* parent title for taxonomy */
				'parent_item_colon' => __( '親作品タイプ：', 'bonestheme' ), /* parent taxonomy title */
				'edit_item' => __( '編集', 'bonestheme' ), /* edit custom taxonomy title */
				'update_item' => __( '更新', 'bonestheme' ), /* update title for taxonomy */
				'add_new_item' => __( '作品タイプを追加する', 'bonestheme' ), /* add new title for taxonomy */
				'new_item_name' => __( '作品タイプの名前', 'bonestheme' ) /* name title for taxonomy */
			),
			'show_admin_column' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'work_type' ),
      'has_archive' => true,  //trueにすると投稿した記事の一覧ページを作成することができる
		)
	);

	// now let's add custom tags (these act like categories)
	register_taxonomy( 'custom_tag',
		array('works'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
		array('hierarchical' => false,    /* if this is false, it acts like tags */
			'labels' => array(
				'name' => __( '作品タグ', 'bonestheme' ), /* name of the custom taxonomy */
				'singular_name' => __( '作品タグ', 'bonestheme' ), /* single taxonomy name */
				'all_items' => __( '作品タグ一覧', 'bonestheme' ), /* all title for taxonomies */
				'edit_item' => __( '編集', 'bonestheme' ), /* edit custom taxonomy title */
				'update_item' => __( '更新', 'bonestheme' ), /* update title for taxonomy */
				'add_new_item' => __( '作品タグを追加する', 'bonestheme' ), /* add new title for taxonomy */
				'new_item_name' => __( '作品タグの名前', 'bonestheme' ) /* name title for taxonomy */
			),
			'show_admin_column' => true,
			'show_ui' => true,
			'query_var' => true,
			'rewrite' => array( 'slug' => 'work_tag' ),
      'has_archive' => true,  //trueにすると投稿した記事の一覧ページを作成することができる
		)
	);

// カスタム投稿タイプの表示件数を指定
function easel_change_posts_per_page($query) {
  if ( is_admin() || ! $query->is_main_query() )
      return;
  if ( $query->is_archive('works') ) { //カスタム投稿タイプを指定
      $query->set( 'posts_per_page', '12' ); //表示件数を指定
  }
}
add_action( 'pre_get_posts', 'easel_change_posts_per_page' );

// 作品タイプに説明文を追加させる
// function add_term_fields() {
//   echo '<div class="form-field">
//        <label for="easel_term_disc">一覧ページに表示するテキスト</label>';
// 			 wp_editor(
// 				'',
// 				'easel_term_disc',
// 				array (
// 				'textarea_rows' => 5
// 			 ,	'textarea_name' => 'easel_term_disc'
// 			 ,   'media_buttons' => true
// 			 ,   'teeny'     => true
// 			 ,   'tinymce'     => true
// 				)
// 			);
// echo '<p>ここに入力したテキストがアーカイブページのタイトル下に表示されます。タグも使えます。</p>
//      </div>';
// }
// add_action('custom_cat_add_form_fields', 'add_term_fields');

// ターム一覧ページの編集画面に要素を追加する関数
function edit_term_fields( $tag ) {
    //まずは、すでに設定されている情報を取得
    $value = get_term_meta($tag->term_id, 'easel_term_disc', 1);
    echo '<tr class="form-field">
             <th><label for="easel_term_disc">一覧ページに表示するテキスト</label></th>
             <td>';
						 wp_editor(
						 	$value,
						 	'easel_term_disc',
						 	array (
						 	'textarea_rows' => 5
						 ,   'media_buttons' => true
						 ,   'teeny'     => true
						 ,   'tinymce'     => true
						 	)
						);
		echo '<p>ここに入力したテキストがアーカイブページのタイトル下に表示されます。タグも使えます。</p>
   </td>
	 </tr>';
}
//フック
add_action('custom_cat_edit_form_fields', 'edit_term_fields');

function save_terms( $term_id ) {
  if (array_key_exists('easel_term_disc', $_POST)) {
    update_term_meta( $term_id, 'easel_term_disc', $_POST['easel_term_disc']);
  }
}
//新規追加用フック
add_action( 'create_term', 'save_terms' );   // => 他が保存される前
add_action( 'created_term', 'save_terms' );  // => 他が保存された後

//編集画面用フック
add_action( 'edit_terms', 'save_terms' );    // => 他が保存される前
add_action( 'edited_terms', 'save_terms' );  // => 他が保存された後

// 作品管理画面で作品を日付順に並べる
function set_post_types_admin_order( $wp_query ) {
  if (is_admin()) {
    $post_type = $wp_query->query['post_type'];
    if ( $post_type == 'works' ) {
      $wp_query->set('orderby', 'date');
      $wp_query->set('order', 'DESC');
    }
  }
}
add_filter('pre_get_posts', 'set_post_types_admin_order');

?>
