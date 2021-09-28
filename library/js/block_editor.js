( function( data, lodash, blocks, editor, i18n, element, hooks, components, compose ) {

 // ▼変数を指定
	var el = element.createElement;
 const { assign } = lodash;
 const { __ } = i18n;
 const { AlignmentToolbar, BlockControls, RichText,InspectorControls, InnerBlocks } = editor;
 const { registerBlockType, Editable, Toolbar } = blocks;
 // ▲変数を指定

 // ▼▼ブロックを登録
	registerBlockType( 'gutenberg-custom-blocks/sample-block-01', {
   //▼ ブロック名
		title: 'サンプルブロック01',
   //▼ アイコンとカラー
		icon: {
     foreground: 'red',
     src: 'wordpress',
   },
   //▼ 登録するカテゴリー
		category: 'common',
   //▼ 値
		attributes: {
			content: {
				type: 'string',
			},
		},
   //▼ オプション設定
   supports: {
     //save関数で返される要素に対する設定
     align: true, //(default:false) ブロックのalign設定。配列で個別指定も可能 (left, center, right, wide, full)
     customClassName: true, //(default:true)クラス名の設定。有効にするとオリジナルのクラス名を入力する欄が表示される。
     className: false, //(default:true)ブロック要素を作成した際に付く　.wp-block-[ブロック名]で自動生成されるクラス名の設定。
   },
   // ▼ブロックの編集画面
		edit: function( props ) {
			var attributes = props.attributes;

     var content = attributes.content;
     var alignment = attributes.alignment;

     function onChangeContent( newContent ) {
       props.setAttributes( { content: newContent } );
     }

			return [
       el( RichText, {
         tagName: 'p',
         className: 'sampleBlock01 ' + props,
         placeholder: 'テキストを入力',
         value: content,
         onChange: onChangeContent,
       } ),
			];
		},
   // ▲ブロックの編集画面
   // ▼ブロックを保存
		save: function( props ) {
     return null;
		},
   // ▲ブロックを保存
	} );
 // ▲▲ブロックを登録
} )(
	window.wp.data,
	window.lodash,
	window.wp.blocks,
	window.wp.editor,
	window.wp.i18n,
	window.wp.element,
	window.wp.hooks,
	window.wp.components,
	window.wp.compose,
);
