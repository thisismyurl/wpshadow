/**
 * Scan Tools JavaScript
 * Shared functionality for Quick Scan and Deep Scan tool pages
 *
 * @package WPShadow
 */

(function ($) {
	'use strict';

	/**
	 * Restore button to original state
	 */
	function restoreButton($button) {
		$button.prop( 'disabled', false ).text( $button.data( 'original-text' ) );
	}

	/**
	 * Initialize scan tool functionality
	 */
	function initScanTool() {
		// Store original button text
		$( '.wpshadow-run-scan' ).each(
			function () {
				$( this ).data( 'original-text', $( this ).text() );
			}
		);

		$( '.wpshadow-run-scan' ).on(
			'click',
			function (e) {
				e.preventDefault();

				var $button          = $( this );
				var scanType         = $button.data( 'scan-type' );
				var scanName         = scanType === 'quick' ? 'Quick Scan' : 'Deep Scan';
				var timeout          = scanType === 'quick' ? 60000 : 300000; // 1 min for quick, 5 min for deep
				var progressSpeed    = scanType === 'quick' ? 10 : 5; // Quick scan progresses faster
				var progressInterval = scanType === 'quick' ? 500 : 1000;

				var $progress     = $( '.scan-progress' );
				var $progressBar  = $( '.progress-bar' );
				var $progressFill = $( '.progress-fill' );
				var $progressText = $( '.progress-text' );
				var $results      = $( '.scan-results' );

				// Ensure ajaxurl is available
				var ajaxUrl = typeof ajaxurl !== 'undefined' ? ajaxurl : $button.data( 'ajax-url' );
				if ( ! ajaxUrl) {
					var message = 'Error: AJAX URL not available. Please contact support.';
					if (window.WPShadowDesign && typeof window.WPShadowDesign.alert === 'function') {
						window.WPShadowDesign.alert( 'AJAX unavailable', message, 'error' );
					} else {
						window.WPShadowModal.alert(
							{
								title: 'AJAX Unavailable',
								message: message,
								type: 'danger'
							}
						);
					}
					return;
				}

				// Disable button and show progress
				$button.prop( 'disabled', true ).text( 'Running...' );
				$progress.removeClass( 'hidden' );
				$progress.attr( 'aria-busy', 'true' );
				$progressBar.attr( 'aria-valuenow', 0 );
				$progressFill.css( 'width', '0%' ).css( 'background-color', '#2271b1' );
				$progressText.text( 'Starting ' + scanName.toLowerCase() + '...' + (scanType === 'deep' ? ' This may take several minutes.' : '') );
				$results.empty();

				// Simulate progress
				var progress                  = 0;
				var progressAnimationInterval = setInterval(
					function () {
						if (progress < 90) {
							progress           += Math.random() * progressSpeed;
							var currentProgress = Math.min( progress, 90 );
							$progressFill.css( 'width', currentProgress + '%' );
							$progressBar.attr( 'aria-valuenow', Math.round( currentProgress ) );
						}
					},
					progressInterval
				);

				// Run scan via AJAX
				$.ajax(
					{
						url: ajaxUrl,
						type: 'POST',
						data: {
							action: 'wpshadow_' + scanType + '_scan',
							nonce: $button.data( 'nonce' ),
							mode: 'now'
						},
						timeout: timeout,
						success: function (response) {
							clearInterval( progressAnimationInterval );
							$progressFill.css( 'width', '100%' );
							$progressBar.attr( 'aria-valuenow', 100 );

							if (response.success) {
								var data = response.data;
								$progressText.text( data.message || scanName + ' completed successfully!' );

								// Show results
								var resultsHtml = '<div class="notice notice-success"><p><strong>' + scanName + ' Complete!</strong></p>';
								resultsHtml    += '<p>Completed: ' + data.completed + ' / ' + data.total + ' diagnostics</p>';
								resultsHtml    += '<p>Findings: ' + data.findings_count + '</p>';
								if (data.findings_by_category) {
									resultsHtml += '<p>Categories affected: ' + Object.keys( data.findings_by_category ).length + '</p>';
								}
								resultsHtml += '</div>';
								$results.html( resultsHtml );

								// Refresh page after delay
								setTimeout(
									function () {
										$progress.attr( 'aria-busy', 'false' );
										window.location.href = $button.data( 'redirect-url' );
									},
									2000
								);
							} else {
								$progressText.text( 'Error: ' + (response.data || 'Unknown error') );
								$results.html( '<div class="notice notice-error"><p>' + (response.data || scanName + ' failed') + '</p></div>' );
								restoreButton( $button );
								$progress.attr( 'aria-busy', 'false' );
							}
						},
						error: function (xhr, status, error) {
							clearInterval( progressAnimationInterval );
							$progressFill.css( 'width', '100%' ).css( 'background-color', '#d63638' );
							$progressBar.attr( 'aria-valuenow', 100 );

							var errorMsg = 'Error: Unable to complete ' + scanName.toLowerCase();
							if (status === 'timeout') {
								errorMsg = 'Error: ' + scanName + ' timed out. Please try again or schedule for off-peak hours.';
							}

							$progressText.text( errorMsg );
							$results.html( '<div class="notice notice-error"><p>' + errorMsg + '</p></div>' );
							restoreButton( $button );
							$progress.attr( 'aria-busy', 'false' );
						}
					}
				);
			}
		);
	}

	// Initialize on document ready
	$( document ).ready(
		function () {
			initScanTool();
		}
	);

})( jQuery );
