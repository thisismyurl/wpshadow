/**
 * CPT Version History
 *
 * Handles content versioning and restoration.
 *
 * @package    WPShadow
 * @subpackage Assets
 * @since      0.6034.1200
 */

(function ($) {
	'use strict';

	function showConfirm(message, onConfirm) {
		if (window.WPShadowModal && typeof window.WPShadowModal.confirm === 'function') {
			window.WPShadowModal.confirm(
				{
					title: 'Please Confirm',
					message: message,
					confirmText: 'Continue',
					cancelText: 'Cancel',
					type: 'warning',
					onConfirm: onConfirm,
					onCancel: function () {}
				}
			);
			return;
		}

		if (window.confirm( message )) {
			onConfirm();
		}
	}

	/**
	 * Initialize version history
	 */
	function initVersionHistory() {
		if ( ! $( '.wpshadow-version-history-box' ).length) {
			return;
		}

		bindEvents();
	}

	/**
	 * Bind event handlers
	 */
	function bindEvents() {
		$( document ).on( 'click', '.wpshadow-restore-version', handleRestore );
		$( document ).on( 'click', '.wpshadow-delete-version', handleDelete );
		$( document ).on( 'click', '.wpshadow-version-item', handleVersionClick );
		$( document ).on( 'click', '.wpshadow-compare-versions', handleCompare );
	}

	/**
	 * Handle version click (expand/collapse)
	 */
	function handleVersionClick(e) {
		if ($( e.target ).is( 'button' )) {
			return; // Don't toggle if clicking a button
		}

		const $item    = $( this );
		const $details = $item.find( '.wpshadow-version-details' );

		$item.toggleClass( 'expanded' );
		$details.slideToggle();
	}

	/**
	 * Handle version restoration
	 */
	function handleRestore(e) {
		e.preventDefault();
		e.stopPropagation();

		const $button      = $( this );
		const versionIndex = $button.data( 'version-index' );
		const postId       = $( '#post_ID' ).val();

		showConfirm(
			wpShadowVersions.i18n.confirmRestore,
			function () {
				$button.prop( 'disabled', true ).text( wpShadowVersions.i18n.restoring );

				$.ajax(
					{
						url: wpShadowVersions.ajaxUrl,
						type: 'POST',
						data: {
							action: 'wpshadow_restore_version',
							nonce: wpShadowVersions.nonce,
							post_id: postId,
							version_index: versionIndex
						},
						success: function (response) {
							if (response.success) {
								showSuccess( wpShadowVersions.i18n.restored );

								// Reload page after 1 second to show restored content
								setTimeout(
									function () {
										window.location.reload();
									},
									1000
								);
							} else {
								showError( response.data.message || wpShadowVersions.i18n.restoreFailed );
								$button.prop( 'disabled', false ).text( wpShadowVersions.i18n.restore );
							}
						},
						error: function () {
							showError( wpShadowVersions.i18n.restoreFailed );
							$button.prop( 'disabled', false ).text( wpShadowVersions.i18n.restore );
						}
					}
				);
			}
		);

	}

	/**
	 * Handle version deletion
	 */
	function handleDelete(e) {
		e.preventDefault();
		e.stopPropagation();

		const $button      = $( this );
		const versionIndex = $button.data( 'version-index' );
		const postId       = $( '#post_ID' ).val();

		showConfirm(
			wpShadowVersions.i18n.confirmDelete,
			function () {
				$button.prop( 'disabled', true );

				$.ajax(
					{
						url: wpShadowVersions.ajaxUrl,
						type: 'POST',
						data: {
							action: 'wpshadow_delete_version',
							nonce: wpShadowVersions.nonce,
							post_id: postId,
							version_index: versionIndex
						},
						success: function (response) {
							if (response.success) {
								$button.closest( '.wpshadow-version-item' ).slideUp(
									function () {
										$( this ).remove();
										updateVersionCount();
									}
								);
								showSuccess( wpShadowVersions.i18n.deleted );
							} else {
								showError( response.data.message || wpShadowVersions.i18n.deleteFailed );
								$button.prop( 'disabled', false );
							}
						},
						error: function () {
							showError( wpShadowVersions.i18n.deleteFailed );
							$button.prop( 'disabled', false );
						}
					}
				);
			}
		);
	}

	/**
	 * Handle version comparison
	 */
	function handleCompare(e) {
		e.preventDefault();

		const $button      = $( this );
		const versionIndex = $button.data( 'version-index' );
		const $details     = $button.closest( '.wpshadow-version-item' ).find( '.wpshadow-version-details' );

		// Simple comparison: just show the version details
		if ( ! $button.closest( '.wpshadow-version-item' ).hasClass( 'expanded' )) {
			$button.closest( '.wpshadow-version-item' ).addClass( 'expanded' );
			$details.slideDown();
		}
	}

	/**
	 * Update version count display
	 */
	function updateVersionCount() {
		const count = $( '.wpshadow-version-item' ).length;
		$( '.wpshadow-version-count' ).text( count );

		if (count === 0) {
			$( '.wpshadow-version-list' ).html(
				'<p class="wpshadow-no-versions">' + wpShadowVersions.i18n.noVersions + '</p>'
			);
		}
	}

	/**
	 * Show error message
	 */
	function showError(message) {
		const $notice = $(
			'<div class="notice notice-error is-dismissible"><p>' +
						message + '</p></div>'
		);
		$( '.wpshadow-version-history-box' ).prepend( $notice );

		setTimeout(
			function () {
				$notice.fadeOut(
					function () {
						$( this ).remove();
					}
				);
			},
			5000
		);
	}

	/**
	 * Show success message
	 */
	function showSuccess(message) {
		const $notice = $(
			'<div class="notice notice-success is-dismissible"><p>' +
						message + '</p></div>'
		);
		$( '.wpshadow-version-history-box' ).prepend( $notice );

		setTimeout(
			function () {
				$notice.fadeOut(
					function () {
						$( this ).remove();
					}
				);
			},
			3000
		);
	}

	/**
	 * Format timestamp
	 */
	function formatTimestamp(timestamp) {
		const date = new Date( timestamp * 1000 );
		return date.toLocaleString();
	}

	// Initialize when DOM is ready
	$( document ).ready(
		function () {
			initVersionHistory();
		}
	);

})( jQuery );
