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
