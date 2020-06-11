<?php
if ( !defined( 'ABSPATH' ) ) exit;

?>

<form  action="" id="makelist" name="makelist">
  <h2>作品リスト生成</h2>
      <table class="form-table">
      <tr valign="top">
          <th scope="row"><label for="easel_list_type">リストタイプ</label>
          </th>
          <td>
            <label><input type="radio" name="easel_list_type" value="illust_m" id="illust_m" checked>イラスト（サムネイル大）</label><br>
            <label><input type="radio" name="easel_list_type" value="illust_s" id="illust_s">イラスト（サムネイル小）</label><br>
            <label><input type="radio" name="easel_list_type" value="text_2l" id="text_2l">文章（２行）</label><br>
            <label><input type="radio" name="easel_list_type" value="text_1l" id="text_1l">文章（１行）</label><br>
            <label><input type="radio" name="easel_list_type" value="update" id="update">更新履歴</label>
          </td>
      </tr>
      <tr valign="top">
          <th scope="row"><label for="easel_list_worktype">表示する作品タイプ</label>
          </th>
          <td>
              <?php
                $terms = get_terms('custom_cat');
                foreach ( $terms as $term ) {
                    echo '<label for="' . $term->slug . '"><input name="easel_list_worktype" type="checkbox" id="' . $term->slug . '" value="' . $term->slug . '"> ' . $term->name . '</label><br>';
                } ?>
          </td>
      </tr>
      <tr valign="top">
          <th scope="row"><label for="easel_list_count">表示件数</label>
          </th>
          <td>
              <input type="number" name="easel_list_count" value="-1">
              <p>全て表示する場合は「-1」にしてください。</p>
          </td>
      </tr>
      <tr valign="top">
          <th scope="row"><label for="easel_list_order">表示順</label>
          </th>
          <td>
            <label><input type="radio" name="easel_list_order" value="dayDESC" checked>日付順（新→古）</label><br>
            <label><input type="radio" name="easel_list_order" value="dayASC">日付順（古→新）</label><br>
            <label><input type="radio" name="easel_list_order" value="nameDESC">名前順（あいう漢字ABC）</label><br>
            <label><input type="radio" name="easel_list_order" value="nameASC">名前順（CBA漢字ういあ）</label><br>
            <label><input type="radio" name="easel_list_order" value="rand">ランダム表示</label>
          </td>
      </tr>
      <tr valign="top">
          <th scope="row"><label for="easel_original_class">class追加</label>
          </th>
          <td>
              <input type="text" name="easel_original_class" id="easel_original_class">
              <p>自由にカスタマイズしたい人用<br>
              ここに入力した文字列がulのclassとして設定されます</p>
          </td>
      </tr>
      </table>

  <input type="button" value="OK" id="paka3_ei_btn_yes" class="button button-primary" />
  <input type="button" value="キャンセル" id="paka3_ei_btn_no"  class="button" />
</form>

<script type="text/javascript">
jQuery(function($) {

	$(document).ready(function() {
		$('#paka3_ei_btn_yes').on('click', function() {
      var makelist = document.getElementById( "makelist" ) ;

      // リストタイプを取得
      var radioNodeList = makelist.easel_list_type ;
      var listTypeValue = radioNodeList.value ;
      var text1;
      switch ( listTypeValue ){
        case 'illust_m':
          text1 = 'new_illust ';
          break;
        case 'illust_s':
          text1 = 'new_illust type=small ';
          break;
        case 'text_2l':
          text1 = 'new_text ';
          break;
        case 'text_1l':
          text1 = 'new_text type=line1 ';
          break;
        case 'update':
          text1 = 'new_list ';
          break;
      }

      // 出力する作品タイプを取得
      const checkedlist = [];
      var text2;
      var countcheck = 0;
      const worktypes = document.getElementsByName("easel_list_worktype");
      for (let i = 0; i < worktypes.length; i++){
        if(worktypes[i].checked){ //(color2[i].checked === true)と同じ
          checkedlist.push(worktypes[i].value);
          countcheck++;
        }
      }
      if (countcheck > 0) {
        text2 = 'work_type=' + checkedlist ;
      } else {
        text2 = '';
      }

      // 表示件数の取得
      var makecount = makelist.easel_list_count ;
      var counts = makecount.value;

      // 表示順を取得
      var radioListOrder = makelist.easel_list_order ;
      var ListOrder = radioListOrder.value ;
      var text3;
      switch ( ListOrder ){
        case 'dayDESC':
          text3 = ' orderby=post_date order=DESC ';
          break;
        case 'dayASC':
          text3 = ' orderby=post_date order=ASC ';
          break;
        case 'nameDESC':
          text3 = ' orderby=title order=DESC ';
          break;
        case 'nameASC':
          text3 = ' orderby=title order=ASC ';
          break;
        case 'rand':
          text3 = ' orderby=rand ';
          break;
      }

      // クラスの取得
      var orgClass = $('#easel_original_class').val() ;
      if (orgClass == '') {
        text4 = '';
      } else {
        text4 = ' class=' + orgClass;
      }

			//inlineのときはwindow
			top.send_to_editor( '[' + text1 + text2 + text3 + 'count=' + counts + text4 + ']' );
			top.tb_remove();
		});
		$('#paka3_ei_btn_no').on('click', function() {
			top.tb_remove();
		});

		//Enterキーが入力されたとき
		$('#paka3_editer_insert_content').on('keypress',function () {
			if(event.which == 13) {
				$('#paka3_ei_btn_yes').trigger("click");
			}
			//Form内のエンター：サブミット回避
			return event.which !== 13;
		});
	});
})
</script>
