// Advanced Custom Fields: Custom WYSIWYG Styles
// http://www.webdesign-muenchen.de/acf/
// License: GPLv2 or later
// License URI: http://www.gnu.org/licenses/gpl-2.0.html

acf.add_filter('wysiwyg_tinymce_settings', function( mceInit, id ){

    var gOldSetup = mceInit.setup || function(){};

    mceInit.setup = function(editor){
        editor.on('init', function(){
            var c = jQuery(editor.editorContainer),
                name = c.closest('.acf-field-wysiwyg').data('name'),
                id = jQuery('#post_ID').val();

            jQuery(editor.iframeElement).contents().find('body').addClass('acf_'+name).addClass('id_'+id);            

        });

        gOldSetup(editor);
    };

    // return
	return mceInit;

});