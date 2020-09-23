<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap cf">

					<main id="main" class="search" role="main">
						<h1 class="archive-title">「<?php echo esc_attr(get_search_query()); ?>」の検索結果</h1>

						<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article">

								<header class="entry-header article-header">

									<h3 class="search-title entry-title"><a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>"><?php the_title(); ?></a></h3>

                  						<p class="byline entry-meta vcard">
                    							<?php printf( __( '%1$s', 'bonestheme' ),
                   							    /* the time the post was published */
                   							    '<time class="updated entry-time" datetime="' . get_the_time('Y-m-d') . '" itemprop="datePublished">' . get_the_time(get_option('date_format')) . '</time>',
                    							); ?>
                  						</p>

								</header>

								<section class="entry-content">
										<?php the_excerpt( '<span class="read-more">' . __( '続きを読む', 'bonestheme' ) . '</span>' ); ?>

								</section>

								<footer class="article-footer">

									<?php if(get_the_category_list(', ') != ''): ?>
                  					<?php printf( __( '%1$s', 'bonestheme' ), get_the_category_list(', ') ); ?>
                  					<?php endif; ?>

                 					<?php the_tags( '<p class="tags"><span class="tags-title">' . __( '', 'bonestheme' ) . '</span> ', ', ', '</p>' ); ?>

								</footer> <!-- end article footer -->

							</article>

						<?php endwhile; ?>

								<?php bones_page_navi(); ?>

							<?php else : ?>

									<article id="post-not-found" class="hentry cf">
										<header class="article-header">
											<h1><?php _e( '該当するページがありません。', 'bonestheme' ); ?></h1>
										</header>
										<section class="entry-content">
											<p><?php _e( '検索語句を変えてみてください。', 'bonestheme' ); ?></p>
										</section>
									</article>

							<?php endif; ?>

						</main>
					</div>

			</div>

<?php get_footer(); ?>
