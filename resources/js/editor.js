/* global wp */

(function($) {
        var Editor = function( $el ) {

                var content = null;

                var $editor = null;


                var open = function() {
                        content = $el.text();
                        if ($editor === null) {
                                $editor = $('<form style="display:none;" action="#">' +
                                            '<span><label for="value">Value: <input type="text" name="value" /></label></span>' +
                                            '</form>');
                                var submit = function(e) {
                                        e.preventDefault();
                                        e.stopPropagation();
                                        content = $editor.find('input[name="value"]').val();
                                        close();
                                };
                                $editor.on('submit', submit);
                                $editor.find('input[name="value"]').on('blur', submit);
                        }
                        $editor.find('input[name="value"]').val(content);
                        $el.after($editor);
                        $editor.dialog({ title: 'Input value' });
                        $editor.find('input[name="value"]').select();
                };

                var close = function() {
                        if ($el.text() != content) {
                                $el.text(content);
                                $el.trigger('editor:updated-content', [content]);
                        }
                        $editor.dialog('close');
                        $editor.detach();
                }

                $el.click(open);

        };

        if (typeof(wp.datafeed) !== 'object') {
                wp.datafeed = {};
        }

        wp.datafeed.InlineEditor = Editor;
        $(document).trigger('datafeed:editor-loaded');
})(jQuery);