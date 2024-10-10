
(function() {
	// ビジュアルエディタにプルダウンメニューの追加
	// 参考　https://demeniguis.com/2018/09/10/%E3%83%93%E3%82%B8%E3%83%A5%E3%82%A2%E3%83%AB%E3%82%A8%E3%83%87%E3%82%A3%E3%82%BF%E3%81%AB%E7%8B%AC%E8%87%AA%E3%81%AE%E3%82%AF%E3%82%A4%E3%83%83%E3%82%AF%E3%82%BF%E3%82%B0%E3%82%92%E8%BF%BD%E5%8A%A0/
	tinymce.PluginManager.add('easel_mce_button', function( editor, url ) {
		editor.addButton( 'easel_mce_button', {
			text: 'EASELタグ',
			icon: false,
			type: 'menubutton',
			menu: [
							{
								text: 'ショートコード',
								menu: [
									{
									text: '更新履歴一覧',
									onclick: function() {
										var selected_text = editor.selection.getContent();
										var return_text = '';
										return_text = '[new_list count=5]';
										editor.insertContent(return_text);
									}
								},
								{
									text: 'イラスト一覧',
									onclick: function() {
										var selected_text = editor.selection.getContent();
										var return_text = '';
										return_text = '[new_illust count=-1 work_type=illust]';
										editor.insertContent(return_text);
									}
								},
								{
									text: '小説一覧',
									onclick: function() {
										var selected_text = editor.selection.getContent();
										var return_text = '';
										return_text = '[new_text count=-1 work_type=text]';
										editor.insertContent(return_text);
									}
								}
							]
						},
						{
							text: 'カラム横並び',
							menu: [
							{
							text: '２カラム',
							onclick: function() {
								var selected_text = editor.selection.getContent();
								var return_text = '';
								return_text = '<div class="col2-wrap"><div class="col-left">左カラムの内容をここに記入</div><div class="col-right">右カラムの内容をここに記入</div></div>';
								editor.insertContent(return_text);
							}
						},
								{
								text: '３カラム',
								onclick: function() {
									var selected_text = editor.selection.getContent();
									var return_text = '';
									return_text = '<div class="col3-wrap"><div class="col-left">左カラムの内容をここに記入</div><div class="col-center">中央カラムの内容をここに記入</div><div class="col-right">右カラムの内容をここに記入</div></div>';
									editor.insertContent(return_text);
								}
							}
						]
						},
								{
									text: 'リンクボタン',
									menu: [
												{
												text: '青',
												onclick: function() {
													var selected_text = editor.selection.getContent();
													var return_text = '';
													if(selected_text.length > 0){
														return_text = '<span class="blue-btn">' + selected_text + '</span>';
													}else{
														return_text = '<span class="blue-btn"><a href="#">この文字にリンクを貼る</a></span>';
													}
													editor.insertContent(return_text);
												}
											},
												{
												text: '赤',
												onclick: function() {
													var selected_text = editor.selection.getContent();
													var return_text = '';
													if(selected_text.length > 0){
														return_text = '<span class="red-btn">' + selected_text + '</span>';
													}else{
														return_text = '<span class="red-btn"><a href="#">この文字にリンクを貼る</a></span>';
													}
													editor.insertContent(return_text);
												}
											},
												{
												text: '黄',
												onclick: function() {
													var selected_text = editor.selection.getContent();
													var return_text = '';
													if(selected_text.length > 0){
														return_text = '<span class="yellow-btn">' + selected_text + '</span>';
													}else{
														return_text = '<span class="yellow-btn"><a href="#">この文字にリンクを貼る</a></span>';
													}
													editor.insertContent(return_text);
												}
											},
												{
												text: '緑',
												onclick: function() {
													var selected_text = editor.selection.getContent();
													var return_text = '';
													if(selected_text.length > 0){
														return_text = '<span class="green-btn">' + selected_text + '</span>';
													}else{
														return_text = '<span class="green-btn"><a href="#">この文字にリンクを貼る</a></span>';
													}
													editor.insertContent(return_text);
												}
											},
												{
												text: 'ネイビー',
												onclick: function() {
													var selected_text = editor.selection.getContent();
													var return_text = '';
													if(selected_text.length > 0){
														return_text = '<span class="navy-btn">' + selected_text + '</span>';
													}else{
														return_text = '<span class="navy-btn"><a href="#">この文字にリンクを貼る</a></span>';
													}
													editor.insertContent(return_text);
												}
											},
												{
												text: '灰色',
												onclick: function() {
													var selected_text = editor.selection.getContent();
													var return_text = '';
													if(selected_text.length > 0){
														return_text = '<span class="gray-btn">' + selected_text + '</span>';
													}else{
														return_text = '<span class="gray-btn"><a href="#">この文字にリンクを貼る</a></span>';
													}
													editor.insertContent(return_text);
												}
											}
										]
									}
			]
		});
	});
})();
