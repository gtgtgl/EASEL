<?php get_header(); ?>

			<div id="content">

				<div id="inner-content" class="wrap cf">

					<main id="main" class="m-all t-2of3 d-5of7 cf" role="main" itemscope itemprop="mainContentOfPage" itemtype="http://schema.org/Blog">

						<article id="post-not-found" class="hentry cf">

							<header class="article-header">

								<h1><?php _e( '404エラー', 'bonestheme' ); ?></h1>

							</header>

							<section class="entry-content">

								<p><?php _e( 'URLが無効です。', 'bonestheme' ); ?></p>

							</section>

							<section class="search">

									<p><?php get_search_form(); ?></p>

							</section>

						</article>

					</main>

				</div>

			</div>

<?php get_footer(); ?>
