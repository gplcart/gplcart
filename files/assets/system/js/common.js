/* global GplCart */
var GplCart = GplCart || {settings: {}, translations: {}};

(function ($) {

    $(function () {
        $('body').addClass('js');
    });

    /**
     * Calls attached methods
     * @param {Object} object
     * @returns {undefined}
     */
    GplCart.attach = function (object) {
        if ('attach' in object) {
            $.each(object.attach, function () {
                this.call();
            });
        }
    };

    /**
     * Translates a string
     * @param {type} text
     * @param {type} options
     * @returns {type}
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
     * @param {type} str
     * @param {type} args
     * @returns {unresolved}
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
     * @param {type} str
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
     * Loads Google Maps
     * @param {type} lat
     * @param {type} lng
     * @returns {undefined}
     */
    GplCart.gmap = function (lat, lng, key) {

        var ifr = '',
                src = 'https://www.google.com/maps/embed/v1/place?key=' + key + '&zoom=14';

        src += '&q=' + lat;

        if (lng) {
            src += ',' + lng;
        }

        ifr += '<iframe frameborder="0"';
        ifr += 'style="border:0" src="' + src + '" allowfullscreen></iframe>';

        $('#map-container').html(ifr);
    };

    /**
     * Processes AJAX requests for a job widget
     * @param {type} settings
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
                    console.log(arguments);
                    return false;
                }

                if ('redirect' in data && data.redirect) {
                    window.location.replace(data.redirect);
                }

                if ('finish' in data && data.finish) {
                    widget.find('.progress-bar').css('width', '100%')
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
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(arguments);
            }
        });
    };

    /**
     * Generates a random string
     * @param {type} portion
     * @returns {String}
     */
    GplCart.randomString = function (portion) {
        portion = portion || -8;
        return Math.random().toString(36).slice(portion);
    };

})(jQuery);