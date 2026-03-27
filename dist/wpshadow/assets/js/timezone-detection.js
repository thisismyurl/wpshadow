/**
 * Timezone Detection Script
 *
 * Automatically detects browser timezone using Intl.DateTimeFormat API
 * and sends to server for WordPress timezone synchronization.
 * Runs on first admin load or can be triggered by settings tool.
 */

(function ( $ ) {
	'use strict';

	const WPShadowTZ = {
		// Detect browser timezone using modern Intl API
		detectBrowserTimezone: function () {
			try {
				// Get timezone from browser's Intl API
				const format = new Intl.DateTimeFormat().resolvedOptions();
				return format.timeZone;
			} catch ( e ) {
				console.error( 'Timezone detection failed:', e );
				return null;
			}
		},

		// Send detected timezone to server
		sendTimezone: function ( timezone ) {
			if ( ! timezone ) {
				return;
			}

			$.ajax(
				{
					url: wpshadowTimezone.ajaxUrl,
					type: 'POST',
					data: {
						action: 'wpshadow_detect_timezone',
						nonce: wpshadowTimezone.nonce,
						timezone: timezone,
					},
					success: function ( response ) {
						if ( response.success ) {
							console.log( 'Timezone set:', response.data.timezone );

							// Show notice if changed
							if ( response.data.timezone !== wpshadowTimezone.current ) {
								WPShadowTZ.showNotice(
									'success',
									'Timezone automatically detected: ' + response.data.timezone
								);
							}
						}
					},
					error: function ( jqXHR ) {
						console.error( 'Timezone detection error:', jqXHR );
					},
				}
			);
		},

		// Display notice to user
		showNotice: function ( type, message ) {
			const noticeClass = 'notice notice-' + type + ' is-dismissible';
			const notice      = $(
				'<div>',
				{
					class: noticeClass,
					html: '<p>' + message + '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button>',
				}
			);

			// Add to admin notices
			const noticesContainer = $( '#wpshadow-notices-container' );
			if ( noticesContainer.length ) {
				noticesContainer.append( notice );
			} else {
				$( '.wp-header-end' ).after( notice );
			}

			// Handle dismiss
			notice.on(
				'click',
				'.notice-dismiss',
				function () {
					notice.fadeOut(
						200,
						function () {
							$( this ).remove();
						}
					);
				}
			);
		},

		// Initialize on page load
		init: function () {
			const detected = this.detectBrowserTimezone();

			if ( detected ) {
				// Always send on page load to keep synced
				this.sendTimezone( detected );
			}
		},
	};

	// Initialize on DOM ready
	$( document ).ready(
		function () {
			WPShadowTZ.init();

			// Make available globally for timezone settings tool
			window.WPShadowTZ = WPShadowTZ;
		}
	);

})( jQuery );
