/**
 * JS for ONLYOFFICE Files List Table.
 *
 * @package Onlyoffice_Plugin
 */

/* global ClipboardJS */

(function ($) {
	'use strict';

	var copyOnlyofficeEditorURLClipboard = new ClipboardJS( '.onlyoffice-editor-link.button-link.has-icon' );

	copyOnlyofficeEditorURLClipboard.on(
		'success',
		function ( event ) {
			var triggerElement = $( event.trigger );
			var	successElement = $( '.success', triggerElement.closest( '.copy-to-clipboard-container' ) );

			$( '.link.column-link .copy-to-clipboard-container .success' ).addClass( 'hidden' );

			event.clearSelection();
			triggerElement.trigger( 'focus' );

			clearTimeout( copyOnlyofficeEditorURLClipboard );
			successElement.removeClass( 'hidden' );

			copyOnlyofficeEditorURLClipboard = setTimeout(
				function () {
					successElement.addClass( 'hidden' );
				},
				3000
			);

			wp.a11y.speak( wp.i18n.__( 'The file URL has been copied to your clipboard' ) );
		}
	);
})( jQuery );
