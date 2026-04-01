/**
 * CPT Live Preview
 *
 * Handles live preview functionality for custom post types.
 *
 * @package    WPShadow
 * @subpackage Assets
 * @since      0.6034.1200
 */

(function ($) {
	'use strict';

	let previewIframe       = null;
	let currentDevice       = 'desktop';
	let autoRefreshEnabled  = false;
	let autoRefreshInterval = null;

	/**
	 * Initialize live preview
	 */
	function initLivePreview() {
		const $previewBox = $( '.wpshadow-live-preview-box' );

		if ( ! $previewBox.length) {
			return;
		}

		bindEvents();
		loadPreview();
	}

	/**
	 * Bind event handlers
	 */
	function bindEvents() {
		// Device switcher
		$( document ).on( 'click', '.wpshadow-device-button', handleDeviceSwitch );

		// Refresh button
		$( document ).on( 'click', '.wpshadow-preview-refresh', handleRefresh );

		// Auto-refresh toggle
		$( document ).on( 'change', '.wpshadow-auto-refresh', handleAutoRefreshToggle );

		// Content changes
		if (typeof wp !== 'undefined' && wp.data) {
			// Gutenberg editor
			wp.data.subscribe( handleEditorChange );
		} else {
			// Classic editor
			$( document ).on( 'input', '#title, #content', debounce( handleEditorChange, 1000 ) );
		}
	}

	/**
	 * Load preview content
	 */
	function loadPreview() {
		const $previewContainer = $( '.wpshadow-preview-container' );
		const postId            = $( '#post_ID' ).val();

		if ( ! postId) {
			showPreviewMessage( wpShadowLivePreview.i18n.saveFirst );
			return;
		}

		showLoadingIndicator();

		$.ajax(
			{
				url: wpShadowLivePreview.ajaxUrl,
				type: 'POST',
				data: {
					action: 'wpshadow_get_preview_url',
					nonce: wpShadowLivePreview.nonce,
					post_id: postId
				},
				success: function (response) {
					if (response.success && response.data.url) {
						renderPreview( response.data.url );
					} else {
						showPreviewMessage( response.data.message || wpShadowLivePreview.i18n.loadFailed );
					}
				},
				error: function () {
					showPreviewMessage( wpShadowLivePreview.i18n.loadFailed );
				},
				complete: function () {
					hideLoadingIndicator();
				}
			}
		);
	}

	/**
	 * Render preview iframe
	 */
	function renderPreview(url) {
		const $previewContainer = $( '.wpshadow-preview-container' );

		$previewContainer.empty();

		previewIframe = $( '<iframe>' )
			.attr( 'src', url )
			.attr( 'title', wpShadowLivePreview.i18n.previewTitle )
			.addClass( 'wpshadow-preview-iframe' )
			.addClass( 'wpshadow-device-' + currentDevice );

		$previewContainer.append( previewIframe );
	}

	/**
	 * Handle device switch
	 */
	function handleDeviceSwitch(e) {
		e.preventDefault();

		const $button = $( this );
		const device  = $button.data( 'device' );

		if (device === currentDevice) {
			return;
		}

		// Update button states
		$( '.wpshadow-device-button' ).removeClass( 'active' );
		$button.addClass( 'active' );

		// Update iframe class
		if (previewIframe) {
			previewIframe
				.removeClass( 'wpshadow-device-desktop wpshadow-device-tablet wpshadow-device-mobile' )
				.addClass( 'wpshadow-device-' + device );
		}

		currentDevice = device;
	}

	/**
	 * Handle refresh button click
	 */
	function handleRefresh(e) {
		e.preventDefault();
		loadPreview();
	}

	/**
	 * Handle auto-refresh toggle
	 */
	function handleAutoRefreshToggle(e) {
		autoRefreshEnabled = $( this ).is( ':checked' );

		if (autoRefreshEnabled) {
			startAutoRefresh();
		} else {
			stopAutoRefresh();
		}
	}

	/**
	 * Start auto-refresh
	 */
	function startAutoRefresh() {
		stopAutoRefresh(); // Clear any existing interval

		autoRefreshInterval = setInterval(
			function () {
				if (previewIframe) {
					previewIframe[0].contentWindow.location.reload();
				}
			},
			3000
		); // Refresh every 3 seconds
	}

	/**
	 * Stop auto-refresh
	 */
	function stopAutoRefresh() {
		if (autoRefreshInterval) {
			clearInterval( autoRefreshInterval );
			autoRefreshInterval = null;
		}
	}

	/**
	 * Handle editor content change
	 */
	function handleEditorChange() {
		if (autoRefreshEnabled && previewIframe) {
			previewIframe[0].contentWindow.location.reload();
		}
	}

	/**
	 * Show loading indicator
	 */
	function showLoadingIndicator() {
		const $previewContainer = $( '.wpshadow-preview-container' );
		$previewContainer.addClass( 'loading' );
	}

	/**
	 * Hide loading indicator
	 */
	function hideLoadingIndicator() {
		const $previewContainer = $( '.wpshadow-preview-container' );
		$previewContainer.removeClass( 'loading' );
	}

	/**
	 * Show preview message
	 */
	function showPreviewMessage(message) {
		const $previewContainer = $( '.wpshadow-preview-container' );
		$previewContainer.html( '<div class="wpshadow-preview-message">' + message + '</div>' );
	}

	/**
	 * Debounce function
	 */
	function debounce(func, wait) {
		let timeout;
		return function () {
			const context = this;
			const args    = arguments;
			clearTimeout( timeout );
			timeout = setTimeout(
				function () {
					func.apply( context, args );
				},
				wait
			);
		};
	}

	// Initialize when DOM is ready
	$( document ).ready(
		function () {
			initLivePreview();
		}
	);

	// Cleanup on page unload
	$( window ).on(
		'beforeunload',
		function () {
			stopAutoRefresh();
		}
	);

})( jQuery );
