$(function () {

    $('[data-panel-id]').each(function () {
        var panelId = $(this).attr('data-panel-id');
        var state = Cookies.get('panel-' + panelId);
        if (state === "1") {
            $(this).parents('.panel').find('.panel-body').toggleClass('hide');
            $(this).toggleClass('fa-chevron-up fa-chevron-down');
        }
    });

    $('.panel-heading .fa').click(function () {

        var toggle = $(this);
        var panelId = toggle.attr('data-panel-id');
        var body = toggle.parents('.panel').find('.panel-body');

        body.toggleClass('hide');

        if (body.is(':visible')) {
            toggle.toggleClass('fa-chevron-up fa-chevron-down');
            Cookies.set('panel-' + panelId, 1, {expires: 365, path: '/'});

            if (panelId === 'ga-traffic') {
                GplCart.theme.chart('traffic');
            }
        } else {
            toggle.toggleClass('fa-chevron-down fa-chevron-up');
            Cookies.set('panel-' + panelId, 0, {expires: 365, path: '/'});
        }
    });

    GplCart.theme.chart('traffic');

});
