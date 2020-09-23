			<footer class="footer" role="contentinfo" itemscope itemtype="http://schema.org/WPFooter">

				<?php if ( is_active_sidebar( 'footer_left' ) || is_active_sidebar( 'footer_center' ) || is_active_sidebar( 'footer_right' )  ) : ?>
				<div id="footer_widget">
					<div class="wrap">
						<div class="footer_left"><?php dynamic_sidebar( 'footer_left' ); ?></div>
						<div class="footer_center"><?php dynamic_sidebar( 'footer_center' ); ?></div>
						<div class="footer_right"><?php dynamic_sidebar( 'footer_right' ); ?></div>
					</div>
				</div>
				<?php endif; ?>

				<div id="inner-footer" class="wrap">

					<nav role="navigation">
						<?php wp_nav_menu(array(
    					'container' => 'div',                           // enter '' to remove nav container (just make sure .footer-links in _base.scss isn't wrapping)
    					'container_class' => 'footer-links',         // class of container (should you choose to use it)
    					'menu' => __( 'Footer Links', 'bonestheme' ),   // nav name
    					'menu_class' => 'nav footer-nav',            // adding custom nav class
    					'theme_location' => 'footer-links',             // where it's located in the theme
    					'before' => '',                                 // before the menu
    					'after' => '',                                  // after the menu
    					'link_before' => '',                            // before each link
    					'link_after' => '',                             // after each link
    					'depth' => 0,                                   // limit the depth of the nav
    					'fallback_cb' => 'bones_footer_links_fallback'  // fallback function
						)); ?>
					</nav>

						<?php
						$footer_text = get_option('easel_footer_text');
						$year = get_the_time('Y');
						if ($footer_text == false) { ?>
							<p class="source-org copyright">Copyright (c) <?php get_the_time('Y'); ?> <?php bloginfo( 'name' ) ?>, with WP theme <a href="https://easel.gt-gt.org/">EASEL</a></p>
						  <?php } else { ?>
						  <p class="source-org copyright"><?php echo $footer_text; ?>, with WP theme <a href="https://easel.gt-gt.org/">EASEL</a></p>
					<?php } ?>

				</div>

			</footer>

		</div>

		<?php wp_footer(); ?>

	</body>

</html> <!-- end of site. what a ride! -->
