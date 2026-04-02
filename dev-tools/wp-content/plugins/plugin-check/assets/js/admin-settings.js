/**
 * Admin Settings JavaScript
 */

/* global pluginCheckSettings */

( function () {
	'use strict';

	document.addEventListener( 'DOMContentLoaded', function () {
		const providerSelect = document.getElementById( 'ai_provider' );
		const apiKeyInput = document.getElementById( 'ai_api_key' );
		const modelSelect = document.getElementById( 'ai_model' );

		if ( ! providerSelect || ! apiKeyInput || ! modelSelect ) {
			return;
		}

		// Store the initially selected model value from the backend.
		// This value should persist across AJAX reloads.
		let savedModelValue = modelSelect.dataset.initialValue || '';
		let isFirstLoad = true;

		/**
		 * Updates field states based on provider selection.
		 */
		function updateFields() {
			const provider = providerSelect.value;

			if ( provider ) {
				apiKeyInput.disabled = false;
				modelSelect.disabled = false;
				updateModelOptions( provider );
			} else {
				apiKeyInput.disabled = true;
				modelSelect.disabled = true;
				modelSelect.value = '';
				savedModelValue = '';
			}
		}

		/**
		 * Fetches and updates model options for the selected provider.
		 *
		 * @param {string} provider Provider key.
		 */
		function updateModelOptions( provider ) {
			// Save current value before clearing (in case user changed it).
			// BUT: Don't override savedModelValue on first load - use data-initial-value.
			if (
				! isFirstLoad &&
				modelSelect.value &&
				modelSelect.value !== ''
			) {
				savedModelValue = modelSelect.value;
			}

			// Show loading state.
			modelSelect.disabled = true;
			modelSelect.innerHTML =
				'<option value="">' +
				pluginCheckSettings.loadingText +
				'</option>';

			// Fetch models via AJAX.
			const formData = new FormData();
			formData.append( 'action', 'plugin_check_get_models' );
			formData.append( 'nonce', pluginCheckSettings.nonce );
			formData.append( 'provider', provider );
			formData.append( 'api_key', apiKeyInput.value || '' );

			fetch( pluginCheckSettings.ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: formData,
			} )
				.then( function ( response ) {
					return response.json();
				} )
				.then( function ( response ) {
					modelSelect.innerHTML = '';

					// Add default option.
					const defaultOption = document.createElement( 'option' );
					defaultOption.value = '';
					defaultOption.textContent =
						pluginCheckSettings.selectModelText;
					modelSelect.appendChild( defaultOption );

					if ( response.success && response.data ) {
						let modelFound = false;

						// Add model options.
						Object.keys( response.data ).forEach( function ( key ) {
							const option = document.createElement( 'option' );
							option.value = key;
							option.textContent = response.data[ key ];

							// Check if this is the saved model value.
							if ( savedModelValue === key ) {
								option.selected = true;
								modelFound = true;
							}

							modelSelect.appendChild( option );
						} );

						// If the saved model wasn't found in the list, clear it.
						if ( ! modelFound && savedModelValue ) {
							savedModelValue = '';
						}
					} else {
						// Show no models message.
						const noModelsOption =
							document.createElement( 'option' );
						noModelsOption.value = '';
						noModelsOption.textContent =
							pluginCheckSettings.noModelsText;
						modelSelect.appendChild( noModelsOption );
					}

					modelSelect.disabled = false;

					// Mark that we've completed the first load.
					isFirstLoad = false;
				} )
				.catch( function ( error ) {
					console.error( 'Error fetching models:', error );

					modelSelect.innerHTML = '';
					const errorOption = document.createElement( 'option' );
					errorOption.value = '';
					errorOption.textContent = pluginCheckSettings.errorText;
					modelSelect.appendChild( errorOption );

					modelSelect.disabled = false;

					// Mark that we've completed the first load even on error.
					isFirstLoad = false;
				} );
		}

		// Bind events.
		providerSelect.addEventListener( 'change', updateFields );
		apiKeyInput.addEventListener( 'change', function () {
			if ( providerSelect.value ) {
				updateModelOptions( providerSelect.value );
			}
		} );
		modelSelect.addEventListener( 'change', function () {
			savedModelValue = modelSelect.value || '';
		} );

		// Initial update.
		updateFields();
	} );
} )();
