/**
 * JS for ONLYOFFICE Docs TinyMCE.
 *
 * @package Onlyoffice_Wordpress
 */

( function ( ) {
	tinymce.PluginManager.add(
		"onlyoffice-tinymce",
		function ( editor ) {
			editor.addButton(
				"onlyoffice-tinymce",
				{
					tooltip: "ONLYOFFICE",
					onclick: function () {
						let frameOnlyoffice = wp.media.frames.onlyoffice;

						if (frameOnlyoffice) {
							frameOnlyoffice.open();
							return;
						}

						let onlyofficeAllowedMimes = [];

						for (let ext of ONLYOFFICE.formatsUtils.getViewableExtensions()) {
							var mimeType = getMimeType( ext );

							if ( mimeType ) {
								onlyofficeAllowedMimes.push( mimeType );
							}
						}

						frameOnlyoffice = wp.media.frames.onlyoffice = wp.media(
							{
								title: wp.i18n.__( "Select or Upload Media" ),
								library: {
									type: onlyofficeAllowedMimes
								}
							}
						);

						frameOnlyoffice.on(
							"select",
							function () {
								const selectedAttachment = frameOnlyoffice.state().get( 'selection' ).first();

								let params = {
									id: selectedAttachment.id,
									fileName: selectedAttachment.attributes.filename,
								}

								const wpOnlyofficeBlock = "<!-- wp:onlyoffice-wordpress/onlyoffice " + JSON.stringify( params ) + " -->";

								let wpOnlyofficeBody = "[onlyoffice id=" + selectedAttachment.id + " /]";

								let wpOnlyofficeBlockEnd = "<!-- /wp:onlyoffice-wordpress/onlyoffice -->"

								editor.insertContent( wpOnlyofficeBlock + wpOnlyofficeBody + wpOnlyofficeBlockEnd );
							}
						);

						frameOnlyoffice.open();
					}
				}
			);
		}
	);

	var getMimeType = function ( name ) {
		var allTypes = ONLYOFFICE.mimeTypes;

		if ( allTypes[name] !== undefined ) {
			return allTypes[name];
		}

		for ( var key in allTypes ) {
			if ( key.indexOf( name ) !== -1 ) {
				return allTypes[key];
			}
		}

		return false;
	};
})();
