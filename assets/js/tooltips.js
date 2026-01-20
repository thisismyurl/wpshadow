/**
 * WPShadow Tooltips - Core Functionality
 */

( function() {
	'use strict';

	/**
	 * Tooltip manager - handles showing/hiding tooltips across wp-admin.
	 */
	var WPShadowTooltips = {
		tooltipData: window.wpshadowTooltips || {},
		disabledCategories: window.wpshadowDisabledTipCategories || [],
		dismissedTips: window.wpshadowDismissedTips || [],
		nonce: ( window.wpshadowTipNonce && window.wpshadowTipNonce.nonce ) ? window.wpshadowTipNonce.nonce : '',
		tooltipElements: new Map(),
		activeTooltip: null,
		hoverDelay: 800, // ms before showing tooltip
		hoverTimer: null,

		init: function() {
			if ( !this.tooltipData || Object.keys( this.tooltipData ).length === 0 ) {
				return;
			}

			this.createTooltips();
			this.attachEventListeners();
		},

		createTooltips: function() {
			var self = this;

			Object.keys( this.tooltipData ).forEach( function( tipId ) {
				var tip = self.tooltipData[ tipId ];

				// Skip if category is disabled
				if ( self.disabledCategories.indexOf( tip.category ) !== -1 ) {
					return;
				}

				// Skip if already dismissed by user
				if ( self.dismissedTips.indexOf( tipId ) !== -1 ) {
					return;
				}

				var elements = document.querySelectorAll( tip.selector );
				if ( elements.length === 0 ) {
					return;
				}

				elements.forEach( function( el ) {
					self.createTooltipForElement( el, tip );
				} );
			} );
		},

		createTooltipForElement: function( element, tipData ) {
			var tooltipEl = document.createElement( 'div' );
			tooltipEl.className = 'wpshadow-tooltip ' + tipData.level;
			tooltipEl.setAttribute( 'data-tip-id', tipData.id );
			tooltipEl.innerHTML = '<span class="wpshadow-tooltip-title">' +
				this.escapeHtml( tipData.title ) +
				'</span><p class="wpshadow-tooltip-message">' +
				this.escapeHtml( tipData.message ) +
				'</p><button class="wpshadow-tooltip-dismiss" aria-label="Dismiss tip"></button>';

			document.body.appendChild( tooltipEl );

			// Attach dismiss handler
			var dismissBtn = tooltipEl.querySelector( '.wpshadow-tooltip-dismiss' );
			var self = this;
			dismissBtn.addEventListener( 'click', function( e ) {
				e.stopPropagation();
				self.dismissTip( tipData.id );
				tooltipEl.classList.remove( 'visible' );
			} );

			// Attach hover listeners
			element.addEventListener( 'mouseenter', function() {
				self.showTooltip( tooltipEl, element );
			} );

			element.addEventListener( 'mouseleave', function() {
				self.hideTooltip( tooltipEl );
			} );

			tooltipEl.addEventListener( 'mouseleave', function() {
				self.hideTooltip( tooltipEl );
			} );

			this.tooltipElements.set( element, tooltipEl );
		},

		showTooltip: function( tooltipEl, triggerEl ) {
			var self = this;

			// Clear any existing hover timer
			if ( this.hoverTimer ) {
				clearTimeout( this.hoverTimer );
			}

			this.hoverTimer = setTimeout( function() {
				// Hide any previously active tooltip
				if ( self.activeTooltip && self.activeTooltip !== tooltipEl ) {
					self.activeTooltip.classList.remove( 'visible' );
				}

				self.activeTooltip = tooltipEl;
				self.positionTooltip( tooltipEl, triggerEl );
				tooltipEl.classList.add( 'visible' );
			}, this.hoverDelay );
		},

		hideTooltip: function( tooltipEl ) {
			if ( this.hoverTimer ) {
				clearTimeout( this.hoverTimer );
			}

			if ( tooltipEl === this.activeTooltip ) {
				this.activeTooltip = null;
			}

			tooltipEl.classList.remove( 'visible' );
		},

		positionTooltip: function( tooltipEl, triggerEl ) {
			var triggerRect = triggerEl.getBoundingClientRect();
			var tooltipRect = tooltipEl.getBoundingClientRect();
			var viewportWidth = window.innerWidth;
			var viewportHeight = window.innerHeight;
			var offset = 10;
			var x = 0;
			var y = 0;
			var arrowClass = 'arrow-top';

			// Prefer showing below the trigger
			y = triggerRect.bottom + offset + window.scrollY;

			// If not enough space below, show above
			if ( y + tooltipRect.height > viewportHeight + window.scrollY ) {
				y = triggerRect.top - offset - tooltipRect.height + window.scrollY;
				arrowClass = 'arrow-bottom';
			}

			// Center horizontally relative to trigger
			x = triggerRect.left + ( triggerRect.width - tooltipRect.width ) / 2;

			// Keep tooltip in viewport horizontally
			if ( x < 10 ) {
				x = 10;
			}

			if ( x + tooltipRect.width > viewportWidth - 10 ) {
				x = viewportWidth - tooltipRect.width - 10;
			}

			tooltipEl.className = tooltipEl.className.replace( /arrow-\w+/g, '' );
			tooltipEl.classList.add( arrowClass );
			tooltipEl.style.left = x + 'px';
			tooltipEl.style.top = y + 'px';
		},

		dismissTip: function( tipId ) {
			if ( !this.nonce ) {
				return;
			}

			var self = this;
			var xhr = new XMLHttpRequest();
			xhr.open( 'POST', window.ajaxurl || '/wp-admin/admin-ajax.php', true );
			xhr.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded' );

			xhr.onload = function() {
				if ( xhr.status === 200 ) {
					// Tip dismissed on server; update local state
					self.dismissedTips.push( tipId );
				}
			};

			var params = 'action=wpshadow_dismiss_tip&nonce=' + encodeURIComponent( this.nonce ) +
				'&tip_id=' + encodeURIComponent( tipId );
			xhr.send( params );
		},

		escapeHtml: function( text ) {
			var div = document.createElement( 'div' );
			div.textContent = text;
			return div.innerHTML;
		},

		attachEventListeners: function() {
			var self = this;

			// Reposition tooltips on window resize
			var resizeTimeout;
			window.addEventListener( 'resize', function() {
				clearTimeout( resizeTimeout );
				resizeTimeout = setTimeout( function() {
					self.tooltipElements.forEach( function( tooltipEl, triggerEl ) {
						if ( tooltipEl.classList.contains( 'visible' ) ) {
							self.positionTooltip( tooltipEl, triggerEl );
						}
					} );
				}, 250 );
			} );

			// Hide tooltips when pressing Escape
			document.addEventListener( 'keydown', function( e ) {
				if ( e.key === 'Escape' && self.activeTooltip ) {
					self.hideTooltip( self.activeTooltip );
				}
			} );
		},
	};

	// Initialize on document ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', function() {
			WPShadowTooltips.init();
		} );
	} else {
		WPShadowTooltips.init();
	}

	// Expose to global scope for debugging
	window.WPShadowTooltips = WPShadowTooltips;
} )();
