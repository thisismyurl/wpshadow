/**
 * Recovery Mode Handler
 * Handles exit recovery mode via AJAX without page redirect
 */
const WPShadowRecovery = {
	/**
	 * Exit recovery mode via AJAX
	 *
	 * @param {boolean} clearProblematic Whether to clear problematic plugins list
	 */
	exitRecoveryMode: function( clearProblematic ) {
		clearProblematic = clearProblematic || false;

		// Try to find the form - could be on notice or emergency dashboard
		let form = document.getElementById( 'wpshadow-exit-recovery-form' );
		if ( ! form ) {
			form = document.getElementById( 'wpshadow-exit-recovery-notice-form' );
		}
		if ( ! form ) {
			// Fallback: find any form with the recovery nonce field
			const nonceForms = document.querySelectorAll( 'form' );
			for ( let i = 0; i < nonceForms.length; i++ ) {
				if ( nonceForms[i].querySelector( 'input[name="wpshadow_recovery_nonce"]' ) ) {
					form = nonceForms[i];
					break;
				}
			}
		}

		if ( ! form ) {
			console.error( 'Recovery form not found' );
			alert( 'Error: Could not find recovery form' );
			return;
		}

		const nonceField = form.querySelector( 'input[name="wpshadow_recovery_nonce"]' );
		const nonce = nonceField ? nonceField.value : '';

		if ( ! nonce ) {
			console.error( 'Recovery nonce not found' );
			alert( 'Error: Security nonce not found' );
			return;
		}

		// Get all buttons and disable them
		const buttons = form.querySelectorAll( 'button' );
		buttons.forEach( function( btn ) {
			btn.disabled = true;
			btn.style.opacity = '0.6';
		} );

		// Send AJAX request with cache-busting timestamp
		const formData = new FormData();
		formData.append( 'action', 'wpshadow_exit_recovery_ajax' );
		formData.append( 'nonce', nonce );
		formData.append( '_t', Date.now() ); // Cache busting
		if ( clearProblematic ) {
			formData.append( 'clear_problematic', '1' );
		}

		fetch( ajaxurl || '/wp-admin/admin-ajax.php', {
			method: 'POST',
			body: formData,
			credentials: 'same-origin',
			headers: {
				'Cache-Control': 'no-cache, no-store, must-revalidate',
				'Pragma': 'no-cache',
				'Expires': '0'
			}
		} )
			.then( function( response ) {
				return response.json();
			} )
			.then( function( data ) {
				if ( data.success ) {
					// Force a hard reload without cache
					// Remove any recovery-related URL parameters
					const url = new URL( window.location );
					url.searchParams.delete( 'recovery' );
					url.searchParams.delete( 'error' );
					url.searchParams.set( '_reload', Date.now() );
					
					// Use location.replace to prevent back button issues
					window.location.replace( url.toString() );
					
					// Fallback: force reload if replace doesn't work
					setTimeout( function() {
						window.location.reload( true );
					}, 100 );
				} else {
					console.error( 'Failed to exit recovery mode:', data.data?.message );
					// Re-enable buttons
					buttons.forEach( function( btn ) {
						btn.disabled = false;
						btn.style.opacity = '1';
					} );
					alert( 'Error: ' + ( data.data?.message || 'Failed to exit recovery mode' ) );
				}
			} )
			.catch( function( error ) {
				console.error( 'AJAX error:', error );
				// Re-enable buttons
				buttons.forEach( function( btn ) {
					btn.disabled = false;
					btn.style.opacity = '1';
				} );
				alert( 'Error: Network request failed' );
			} );
	}
};
