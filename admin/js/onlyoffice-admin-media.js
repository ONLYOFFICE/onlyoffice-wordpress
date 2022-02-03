(function ($) {
    'use strict';

    if (!$('.attachments-browser')) return;

    var checkExist = setInterval(function () { // ToDo: change to something better
        if (wp.media.frames.browse) {
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
        // ToDo: check model to see if we can edit that

        $(wp.media.frames.edit.el).find("div.actions > a.view-attachment").prepend("<div><a class=\"oo-edit\" href=\"#\">Edit in ONLYOFFICE</a></div");
        $(wp.media.frames.edit.el).find("div.actions a.oo-edit").click(function () {
            var url = "/wp-json/onlyoffice/editor/" + wp.media.frames.edit.model.id + "?_wpnonce=" + oo_media.nonce;
            window.open(url,'_blank');
        });
    }

})(jQuery);