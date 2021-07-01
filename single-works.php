<?php
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
		} else {
			$slug = $term->slug;
		}
	}
}
 ?>

<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap cf">

						<main id="main" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article">

							<header class="article-header entry-header">

								<?php
								if ( has_term( 'update', 'custom_cat') || $slug == 'update' ) { ?>
								<div class="post-date">
									<span class="day"><?php the_date('d'); ?></span>
									<span class="year"><?php the_time('Y.n'); ?></span>
								</div>
								<?php }; ?>

								<h1 class="entry-title single-title" itemprop="headline" rel="bookmark">
									<?php the_title(); ?>

										<?php
										global $page, $numpages, $multipage, $more, $pagenow;
										if($multipage){
											echo '<span class="page-num"> - '.$page.'/'.$numpages.'</span>';
										}
										?>
									</h1>
								<?php ; ?>

							</header> <?php // end article header ?>

								<section class="entry-content cf">
									<?php
										the_content();
										include( 'library/parts/pagination.php' );
									?>
								</section> <!-- end article section -->
							<footer class="article-footer">

								<?php if (  !has_term( 'update', 'custom_cat') && $slug != 'update' ) {
									the_date('', '<span class="day">', '</span>' );
								 }

								printf('<span class="category"><i class="fas fa-folder"></i>%1$s</span>', get_the_term_list( $post->ID,'custom_cat', '', ' ', '' ) );
								if ( has_term( '', 'custom_tag', $post->ID ))	{
									printf( '<span class="tag"><i class="fas fa-tag"></i>' . __('', 'bonestheme' ) . '%1$s</span>' , get_the_term_list( $post->ID,'custom_tag', '', ',', '' ) );
								}
								?>

							</footer>

							<?php
							if(get_option('easel_allow_comments_works') === '1') {
								comments_template();
							}
							?>

							</article>

							<?php endwhile; ?>

							<?php else : ?>

									<article id="post-not-found" class="hentry cf">
										<header class="article-header">
											<h1><?php _e( '投稿が見つかりません。', 'bonestheme' ); ?></h1>
										</header>
										<section class="entry-content">
											<p><?php _e( '誤りがないか確認してください。', 'bonestheme' ); ?></p>
										</section>
									</article>

							<?php endif; ?>

							<?php
							global $post;
							$array = get_the_terms( $post->ID, 'custom_cat' );
							?>

							<div class="pagination single">
								<?php next_post_link("%link", "%title", TRUE, '', "custom_cat"); ?>
								<?php previous_post_link("%link", "%title", TRUE, '', "custom_cat"); ?>
							</div>

						</main>

				</div>

			</div>

<?php get_footer(); ?>
