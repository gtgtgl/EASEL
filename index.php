<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap cf">

						<main id="main" class="" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

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

									<?php if ( has_post_thumbnail() ) : ?>
									<figure class="eye-catch" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
									<?php
									  if (has_post_thumbnail()) {
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

									  } else {
									    $url = get_singular_eyecatch_image_url();
									    $size = get_image_width_and_height($url);
									    $width = isset($size['width']) ? $size['width'] : 800;
									    $height = isset($size['height']) ? $size['height'] : 600;
									    echo ' <img src="'.$url.'" width="'.$width.'" height="'.$height.'" alt="">';
									  }
									  ?>
									</figure>
									<?php endif; ?>

								<section class="entry-content cf">
									<?php the_content('続きを読む'); ?>
								</section>

								<footer class="article-footer">

                 	<?php printf( '<span class="category">' . __('', 'bonestheme' ) . '%1$s</span>' , get_the_category_list(' ') ); ?>
                 	<?php printf( '<span class="tag">' . __('', 'bonestheme' ) . '%1$s</span>' , get_the_tag_list('', ',','') ); ?>

								</footer>

							</article>

							<?php endwhile; ?>

									<?php bones_page_navi(); ?>

							<?php else : ?>

									<article id="post-not-found" class="hentry cf">
											<header class="article-header">
												<h1><?php _e( 'Oops, Post Not Found!', 'bonestheme' ); ?></h1>
										</header>
											<section class="entry-content">
												<p><?php _e( 'Uh Oh. Something is missing. Try double checking things.', 'bonestheme' ); ?></p>
										</section>
										<footer class="article-footer">
												<p><?php _e( 'This is the error message in the index.php template.', 'bonestheme' ); ?></p>
										</footer>
									</article>

							<?php endif; ?>


						</main>

					<?php get_sidebar(); ?>

				</div>

			</div>


<?php get_footer(); ?>
