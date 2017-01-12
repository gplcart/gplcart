/* global GplCart, Backend, CodeMirror  */
(function (GplCart, $) {

    Backend.include.editor = {attach: {}};

    /**
     * Setup code mirror plugin
     * @returns {undefined}
     */
    Backend.include.editor.attach.codemirror = function () {

        if (typeof CodeMirror === 'undefined') {
            return;
        }

        var textarea,
                map,
                ext,
                mode,
                settings,
                default_settings,
                element = $('*[data-codemirror="true"]');

        textarea = element.get(0);

        if ($.isEmptyObject(textarea)) {
            return;
        }

        map = {
            css: {name: 'css'},
            twig: {name: 'twig'},
            js: {name: 'javascript'},
            php: {name: 'htmlmixed'}
        };

        if (GplCart.settings.editor && GplCart.settings.editor.file_extension) {
            ext = GplCart.settings.editor.file_extension;
        }

        mode = map[ext] || map.php;
        default_settings = {mode: mode, lineNumbers: true};
        settings = element.data('codemirror-settings') || default_settings;

        CodeMirror.fromTextArea(textarea, settings);
    };

    /**
     * Call attached above methods when DOM is ready
     * @returns {undefined}
     */
    $(function () {
        GplCart.attach(Backend.include.editor);
    });


})(GplCart, jQuery);
