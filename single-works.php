<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap cf">

						<main id="main" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article">

							<header class="article-header entry-header">

								<div class="post-date">
									<span class="day"><?php the_date('d'); ?></span>
									<span class="year"><?php the_time('Y.n'); ?></span>
								</div>

								<h1 class="entry-title single-title" itemprop="headline" rel="bookmark"><?php the_title(); ?></h1>

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
										<footer class="article-footer">
											<p><?php _e( 'This is the error message in the single-custom_type.php template.', 'bonestheme' ); ?></p>
										</footer>
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
