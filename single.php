<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap cf">

					<main id="main" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

						<?php if (have_posts()) : while (have_posts()) : the_post(); ?>

<article id="post-<?php the_ID(); ?>" <?php post_class('cf'); ?> role="article" itemscope itemprop="blogPost" itemtype="http://schema.org/BlogPosting">

	<header class="article-header entry-header">

		<div class="post-date">
			<span class="day"><?php the_date('d'); ?></span>
			<span class="year"><?php the_time('Y.n'); ?></span>
		</div>

		<h1 class="entry-title single-title" itemprop="headline" rel="bookmark"><?php the_title(); ?></h1>

	</header> <?php // end article header ?>

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

	<section class="entry-content" itemprop="articleBody">
		<?php
			// the content (pretty self explanatory huh)
			the_content('続きを読む');
			include( 'library/parts/pagination.php' );
		?>
	</section> <?php // end article section ?>

	<footer class="article-footer">

		<?php
		printf( '<span class="category"><i class="fas fa-folder"></i>' . __('', 'bonestheme' ) . '%1$s</span>' , get_the_category_list(' ') );
		if ( has_tag())	{
			printf( '<span class="tag"><i class="fas fa-tag"></i>' . __('', 'bonestheme' ) . '%1$s</span>' , get_the_tag_list('', ',','') );
		}
		?>

	</footer>

	<?php //comments_template(); ?>

</article> <?php // end article ?>

						<?php endwhile; ?>

						<?php else : ?>

							<article id="post-not-found" class="hentry cf">
									<header class="article-header">
										<h1><?php _e( '投稿がみつかりません。', 'bonestheme' ); ?></h1>
									</header>
							</article>

						<?php endif; ?>

					</main>

					<?php get_sidebar(); ?>

				</div>

			</div>

<?php get_footer(); ?>
