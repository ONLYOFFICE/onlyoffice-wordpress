/*
 * (c) Copyright Ascensio System SIA 2023
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
                    var mimeType = null;
                    switch (ext) {
                        case '.docxf': {
                            mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.docxf';
                            break;
                        }
                        case '.oform': {
                            mimeType = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.oform';
                            break;
                        }
                        default: {
                            mimeType = getMimeType(ext);
                            break;
                        }
                    }

                    if (mimeType){
                        onlyofficeAllowedMimes.push(mimeType);
                    }
                }

                frameOnlyoffice = wp.media.frames.onlyoffice = wp.media({
                    title: wp.i18n.__("Select or Upload Media"),
                    library: {
                        type: onlyofficeAllowedMimes
                    }
                });

                frameOnlyoffice.on("select", function() {
                    const selectedAttachment = frameOnlyoffice.state().get('selection').first();

                    let params = {
                        id: selectedAttachment.id,
                        fileName: selectedAttachment.attributes.filename,
                    }

                    const wpOnlyofficeBlock = `<!-- wp:onlyoffice-wordpress/onlyoffice ${JSON.stringify(params)} -->`;

                    let wpOnlyofficeBody = "[onlyoffice id=" + selectedAttachment.id + " /]";

                    let wpOnlyofficeBlockEnd = "<!-- /wp:onlyoffice-wordpress/onlyoffice -->"

                    editor.insertContent(wpOnlyofficeBlock + wpOnlyofficeBody + wpOnlyofficeBlockEnd);
                });

                frameOnlyoffice.open();
            }
        });
    });

    var getMimeType = function( name ) {
        var allTypes = oo_media.mimeTypes;

        if (allTypes[name] !== undefined) {
            return allTypes[name];
        }

        for(var key in allTypes) {
            if(key.indexOf(name) !== -1) {
                return allTypes[key];
            }
        }

        return false;
    };
})();
