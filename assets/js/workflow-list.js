/**
 * Workflow List - JavaScript
 *
 * Handles workflow list interactions (enable/disable, run, delete)
 */


jQuery( document ).ready(
	function ($) {
		if ( $( '.wpshadow-workflow-list' ).length === 0 ) {
			return;
		}

		// Toggle workflow enabled/disabled
		$( '.workflow-enable-toggle' ).on(
			'change',
			function () {
				const $toggle    = $( this );
				const $card      = $toggle.closest( '.workflow-card' );
				const workflowId = $card.data( 'workflow-id' );
				const enabled    = $toggle.is( ':checked' );

				$.post(
					ajaxurl,
					{
						action: 'wpshadow_toggle_workflow',
						nonce: wpshadowWorkflow.nonce,
						workflow_id: workflowId,
						enabled: enabled
					},
					function (response) {
						if (response.success) {
							$card.toggleClass( 'enabled', enabled );
							$card.toggleClass( 'disabled', ! enabled );

							showNotice( 'Workflow ' + (enabled ? 'enabled' : 'disabled'), 'success' );
						} else {
							// Revert toggle on error
							$toggle.prop( 'checked', ! enabled );
							showNotice( response.data.message || 'Error toggling workflow', 'error' );
						}
					}
				);
			}
		);

		// Run workflow manually
		$( '.workflow-run-btn' ).on(
			'click',
			function () {
				const $btn         = $( this );
				const workflowId   = $btn.data( 'workflow-id' );
				const $card        = $btn.closest( '.workflow-card' );
				const workflowName = $card.find( '.workflow-name' ).text();

				const runWorkflow = function () {
					$btn.prop( 'disabled', true ).text( 'Running...' );

					$.post(
						ajaxurl,
						{
							action: 'wpshadow_run_workflow',
							nonce: wpshadowWorkflow.nonce,
							workflow_id: workflowId
						},
						function (response) {
							$btn.prop( 'disabled', false ).text( 'Run Now' );

							if (response.success) {
								showNotice( 'Workflow executed successfully!', 'success' );
							} else {
								showNotice( response.data.message || 'Error running workflow', 'error' );
							}
						}
					).fail(
						function () {
							$btn.prop( 'disabled', false ).text( 'Run Now' );
							showNotice( 'Error running workflow', 'error' );
						}
					);
				};

				if (window.WPShadowDesign && typeof window.WPShadowDesign.confirm === 'function') {
					window.WPShadowDesign.confirm( 'Run workflow "' + workflowName + '" now?', runWorkflow );
					return;
				}

				window.WPShadowModal.confirm(
					{
						title: 'Run Workflow',
						message: 'Run workflow "' + workflowName + '" now?',
						confirmText: 'Run',
						cancelText: 'Cancel',
						type: 'info',
						onConfirm: runWorkflow
					}
				);
			}
		);

		// Test workflow (dry run)
		$( '.workflow-test-btn' ).on(
			'click',
			function () {
				const $btn         = $( this );
				const workflowId   = $btn.data( 'workflow-id' );
				const $card        = $btn.closest( '.workflow-card' );
				const workflowName = $card.find( '.workflow-name' ).text();

				$btn.prop( 'disabled', true ).text( 'Testing...' );

				$.post(
					ajaxurl,
					{
						action: 'wpshadow_test_workflow',
						nonce: wpshadowWorkflow.nonce,
						workflow_id: workflowId
					},
					function (response) {
						$btn.prop( 'disabled', false ).text( 'Test' );

						if (response.success) {
							showNotice( 'Test completed! Workflow runs successfully (no changes made).', 'success' );
						} else {
							showNotice( response.data.message || 'Test found issues in workflow', 'error' );
						}
					}
				).fail(
					function () {
						$btn.prop( 'disabled', false ).text( 'Test' );
						showNotice( 'Error testing workflow', 'error' );
					}
				);
			}
		);

		// Delete workflow
		$( '.workflow-delete-btn' ).on(
			'click',
			function () {
				const $btn         = $( this );
				const workflowId   = $btn.data( 'workflow-id' );
				const $card        = $btn.closest( '.workflow-card' );
				const workflowName = $card.find( '.workflow-name' ).text();

				const deleteWorkflow = function () {
					$btn.prop( 'disabled', true );

					$.post(
						ajaxurl,
						{
							action: 'wpshadow_delete_workflow',
							nonce: wpshadowWorkflow.nonce,
							workflow_id: workflowId
						},
						function (response) {
							if (response.success) {
								$card.fadeOut(
									function () {
										$( this ).remove();

										// Check if all workflows deleted
										if ($( '.workflow-card' ).length === 0) {
											location.reload();
										}
									}
								);

								showNotice( 'Workflow deleted successfully', 'success' );
							} else {
								$btn.prop( 'disabled', false );
								showNotice( response.data.message || 'Error deleting workflow', 'error' );
							}
						}
					);
				};

				if (window.WPShadowDesign && typeof window.WPShadowDesign.confirm === 'function') {
					window.WPShadowDesign.confirm( 'Delete workflow "' + workflowName + '"? This cannot be undone.', deleteWorkflow );
					return;
				}

				window.WPShadowModal.confirm(
					{
						title: 'Delete Workflow',
						message: 'Delete workflow "' + workflowName + '"? This cannot be undone.',
						confirmText: 'Delete',
						cancelText: 'Cancel',
						type: 'danger',
						onConfirm: deleteWorkflow
					}
				);
			}
		);

		// Show admin notice
		function showNotice(message, type) {
			const $notice = $( '<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>' );
			$( '.wrap' ).prepend( $notice );

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

		// ============================================================================
		// Suggested Workflows & Examples (extracted from inline script)
		// ============================================================================

		const $exampleList      = $( '#example-list' );
		const $suggestedButtons = $( '.create-suggested-workflow' );

		// Apply dynamic colors to suggested icon cards
		$( '.suggested-icon[data-color]' ).each(
			function () {
				const color = $( this ).data( 'color' );
				if (color) {
					$( this ).css( 'background-color', color );
				}
			}
		);

		// Handle suggested workflow creation
		$suggestedButtons.on(
			'click',
			function (e) {
				e.preventDefault();
				const $btn = $( this );
				if ($btn.prop( 'disabled' )) {
					return;
				}

				const $card      = $btn.closest( '.suggested-card' );
				const title      = $btn.data( 'title' );
				const trigger    = $card.data( 'trigger' );
				const actionsRaw = $card.data( 'actions' );
				let actions      = [];

				if (Array.isArray( actionsRaw )) {
					actions = actionsRaw;
				} else if (typeof actionsRaw === 'string') {
					try {
						const parsed = JSON.parse( actionsRaw );
						if (Array.isArray( parsed )) {
							actions = parsed;
						}
					} catch (e) {
						actions = [];
					}
				}

				const defaultLabel = $btn.data( 'label' ) || 'Create from suggestion';
				$btn.prop( 'disabled', true ).text( 'Creating...' );

				$.post(
					ajaxurl,
					{
						action: 'wpshadow_create_suggested_workflow',
						nonce: wpshadowWorkflow.nonce,
						title: title,
						trigger: trigger,
						actions: JSON.stringify( actions ),
					},
					function (response) {
						if (response.success) {
							showNotice( response.data.message || 'Workflow created successfully!', 'success' );
							setTimeout(
								function () {
									window.location = response.data.redirect || window.location.href;
								},
								800
							);
						} else {
							$btn.prop( 'disabled', false ).text( defaultLabel );
							const message = response.data && response.data.message ? response.data.message : 'Could not create workflow';
							showNotice( message, 'error' );
						}
					}
				).fail(
					function () {
						$btn.prop( 'disabled', false ).text( defaultLabel );
						showNotice( 'Network error. Please try again.', 'error' );
					}
				);
			}
		);

		/**
		 * Load and render examples
		 */
		function loadExamples() {
			$.post(
				ajaxurl,
				{
					action: 'wpshadow_get_examples',
					nonce: wpshadowWorkflow.nonce,
				},
				function (response) {
					if (response.success) {
						renderExamples( response.data.examples );
					}
				}
			);
		}

		/**
		 * Render examples in the list
		 */
		function renderExamples(examples) {
			$exampleList.empty();

			if ( ! examples || Object.keys( examples ).length === 0) {
				$exampleList.html( '<p>No more examples available.</p>' );
				return;
			}

			Object.entries( examples ).forEach(
				function ([exampleKey, example]) {
					const $item = $( '<div class="example-item" data-example-key="' + exampleKey + '">' );

					// Icon mapping
					const iconMap = {
						heart: 'heart',
						'admin-appearance': 'admin-appearance',
						shield: 'shield',
						'admin-users': 'admin-users',
						lock: 'lock',
						download: 'download',
						'admin-tools': 'admin-tools',
						'image-rotate': 'image-rotate',
						database: 'database',
					};

					const icon = iconMap[example.icon] || 'admin-tools';

					const html           = `
					< div class          = "example-item-header" >
						< div class      = "example-item-icon" >
							< span class = "dashicons dashicons-${icon}" > < / span >
						< / div >
						< h4 class = "example-item-title" > ${$( '<div>' ).text( example.name ).html()} < / h4 >
					< / div >
					< p class     = "example-item-description" > ${$( '<div>' ).text( example.description ).html()} < / p >
					< button type = "button" class = "example-item-button" >
						Use Example
					< / button >
					`;

					$item.html( html );
					$exampleList.append( $item );
				}
			);

			// Bind click handlers
			attachExampleHandlers();
		}

		/**
		 * Attach event handlers to example items
		 */
		function attachExampleHandlers() {
			$exampleList.on(
				'click',
				'.example-item-button',
				function (e) {
					e.preventDefault();
					const $button    = $( this );
					const $item      = $button.closest( '.example-item' );
					const exampleKey = $item.data( 'example-key' );

					createFromExample( exampleKey, $button, $item );
				}
			);

			// Also allow clicking the whole item to trigger the button
			$exampleList.on(
				'click',
				'.example-item',
				function (e) {
					if (e.target.classList.contains( 'example-item-button' )) {
						return;
					}
					$( this ).find( '.example-item-button' ).click();
				}
			);
		}

		/**
		 * Create a workflow from the selected example
		 */
		function createFromExample(exampleKey, $button, $item) {
			if ($button.prop( 'disabled' )) {
				return;
			}

			$item.addClass( 'example-item-loading' );
			$button.prop( 'disabled', true ).text( 'Creating...' );

			$.post(
				ajaxurl,
				{
					action: 'wpshadow_create_from_example',
					nonce: wpshadowWorkflow.nonce,
					example_key: exampleKey,
				},
				function (response) {
					if (response.success) {
						// Reload the examples to show updated list
						loadExamples();

						// Show success message
						showNotice( 'Workflow created successfully! Reload the page to see it.', 'success' );

						// Reload page after 1 second
						setTimeout(
							function () {
								location.reload();
							},
							1000
						);
					} else {
						$item.removeClass( 'example-item-loading' );
						$button.prop( 'disabled', false ).text( 'Use Example' );
						showNotice( response.data.message || 'Error creating workflow', 'error' );
					}
				}
			);
		}

		// Initial load
		if ($exampleList.length) {
			loadExamples();
		}
	}
);
