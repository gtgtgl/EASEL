<?php
$term = get_queried_object();
$slug = '';
if ($term instanceof WP_Term) {
  $ancestors = array_reverse(get_ancestors($term->term_id, 'custom_cat'));
  $root = $ancestors ? get_term($ancestors[0], 'custom_cat') : $term;
  $slug = $root instanceof WP_Term ? $root->slug : '';
}

	if ( $slug == "illust" ) {
	get_template_part( 'library/parts/works-illust' );
}	elseif ( $slug == "update" ) {
	get_template_part( 'library/parts/works-update' );
} else {
	get_template_part( 'library/parts/works-text' );
};
	 ?>
