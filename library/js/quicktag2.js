(function ($) {
    'use strict';

    tinymce.create('tinymce.plugins.easel_btn', {
        init: function (ed) {
            ed.addButton('easel_mce_button_2', {
                title: 'My Plugin',
                icon: 'dashicon dashicons-admin-links',
                onclick: function () {
                    var url = "media-upload.php?tab=easel_tab&type=easel_type&TB_iframe=true&width=600&height=550";
                    tb_show("My Caption", url);
                }
            });
        }
    });

    tinymce.PluginManager.add('easel_mce_button_2', tinymce.plugins.easel_btn);
})(jQuery);
