/**
 * JS for ONLYOFFICE Settings.
 *
 * @package Onlyoffice_Plugin
 */

(function ( $ ) {
	'use strict';

	var isConfirmed = false;

	$( '#onlyoffice-settings-form' ).on(
		'submit',
		function () {
			if ( ! isConfirmed ) {
				var confirmMessage;

				if ( $( '#onlyoffice_settings_allowed_owerwrite_network_settings' ).length ) {
					if ( $( '#onlyoffice_settings_allowed_owerwrite_network_settings' ).prop( 'checked' ) ) {
						confirmMessage = wp.i18n.__( 'Do you want to allow sites administrators to configure the plugin themselves?', 'onlyoffice-plugin' );
					} else {
						confirmMessage = wp.i18n.__( 'Do you want to apply these settings to all sites?', 'onlyoffice-plugin' );
					}
				}

				if ( $( '#onlyoffice_settings_inherit_network_settings' ).length && $( '#onlyoffice_settings_inherit_network_settings' ).prop( 'checked' ) ) {
					confirmMessage = wp.i18n.__( 'Do you want to apply the network settings?', 'onlyoffice-plugin' );
				}

				if ( confirmMessage ) {
					$( '#onlyoffice-setting-confirm-dialog' ).find( '.onlyoffice-setting-confirm-message' ).text( confirmMessage );
					$( '#onlyoffice-setting-confirm-dialog' ).removeClass( 'hidden' );
					return false;
				} else {
					return true;
				}
			}
		}
	);

	$( '#onlyoffice-setting-confirm-dialog' ).find( '.onlyoffice-setting-confirm-dialog-ok' ).click(
		function () {
			isConfirmed = true;
			$( '#onlyoffice-settings-form' ).submit();
		}
	)

	$( '#onlyoffice-setting-confirm-dialog' ).find( '.onlyoffice-setting-confirm-dialog-cancel' ).click(
		function () {
			$( '#onlyoffice-setting-confirm-dialog' ).addClass( 'hidden' );
		}
	)

	$( '#onlyoffice_settings_inherit_network_settings' ).change(
		function () {
			$( '#onlyoffice_settings_docserver_url, #onlyoffice_settings_docserver_jwt, #onlyoffice_settings_jwt_header' ).prop( 'disabled', $( this ).prop( 'checked' ) );
		}
	);
})( jQuery );