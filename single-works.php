<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap cf">

						<main id="main" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article">

							<header class="article-header entry-header">

								<?php
								$terms = get_the_terms($post->ID,'custom_cat'); // タームのIDを取得
								foreach ($terms as $term) {
									// code...
									$term_id = $term->term_id;
									$ancestors = get_ancestors($term_id, 'custom_cat'); // タクソノミースラッグを指定してタームの配列を取得
									$ancestors = array_reverse($ancestors); // 子親の順番で表示されるので、親子の順番に変更
								  $ancestor = $ancestors[0]; // 階層が最も上の祖先タームIDを取り出す
								    $parent_term = get_term($ancestor, 'custom_cat'); // タームIDとタクソノミースラッグを指定してターム情報を取得
								    $slug = $parent_term->slug; // タームスラッグを取得
								}
								if ( has_term( 'update', 'custom_cat') || $slug == 'update' ) { ?>
								<div class="post-date">
									<span class="day"><?php the_date('d'); ?></span>
									<span class="year"><?php the_time('Y.n'); ?></span>
								</div>
								<?php }; ?>

								<h1 class="entry-title single-title" itemprop="headline" rel="bookmark"><?php the_title(); ?></h1>
								<?php ; ?>

							</header> <?php // end article header ?>

								<section class="entry-content cf">
									<?php
										the_content();
										wp_link_pages( array(
											'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'bonestheme' ) . '</span>',
											'after'       => '</div>',
											'link_before' => '<span>',
											'link_after'  => '</span>',
										) );
									?>
								</section> <!-- end article section -->
							<footer class="article-footer">


								<?php if (  !has_term( 'update', 'custom_cat') && $slug != 'update' ) { ?>
								<span class="day"><?php the_date(); ?></span>
								<?php }; ?>

								<?php
								$terms = get_the_terms($post->ID,'custom_cat');
								foreach( $terms as $term ) {
								echo '<span class="category"><a href="'.get_term_link($term->slug, 'custom_cat').'">'.$term->name.'</a></span>';
								}
								?>

							</footer>

							</article>

							<?php endwhile; ?>

							<?php else : ?>

									<article id="post-not-found" class="hentry cf">
										<header class="article-header">
											<h1><?php _e( 'Oops, Post Not Found!', 'bonestheme' ); ?></h1>
										</header>
										<section class="entry-content">
											<p><?php _e( 'Uh Oh. Something is missing. Try double checking things.', 'bonestheme' ); ?></p>
										</section>
									</article>

							<?php endif; ?>

							<?php
							global $post;
							$array = get_the_terms( $post->ID, 'custom_cat' );
							?>

							<div class="pagination single">
								<?php next_post_link("%link", "%title", TRUE, "$array[slug]", "custom_cat"); ?>
								<?php previous_post_link("%link", "%title", TRUE, "$array[slug]", "custom_cat"); ?>
							</div>

						</main>

				</div>

			</div>

<?php get_footer(); ?>
