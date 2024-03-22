/**
 * JS for ONLYOFFICE Settings.
 *
 * @package Onlyoffice_Plugin
 */

(function ( $ ) {
	'use strict';

	$( '#inherit_network_settings' ).change(
		function () {
			$( '#docserver_url, #docserver_jwt, #jwt_header' ).prop( 'disabled', $( this ).prop( 'checked' ) );
		}
	);
})( jQuery );