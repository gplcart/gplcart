/* global window, jQuery, Gplcart */
var Gplcart = typeof Gplcart !== 'undefined' ? Gplcart : {
    onload: {},
    modules: {},
    settings: {},
    translations: {}
};

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
     * Attach a hook
     * @param {String} name
     * @param {Array} args
     * @returns {undefined}
     */
    Gplcart.attachHook = function (name, args) {

        var res, ret, args, hook, fargs = arguments;

        hook = 'hook' + name.replace(/(^|\.)([a-z])/g, function (s) {
            return s.toUpperCase();
        }).replace(/\./g, '');

        $.each(Gplcart.modules, function (i, mfuncs) {
            $.each(mfuncs, function (fname, func) {
                if (fname === hook && $.isFunction(func)) {

                    args = [];
                    for (var i = 1; i < fargs.length; i++) {
                        args.push(fargs[i]);
                    }

                    res = func.apply(null, args);
                    if (res !== undefined) {
                        ret = res;
                        return false;
                    }
                }
            });
        });

        return ret;
    };

    /**
     * Translates a string
     * @param {String} text
     * @param {Object} options
     * @returns {String}
     */
    Gplcart.text = function (text, options) {

        options = typeof options === 'undefined' ? {} : options;

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

        var job = typeof settings === 'undefined' ? Gplcart.settings.job : settings;
        var selector = typeof Gplcart.settings.job.selector === 'undefined' ? '#job-widget-' + Gplcart.settings.job.id : Gplcart.settings.job.selector;
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

        if (typeof els === 'undefined') {
            els = el.closest('table').find('tbody tr').find('td:eq(0) input:checkbox');
        } else {
            els = $('input[type="checkbox"][name="' + els + '"]');
        }

        els.prop('checked', el.is(':checked'));
    };

})(window, jQuery, Gplcart);