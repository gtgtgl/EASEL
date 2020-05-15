<?php
/*
 Template Name: サイドバーあり
*/
?>

<?php get_header(); ?>
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

			<div id="content">

				<h1 class="page-title"><?php the_title(); ?></h1>

				<div id="inner-content" class="wrap cf">

						<main id="main" class="m-all t-all d-all" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

							<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

							<article id="post-<?php the_ID(); ?>" <?php post_class( 'cf' ); ?> role="article" itemscope itemtype="http://schema.org/BlogPosting">

								<section class="entry-content cf" itemprop="articleBody">
									<?php
										the_content();
										wp_link_pages( array(
											'before'      => '<div class="page-links"><span class="page-links-title">' . __( 'Pages:', 'bonestheme' ) . '</span>',
											'after'       => '</div>',
											'link_before' => '<span>',
											'link_after'  => '</span>',
										) );
									?>
								</section>

							</article>

							<?php endwhile; else : ?>

									<article id="post-not-found" class="hentry cf">
											<header class="article-header">
												<h1><?php _e( 'Oops, Post Not Found!', 'bonestheme' ); ?></h1>
										</header>
									</article>

							<?php endif; ?>

						</main>

				<div id="sidebar2" class="sidebar last-col" role="complementary">

					<?php if ( is_active_sidebar( 'sidebar2' ) ) : ?>

						<?php dynamic_sidebar( 'sidebar2' ); ?>

					<?php else : ?>

						<?php
							/*
							 * This content shows up if there are no widgets defined in the backend.
							*/
						?>

						<div class="no-widgets">
							<p><?php _e( 'ダッシュボード＞外観＞ウィジェット＞サイドバー２　に、ここに表示したいコンテンツを追加してください。', 'bonestheme' );  ?></p>
						</div>

					<?php endif; ?>

				</div>


				</div>

			</div>


<?php get_footer(); ?>
