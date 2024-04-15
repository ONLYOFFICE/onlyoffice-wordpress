/**
 * JS for ONLYOFFICE Formats Utils.
 *
 * @package Onlyoffice_Wordpress
 */

(function () {
	if ( ! window.ONLYOFFICE ) {
		window.ONLYOFFICE = {};
	}

	window.ONLYOFFICE.formatsUtils = {
		getDocumentType: function ( fileName ) {
			const extension = this.getExtension( fileName );

			const sizeFormats = ONLYOFFICE.formats.length;
			for ( let i = 0; i < sizeFormats; i++ ) {
				if ( ONLYOFFICE.formats[i].name === extension ) {
					return ONLYOFFICE.formats[i].type;
				}
			}

			return null;
		},

		getExtension: function ( fileName ) {
			var parts = fileName.toLowerCase().split( "." );

			return parts.pop();
		},

		getFileName: async function ( id ) {
			const mediaMeta = await wp.apiFetch( { path: "/wp/v2/media/" + id } );
			if ( mediaMeta != null && mediaMeta.hasOwnProperty( 'source_url' ) ) {
					const filePath = mediaMeta['source_url'];
					const baseUrl  = filePath.split( '?' )[0];
					const baseName = baseUrl.split( '\\' ).pop().split( '/' ).pop();

					return baseName;
			}

			return null;
		},

		getViewableExtensions: function ( ) {
			return ONLYOFFICE.formats
				.filter( ( format ) => format.actions.includes( 'view' ) )
				.map( format => format.name );
		}
	}
})();
