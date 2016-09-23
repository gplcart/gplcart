/* global GplCart, Backend */
var Backend = Backend || {html: {}, ui: {}, attach: {}, settings: {}, include: {}};

(function ($) {

    /**
     * Module settings
     * @var object
     */
    Backend.settings.imageContainer = '.image-container';
    Backend.settings.imageModal = '#select-image-modal';

    /**
     * Returns html for modal
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @param {String} footer
     * @returns {String}
     */
    Backend.html.modal = function (content, id, header, footer) {

        var html = '';

        html += '<div class="modal fade" id="' + id + '">';
        html += '<div class="modal-dialog">';
        html += '<div class="modal-content">';
        html += '<div class="modal-header clearfix">';
        html += '<button type="button" class="btn btn-default pull-right" data-dismiss="modal">';
        html += '<i class="fa fa-times"></i></button>';

        if (typeof header !== 'undefined') {
            html += '<h3 class="modal-title pull-left">' + header + '</h3>';
        }

        html += '</div>';
        html += '<div class="modal-body">' + content + '</div>';

        if (typeof footer !== 'undefined') {
            html += '<div class="modal-footer">' + footer + '</div>';
        }

        html += '</div>';
        html += '</div>';

        return html;
    };

    /**
     * Returns HTML of loading indicator
     * @returns {String}
     */
    Backend.html.loading = function () {

        var html = '';

        html += '<div class="modal loading show">';
        html += '<div class="modal-dialog">';
        html += '<div class="modal-content">';
        html += '<div class="modal-body">';
        html += '<div class="progress">';
        html += '<div class="progress-bar progress-bar-striped active"></div>';
        html += '</div></div></div></div></div>';
        html += '<div class="modal-backdrop loading fade in"></div>';

        return html;
    };

    /**
     * Transforms an object of items into <select> options
     * @param {Object} items
     * @param {String} selected
     * @returns {String}
     */
    Backend.html.options = function (items, selected) {

        var options = '';

        for (var i in items) {
            if (selected === i) {
                options += '<option value="' + i + '" selected>' + items[i] + '</option>';
            } else {
                options += '<option value="' + i + '">' + items[i] + '</option>';
            }
        }

        return options;
    };

    /**
     * Displays a modal popup with a custom content
     * @param {String} content
     * @param {String} id
     * @param {String} header
     * @param {String} footer
     * @returns {undefined}
     */
    Backend.ui.modal = function (content, id, header, footer) {

        id = id.replace(/^[^a-z]+|[^\w:.-]+/gi, '');

        var html = Backend.html.modal(content, id, header, footer);

        $('.modal').remove();
        $('body').append(html);
        $('#' + id).modal('show');
    };

    /**
     * Displays a loading indicator
     * @param {Boolean} mode
     */
    Backend.ui.loading = function (mode) {

        if (mode === false) {
            $('body').find('.loading').remove();
        } else {
            var html = Backend.html.loading();
            $('body').append(html);
        }
    };

    /**
     * Displays an alert popup with a custom message
     * @param {String} message
     * @param {String} type
     * @returns {undefined}
     */
    Backend.ui.alert = function (text, severity) {
        if ($.fn.puigrowl) {

            $('.growl-message').remove();
            $('body').append('<div class="growl-message"></div>');

            var settings = {life: 1000};
            var message = [{severity: severity, summary: '', detail: text}];
            $('.growl-message').puigrowl(settings).puigrowl('show', message);
        }
    };

    /**
     * Creates a chart
     * @param {String} source
     * @param {String} type
     * @returns {undefined}
     */
    Backend.ui.chart = function (source, type) {

        if (typeof Chart === 'undefined') {
            return;
        }

        var key = 'chart_' + source;
        var settings = GplCart.settings;

        if (!settings[key] || !settings[key].datasets) {
            return;
        }

        var el = document.getElementById('chart-' + source);

        if (!el) {
            return;
        }

        var data = {
            labels: settings[key].labels,
            datasets: settings[key].datasets
        };

        var options = {
            type: type,
            data: data,
            options: settings[key].options
        };

        new Chart(el, options);
    };

    /**
     * Handles bulk actions
     * @returns {undefined}
     */
    Backend.attach.bulkAction = function () {

        var selector = $('*[data-action]');
        var inputs = $('input[name^="selected"]');

        selector.click(function () {

            var selected = [];
            inputs.each(function () {
                if ($(this).is(':checked')) {
                    selected.push($(this).val());
                }
            });

            if (selected.length < 1) {
                return false;
            }

            var conf = confirm($(this).data('action-confirm'));

            if (!conf) {
                return false;
            }

            $.ajax({
                method: 'POST',
                url: GplCart.settings.urn,
                data: {
                    selected: selected,
                    token: GplCart.settings.token,
                    action: $(this).data('action'),
                    value: $(this).data('action-value')
                },
                success: function () {
                    location.reload(true);
                },
                beforeSend: function () {
                    Backend.ui.loading(true);
                },
                complete: function () {
                    Backend.ui.loading(false);
                }
            });
            
            return false;
        });
    };

    /**
     * Check / uncheck multiple checkboxes
     * @returns {undefined}
     */
    Backend.attach.selectAll = function () {

        var input = $('.select-all');
        var selector = $('#select-all');

        selector.click(function () {
            input.prop('checked', $(this).is(':checked'));
        });
    };

    /**
     * Clears all filters
     * @param {Object} settings
     * @returns {undefined}
     */
    Backend.attach.clearFilter = function (settings) {
        $('.clear-filter').click(function () {
            window.location.replace(GplCart.settings.urn.split("?")[0]);
        });
    };

    /**
     * Rerforms filter query
     * @returns {undefined}
     */
    Backend.attach.filterQuery = function () {

        var input = $('.filters :input');
        var selector = $('.filters .filter');

        selector.click(function () {

            var query = input.filter(function (i, e) {
                return $(e).val() !== "";
            }).serialize();

            if (!query) {
                return false;
            }

            var url = GplCart.settings.urn.split("?")[0] + '?' + query;
            window.location.replace(url);
            return false;
        });
    };

    /**
     * Session time left counter
     * @returns {undefined}
     */
    Backend.attach.countdown = function () {

        if ($.fn.countdown) {

            var format = '%M:%S';
            var result = $('#session-expires');
            var limit = GplCart.settings.session_limit;

            result.countdown(limit, function (event) {
                $(this).html(event.strftime(format));
            });
        }
    };

    /**
     * Adds WYSIWYG editor to a textarea
     * @returns {undefined}
     */
    Backend.attach.wysiwyg = function () {

        var input = $('textarea.summernote');
        var lang = GplCart.settings.lang_region;

        var settings = {
            height: 150,
            lang: lang,
            toolbar: [
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['style', ['style']],
                ['para', ['ul', 'ol']],
                ['table', ['table']],
                ['insert', ['link', 'picture', 'hr']],
                ['view', ['fullscreen', 'codeview']]
            ]};

        input.summernote(settings);
    };

    /**
     * Delete uploaded images
     * @returns {undefined}
     */
    Backend.attach.deleteImages = function () {

        var item = 'div.thumb';
        var selector = '.image-container .delete-image';
        var container = Backend.settings.imageContainer;

        $(document).on('click', selector, function () {
            $(this).closest(item).remove();
            var input = '<input name="delete_image[]" value="' + $(this).attr('data-file-id') + '" type="hidden">';
            $(container).append(input);
            return false;
        });
    };

    /**
     * Makes images sortable
     * @returns {undefined}
     */
    Backend.attach.imagesSortable = function () {

        var params = {
            items: '> div > .thumb',
            handle: '.handle',
            stop: function () {
                $('input[name$="[weight]"]').each(function (i, v) {
                    $(this).val(i);
                });
            }
        };

        var container = Backend.settings.imageContainer;
        $(container).sortable(params);
    };

    /**
     * AJAX image upload
     * @returns {undefined}
     */
    Backend.attach.fileUpload = function () {

        var fileinput = $('#fileinput');
        var container = $(Backend.settings.imageContainer);

        fileinput.fileupload({
            dataType: 'json',
            url: GplCart.settings.base + 'ajax',
            formData: {
                type: fileinput.attr('data-entity-type'),
                action: 'uploadImageAjax',
                token: GplCart.settings.token
            },
            done: function (e, data) {

                if ('result' in data && data.result.files) {

                    $.each(data.result.files, function (index, file) {
                        if (file.html) {
                            container.append(file.html);
                        }
                    });

                    container.find('input[name$="[weight]"]').each(function (i) {
                        $(this).val(i);
                    });
                }
            }
        });
    };

    /**
     * Makes an input autocomplete
     * @returns {undefined}
     */
    Backend.attach.searchAutocomplete = function () {

        var keyword = $('#search-form [name="q"]');
        var type = $('#search-form [name="search_id"]');

        var position = {
            my: "right top",
            at: "right bottom"
        };

        keyword.autocomplete({
            minLength: 2,
            position: position,
            source: function (request, response) {

                var params = {
                    id: type.val(),
                    term: request.term,
                    action: 'adminSearchAjax',
                    token: GplCart.settings.token
                };

                var url = GplCart.settings.base + 'ajax';

                $.post(url, params, function (data) {
                    response($.map(data, function (value, key) {
                        return {suggestion: value};
                    }));
                });
            },
            select: function () {
                return false;
            }
        }).autocomplete('instance')._renderItem = function (ul, item) {
            return $('<li>').append('<a>' + item.suggestion + '</a>').appendTo(ul);
        };

        // Retain searching on focus
        keyword.focus(function () {
            if ($(this).val()) {
                $(this).autocomplete("search");
            }
        });
    };

    /**
     * Memorize search type
     * @returns {undefined}
     */
    Backend.attach.searchMemorize = function () {

        var cookie_id = 'search-id';
        var cookie_settings = {expires: 365, path: '/'};
        var input = $('#search-form [name="search_id"]');

        var search_id = Cookies.get(cookie_id);

        if (search_id) {
            input.val(search_id);
        }

        input.change(function () {
            Cookies.set(cookie_id, $(this).val(), cookie_settings);
        });
    };

    /**
     * Sets up traffic chart
     * @returns {undefined}
     */
    Backend.attach.chartTraffic = function () {
        Backend.ui.chart('traffic', 'line');
    };
    
    /**
     * Sets up Google Map
     * @returns {undefined}
     */
    Backend.attach.map = function () {
        if (GplCart.settings.map) {
            GplCart.gmap(GplCart.settings.map[0], GplCart.settings.map[1]);
        }
    };

    /**
     * Init the module when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        GplCart.attach(Backend);
    });

})(jQuery);