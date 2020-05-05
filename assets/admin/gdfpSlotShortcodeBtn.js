/**
 * Create a button to insert the gdfp_slot shortcode, on tinyMCE editor
 */
(function() {
    tinymce.create('tinymce.plugins.gdfpButtons', {
        init : function(ed, url) {
            ed.addCommand('gdfpSlotPopup', function() {
                ed.windowManager.open({
                    file : url + '/shortcode/gdfp_slot_popup.html',
                    title: 'Google DFP Publicidade',
                    width : 420,
                    height : 600,
                    inline : 1
                }, {
                    plugin_url : url
                });
            });

            ed.addButton('gdfp_slot', {
                title : 'Google DFP Banner', 
                cmd : 'gdfpSlotPopup', 
                image: url + '/shortcode/double_click_icon.png' 
            });
        },

        createControl : function(n, cm) {
            return null;
        },
    });

    tinymce.PluginManager.add('gdfp_slot', tinymce.plugins.gdfpButtons);
})();