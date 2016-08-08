/* global GplCart */
var GplCart = {
    settings: {}, // Settings
    translations: {}, // Translations
    theme: {}, // Theme functions
    text: function (text, options) {
        return text;
    },
    logout: function (interval) {
        setInterval(function () {
            window.location.replace(GplCart.settings.base + 'logout'); // Automatically log out
        }, interval);
    },
    gmap: function (lat, lng) {
        $.getScript('https://www.google.com/jsapi', function () {
            google.load('maps', '3', {callback: function () {
                    if (lng === false) {
                        geocoder = new google.maps.Geocoder();
                        geocoder.geocode({'address': lat}, function (results, status) {
                            if (status === google.maps.GeocoderStatus.OK) {

                                var lat = results[0].geometry.location.lat();
                                var lng = results[0].geometry.location.lng();
                                var options = {zoom: 10, center: {lat: lat, lng: lng}};

                                var map = new google.maps.Map(document.getElementById('map-container'), options);
                                new google.maps.Marker({position: {lat: lat, lng: lng}, map: map});

                            } else {
                                console.log("Geocode was not successful for the following reason: " + status);
                            }
                        });
                    } else {
                        var options = {zoom: 10, center: {lat: lat, lng: lng}};
                        var map = new google.maps.Map(document.getElementById('map-container'), options);
                        new google.maps.Marker({position: {lat: lat, lng: lng}, map: map});
                    }
                }});
        });
    },
    job: function () {

        var widget = $('div#job-widget-' + GplCart.settings.job.id);

        $.ajax({
            url: GplCart.settings.job.url,
            data: {process_job: GplCart.settings.job.id},
            dataType: 'json',
            success: function (data) {

                if (typeof data !== 'object' || jQuery.isEmptyObject(data)) {
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

                GplCart.job(widget);

            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(arguments);
            }
        });
    },
};