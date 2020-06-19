<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap cf">

					<main id="main" class="archive" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

						<h1 class="archive-title h2"><?php post_type_archive_title(); ?></h1>

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">

								<header class="article-header">

									<h1 class="h2 entry-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h1>
									<p class="byline entry-meta vcard">
											<?php
												printf(__('<time class="updated" datetime="%1$s" itemprop="datePublished">%2$s</time>', 'bonestheme'), get_the_time('Y-m-d'), get_the_time(__('Y-m-d', 'bonestheme')));
												?>
									</p>

								</header>

								<section class="entry-content cf">

									<?php the_excerpt(); ?>

								</section>

								<footer class="article-footer">

								</footer>

							</article>

							<?php endwhile; ?>

									<?php bones_page_navi(); ?>

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

						</main>

				</div>

			</div>

<?php get_footer(); ?>
