/**
 * Phone Home Indicator Scripts
 *
 * @package WPShadow
 * @since   1.6004.0300
 */

(function($) {
	'use strict';

	var modal;

	/**
	 * Fetch and display connection details.
	 */
	function loadConnectionDetails() {
		$.ajax({
			url: wpshadowPhoneHome.ajax_url,
			type: 'POST',
			data: {
				action: 'wpshadow_get_phone_home_details',
				nonce: wpshadowPhoneHome.nonce
			},
			beforeSend: function() {
				$('#wpshadow-connections-content').html(
					'<p>' + (wpshadowPhoneHome.strings.loading || 'Loading...') + '</p>'
				);
			},
			success: function(response) {
				if (response.success && response.data) {
					displayConnections(response.data);
				} else {
					$('#wpshadow-connections-content').html(
						'<p class="wps-connection-empty">' + 
						(wpshadowPhoneHome.strings.no_data || 'No connection data available.') + 
						'</p>'
					);
				}
			},
			error: function() {
				$('#wpshadow-connections-content').html(
					'<p class="wps-connection-empty">Failed to load connection details.</p>'
				);
			}
		});
	}

	/**
	 * Display connection details in modal.
	 *
	 * @param {Object} data Connection data from server.
	 */
	function displayConnections(data) {
		var html = '<div class="wps-connection-container">';

		if (!data.connections || data.connections.length === 0) {
			html += '<p class="wps-connection-empty">' + 
				(wpshadowPhoneHome.strings.no_data || 'No recent connections found.') + 
				'</p>';
		} else {
			html += '<table class="wp-list-table widefat fixed striped wps-connection-table">';
			html += '<thead><tr>';
			html += '<th>Timestamp</th><th>Endpoint</th><th>Status</th><th>Response Time</th>';
			html += '</tr></thead><tbody>';

			$.each(data.connections, function(i, conn) {
				html += '<tr>';
				html += '<td>' + (conn.timestamp || '-') + '</td>';
				html += '<td>' + (conn.endpoint || '-') + '</td>';
				html += '<td>' + (conn.status || '-') + '</td>';
				html += '<td>' + (conn.response_time || '-') + '</td>';
				html += '</tr>';
			});

			html += '</tbody></table>';
		}

		html += '<div class="wps-connection-footer">';
		html += '<p class="wps-connection-footer-text">';
		html += 'All connections are encrypted and logged for security purposes. ';
		html += 'Learn more about our <a href="https://wpshadow.com/privacy" target="_blank">Privacy Policy</a>.';
		html += '</p></div>';
		html += '</div>';

		$('#wpshadow-connections-content').html(html);
	}

	/**
	 * Initialize modal functionality.
	 */
	function initModal() {
		$('#wpshadow-view-connections').on('click', function(e) {
			e.preventDefault();

			if (!modal) {
				// Create modal on first click
				modal = $('<div>')
					.attr('id', 'wpshadow-phone-home-modal')
					.addClass('wps-phone-home-modal')
					.append(
						$('<div>').addClass('wps-modal-overlay').on('click', closeModal),
						$('<div>').addClass('wps-modal-content').append(
							$('<div>').addClass('wps-modal-header').append(
								$('<h2>').text(wpshadowPhoneHome.strings.modal_title || 'Network Activity'),
								$('<button>').addClass('wps-modal-close').html('&times;').on('click', closeModal)
							),
							$('<div>').attr('id', 'wpshadow-connections-content').addClass('wps-modal-body')
						)
					)
					.appendTo('body');
			}

			modal.show();
			loadConnectionDetails();
		});
	}

	/**
	 * Close the modal.
	 */
	function closeModal() {
		if (modal) {
			modal.hide();
		}
	}

	// Initialize on document ready
	$(document).ready(function() {
		initModal();
	});

})(jQuery);
