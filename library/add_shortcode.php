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
            <label><input type="radio" name="easel_list_type" value="text_noexcerpt" id="text_noexcerpt">文章（抜粋なし）</label><br>
            <label><input type="radio" name="easel_list_type" value="text_update" id="text_update">更新履歴</label>
          </td>
      </tr>

      <tr valign="top">
          <th scope="row"><label>表示する投稿タイプ</label>
          </th>
          <td>
          <label><input type="radio" name="easel_post_type" value="work" id="posttype_work" onclick="switchPostType()" checked>作品</label><br>
          <label><input type="radio" name="easel_post_type" value="post" id="posttype_post" onclick="switchPostType()">投稿</label><br>
          </td>
      </tr>

      <tr valign="top" id="easel_list_worktype">
          <th scope="row"><label for="easel_list_worktype">表示する作品タイプ</label>
          </th>
          <td>
              <?php
              $args = array(
                'orderby'       => 'ID',
                'order'         => 'ASC',
                'hierarchical'  => true,
              );
                $terms = get_terms('custom_cat', $args);
                foreach ( $terms as $term ) {
                    echo '<label for="' . $term->slug . '"><input name="easel_list_worktype" type="checkbox" id="' . $term->slug . '" value="' . $term->slug . '"> ' . $term->name . '</label><br>';
                }
                 ?>
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
      <tr valign="top" id="easel_budge">
          <th scope="row"><label>バッジ表示</label>
          </th>
          <td>
            <p>リストに作品タイプ・作品タグを表示することができます。<br>
            ※現在、リストタイプ「文章」の場合のみ有効です</p>
              <label for="easel_show_worktype"><input name="easel_show_worktype" type="checkbox" id="easel_show_worktype"> 作品タイプを表示する</label><br>
              <label for="easel_show_worktag"><input name="easel_show_worktag" type="checkbox" id="easel_show_worktag"> 作品タグを表示する</label>
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

  <input type="button" value="挿入" id="easel_insert_button" class="button button-primary" style="margin-bottom: 20px;" />
  <input type="button" value="キャンセル" id="easel_cancel_button"  class="button" />
</form>

<script type="text/javascript">

var postType = document.getElementsByName('easel_post_type');
var listWorkType = document.getElementById('easel_list_worktype');
var budge = document.getElementById('easel_budge');
// 投稿タイプ選択
function switchPostType() {

  if (postType[0].checked) {
    // 作品タイプを選択した場合
    listWorkType.style.display = "";
    budge.style.display = "";

  } else if (postType[1].checked) {
    // 投稿を選択した場合
    var inputItem = document.getElementById("easel_list_worktype").getElementsByTagName("input");
    for(var i=0; i<inputItem.length; i++){
      inputItem[i].checked = "";
    }
    var inputItem2 = document.getElementById("easel_budge").getElementsByTagName("input");
    for(var i=0; i<inputItem2.length; i++){
      inputItem2[i].checked = "";
    }
    listWorkType.style.display = "none";
    budge.style.display = "none";
  } else {
    return;
  }
}
jQuery(function($) {

  $(document).ready(function() {

		$('#easel_insert_button').on('click', function() {
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
        case 'text_noexcerpt':
          text1 = 'new_text type=noexcerpt ';
          break;
        case 'text_update':
          text1 = 'new_list ';
          break;
      }

      // 出力する投稿タイプを取得
      var easelListPostType = makelist.easel_post_type.value ;
      var postTypeValue;
      switch ( easelListPostType ){
        case 'post':
          postTypeValue = 'post_type=post ';
          break;
        case 'work':
          postTypeValue = 'post_type=works ';
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

      // 作品タイプ・タグを表示するかどうか
      var showtype = makelist.easel_show_worktype.checked;
      var showtag = makelist.easel_show_worktag.checked;
      var text5;
      var text6;
      if (showtype == true) {
        text5 = ' showtype=true';
      } else {
        text5 = '';
      }
      if (showtag == true) {
        text6 = ' showtag=true';
      } else {
        text6 = '';
      }

      // クラスの取得
      var orgClass = $('#easel_original_class').val() ;
      if (orgClass == '') {
        text4 = '';
      } else {
        text4 = ' class=' + orgClass;
      }

			//inlineのときはwindow
			top.send_to_editor( '[' + text1 + postTypeValue + text2 + text3 + 'count=' + counts + text4 + text5 + text6 + ']' );
			top.tb_remove();
		});
		$('#easel_cancel_button').on('click', function() {
			top.tb_remove();
		});

		//Enterキーが入力されたとき
		$('#paka3_editer_insert_content').on('keypress',function () {
			if(event.which == 13) {
				$('#easel_insert_button').trigger("click");
			}
			//Form内のエンター：サブミット回避
			return event.which !== 13;
		});
	});
})
</script>
