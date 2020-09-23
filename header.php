<!doctype html>

<head>
	<meta charset="utf-8">

	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<meta name="HandheldFriendly" content="True">
	<meta name="MobileOptimized" content="320">
	<meta name="viewport" content="width=device-width, initial-scale=1"/>

	<link rel="pingback" href="<?php bloginfo('pingback_url'); ?>">

	<link href="https://use.fontawesome.com/releases/v5.12.1/css/all.css" rel="stylesheet">
	<?php wp_head(); ?>
	<?php if (is_singular() )
	{
	$article_css = get_post_meta($post->ID,'custom_css',true);
	    if($article_css)
	    {
	    echo <<<EOS
<style>
$article_css
</style>
EOS;
	    }
	}
	?>
</head>

<?php if(get_option('easel_totop') === 'visible') : ?>
<a href="#" id="totop"></a>
<?php endif; ?>

<body <?php body_class(); ?> itemscope itemtype="http://schema.org/WebPage">

	<div id="container">

		<header class="header" role="banner" itemscope itemtype="http://schema.org/WPHeader">

			<div id="inner-header" class="wrap cf">

				<?php
				if ( !get_option('easel_title_image_url') ) : ?>
					<p id="logo" class="h1" itemscope itemtype="http://schema.org/Organization" itemprop="name about"><a href="<?php echo home_url(); ?>" rel="nofollow" itemprop="url"><?php bloginfo('name'); ?></a></p>

				<?php else : ?>

					<a href="<?php echo home_url(); ?>" class="logo" rel="nofollow" itemprop="name about"><img src="<?php echo get_option('easel_title_image_url'); ?>" alt="<?php bloginfo('name'); ?>"></a>

				<?php endif; ?>

				<p class="site-desc"><?php bloginfo('description'); ?></p>

				<label id="formenubar" for="menubar"><i class="fas fa-bars"></i></label>
				<input type="checkbox" name="menubar" id="menubar">

				<nav role="navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
				<label id="nav-back" for="menubar"></label>
					<?php wp_nav_menu(array(
  					         'container' => false,                           // remove nav container
  					         'container_class' => 'menu',                 // class of container (should you choose to use it)
  					         'menu' => __( 'ヘッダーメニュー', 'bonestheme' ),  // nav name
  					         'menu_class' => 'nav top-nav',               // adding custom nav class
  					         'theme_location' => 'main-nav',                 // where it's located in the theme
  					         'before' => '',                                 // before the menu
      			               'after' => '',                                  // after the menu
      			               'link_before' => '',                            // before each link
      			               'link_after' => '',                             // after each link
      			               'depth' => 0,                                   // limit the depth of the nav
  					         'fallback_cb' => ''                             // fallback function (if there is one)
					)); ?>

				</nav>

			</div>

		</header>
