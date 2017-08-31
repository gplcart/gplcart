/* global GplCart */
var GplCart = GplCart || {settings: {}, translations: {}, onload: {}, modules: {}};

(function ($, GplCart) {

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

                if (typeof data !== 'object' || $.isEmptyObject(data)) {
                    console.warn(arguments);
                    return false;
                }

                if ('redirect' in data && data.redirect) {
                    window.location.replace(data.redirect);
                }

                if ('finish' in data && data.finish) {
                    widget.find('.progress-bar').css('width', '100%');
                    widget.hide();
                    return false;
                }

                if ('progress' in data) {
                    widget.find('.progress-bar').css('width', data.progress + '%');
                }

                if ('message' in data) {
                    widget.find('.message').html(data.message);
                }

                GplCart.job(settings);

            },
            error: function () {
                console.warn(arguments);
            }
        });
    };

})(jQuery, GplCart);