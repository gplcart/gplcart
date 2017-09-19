/* global window, jQuery, GplCart */
var GplCart = GplCart || {settings: {}, translations: {}, onload: {}, modules: {}};

(function (window, $, GplCart) {

    $(function () {
        $('body').addClass('js');
        $.each(GplCart.onload, function () {
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
    GplCart.text = function (text, options) {
        options = options || {};
        if (options) {
            text = GplCart.formatString(text, options);
        }
        return text;
    };

    /**
     * Format strings using placeholders
     * @param {String} str
     * @param {Object} args
     * @returns {String}
     */
    GplCart.formatString = function (str, args) {

        for (var key in args) {
            switch (key.charAt(0)) {
                case '@':
                    args[key] = GplCart.escape(args[key]);
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
    GplCart.escape = function (str) {

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
    GplCart.job = function (settings) {

        var job = settings || GplCart.settings.job;
        var selector = GplCart.settings.job.selector || '#job-widget-' + GplCart.settings.job.id;
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

                        GplCart.job(settings);
                    }
                }
            }
        });
    };

    /**
     * OnChange handler for a bulk action selector
     * @param {Object} e
     * @returns {undefined}
     */
    GplCart.action = function (e) {

        var conf, form, selected = [], el = $(e.target);

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
                alert(GplCart.text('Please select at least one item'));
            }
        }
    };

    /**
     * Check / uncheck multiple checkboxes
     * @returns {undefined}
     */
    GplCart.onload.selectAll = function () {
        $('#select-all').click(function () {
            $('.select-all').prop('checked', $(this).is(':checked'));
        });
    };

})(window, jQuery, GplCart);