/*
 * (c) Copyright Ascensio System SIA 2022
 *
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; either version 2
 * of the License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

(function() {
    tinymce.PluginManager.add("onlyoffice-tinymce", function(editor) {
        editor.addButton("onlyoffice-tinymce", {
            tooltip: "ONLYOFFICE",
            onclick:  function() {
                let frameOnlyoffice = wp.media.frames.onlyoffice;

                if (frameOnlyoffice) {
                    frameOnlyoffice.open();
                    return;
                }

                const onlyofficeAllowedExts = oo_media.formats.toString().split(" ").join("").split(".").join("").split(",");
                let onlyofficeAllowedMimes = [];

                for (let ext of onlyofficeAllowedExts) {
                    onlyofficeAllowedMimes.push(acf.getMimeType(ext));
                }

                frameOnlyoffice = wp.media.frames.onlyoffice = wp.media({
                    title: wp.i18n.__("Select or Upload Media"),
                    library: {
                        type: onlyofficeAllowedMimes
                    }
                });

                frameOnlyoffice.on("select", function() {
                    const selectedAttachment = frameOnlyoffice.state().get('selection').first();
                    const editorUrl = oo_media.getEditorUrl + selectedAttachment.id;

                    fetch(editorUrl).then((r) => r.json()).then((data) => {

                        let params = {
                            selectedAttachment: selectedAttachment,
                            getEditorUrl: oo_media.getEditorUrl,
                            url: data.url,
                            formats: oo_media.formats
                        }

                        const wpOnlyofficeBlock = `<!-- wp:onlyoffice-wordpress/onlyoffice-wordpress-block ${JSON.stringify(params)} -->`;

                        let wpOnlyofficeBody = "<div style='height:650px; max-width:inherit; padding:20px;'>" +
                                                    "<iframe width='100%' height='100%' src='" + data.url + "'></iframe>" +
                                                "</div>";

                        let wpOnlyofficeBlockEnd = "<!-- /wp:onlyoffice-wordpress/onlyoffice-wordpress-block -->"

                        editor.insertContent(wpOnlyofficeBlock + wpOnlyofficeBody + wpOnlyofficeBlockEnd);
                    });
                });

                frameOnlyoffice.open();
            }
        });
    });
})();
