<?php
if ( !defined( 'ABSPATH' ) ) exit;
 ?>

<h2>EASEL設定</h2>
<p>WordPressテーマ「EASEL」で行える設定です。
  詳しくは<a href="https://easel.gt-gt.org/" target="_blank">EASEL設定マニュアル</a>をご確認ください。</p>

<form method="post" action="options.php">

  <?php settings_fields( 'easel_settings' ); ?>
  <?php do_settings_sections( 'easel_settings' ); ?>

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
        </td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="easel_footer_text">サイト最下部コピーライト</label>
        </th>
        <td><input name="easel_footer_text" id="easel_footer_text" type="text" value="<?php echo get_option('easel_footer_text'); ?>" class="regular-text" placeholder="copyright (c) サイト名等"><br>
        サイト最下部に表示されるコピーライトテキストを変更できます。</td>
    </tr>
    <tr valign="top">
        <th scope="row"><label for="easel_pass_blur">パス付きイラストのサムネイルをぼかす</label>
        </th>
        <td>
          <input name="easel_pass_blur" type="checkbox" id="easel_pass_blur" value="1" <?php checked( 1, get_option('easel_pass_blur')); ?> /> 行う</label><br>
          チェックを入れると、パスワード付きのイラスト作品のサムネイルを自動でぼかします。
        </td>
    </tr>
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

</form>
