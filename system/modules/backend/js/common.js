/* global GplCart, Backend, Chart */
var Backend = Backend || {html: {}, ui: {}, attach: {}, settings: {}, include: {}};

(function (window, document, GplCart, $) {

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

        var i, attr = '', options = '';

        for (i in items) {
            if (items.hasOwnProperty(i)) {

                if (selected === i) {
                    attr = ' selected';
                }

                options += '<option value="' + i + '"' + attr + '>' + items[i] + '</option>';
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

        var html;

        if (mode === false) {
            $('body').find('.loading').remove();
        } else {
            html = Backend.html.loading();
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

        var settings, message;

        if ($.fn.puigrowl) {

            $('.growl-message').remove();
            $('body').append('<div class="growl-message"></div>');

            settings = {life: 1000};
            message = [{severity: severity, summary: '', detail: text}];
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

        var el,
                data,
                options,
                key = 'chart_' + source,
                settings = GplCart.settings;

        if (typeof Chart === 'undefined') {
            return;
        }

        if (!settings[key] || !settings[key].datasets) {
            return;
        }

        el = document.getElementById('chart-' + source);

        if (!el) {
            return;
        }

        data = {
            labels: settings[key].labels,
            datasets: settings[key].datasets
        };

        options = {
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

        var conf,
                selected = [],
                selector = $('*[data-action]'),
                inputs = $('input[name^="selected"]');

        selector.click(function () {

            inputs.each(function () {
                if ($(this).is(':checked')) {
                    selected.push($(this).val());
                }
            });

            if (selected.length < 1) {
                return false;
            }

            conf = confirm($(this).data('action-confirm'));

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

        var input = $('.select-all'),
                selector = $('#select-all');

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

        var url,
                query,
                input = $('.filters :input'),
                selector = $('.filters .filter');

        selector.click(function () {

            query = input.filter(function (i, e) {
                return $(e).val() !== "";
            }).serialize();

            if (!query) {
                return false;
            }

            url = GplCart.settings.urn.split("?")[0] + '?' + query;
            window.location.replace(url);
            return false;
        });
    };

    /**
     * Adds WYSIWYG editor to a textarea
     * @returns {undefined}
     */
    Backend.attach.wysiwyg = function () {

        var input = $('textarea.summernote'),
                lang = GplCart.settings.lang_region,
                settings = {
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

        if ($.fn.summernote) {
            input.summernote(settings);
        }
    };

    /**
     * Delete uploaded images
     * @returns {undefined}
     */
    Backend.attach.deleteImages = function () {

        var input,
                item = 'div.thumb',
                selector = '.image-container .delete-image',
                container = Backend.settings.imageContainer;

        $(document).on('click', selector, function () {
            $(this).closest(item).remove();
            input = '<input name="delete_image[]" value="' + $(this).attr('data-file-id') + '" type="hidden">';
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
        },
        container = Backend.settings.imageContainer;
        $(container).sortable(params);
    };

    /**
     * AJAX image upload
     * @returns {undefined}
     */
    Backend.attach.fileUpload = function () {

        var fileinput = $('#fileinput'),
                container = $(Backend.settings.imageContainer);

        if (!$.fn.fileupload) {
            return null;
        }

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

        var key;

        if (!GplCart.settings.map) {
            return null;
        }

        if (!GplCart.settings.map.key) {
            console.warn('Please specify a browser API key for Google Maps at admin/settings/common');
            return null;
        }

        key = GplCart.settings.map.key;

        if (GplCart.settings.map.address) {
            GplCart.gmap(GplCart.settings.map.address, false, key);
            return null;
        }

        if (GplCart.settings.map[0] && GplCart.settings.map[1]) {
            GplCart.gmap(GplCart.settings.map[0], GplCart.settings.map[1], key);
            return null;
        }

        console.warn('Invalid arguments for Google Maps');
    };

    /**
     * Handles CLI terminal
     * @returns {undefined}
     */
    Backend.attach.terminal = function () {

        if (!$.fn.puiterminal) {
            return;
        }

        var handler,
                terminal,
                id = 'terminal-wrapper',
                link = $('*[data-terminal]'),
                wrapper = '<div id="terminal"></div>';

        link.click(function () {

            Backend.ui.modal(wrapper, id);

            terminal = $('#terminal');

            terminal.puiterminal({
                prompt: '> ',
                handler: function (request, response) {

                    request = $.trim(request);

                    if (request === 'clear') {
                        terminal.puiterminal('clear');
                        return false;
                    }

                    handler = this;

                    $.ajax({
                        method: 'POST',
                        url: GplCart.settings.urn,
                        data: {
                            command: 'gplcart ' + request,
                            cli_token: GplCart.settings.token
                        },
                        success: function (data) {
                            response.call(handler, data);
                        }
                    });
                }
            });

            return false;
        });
    };

    /**
     * Init the module when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        GplCart.attach(Backend);
    });

})(window, document, GplCart, jQuery);