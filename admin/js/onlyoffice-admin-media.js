(function ($) {
    'use strict';

    if (!$('.attachments-browser')) return;

    var checkExist = setInterval(function () {
        if (wp.media && wp.media.frames.browse) {
            clearInterval(checkExist);
            bindEditAction();
        }
    }, 100);

    function bindEditAction() {
        wp.media.frames.browse.on('edit:attachment', function () {
            if (wp.media.frames.edit.content.renderOld) return;

            wp.media.frames.edit.content.renderOld = wp.media.frames.edit.content.render;
            wp.media.frames.edit.content.render = function(e) {
                wp.media.frames.edit.content.renderOld(e);
                addButton();
            }
            addButton();
        });
    }

    function addButton() {
        var filename = wp.media.frames.edit.model.attributes.filename;
        var ext = filename.substring(filename.lastIndexOf('.'))

        if (oo_media.openable.includes(ext)) {

            $(wp.media.frames.edit.el).find("div.actions > a.view-attachment").prepend("<div><a class=\"oo-editor\" href=\"#\">Edit in ONLYOFFICE</a></div");
            $(wp.media.frames.edit.el).find("div.actions a.oo-editor").click(function () {
                var url = "/wp-json/onlyoffice/editor/" + wp.media.frames.edit.model.id + "?_wpnonce=" + oo_media.nonce;
                if (oo_media.permalinkStructure === '') {
                    url = "/index.php?rest_route=/onlyoffice/editor/" + wp.media.frames.edit.model.id + "&_wpnonce=" + oo_media.nonce;
                }
                window.open(url, '_blank');
            });
        }
    }

})(jQuery);
