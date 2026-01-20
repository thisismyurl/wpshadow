/**
 * WPShadow A11y Audit - On-demand Page Scanning
 *
 * Provides AJAX functionality for administrators to run
 * accessibility audits on specific pages from the feature page.
 *
 * @package WPShadow
 */

( function() {
	'use strict';

	const A11yAudit = {
		nonce: window.wpshadowA11y?.nonce || '',
		ajaxUrl: window.wpshadowA11y?.ajaxUrl || '',
		isRunning: false,

		/**
		 * Initialize audit functionality.
		 */
		init() {
			const auditButtons = document.querySelectorAll( '.wpshadow-audit-page-btn' );
			auditButtons.forEach( ( btn ) => {
				btn.addEventListener( 'click', ( e ) => {
					e.preventDefault();
					this.runAudit( btn );
				} );
			} );
		},

		/**
		 * Run audit on a specific page.
		 *
		 * @param {HTMLElement} button - Trigger button element.
		 */
		runAudit( button ) {
			if ( this.isRunning ) {
				return;
			}

			const pageId = button.dataset.pageId || 0;
			const pageTitle = button.dataset.pageTitle || 'Page';

			if ( pageId <= 0 ) {
				alert( 'Invalid page ID' );
				return;
			}

			this.isRunning = true;
			button.disabled = true;
			const originalText = button.textContent;
			button.textContent = 'Auditing...';
			button.classList.add( 'wpshadow-auditing' );

			fetch( this.ajaxUrl, {
				method: 'POST',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
				},
				body: new URLSearchParams( {
					action: 'wpshadow_audit_page',
					nonce: this.nonce,
					page_id: pageId,
				} ),
			} )
				.then( ( response ) => response.json() )
				.then( ( data ) => {
					this.handleAuditResult( data, pageTitle );
				} )
				.catch( ( error ) => {
					console.error( 'Audit error:', error );
					alert( 'Error running audit: ' + error.message );
				} )
				.finally( () => {
					this.isRunning = false;
					button.disabled = false;
					button.textContent = originalText;
					button.classList.remove( 'wpshadow-auditing' );
				} );
		},

		/**
		 * Handle audit results and display in modal/panel.
		 *
		 * @param {Object} data - AJAX response data.
		 * @param {string} pageTitle - Page being audited.
		 */
		handleAuditResult( data, pageTitle ) {
			if ( ! data.success ) {
				alert( 'Audit failed: ' + ( data.data?.message || 'Unknown error' ) );
				return;
			}

			const { issues_count, issues } = data.data;
			const resultHtml = this.formatResults( pageTitle, issues_count, issues );

			// Display in a lightbox/modal
			this.showResultsModal( resultHtml );
		},

		/**
		 * Format audit results as HTML.
		 *
		 * @param {string} pageTitle - Page title.
		 * @param {number} issuesCount - Total issues found.
		 * @param {Array} issues - Issue objects with type/message.
		 * @return {string} Formatted HTML.
		 */
		formatResults( pageTitle, issuesCount, issues ) {
			let html = `<h3>Accessibility Audit: ${pageTitle}</h3>`;

			if ( issuesCount === 0 ) {
				html += '<p class="wpshadow-audit-success">✓ No accessibility issues detected!</p>';
				return html;
			}

			html += `<p class="wpshadow-audit-count"><strong>${issuesCount} issues found</strong></p>`;
			html += '<ul class="wpshadow-audit-issues">';

			issues.forEach( ( issue ) => {
				const iconMap = {
					missing_alt: '🖼️',
					invalid_aria: '⚙️',
					positive_tabindex: '⌨️',
					contrast: '🎨',
				};
				const icon = iconMap[ issue.type ] || '⚠️';
				html += `<li>${icon} ${issue.message}</li>`;
			} );

			html += '</ul>';
			return html;
		},

		/**
		 * Display results in a modal.
		 *
		 * @param {string} html - HTML content to display.
		 */
		showResultsModal( html ) {
			// Create or reuse modal
			let modal = document.getElementById( 'wpshadow-audit-modal' );
			if ( ! modal ) {
				modal = document.createElement( 'div' );
				modal.id = 'wpshadow-audit-modal';
				modal.className = 'wpshadow-modal wpshadow-audit-modal';
				document.body.appendChild( modal );

				// Add close button
				const closeBtn = document.createElement( 'button' );
				closeBtn.className = 'wpshadow-modal-close';
				closeBtn.textContent = '×';
				closeBtn.addEventListener( 'click', () => {
					modal.style.display = 'none';
				} );
				modal.appendChild( closeBtn );

				// Content container
				const content = document.createElement( 'div' );
				content.className = 'wpshadow-modal-content';
				modal.appendChild( content );
			}

			// Update content
			const content = modal.querySelector( '.wpshadow-modal-content' );
			content.innerHTML = html;
			modal.style.display = 'flex';

			// Close on outside click
			modal.addEventListener( 'click', ( e ) => {
				if ( e.target === modal ) {
					modal.style.display = 'none';
				}
			} );
		},
	};

	// Initialize when DOM ready
	if ( document.readyState === 'loading' ) {
		document.addEventListener( 'DOMContentLoaded', () => A11yAudit.init() );
	} else {
		A11yAudit.init();
	}

	// Export for global access
	window.WPShadowA11y = A11yAudit;
} )();
