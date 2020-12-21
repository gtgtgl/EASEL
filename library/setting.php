<?php
if ( !defined( 'ABSPATH' ) ) exit;
 ?>
 <script>
 jQuery("document").ready(function() {
     jQuery( "#tabs" ).tabs();
 });
 </script>

 <h2>EASEL設定</h2>
 <p>WordPressテーマ「EASEL」で行える設定です。
  詳しくは<a href="https://easel.gt-gt.org/" target="_blank">EASEL設定マニュアル</a>をご確認ください。</p>

 <?php
 global $wp_rewrite;
 if ( $wp_rewrite->permalink_structure === '' && get_option('easel_rewrite_permalink') === 'changed' ) :
 ?>
 <div class="error" style="background: #ffebef;">
   <p>
     パーマリンク設定が「基本」のままになっています。<br>
     このままだと作品URLが404エラーになってしまうので、
     「作品タイプ階層準拠」設定を利用する場合は、設定＞パーマリンク設定を「基本」<strong>以外</strong>に変更してください。
   </p>
 </div>
 <?php
endif;
 ?>

<form method="post" action="options.php">
  <?php settings_fields( 'easel_settings' ); ?>
  <?php do_settings_sections( 'easel_settings' ); ?>

  <?php
    $easel_tab1 = 'サイト設定・ビジュアル設定';
    $easel_tab2 = '作品展示設定';
    $easel_tab3 = 'OGP設定(β)';
    $easel_tab4 = '予備設定';
   ?>

 <div id="tabs">
     <ul>
         <li><a href="#tabs-1"><?php echo $easel_tab1; ?></a></li>
         <li><a href="#tabs-2"><?php echo $easel_tab2; ?></a></li>
         <li><a href="#tabs-3"><?php echo $easel_tab3; ?></a></li>
         <li><a href="#tabs-4"><?php echo $easel_tab4; ?></a></li>
     </ul>
     <div id="tabs-1">
       <h3><?php echo $easel_tab1; ?></h3>
        <table class="form-table">
          <tr valign="top">
              <th scope="row"><label for="easel_title_image_url">サイトのロゴ画像</label>
              </th>
              <td>
                <?php
                  generate_upload_image_tag('easel_title_image_url', get_option('easel_title_image_url'));
                ?>
                  <p>画像を登録しない場合、サイト名のテキストが表示されます。</p>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="easel_footer_text">サイト最下部コピーライト</label>
              </th>
              <td><label><input name="easel_footer_text" id="easel_footer_text" type="text" value="<?php echo get_option('easel_footer_text'); ?>" class="regular-text" placeholder="copyright (c) サイト名等"></label><br>
              サイト最下部に表示されるコピーライトテキストを変更できます。</td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="easel_base_color">ベースデザイン変更</label>
              </th>
              <td>
                <p>サイトのデザインを変更します。子テーマでカスタマイズがされている場合はそちらが優先されます。</p>
                <label><input type="radio" name="easel_base_color" value="basic_color" <?php checked(get_option('easel_base_color') , 'basic_color'); ?>>
                ベーシック</label><br>
                <label><input type="radio" name="easel_base_color" value="blue_white" <?php checked(get_option('easel_base_color') , 'blue_white'); ?>>
                ブルー＆ホワイト</label><br>
                <label><input type="radio" name="easel_base_color" value="pink_white" <?php checked(get_option('easel_base_color') , 'pink_white'); ?>>
                ピンク＆ホワイト</label><br>
                <label><input type="radio" name="easel_base_color" value="orange_white" <?php checked(get_option('easel_base_color') , 'orange_white'); ?>>
                オレンジ＆ホワイト</label><br>
                <label><input type="radio" name="easel_base_color" value="piano" <?php checked(get_option('easel_base_color') , 'piano'); ?>>
                イボニー＆アイボリー</label><br>
              </td>
          </tr>
          <tr valign="top">
              <th scope="row"><label for="easel_totop">最上部へ戻るボタン</label>
              </th>
              <td>
                <p>サイトの右下に、最上部へ戻るボタンを表示することができます。</p>
                <label><input type="radio" name="easel_totop" value="visible" <?php checked(get_option('easel_totop') , 'visible'); ?>>
                表示する</label><br>
                <label><input type="radio" name="easel_totop" value="hidden" <?php checked(get_option('easel_totop') , 'hidden'); ?>>
                表示しない</label>
              </td>
          </tr>
          <tr valign="top">
            <th scope="row">
              コメント機能を使用する
            </th>
            <td>
              <label><input name="easel_allow_comments_posts" type="checkbox" id="easel_allow_comments_posts" value="1" <?php checked( 1, get_option('easel_allow_comments_posts')); ?> /> 「投稿」でコメント機能を使用する</label><br>
              <label><input name="easel_allow_comments_works" type="checkbox" id="easel_allow_comments_works" value="1" <?php checked( 1, get_option('easel_allow_comments_works')); ?> /> 「作品」でコメント機能を使用する</label><br>
              <p>コメント機能の詳細は、<a href="options-discussion.php">設定＞ディスカッション設定</a>にて行ってください。</p>
            </td>
          </tr>
        </table>
        <p class="submit">
          <?php submit_button(); ?>
        </p>
     </div>

     <div id="tabs-2">
       <h3><?php echo $easel_tab2; ?></h3>
        <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="easel_set_terms">作品タイプの初期設定を行う</label>
            </th>
            <td>
              <input name="easel_set_terms" type="checkbox" id="easel_set_terms" value="1" <?php checked( 1, get_option('easel_set_terms')); ?> /> 行う</label>
              <p>チェックを入れて設定を保存すると、作品タイプ
                <?php
                  global $easel_terms;
                  foreach ( $easel_terms as $easel_term ) {
                      echo '「'. $easel_term[1] . '」';
                  } ?>
                  が生成されます。<br>
                  生成後、使用しない作品タイプは削除してかまいません。
                 </p>
            </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="easel_pass_blur">パス付きイラストのサムネイルをぼかす</label>
          </th>
          <td>
            <label><input name="easel_pass_blur" type="checkbox" id="easel_pass_blur" value="1" <?php checked( 1, get_option('easel_pass_blur')); ?> /> 行う</label><br>
            <p>チェックを入れると、パスワード付きのイラスト作品のサムネイルを自動でぼかします。</p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="easel_make_indent">作品タイプ「文章」の全文で字下げを行う</label>
          </th>
          <td>
            <label><input name="easel_make_indent" type="checkbox" id="easel_make_indent" value="1" <?php checked( 1, get_option('easel_make_indent')); ?> /> 行う</label><br>
            <p>チェックを入れると、作品タイプ「文章」およびその子タイプの作品の全文で字下げが自動で行われます。</p>
          </td>
        </tr>
        <tr valign="top">
          <th scope="row"><label for="easel_make_indent">ショートコードで指定した本文の範囲だけ字下げを行う</label>
          </th>
          <td>
            <label><input name="easel_make_main_content_indent" type="checkbox" id="easel_make_main_content_indent" value="1" <?php checked( 1, get_option('easel_make_main_content_indent')); ?> /> 行う</label><br>
            <p>チェックを入れると、[main_content]ショートコードで指定した本文部分のみが字下げされます。あとがきなどを書きたい方向け。</p>
          </td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="easel_rewrite_permalink">作品投稿のパーマリンク</label>
            </th>
            <td>
              <label><input type="radio" name="easel_rewrite_permalink" value="default" <?php checked( get_option('easel_rewrite_permalink') , 'default'); ?>>
              <strong>初期設定：</strong><?php echo home_url(); ?>/works/作品スラッグ</label><br>
              <label><input type="radio" name="easel_rewrite_permalink" value="changed" <?php checked( get_option('easel_rewrite_permalink') , 'changed'); ?>>
              <strong>作品タイプ階層準拠：</strong><?php echo home_url(); ?>/works/作品タイプスラッグ/作品ID（投稿個別で固定の数字）</label><br>
              <p>「作品タイプ階層準拠」設定にすると、作品URLの末尾部分は変更できなくなりますが、<br>
              アドレスが作品タイプ階層になるため分かりやすく、パーマリンクが被らないように配慮する必要がなくなるため、オススメです。<br>
              サイト運営途中で設定を変更する場合は、リンク切れにご注意ください。<br>
              <span class="red">※「作品タイプ階層準拠」に設定する場合は、パーマリンク設定を「基本」<strong>以外</strong>に設定してください。<br>
              ※この設定を変更した後は、必ず「設定＞パーマリンク設定」から「変更を保存」をクリックし、パーマリンクを設定しなおしてください。</span></p>
            </td>
        </tr>
        </table>
        <p class="submit">
          <?php submit_button(); ?>
        </p>
     </div>

     <div id="tabs-3">
       <h3><?php echo $easel_tab3; ?></h3>
       <p><span class="red">※テスト運用中につき、環境によっては設定が上手くいかないかもです。</span></p>
        <table class="form-table">
         <tr valign="top">
             <th scope="row"><label for="easel_ogp_image">OGP画像</label>
             </th>
             <td>
               <?php
                 generate_upload_image_tag('easel_ogp_image', get_option('easel_ogp_image'));
               ?>
                 <p>TwitterなどでサイトのURLをシェアしたとき、<br>
                   当該ページにアイキャッチ画像が設定されていなかった場合、この画像が表示されます。</p>
             </td>
         </tr>
         <tr valign="top">
             <th scope="row"><label for="easel_ogp_twitter_card">Twitterカードの画像の大きさ</label>
             </th>
             <td>
               <p>TwitterでサイトURLをシェアしたときの画像の大きさを選ぶことができます。</p>
               <label><input type="radio" name="easel_ogp_twitter_card" value="summary" <?php checked(get_option('easel_ogp_twitter_card') , 'summary'); ?>>
               小さい画像（200px × 200px程度）</label><br>
               <label><input type="radio" name="easel_ogp_twitter_card" value="summary_large_image" <?php checked(get_option('easel_ogp_twitter_card') , 'summary_large_image'); ?>>
               大きい画像（1200px × 630px程度）</label>
             </td>
         </tr>
         <tr valign="top">
             <th scope="row"><label for="easel_ogp_description">サイトの説明</label></th>
             <td>
               <textarea name="easel_ogp_description" id="easel_ogp_description" placeholder="サイトの説明" rows="3" cols="60"><?php echo get_option('easel_ogp_description'); ?></textarea>
               <p>サイトのトップページの説明文。twitterカードや検索エンジンの概要文に使われます。60文字程度でご記入ください。<br>
                 ※投稿、固定ページおよび作品ページにおいて、「抜粋」が入力されている場合は抜粋が使用されます。</p>
             </td>
         </tr>
       </table>
       <p class="submit">
         <?php submit_button(); ?>
       </p>
     </div>

     <div id="tabs-4">
       <h3><?php echo $easel_tab4; ?></h3>
       <p>現在はサイトに反映されませんが、今後活用するかもしれない設定です</p>
        <table class="form-table">
        <tr valign="top">
            <th scope="row"><label for="easel_twitter">TwitterURL</label></th>
            <td><input name="easel_twitter" id="easel_twitter" type="text" value="<?php echo get_option('easel_twitter'); ?>" class="regular-text" placeholder="https://twitter.com/userID"><br>
            ※現在のバージョンでは使用しませんが、後々のアップデートで使用するかもです</td>
        </tr>
        <tr valign="top">
            <th scope="row"><label for="easel_pixiv">PixivURL</label></label></th>
            <td><input name="easel_pixiv" id="easel_pixiv" type="text" value="<?php echo get_option('easel_pixiv'); ?>" class="regular-text" placeholder="https://www.pixiv.net/users/userID"><br>
            ※現在のバージョンでは使用しませんが、後々のアップデートで使用するかもです</td>
        </tr>
        </table>

        <p class="submit">
          <?php submit_button(); ?>
        </p>
     </div>
 </div>
</form>
