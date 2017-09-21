/* global window, jQuery, Gplcart */
var Gplcart = Gplcart || {settings: {}, translations: {}, onload: {}, modules: {}};

(function (window, $, Gplcart) {

    "use strict";

    $(function () {
        $('body').addClass('js');
        $.each(Gplcart.onload, function () {
            if ($.isFunction(this)) {
                this.call();
            }
        });
    });

    /**
     * Translates a string
     * @param {String} text
     * @param {Object} options
     * @returns {String}
     */
    Gplcart.text = function (text, options) {

        options = options || {};

        if (Gplcart.translations[text]) {
            text = Gplcart.translations[text];
        }

        if (options) {
            text = Gplcart.formatString(text, options);
        }

        return text;
    };

    /**
     * Format strings using placeholders
     * @param {String} str
     * @param {Object} args
     * @returns {String}
     */
    Gplcart.formatString = function (str, args) {

        for (var key in args) {
            switch (key.charAt(0)) {
                case '@':
                    args[key] = Gplcart.escape(args[key]);
                    break;
                case '!':
                    break;
                case '%':
                default:
                    args[key] = '<i class="placeholder">' + args[key] + '</i>';
                    break;
            }

            str = str.replace(key, args[key]);
        }

        return str;
    };

    /**
     * Escapes a string
     * @param {String} str
     * @returns {String}
     */
    Gplcart.escape = function (str) {

        var character, regex,
                replace = {'&': '&amp;', '"': '&quot;', '<': '&lt;', '>': '&gt;'};

        str = String(str);

        for (character in replace) {
            if (replace.hasOwnProperty(character)) {
                regex = new RegExp(character, 'g');
                str = str.replace(regex, replace[character]);
            }
        }

        return str;
    };

    /**
     * Processes AJAX requests for a job widget
     * @param {Object} settings
     * @returns {undefined}
     */
    Gplcart.job = function (settings) {

        var job = settings || Gplcart.settings.job;
        var selector = Gplcart.settings.job.selector || '#job-widget-' + Gplcart.settings.job.id;
        var widget = $(selector);

        $.ajax({
            url: job.url,
            data: {process_job: job.id},
            dataType: 'json',
            success: function (data) {
                if (typeof data === 'object' && !$.isEmptyObject(data)) {

                    if (data.redirect) {
                        window.location.replace(data.redirect);
                    }

                    if (data.finish) {
                        widget.find('.progress-bar').css('width', '100%');
                        widget.hide();
                    } else {

                        if ('progress' in data) {
                            widget.find('.progress-bar').css('width', data.progress + '%');
                        }

                        if (data.message) {
                            widget.find('.message').html(data.message);
                        }

                        Gplcart.job(settings);
                    }
                }
            }
        });
    };

    /**
     * OnChange handler for a bulk action selector
     * @param {Object} el
     * @returns {undefined}
     */
    Gplcart.action = function (el) {

        el = $(el);
        var conf, form, selected = [];

        if (el.val()) {

            $('[name="action[items][]"]').each(function () {
                if ($(this).is(':checked')) {
                    selected.push($(this).val());
                }
            });

            if (selected.length) {
                conf = el.find(':selected').data('confirm');
                if (conf === undefined || confirm(conf)) {
                    form = el.closest('form');
                    form.find('[name="action[confirm]"]').prop('checked', true);
                    form.find('[name="action[submit]"]').click();
                } else {
                    el.val('');
                }

            } else {
                el.val('');
            }
        }
    };

    /**
     * Check / uncheck multiple checkboxes
     * @param {Object} el
     * @param {String} els
     * @returns {undefined}
     */
    Gplcart.selectAll = function (el, els) {

        el = $(el);

        if (els === undefined) {
            els = el.closest('table').find('tbody tr').find('td:eq(0) input:checkbox');
        } else {
            els = $('input[type="checkbox"][name="' + els + '"]');
        }

        els.prop('checked', el.is(':checked'));
    };

})(window, jQuery, Gplcart);