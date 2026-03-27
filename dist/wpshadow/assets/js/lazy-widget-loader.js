/**
 * Lazy Widget Loader
 *
 * Dynamically loads WPShadow dashboard widgets via AJAX after page render.
 * Improves initial dashboard load time by deferring widget rendering.
 *
 * @since 1.602.0100
 */

(function( $ ) {
	'use strict';

	const LazyWidgetLoader = {
		widgets: wpshadowLazyWidgets.widgets || [],
		nonce: wpshadowLazyWidgets.nonce,
		ajaxUrl: wpshadowLazyWidgets.ajaxUrl,
		loadDelay: wpshadowLazyWidgets.loadDelay || 500,
		queue: [],
		loading: false,

		/**
		 * Initialize lazy widget loading
		 *
		 * @since 1.602.0100
		 */
		init: function() {
			// Wait for page to render, then start loading widgets
			if ( document.readyState === 'loading' ) {
				document.addEventListener( 'DOMContentLoaded', this.setup.bind( this ) );
			} else {
				this.setup();
			}
		},

		/**
		 * Setup lazy loading
		 *
		 * @since 1.602.0100
		 */
		setup: function() {
			// Add placeholders for all widgets
			this.widgets.forEach( ( widget ) => {
				this.addPlaceholder( widget );
			} );

			// Start loading widgets after delay
			setTimeout( this.startLoadingWidgets.bind( this ), this.loadDelay );
		},

		/**
		 * Add placeholder for widget
		 *
		 * @since 1.602.0100
		 * @param {Object} widget Widget configuration
		 */
		addPlaceholder: function( widget ) {
			const placeholder = $( `
				<div class="wpshadow-widget-placeholder loading" data-widget-id="${ widget.id }">
					<div class="wpshadow-widget-header">
						<h3>${ widget.title }</h3>
						<span class="wpshadow-loader"></span>
					</div>
					<div class="wpshadow-widget-content">
						<p class="description">Loading...</p>
					</div>
				</div>
			` );

			// Insert placeholder (or replace existing empty widget)
			const existing = $( `[data-widget-id="${ widget.id }"]` );
			if ( existing.length ) {
				existing.replaceWith( placeholder );
			} else {
				$( '#wpshadow-widgets-container' ).append( placeholder );
			}
		},

		/**
		 * Start loading widgets in priority order
		 *
		 * @since 1.602.0100
		 */
		startLoadingWidgets: function() {
			// Sort by priority
			this.queue = [ ...this.widgets ].sort( ( a, b ) => {
				return a.priority - b.priority;
			} );

			// Load first widget
			this.loadNextWidget();
		},

		/**
		 * Load next widget in queue
		 *
		 * @since 1.602.0100
		 */
		loadNextWidget: function() {
			if ( this.queue.length === 0 ) {
				this.onAllWidgetsLoaded();
				return;
			}

			const widget = this.queue.shift();
			this.loadWidget( widget );
		},

		/**
		 * Load individual widget via AJAX
		 *
		 * @since 1.602.0100
		 * @param {Object} widget Widget configuration
		 */
		loadWidget: function( widget ) {
			$.ajax( {
				url: this.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wpshadow_load_widget',
					widget_id: widget.id,
					nonce: this.nonce,
				},
				success: ( response ) => {
					if ( response.success ) {
						this.renderWidget( widget.id, response.data.html, response.data.cached );
					}
					// Load next widget regardless of success
					this.loadNextWidget();
				},
				error: () => {
					// Continue loading other widgets on error
					this.loadNextWidget();
				},
				timeout: 10000, // 10 second timeout per widget
			} );
		},

		/**
		 * Render widget HTML
		 *
		 * @since 1.602.0100
		 * @param {string} widgetId Widget ID
		 * @param {string} html Widget HTML
		 * @param {boolean} cached Whether from cache
		 */
		renderWidget: function( widgetId, html, cached ) {
			const placeholder = $( `[data-widget-id="${ widgetId }"]` );

			if ( ! placeholder.length ) {
				return;
			}

			// Replace placeholder with actual content
			const content = $( html );

			// Add cache indicator if needed
			if ( cached ) {
				content.addClass( 'from-cache' );
			}

			placeholder.replaceWith( content );

			// Trigger custom event for other scripts to hook into
			$( document ).trigger( 'wpshadow-widget-loaded', [ widgetId, html ] );
		},

		/**
		 * Called when all widgets finished loading
		 *
		 * @since 1.602.0100
		 */
		onAllWidgetsLoaded: function() {
			// Trigger event
			$( document ).trigger( 'wpshadow-widgets-all-loaded' );

			// Log performance metrics
			if ( window.console ) {
				const elapsed = performance.now();
				console.log( `WPShadow widgets loaded in ${ Math.round( elapsed ) }ms` );
			}
		},
	};

	// Initialize when ready
	if ( typeof wpshadowLazyWidgets !== 'undefined' ) {
		LazyWidgetLoader.init();
	}

	// Expose for external use
	window.wpshadowLazyWidgets = LazyWidgetLoader;

})( jQuery );
