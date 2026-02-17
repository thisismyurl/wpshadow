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
 * @since 1.6030.2148
 */

jQuery( function( $ ) {
	'use strict';

	/**
	 * Automation Detail Modal Handler
	 */
	const AutomationModal = {
		modal: null,

		/**
		 * Initialize modal
		 */
		init() {
			this.modal = $( '#wpshadow-automation-detail-modal' );

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

			// Close handled by shared modal helpers
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
			if ( window.WPShadowModal && typeof window.WPShadowModal.openStatic === 'function' ) {
				window.WPShadowModal.openStatic( 'wpshadow-automation-detail-modal', { returnFocus: document.activeElement } );
			} else {
				this.modal.addClass( 'wpshadow-modal-show' );
			}
		},

		/**
		 * Close modal
		 */
		close() {
			if ( window.WPShadowModal && typeof window.WPShadowModal.closeStatic === 'function' ) {
				window.WPShadowModal.closeStatic( 'wpshadow-automation-detail-modal' );
			} else {
				this.modal.removeClass( 'wpshadow-modal-show' );
			}
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
			const workflowId = $btn.data( 'workflow-id' );
			const $card = $btn.closest( '.wpshadow-automation-card' );
			const originalText = $btn.text();
			const self = this;

			WPShadowModal.confirm({
				title: 'Delete Automation',
				message: wpshadowAutomationsDashboard.strings.confirmDelete,
				onCancel: function() {
					return;
				},
				onConfirm: function() {
					self.proceedWithAutomationDeletion( $btn, $card, originalText, workflowId );
				}
			});
		},

		proceedWithAutomationDeletion( $btn, $card, originalText, workflowId ) {
			const self = this;
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
						self.showNotice( wpshadowAutomationsDashboard.strings.deleteSuccess, 'success' );
						// Close modal if open
						if ( $( '#wpshadow-automation-detail-modal' ).is( ':visible' ) ) {
							AutomationModal.close();
						}
					} else {
						self.showNotice( response.data.message || wpshadowAutomationsDashboard.strings.deleteError, 'error' );
					}
				},
				error: () => {
					self.showNotice( wpshadowAutomationsDashboard.strings.deleteError, 'error' );
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
			const suggestionId = $btn.data( 'suggestion-id' );
			const title = $btn.data( 'title' );
			const trigger = $btn.data( 'trigger' );
			const actionsRaw = $btn.data( 'actions' );
			let actions = [];
			
			if ( actionsRaw ) {
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
					if ( response.success ) {
						// After creating the automation, replace with next suggestion
						this.replaceWithNextSuggestion( $btn, suggestionId );
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
		 * Replace suggestion with next one via AJAX
		 *
		 * @param {jQuery} $btn Button element
		 * @param {string} suggestionId ID of created suggestion
		 */
		replaceWithNextSuggestion( $btn, suggestionId ) {
			const $card = $btn.closest( '.wpshadow-suggestion-card' );
			const self = this;

			$.ajax({
				url: wpshadowAutomationsDashboard.ajaxUrl,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wpshadow_get_next_suggestion',
					nonce: wpshadowAutomationsDashboard.nonce,
					suggestion_id: suggestionId,
				},
				success: ( response ) => {
					if ( response.success && response.data.suggestion ) {
						const nextSuggestion = response.data.suggestion;

						// Generate HTML for new suggestion card
						const html = `
							<div class="wpshadow-suggestion-icon" style="background: ${ nextSuggestion.color };">
								<span class="dashicons ${ nextSuggestion.icon }"></span>
							</div>
							<h3>${ self.escapeHtml( nextSuggestion.title ) }</h3>
							<p class="wpshadow-suggestion-reason">${ self.escapeHtml( nextSuggestion.reason ) }</p>
							<p class="wpshadow-suggestion-description">${ self.escapeHtml( nextSuggestion.description ) }</p>
							<button 
								type="button" 
								class="wps-btn wps-btn-secondary wps-btn-block create-suggested-workflow" 
								data-suggestion-id="${ nextSuggestion.id }"
								data-title="${ self.escapeHtml( nextSuggestion.title ) }"
								data-trigger="${ nextSuggestion.trigger }"
								data-actions='${ JSON.stringify( nextSuggestion.actions ) }'
							>
								${ wpshadowAutomationsDashboard.strings.createAutomation }
							</button>
						`;

						// Replace card content with smooth fade
						$card.fadeOut( 200, function() {
							$card.find( '.wps-card-body' ).html( html );
							$card.fadeIn( 200 );
						});

						self.showNotice( wpshadowAutomationsDashboard.strings.createdSuccess, 'success' );
					} else {
						// No more suggestions, just show success message
						self.showNotice( 'Automation created successfully!', 'success' );
					}
				},
				error: () => {
					// Reload page on error, fallback to original behavior
					window.location.reload();
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

			// Add to notice slot when available
			const $slot = $( '#wpshadow-page-notices' );
			if ( $slot.length ) {
				$slot.append( $notice );
			} else if ( $( '.wrap' ).length ) {
				$( '.wrap' ).first().prepend( $notice );
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
