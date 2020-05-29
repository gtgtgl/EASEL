<?php get_header(); ?>
			<div id="content">
				<div id="inner-content" class="wrap cf">

						<main id="main" class="illust-wrap" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<h1 class="archive-title h2"><?php single_cat_title(); ?></h1>

							<div class="easel_term_disc">
								<div class="easel_term_disc_inner">
									<?php
									$term_id = get_queried_object_id(); // タームのIDを取得
									$easel_term_disc = get_term_meta($term_id, 'easel_term_disc', 1); // タームの説明を取得
									echo nl2br($easel_term_disc);
									?>
								</div>
							</div>

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php the_title_attribute(); ?>" class="illust">
							<article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article">

								<?php
								if (post_password_required($post) && get_option('easel_pass_blur') === '1') {
									$pass = ' has_pass"';
								} else {
									$pass = null;
								}
								 ?>

							<figure class="eye-catch<?php echo $pass; ?>" itemprop="image" itemscope itemtype="https://schema.org/ImageObject">
							<?php if ( has_post_thumbnail() ) :
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
							    } ?>
							<?php else :
								echo get_the_image();
							endif; ?>
						</figure>

							<section class="entry-content">

								<h3 class="h2"><?php the_title(); ?></h3>
								<p class="byline vcard"><?php
									printf(__('<time class="updated" datetime="%1$s" itemprop="datePublished">%2$s</time>', 'bonestheme'), get_the_time('Y-m-j'), get_the_time(__('Y-m-d', 'bonestheme')), get_the_term_list( get_the_ID(), 'custom_cat', "", ", ", "" ));
								?></p>

								<div class="excerpt">
								<?php the_excerpt(); ?>
							</div>

								</section>

							</article>
							</a>

							<?php endwhile; ?>

									<?php bones_page_navi(); ?>

							<?php else : ?>

									<article id="post-not-found" class="hentry cf">
										<header class="article-header">
											<h1><?php _e( 'Oops, Post Not Found!', 'bonestheme' ); ?></h1>
										</header>
									</article>

							<?php endif; ?>

						</main>

				</div>

			</div>

<?php get_footer(); ?>
