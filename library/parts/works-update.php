<?php get_header(); ?>
<?php
$term_id = get_queried_object_id();
?>

	<div id="content">
		<div id="inner-content" class="wrap cf">

				<main id="main" class="update-wrap" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

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

					<article id="post-<?php the_ID(); ?>" class="hentry update" role="article">

					<section class="entry-content">

					<div class="wrap">
						<p class="byline vcard"><?php
						printf(__('<time class="updated" datetime="%1$s" itemprop="datePublished">%2$s</time>', 'bonestheme'), get_the_time('Y-m-d'), get_the_time(__('Y-m-d', 'bonestheme')), get_the_term_list( get_the_ID(), 'custom_cat', "", ", ", "" ));
						?></p>
						<h3 class="h2"><?php the_title(); ?></h3>
					</div>

						<div class="excerpt">
						<?php the_content('続きを読む'); ?>
					</div>

						</section>

					</article>

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
