/**
 * Automations Dashboard JavaScript
 *
 * Handles interactions on the automations dashboard including:
 * - Detail modal opening/closing
 * - Activity history loading
 * - Run automation functionality
 * - Delete automation functionality
 * - Toggle automation enabled/disabled status
 *
 * @since 1.2601.2148
 */

jQuery( function( $ ) {
	'use strict';

	/**
	 * Automation Detail Modal Handler
	 */
	const AutomationModal = {
		modal: null,
		overlay: null,

		/**
		 * Initialize modal
		 */
		init() {
			this.modal = $( '#wpshadow-automation-detail-modal' );
			this.overlay = this.modal.find( '.wpshadow-modal-overlay' );

			// Open detail modal
			$( document ).on( 'click', '.wpshadow-automation-detail-btn', ( e ) => {
				e.preventDefault();
				const $btn = $( e.currentTarget );
				const workflowId = $btn.data( 'workflow-id' );
				const workflowName = $btn.data( 'workflow-name' );
				const trigger = $btn.data( 'trigger' );
				const action = $btn.data( 'action' );

				this.open( workflowId, workflowName, trigger, action );
			});

			// Close modal
			$( document ).on( 'click', '.wpshadow-modal-close, .wpshadow-modal-overlay', ( e ) => {
				if ( e.target === this.overlay[0] || $( e.target ).closest( '.wpshadow-modal-close' ).length ) {
					this.close();
				}
			});

			// Close on Escape key
			$( document ).on( 'keydown', ( e ) => {
				if ( e.key === 'Escape' && this.modal.is( ':visible' ) ) {
					this.close();
				}
			});
		},

		/**
		 * Open modal with automation details
		 *
		 * @param {string} workflowId Workflow ID
		 * @param {string} workflowName Workflow name
		 * @param {string} trigger Trigger summary
		 * @param {string} action Action summary
		 */
		open( workflowId, workflowName, trigger, action ) {
			this.modal.find( '#wpshadow-modal-automation-name' ).text( workflowName );
			this.modal.find( '#wpshadow-modal-trigger' ).text( trigger );
			this.modal.find( '#wpshadow-modal-action' ).text( action );

			// Set workflow ID for action buttons
			this.modal.find( '#wpshadow-modal-run-btn, #wpshadow-modal-delete-btn' ).data( 'workflow-id', workflowId );
			this.modal.find( '#wpshadow-modal-edit-btn' ).attr( 'href', 'admin.php?page=wpshadow-automations&action=edit&workflow=' + workflowId );

			// Load activity history
			this.loadActivityHistory( workflowId );

			// Show modal
			this.modal.fadeIn( 200 );
			this.modal.css( 'display', 'flex' );
		},

		/**
		 * Close modal
		 */
		close() {
			this.modal.fadeOut( 200 );
		},

		/**
		 * Load activity history for automation
		 *
		 * @param {string} workflowId Workflow ID
		 */
		loadActivityHistory( workflowId ) {
			const $activityList = this.modal.find( '#wpshadow-modal-activity-list' );
			$activityList.html( '<p class="wpshadow-activity-loading">' + wpshadowAutomationsDashboard.strings.loadingActivity + '</p>' );

			$.ajax({
				url: wpshadowAutomationsDashboard.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wpshadow_get_automation_activity',
					nonce: wpshadowAutomationsDashboard.nonce,
					workflow_id: workflowId,
				},
				success: ( response ) => {
					if ( response.success && response.data.length > 0 ) {
						let html = '';
						response.data.forEach( ( activity ) => {
							const date = new Date( activity.timestamp * 1000 );
							const formattedTime = date.toLocaleString();
							html += `
								<div class="wpshadow-activity-item">
									<div class="wpshadow-activity-item-time">${formattedTime}</div>
									<p class="wpshadow-activity-item-text">${this.escapeHtml( activity.message )}</p>
								</div>
							`;
						});
						$activityList.html( html );
					} else {
						$activityList.html( '<p class="wpshadow-activity-empty">' + wpshadowAutomationsDashboard.strings.noActivity + '</p>' );
					}
				},
				error: () => {
					$activityList.html( '<p class="wpshadow-activity-empty">' + wpshadowAutomationsDashboard.strings.errorLoadingActivity + '</p>' );
				},
			});
		},

		/**
		 * Escape HTML special characters
		 *
		 * @param {string} text Text to escape
		 * @return {string} Escaped text
		 */
		escapeHtml( text ) {
			const div = document.createElement( 'div' );
			div.textContent = text;
			return div.innerHTML;
		},
	};

	/**
	 * Automation Actions Handler
	 */
	const AutomationActions = {
		/**
		 * Initialize actions
		 */
		init() {
			// Run automation
			$( document ).on( 'click', '.workflow-run-btn', ( e ) => {
				e.preventDefault();
				this.runAutomation( $( e.currentTarget ) );
			});

			// Delete automation
			$( document ).on( 'click', '.workflow-delete-btn', ( e ) => {
				e.preventDefault();
				this.deleteAutomation( $( e.currentTarget ) );
			});

			// Toggle automation enabled/disabled
			$( document ).on( 'change', '.workflow-enable-toggle', ( e ) => {
				this.toggleAutomation( $( e.currentTarget ) );
			});

			// Suggested workflow creation
			$( document ).on( 'click', '.create-suggested-workflow', ( e ) => {
				e.preventDefault();
				this.createSuggestedWorkflow( $( e.currentTarget ) );
			});
		},

		/**
		 * Run automation
		 *
		 * @param {jQuery} $btn Button element
		 */
		runAutomation( $btn ) {
			const workflowId = $btn.data( 'workflow-id' );
			const originalText = $btn.text();

			$btn.prop( 'disabled', true ).text( 'Running...' );

			$.ajax({
				url: wpshadowAutomationsDashboard.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wpshadow_run_automation',
					nonce: wpshadowAutomationsDashboard.nonce,
					workflow_id: workflowId,
				},
				success: ( response ) => {
					if ( response.success ) {
						this.showNotice( wpshadowAutomationsDashboard.strings.runSuccess, 'success' );
						// Reload activity if modal is open
						if ( $( '#wpshadow-automation-detail-modal' ).is( ':visible' ) ) {
							AutomationModal.loadActivityHistory( workflowId );
						}
					} else {
						this.showNotice( response.data.message || wpshadowAutomationsDashboard.strings.runError, 'error' );
					}
				},
				error: () => {
					this.showNotice( wpshadowAutomationsDashboard.strings.runError, 'error' );
				},
				complete: () => {
					$btn.prop( 'disabled', false ).text( originalText );
				},
			});
		},

		/**
		 * Delete automation
		 *
		 * @param {jQuery} $btn Button element
		 */
		deleteAutomation( $btn ) {
			if ( !confirm( wpshadowAutomationsDashboard.strings.confirmDelete ) ) {
				return;
			}

			const workflowId = $btn.data( 'workflow-id' );
		const $card = $btn.closest( '.wpshadow-automation-card' );
		const originalText = $btn.text();

		$btn.prop( 'disabled', true ).text( 'Deleting...' );

		$.ajax({
			url: wpshadowAutomationsDashboard.ajaxUrl,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wpshadow_delete_automation',
				nonce: wpshadowAutomationsDashboard.nonce,
				workflow_id: workflowId,
				},
				success: ( response ) => {
					if ( response.success ) {
						$card.fadeOut( 300, function() {
							$( this ).remove();
							// Check if list is now empty and show empty state
							if ( $( '.wpshadow-automation-card' ).length === 0 ) {
								window.location.reload();
							}
						});
						this.showNotice( wpshadowAutomationsDashboard.strings.deleteSuccess, 'success' );
						// Close modal if open
						if ( $( '#wpshadow-automation-detail-modal' ).is( ':visible' ) ) {
							AutomationModal.close();
						}
					} else {
						this.showNotice( response.data.message || wpshadowAutomationsDashboard.strings.deleteError, 'error' );
					}
				},
				error: () => {
					this.showNotice( wpshadowAutomationsDashboard.strings.deleteError, 'error' );
				},
				complete: () => {
					$btn.prop( 'disabled', false ).text( originalText );
				},
			});
		},

		/**
		 * Toggle automation enabled/disabled status
		 *
		 * @param {jQuery} $checkbox Checkbox element
		 */
		toggleAutomation( $checkbox ) {
			const $card = $checkbox.closest( '.wpshadow-automation-card' );
			const workflowId = $card.data( 'workflow-id' );
			const isEnabled = $checkbox.prop( 'checked' );

			$.ajax({
				url: wpshadowAutomationsDashboard.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wpshadow_toggle_automation',
					nonce: wpshadowAutomationsDashboard.nonce,
					workflow_id: workflowId,
					enabled: isEnabled ? 1 : 0,
				},
				success: ( response ) => {
					if ( response.success ) {
						// Update card class
						$card.toggleClass( 'disabled', !isEnabled );
						this.showNotice( wpshadowAutomationsDashboard.strings.toggleSuccess, 'success' );
					} else {
						// Revert checkbox
						$checkbox.prop( 'checked', !isEnabled );
						this.showNotice( response.data.message || wpshadowAutomationsDashboard.strings.toggleError, 'error' );
					}
				},
				error: () => {
					// Revert checkbox
					$checkbox.prop( 'checked', !isEnabled );
					this.showNotice( wpshadowAutomationsDashboard.strings.toggleError, 'error' );
				},
			});
		},

		/**
		 * Create suggested workflow
		 *
		 * @param {jQuery} $btn Button element
		 */
		createSuggestedWorkflow( $btn ) {
			const title = $btn.data( 'title' );
			const $card = $btn.closest( '.wpshadow-suggestion-card' );
			const trigger = $card.find( '[data-trigger]' ).data( 'trigger' );
			const actionsRaw = $card.find( '[data-actions]' ).data( 'actions' );
			let actions = [];
			
			// Parse actions data
			if ( Array.isArray( actionsRaw ) ) {
				actions = actionsRaw;
			} else if ( typeof actionsRaw === 'string' ) {
				try {
					actions = JSON.parse( actionsRaw );
				} catch ( e ) {
					actions = [];
				}
			}

			const originalText = $btn.text();
			$btn.prop( 'disabled', true ).text( 'Creating...' );

			$.ajax({
				url: wpshadowAutomationsDashboard.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wpshadow_create_suggested_workflow',
					nonce: wpshadowAutomationsDashboard.nonce,
					title: title,
					trigger: trigger,
					actions: JSON.stringify( actions ),
				},
				success: ( response ) => {
					if ( response.success && response.data.redirect ) {
						window.location.href = response.data.redirect;
					} else {
						this.showNotice( 'Failed to create automation', 'error' );
						$btn.prop( 'disabled', false ).text( originalText );
					}
				},
				error: () => {
					this.showNotice( 'Failed to create automation', 'error' );
					$btn.prop( 'disabled', false ).text( originalText );
				},
			});
		},

		/**
		 * Show admin notice
		 *
		 * @param {string} message Notice message
		 * @param {string} type Notice type (success, error, warning, info)
		 */
		showNotice( message, type = 'info' ) {
			const typeClass = 'notice-' + type;
			const $notice = $( `
				<div class="notice ${typeClass} is-dismissible">
					<p>${this.escapeHtml( message )}</p>
					<button type="button" class="notice-dismiss">
						<span class="screen-reader-text">Dismiss this notice.</span>
					</button>
				</div>
			` );

			// Add to top of page
			const $container = $( '.wps-page-container' );
			if ( $container.length ) {
				$notice.insertAfter( $container.find( '.wps-page-header' ) );
			} else {
				$notice.prependTo( 'body' );
			}

			// Auto-remove after 5 seconds
			setTimeout( () => {
				$notice.fadeOut( 300, function() {
					$( this ).remove();
				});
			}, 5000 );

			// Allow manual dismissal
			$notice.on( 'click', '.notice-dismiss', function() {
				$notice.fadeOut( 300, function() {
					$( this ).remove();
				});
			});
		},

		/**
		 * Escape HTML special characters
		 *
		 * @param {string} text Text to escape
		 * @return {string} Escaped text
		 */
		escapeHtml( text ) {
			const div = document.createElement( 'div' );
			div.textContent = text;
			return div.innerHTML;
		},
	};

	/**
	 * Initialize on document ready
	 */
	AutomationModal.init();
	AutomationActions.init();
});
