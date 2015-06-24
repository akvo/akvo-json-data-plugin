/* global jQuery, _, Backbone, wp, ajaxurl */

(function($) {

        $(document).ready(function() {

                var DataFeed = Backbone.Model.extend({
                        validate: function( attrs, options ) {
                                if ( typeof(attrs.name) !== 'string' || attrs.name === '' ) {
                                        return 'Name must be set!';
                                }
                                if ( typeof(attrs.url) !== 'string'  || attrs.url === '' ) {
                                        return 'The URL must be set!';
                                }
                                if ( typeof(attrs.interval) !== 'undefined' && attrs.interval !== null && (typeof(attrs.interval) !== 'number' || isNaN(attrs.interval))) {
                                        return 'The interval must be a number, if set.' + typeof(attrs.interval) + ' ' + JSON.stringify(attrs.interval);
                                }
                                if ( typeof(attrs.o_interval) !== 'undefined' && attrs.o_interval !== null && (typeof(attrs.o_interval) !== 'number' || isNaN(attrs.o_interval))) {
                                        return 'The o_interval must be a number, if set.';
                                }
                                var invalidItems = [];
                                _.each(_.keys(attrs), function (item) {
                                        if (!_.contains([
                                                'name',
                                                'url',
                                                'o_url',
                                                'interval',
                                                'o_interval',
                                                'key',
                                                'key_parameter'
                                        ], item)) {
                                                invalidItems.push(item);
                                        }
                                });
                                if (invalidItems.length > 0) {
                                        return 'Unknown properties: ' + JSON.stringify(invalidItems);
                                }
                        },
                        urlRoot: ajaxurl,
                        url: function() {
                                return ajaxurl + '?action=datafeed_service&item_name=' + encodeURIComponent(this.get('name')) ;
                        },
                        idAttribute: 'name'
                });

                var DataFeedCollection = Backbone.Collection.extend({
                        model: DataFeed,
                        url: ajaxurl
                });

                var DataFeedView = Backbone.View.extend({
                        template: _.template('<div class="datafeed-info-item"><%- name %></div>' +
                                             '<div class="datafeed-info-item"><%- url %></div>' +
                                             '<div class="datafeed-info-item datafeed-info-editable-item"><%- o_url %></div>' +
                                             '<div class="datafeed-info-item"><%- interval %></div>' +
                                             '<div class="datafeed-info-item datafeed-info-editable-item"><%- o_interval %></div>' +
                                             '<div class="datafeed-info-item datafeed-info-editable-item"><%- key %></div>' +
                                             '<div class="datafeed-info-item datafeed-info-editable-item"><%- key_parameter %></div>' +
                                             '<div class="datafeed-info-item datafeed-info-remove-item"><a href="#">Remove</a></div>' +
                                             '<div class="datafeed-info-item datafeed-info-note-item"></div>'),
                        render: function() {
                                var data = _.clone(this.model.attributes);
                                if (typeof(data.o_url) == 'undefined') {
                                        data.o_url = '';
                                }
                                if (typeof(data.interval) == 'undefined') {
                                        data.interval = null;
                                }
                                if (typeof(data.o_interval) == 'undefined') {
                                        data.o_interval = null;
                                }
                                if (typeof(data.key) == 'undefined') {
                                        data.key = null;
                                }
                                if (typeof(data.key_parameter) == 'undefined') {
                                        data.key_parameter = null;
                                }
                                this.$el.html(this.template(data));
                                this.$el.addClass('datafeed-info');

                                var model = this.model;

                                this.$el.find('.datafeed-info-editable-item').each(function (i, el) {
                                        new wp.datafeed.InlineEditor($(el));
                                        $(el).on('editor:updated-content', function(e, text) {
                                                switch (i) {
                                                case 0:
                                                        model.set( {o_url: text === '' ? null : text} );
                                                        break;
                                                case 1:
                                                        var n = text === '' ? null : parseInt(text);
                                                        model.set( {o_interval: n } );
                                                        break;
                                                case 2:
                                                        model.set( {key: text === '' ? null : text});
                                                        break;
                                                case 3:
                                                        model.set( {key_parameter: text === '' ? null : text});
                                                        break;
                                                }

                                                model.save();
                                        });
                                });

                                this.$el.find('.datafeed-info-remove-item > a').click(function (e) {
                                        model.destroy({ wait: true });
                                        e.preventDefault();
                                })
                                return this;
                        },
                        initialize: function() {
                                var view = this;
                                this.model.on('change', function() {
                                        view.render();
                                });
                                this.model.on('destroy', function() {
                                        view.remove();
                                });
                                this.model.on('invalid', function(model, error) {
                                        view.$el.find('.datafeed-info-note-item').html('Invalid: ' + error);
                                });
                                this.model.on('error', function(model, resp) {
                                        view.$el.find('.datafeed-info-note-item').html('Server error: ' + resp.statusText);
                                });
                        }
                });

                if ( ! window.wp ) {
                        window.wp = {};
                }

                if ( ! window.wp.datafeed ) {
                        window.wp.datafeed = {}
                }

                window.wp.datafeed.DataFeed = DataFeed;
                window.wp.datafeed.DataFeedCollection = DataFeedCollection;
                window.wp.datafeed.DataFeedView = DataFeedView;

                $(document).trigger('datafeed:model-loaded');
        });

})(jQuery);
