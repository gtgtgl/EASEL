<?php

wp_link_pages( array(
  'before'      => '<div class="page-links-next">',
  'after'       => '</div>',
  'next_or_number'       => 'next',
  'nextpagelink'     => __( '<i class="fas fa-angle-right"></i>' ),
  'previouspagelink' => __( '<i class="fas fa-angle-left"></i>' ),
) );
wp_link_pages( array(
  'before'      => '<div class="page-links">',
  'after'       => '</div>',
) );

 ?>
