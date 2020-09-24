(function() {
  tinymce.create( 'tinymce.plugins.example_shortcode_button', {
    init: function( ed, url ) {
      ed.addButton( 'easel_main_content', {
        title: '本文範囲指定',
        icon: 'code',
        cmd: 'example_cmd'
      });

      ed.addCommand( 'example_cmd', function() {
        var selected_text = ed.selection.getContent(),
            return_text = '[main_content]' + selected_text + '[/main_content]';
        ed.execCommand( 'mceInsertContent', 0, return_text );
      });
    },
    createControl : function( n, cm ) {
      return null;
    },
  });
  tinymce.PluginManager.add( 'easel_main_content', tinymce.plugins.example_shortcode_button );
})();
