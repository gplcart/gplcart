GplCart.theme = {
    modal: function (content, id, header, footer) {

        var html = '\
        <div class="modal fade" id="' + id + '">\n\
        <div class="modal-dialog">\n\
        <div class="modal-content">\n\
        <div class="modal-header clearfix">\n\
        <button type="button" class="btn btn-primary pull-right" data-dismiss="modal">\n\
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
    },
    loading: function (mode) {

        if (mode === false) {
            $('body').find('.loading-overlay').remove();
            return;
        }

        var html = '\
        <div class="loading-overlay">\n\
        <div class="loading">\n\
        <div class="progress">\n\
        <div class="progress-bar progress-bar-striped active">\n\
        </div>\n\
        </div>\n\
        </div>\n\
        </div>';

        $('body').append(html);
    },
    alert: function (message, type) {
        $.bootstrapGrowl(message, {
            type: type,
            align: 'right',
            delay: 2000,
            offset: {from: 'bottom', amount: 20}
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

                GplCart.theme.job(widget);

            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.log(arguments);
            }
        });
    },
    chart: function (source) {

        if ('chart' in GplCart.settings && source in GplCart.settings.chart && 'datasets' in GplCart.settings.chart[source]) {
            var traffic = GplCart.settings.chart[source];
            var ctx = document.getElementById('chart-' + source).getContext("2d");
            var data = {labels: traffic.labels, datasets: traffic.datasets};
            var chart = new Chart(ctx).Line(data, traffic.options);
            document.getElementById('chart-' + source + '-legend').innerHTML = chart.generateLegend();
        }
    }
};

$(function () {

    // Hints
    $('.hint').tooltip();
    
    $('.input-group.color').colorpicker();

    // Context help popup
    $('.help.summary a').click(function () {
        var content = $(this).next('.summary').html();
        if (content) {
            GplCart.theme.modal(content, 'help-summary', GplCart.text('Help'));
            return false;
        }
    });

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

    /********************************* Accordion *********************************/

    // Expand/collapse accordion panes
    $(document).on('click', 'form .fa-chevron-up', function () {
        $('form .panel-collapse').collapse('show');
        $(this).toggleClass('fa-chevron-down fa-chevron-up');
        return false;
    });

    $(document).on('click', 'form .fa-chevron-down', function () {
        $('form .panel-collapse:not(.always-visible)').collapse('hide');
        $(this).toggleClass('fa-chevron-up fa-chevron-down');
        return false;
    });

    /********************************* WYSIWYG *********************************/

    $('textarea.summernote').summernote({height: 200, lang: GplCart.settings.lang_region});

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
        formData: {type: fileinput.attr('data-entity-type'), action: 'uploadImage', token: GplCart.settings.token},
        done: function (e, data) {
            $.each(data.result.files, function (index, file) {
                if (file.html) {
                    imageContainer.append(file.html);
                }
            });

            imageContainer.find('input[name$="[weight]"]').each(function (i) {
                $(this).val(i);
            });
        }
    });

    /********************************* Search *********************************/
    
    var searchInput =  $('#search-form [name="q"]');
    var searchTypeSelect = $('#search-form [name="search_id"]');

    searchInput.autocomplete({
        minLength: 2,
        source: function (request, response) {

            var id = searchTypeSelect.val();
            var params = {term: request.term, action: 'adminSearch', id: id, token: GplCart.settings.token};

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

    /********************************* Google map *********************************/

    $(document).on('shown.bs.tab', 'a[href="#contact"]', function (e) {
        if ('map' in GplCart.settings) {
            GplCart.gmap(GplCart.settings.map[0], GplCart.settings.map[1]);
        }
    });
    
    if ('map' in GplCart.settings && 'address' in GplCart.settings.map) {
        GplCart.gmap(GplCart.settings.map.address, false);
    }

});