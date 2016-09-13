/**
 * Displays a modal popup with a custom content
 * @param {type} content
 * @param {type} id
 * @param {type} header
 * @param {type} footer
 * @returns {undefined}
 */
GplCart.theme.modal = function (content, id, header, footer) {

    var html = '\
        <div class="modal fade" id="' + id + '">\n\
        <div class="modal-dialog">\n\
        <div class="modal-content">\n\
        <div class="modal-header clearfix">\n\
        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">\n\
        <i class="fa fa-times"></i></button>';

    if (typeof header !== 'undefined') {
        html += '<h3 class="modal-title pull-left">' + header + '</h3>';
    }

    html += '</div><div class="modal-body">' + content + '</div>';

    if (typeof footer !== 'undefined') {
        html += '<div class="modal-footer">' + footer + '</div>';
    }

    html += '</div></div>';

    $('.modal').remove();
    $('body').append(html);
    $('#' + id).modal('show');
};

/**
 * Displays a loading indicator
 * @param {type} mode
 * @returns {undefined}
 */
GplCart.theme.loading = function (mode) {

    if (mode === false) {
        $('body').find('.loading').remove();
        return;
    }

    var html = '<div class="modal loading show">\n\
        <div class="modal-dialog">\n\
        <div class="modal-content">\n\
        <div class="modal-body">\n\
        <div class="progress">\n\
        <div class="progress-bar progress-bar-striped active"></div>\n\
        </div></div></div></div></div>\n\
        <div class="modal-backdrop loading fade in"></div>';

    $('body').append(html);
};

/**
 * Displays an alert popup with a custom message
 * @param {type} message
 * @param {type} type
 * @returns {undefined}
 */
GplCart.theme.alert = function (message, type) {
    $.bootstrapGrowl(message, {
        type: type,
        align: 'right',
        width: 'auto',
        delay: 2000,
        offset: {from: 'bottom', amount: 20}
    });
};

/**
 * Creates a chart
 * @param {type} source
 * @param {type} type
 * @returns {undefined}
 */
GplCart.theme.chart = function (source, type) {
    var key = 'chart_' + source;
    if (key  in GplCart.settings && 'datasets' in GplCart.settings[key]) {
        var settings = GplCart.settings[key];
        var ctx = document.getElementById('chart-' + source);
        var data = {labels: settings.labels, datasets: settings.datasets};
        var chart = new Chart(ctx, {type: type, data: data, options: settings.options});
    }
};

$(function () {

    // Bulk actions
    $('*[data-action]').click(function () {

        var selected = [];

        $('input[name^="selected"]').each(function () {
            if ($(this).is(':checked')) {
                selected.push($(this).val());
            }
        });

        if (!selected.length) {
            return false;
        }

        var conf = confirm($(this).data('action-confirm'));

        if (conf) {
            $.ajax({
                method: 'POST',
                url: GplCart.settings.urn,
                data: {
                    selected: selected,
                    token: GplCart.settings.token,
                    action: $(this).data('action'),
                    value: $(this).data('action-value')
                },
                success: function (data) {
                    location.reload(true);
                },
                beforeSend: function () {
                    GplCart.theme.loading(true);
                },
                complete: function () {
                    GplCart.theme.loading(false);
                }
            });
        }

        return false;
    });

    // Check / uncheck multiple checkboxes
    $('#select-all').click(function () {
        $('.select-all').prop('checked', $(this).is(':checked'));

    });

    // Clears all filters
    $('.clear-filter').click(function () {
        window.location.replace(GplCart.settings.urn.split("?")[0]);
    });

    // Rerforms filter query
    $('.filters .filter').click(function () {

        var query = $('.filters :input').filter(function (i, e) {
            return $(e).val() !== "";
        }).serialize();

        if (!query) {
            return false;
        }

        var url = GplCart.settings.urn.split("?")[0];

        url += '?' + query;
        window.location.replace(url);
        return false;

    });

    /********************************* Time counter *********************************/

    // Session time left counter
    $('#session-expires').countdown(GplCart.settings.session_limit, function (event) {
        $(this).html(event.strftime('%M:%S'));
    });

    /********************************* WYSIWYG *********************************/

    $('textarea.summernote').summernote({
        height: 150,
        lang: GplCart.settings.lang_region,
        toolbar: [
            ['font', ['bold', 'italic', 'underline', 'clear']],
            ['style', ['style']],
            ['para', ['ul', 'ol']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'hr']],
            ['view', ['fullscreen', 'codeview']]
        ]});

    /********************************* Images *********************************/

    var imageContainer = $('.image-container');

    // Delete images
    $(document).on('click', '.image-container .delete-image', function () {
        $(this).closest('div.thumb').remove();
        imageContainer.append('<input name="delete_image[]" value="' + $(this).attr('data-file-id') + '" type="hidden">');
        return false;
    });

    // Make images sortable
    imageContainer.sortable({
        items: '> .thumb',
        handle: '.handle',
        stop: function () {
            $('input[name$="[weight]"]').each(function (i, v) {
                $(this).val(i);
            });
        }
    });

    var fileinput = $('#fileinput');

    // AJAX image upload
    fileinput.fileupload({
        dataType: 'json',
        url: GplCart.settings.base + 'ajax',
        formData: {
            type: fileinput.attr('data-entity-type'),
            action: 'uploadImageAjax',
            token: GplCart.settings.token
        },
        done: function (e, data) {

            if ('result' in data && 'files' in data.result) {

                $.each(data.result.files, function (index, file) {
                    if (file.html) {
                        imageContainer.append(file.html);
                    }
                });

                imageContainer.find('input[name$="[weight]"]').each(function (i) {
                    $(this).val(i);
                });
            }
        }
    });

    /********************************* Search *********************************/

    var searchInput = $('#search-form [name="q"]');
    var searchTypeSelect = $('#search-form [name="search_id"]');

    searchInput.autocomplete({
        minLength: 2,
        source: function (request, response) {

            var id = searchTypeSelect.val();
            var params = {term: request.term, action: 'adminSearchAjax', id: id, token: GplCart.settings.token};

            $.post(GplCart.settings.base + 'ajax', params, function (data) {
                response($.map(data, function (value, key) {
                    return {suggestion: value}
                }));
            });
        },
        select: function (event, ui) {
            return false;
        },
        position: {
            my: "right top",
            at: "right bottom"
        },
    }).autocomplete('instance')._renderItem = function (ul, item) {
        return $('<li>').append('<a>' + item.suggestion + '</a>').appendTo(ul);
    };

    // Retain searching on focus
    searchInput.focus(function () {
        if ($(this).val()) {
            $(this).autocomplete("search");
        }
    });

    // Memorize search type
    var search_id = Cookies.get('search-id');

    if (search_id) {
        searchTypeSelect.val(search_id);
    }

    searchTypeSelect.change(function () {
        Cookies.set('search-id', $(this).val(), {expires: 365, path: '/'});
    });

});