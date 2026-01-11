/**
 * Settings Auto-Save Handler
 * Saves form fields automatically on change with debounce
 */
( function( $ ) {
	'use strict';

	// Configuration.
	const AUTOSAVE_DELAY = 500; // milliseconds
	let saveTimeout = null;

	/**
	 * Initialize auto-save listeners on all settings forms.
	 */
	function init() {
		const forms = $( '.wps-settings-form' );

		if ( forms.length === 0 ) {
			return;
		}

		// Attach change event listeners to all form inputs/selects.
		forms.on( 'change', 'input, select, textarea', function() {
			scheduleAutoSave( $( this ).closest( 'form' ) );
		} );

		// Also listen for key release on input fields (for text inputs with delays).
		forms.on( 'keyup', 'input[type="text"], input[type="password"], input[type="number"], textarea', function() {
			scheduleAutoSave( $( this ).closest( 'form' ) );
		} );
	}

	/**
	 * Schedule an auto-save with debounce to prevent spam.
	 * @param {jQuery} form - The form element.
	 */
	function scheduleAutoSave( form ) {
		clearTimeout( saveTimeout );

		// Show saving state.
		const statusEl = form.find( '.wps-settings-save-status' );
		statusEl.text( '💾 ' + wps_settings_i18n.saving ).css( 'color', '#666' );

		saveTimeout = setTimeout( function() {
			autoSaveForm( form );
		}, AUTOSAVE_DELAY );
	}

	/**
	 * Perform the actual AJAX save.
	 * @param {jQuery} form - The form element.
	 */
	function autoSaveForm( form ) {
		const statusEl = form.find( '.wps-settings-save-status' );
		const group = form.data( 'settings-group' );
		const nonce = form.find( 'input[name="wps_settings_nonce"]' ).val();

		if ( ! group || ! nonce ) {
			statusEl.text( '❌ ' + wps_settings_i18n.error ).css( 'color', 'red' );
			return;
		}

		// Serialize form data.
		const formData = new FormData( form[0] );
		const data = {
			group: group,
			wps_settings_nonce: nonce,
		};

		// Extract all form values.
		formData.forEach( function( value, key ) {
			if ( key === 'wps_settings_nonce' ) {
				return; // Skip nonce, we already have it.
			}

			// Handle checkbox arrays and multi-select.
			if ( key.includes( '[]' ) ) {
				const baseKey = key.replace( '[]', '' );
				if ( ! Array.isArray( data[baseKey] ) ) {
					data[baseKey] = [];
				}
				data[baseKey].push( value );
			} else {
				data[key] = value;
			}
		} );

		// Perform AJAX request.
		$.ajax( {
			url: wps_settings_i18n.ajaxUrl,
			type: 'POST',
			data: {
				action: 'wps_save_settings',
				nonce: nonce,
				data: JSON.stringify( data ),
			},
			success: function( response ) {
				if ( response.success ) {
					statusEl.text( '✓ ' + wps_settings_i18n.saved ).css( 'color', 'green' );
					setTimeout( function() {
						statusEl.text( '' );
					}, 2000 );
				} else {
					statusEl.text( '❌ ' + ( response.data?.message || wps_settings_i18n.error ) ).css( 'color', 'red' );
				}
			},
			error: function() {
				statusEl.text( '❌ ' + wps_settings_i18n.error ).css( 'color', 'red' );
			},
		} );
	}

	// Initialize on document ready.
	$( document ).ready( init );
} )( jQuery );

