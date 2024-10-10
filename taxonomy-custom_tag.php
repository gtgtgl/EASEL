<?php get_header(); ?>

	<div id="content">
		<div id="inner-content" class="wrap cf">

				<main id="main" class="text-wrap" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

					<h1 class="archive-title h2"><?php single_cat_title(); ?></h1>

					<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

					<article id="post-<?php the_ID(); ?>" class="hentry text" role="article">

					<?php if ( has_post_thumbnail() ) : ?>
					<figure class="eye-catch" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
					<?php
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
								the_post_thumbnail(array($width, $height), $attr);
							} else {
								the_post_thumbnail('full', $attr);
							}
						?>
					</figure>
					<?php endif; ?>

					<section class="entry-content">


					<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>" class="text"><h3 class="h2"><?php the_title(); ?></h3></a>

						<div class="excerpt">
						<?php the_excerpt(); ?>
						</div>

						<p class="byline vcard">
							<?php
							printf(__('<time class="updated" datetime="%1$s" itemprop="datePublished">%2$s</time>', 'bonestheme'), get_the_time('Y-m-j'), get_the_time(__('Y-m-d', 'bonestheme')));
							printf('<span class="category"><i class="fas fa-folder"></i>' . __('', 'bonestheme' ) . '%1$s</span>', get_the_term_list( $post->ID,'custom_cat', '', ' ', '' ));
							if ( has_term( '', 'custom_tag', $post->ID ))	{
								printf( '<span class="tag"><i class="fas fa-tag"></i>' . __('', 'bonestheme' ) . '%1$s</span>' , get_the_term_list( $post->ID,'custom_tag', '', ',', '' ) );
							}
							?>
						</p>

					</section>
					</article>

					<?php endwhile; ?>

							<?php bones_page_navi(); ?>

					<?php else : ?>

							<article id="post-not-found" class="hentry cf">
								<header class="article-header">
									<h1><?php _e( '投稿が見つかりません。', 'bonestheme' ); ?></h1>
								</header>
							</article>

					<?php endif; ?>

				</main>

		</div>

	</div>

	<?php get_footer(); ?>
