/* global jQuery, _, Backbone */
(function ($, document) {

        if ( ! window.wp ) {
                window.wp = {};
        }

        if ( ! window.wp.datafeed ) {
                window.wp.datafeed = {}
        }

        $(document).on('datafeed:model-loaded', function() {
                $('#datafeed-admin-options-add-link').click(function () {
                        $('#datafeed-add-feed-dialog').dialog({
                                title: "Add data feed",
                                modal: true
                        });
                        var form = $('#datafeed-add-feed-dialog-form');
                        form.find('input[type="text"]').val('');
                        $('#datafeed-add-feed-error-msg').html('');
                        form.on('submit', function(e) {
                                e.preventDefault();
                                e.stopPropagation();
                                var data = {
                                        name: form.find('input[name="name"]').val(),
                                        url: form.find('input[name="url"]').val(),
                                        interval: parseInt(form.find('input[name="interval"]').val())
                                };
                                var feed = new wp.datafeed.DataFeed(data);
                                invalid = false;
                                feed.on('invalid', function (e, error) {
                                        alert('invalid: ' + error);
                                        invalid = error;
                                });
                                feed.save();
                                if (invalid) {
                                        $('#datafeed-add-feed-error-msg').html( invalid );
                                } else {
                                        $('#datafeed-admin-options-add-link').trigger('datafeed:added', [ feed ]);
                                        $('#datafeed-add-feed-dialog').dialog('close');
                                }
                        });
                });
        });
})(jQuery, document);

