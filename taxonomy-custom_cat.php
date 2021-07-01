<?php // 表示しているタームアーカイブの親・先祖タームの情報を取得して表示
  $term_id = get_queried_object_id(); // タームのIDを取得
  $ancestors = get_ancestors($term_id, 'custom_cat'); // タクソノミースラッグを指定してタームの配列を取得
  $ancestors = array_reverse($ancestors); // 子親の順番で表示されるので、親子の順番に変更
  if(!empty($ancestors)) {
    $ancestor = $ancestors[0]; // 階層が最も上の祖先タームIDを取り出す
    $parent_term = get_term($ancestor, 'custom_cat'); // タームIDとタクソノミースラッグを指定してターム情報を取得
    $slug = $parent_term->slug; // タームスラッグを取得
  } else {
  $slug = get_term( $term_id, 'custom_cat')->slug; // タームスラッグを取得
  }

	if ( $slug == "illust" || $term == "illust" ) {
	include( 'library/parts/works-illust.php' );
}	elseif ( $slug == "update" || $term == "update" ) {
	include( 'library/parts/works-update.php' );
} else {
	include( 'library/parts/works-text.php' );
};
	 ?>
