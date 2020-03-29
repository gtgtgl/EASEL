<?php

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
			// 'not_found_in_trash' => __( 'Nothing found in Trash', 'bonestheme' ), /* This displays if there is nothing in the trash */
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
			'supports' => array( 'title', 'editor', 'thumbnail', 'author', 'excerpt', 'revisions', 'sticky')
		) /* end of options */
	); /* end of register post type */

	/* this adds your post categories to your custom post type */
	// register_taxonomy_for_object_type( 'category', 'custom_type' );
	/* this adds your post tags to your custom post type */
	// register_taxonomy_for_object_type( 'post_tag', 'custom_type' );

}

	// adding the function to the Wordpress init
	add_action( 'init', 'custom_post_example');

	/*
	for more information on taxonomies, go here:
	http://codex.wordpress.org/Function_Reference/register_taxonomy
	*/

	// now let's add custom categories (these act like categories)
	register_taxonomy( 'custom_cat',
		array('works'), /* if you change the name of register_post_type( 'custom_type', then you have to change this */
		array('hierarchical' => true,     /* if this is true, it acts like categories */
			'labels' => array(
				'name' => __( '作品タイプ', 'bonestheme' ), /* name of the custom taxonomy */
				'singular_name' => __( '作品タイプ', 'bonestheme' ), /* single taxonomy name */
				'search_items' =>  __( '作品タイプを探す', 'bonestheme' ), /* search title for taxomony */
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

// カスタム投稿タイプの表示件数を指定
function change_posts_per_page($query) {
  if ( is_admin() || ! $query->is_main_query() )
      return;
  if ( $query->is_archive('works') ) { //カスタム投稿タイプを指定
      $query->set( 'posts_per_page', '12' ); //表示件数を指定
  }
}
add_action( 'pre_get_posts', 'change_posts_per_page' );

?>
