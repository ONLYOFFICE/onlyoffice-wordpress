/**
 * JS for ONLYOFFICE Frontend Controller.
 *
 * @package Onlyoffice_Docspace_Wordpress
 */

(function ( $ ) {
	document.addEventListener(
		'DOMContentLoaded',
		function () {
			var __                = wp.i18n.__;
			const elementIdPrefix = "editorOnlyoffice-";
			var targetElements    = $( '[id^="' + elementIdPrefix + '"]' );

			if ( typeof DocsAPI === "undefined" ) {
				const onlyofficeErrorTemplate = wp.template( 'onlyoffice-error' );
				targetElements.each(
					function () {
						$( this ).html(
							onlyofficeErrorTemplate(
								{
									email: __( 'ONLYOFFICE cannot be reached. Please contact admin', 'onlyoffice-plugin' )
								}
							)
						);
					}
				);

				return;
			}

			targetElements.each(
				function () {
					const config   = $( this ).data( 'config' );
					const instance = $( this ).data( 'instance' );
					if ( ! config || config === "" ) {
						const onlyofficeErrorTemplate = wp.template( 'onlyoffice-error' );
						$( this ).html(
							onlyofficeErrorTemplate(
								{
									email: __( 'File not found!', 'onlyoffice-plugin' )
								}
							)
						);
					} else {
						new DocsAPI.DocEditor( 'editorOnlyoffice-' + instance, config );
					}
				}
			);
		}
	);
})( jQuery );
