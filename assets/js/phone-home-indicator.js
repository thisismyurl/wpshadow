/**
 * Phone Home Indicator Scripts
 *
 * @package WPShadow
 * @since   1.6004.0300
 */

(function ($) {
	'use strict';

	var modalId = 'wpshadow-connections-modal';

	/**
	 * Fetch and display connection details.
	 */
	function loadConnectionDetails() {
		$.ajax(
			{
				url: wpshadowPhoneHome.ajax_url,
				type: 'POST',
				data: {
					action: 'wpshadow_get_recent_connections',
					_wpnonce: wpshadowPhoneHome.nonce
				},
				beforeSend: function () {
					$( '#wpshadow-connections-content' ).html(
						'<p>' + (wpshadowPhoneHome.strings.loading || 'Loading...') + '</p>'
					);
				},
				success: function (response) {
					if (response.success && response.data) {
						displayConnections( response.data );
					} else {
						$( '#wpshadow-connections-content' ).html(
							'<p class="wps-connection-empty">' +
							(wpshadowPhoneHome.strings.no_data || 'No connection data available.') +
							'</p>'
						);
					}
				},
				error: function () {
					$( '#wpshadow-connections-content' ).html(
						'<p class="wps-connection-empty">Failed to load connection details.</p>'
					);
				}
			}
		);
	}

	/**
	 * Display connection details in modal.
	 *
	 * @param {Object} data Connection data from server.
	 */
	function displayConnections(data) {
		var html = '<div class="wps-connection-container">';

		if ( ! data.connections || data.connections.length === 0) {
			html += '<p class="wps-connection-empty">' +
				(wpshadowPhoneHome.strings.no_data || 'No recent connections found.') +
				'</p>';
		} else {
			html += '<table class="wp-list-table widefat fixed striped wps-connection-table">';
			html += '<thead><tr>';
			html += '<th>Timestamp</th><th>Domain</th><th>URL</th><th>Purpose</th><th>Method</th>';
			html += '</tr></thead><tbody>';

			$.each(
				data.connections,
				function (i, conn) {
					html += '<tr>';
					html += '<td>' + (conn.timestamp || '-') + '</td>';
					html += '<td>' + (conn.domain || '-') + '</td>';
					html += '<td>' + (conn.url || conn.endpoint || '-') + '</td>';
					html += '<td>' + (conn.purpose || '-') + '</td>';
					html += '<td>' + (conn.method || '-') + '</td>';
					html += '</tr>';
				}
			);

			html += '</tbody></table>';
		}

		html += '<div class="wps-connection-footer">';
		html += '<p class="wps-connection-footer-text">';
		html += 'All connections are encrypted and logged for security purposes. ';
		html += 'Learn more about our <a href="https://wpshadow.com/privacy" target="_blank">Privacy Policy</a>.';
		html += '</p></div>';
		html += '</div>';

		$( '#wpshadow-connections-content' ).html( html );
	}

	/**
	 * Initialize modal functionality.
	 */
	function initModal() {
		$( '#wpshadow-view-connections' ).on(
			'click',
			function (e) {
				e.preventDefault();

				if (window.WPShadowModal && typeof window.WPShadowModal.openStatic === 'function') {
					window.WPShadowModal.openStatic( modalId, { returnFocus: e.currentTarget } );
				} else {
					$( '#' + modalId ).addClass( 'wpshadow-modal-show' );
				}

				loadConnectionDetails();
			}
		);
	}

	// Initialize on document ready
	$( document ).ready(
		function () {
			initModal();
		}
	);

})( jQuery );
