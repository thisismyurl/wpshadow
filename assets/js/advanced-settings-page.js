( function() {
	var slider = document.getElementById( 'wpshadow_performance_sample_rate_slider' );
	var display = document.getElementById( 'wpshadow_sample_rate_display' );
	var hiddenInput = document.getElementById( 'wpshadow_performance_sample_rate' );
	var copyButton = document.getElementById( 'wpshadow-copy-system-info' );
	var copyStatus = document.getElementById( 'wpshadow-copy-system-info-status' );

	if ( slider && display && hiddenInput ) {
		slider.addEventListener( 'input', function() {
			var percentage = parseInt( this.value, 10 );
			display.textContent = percentage + '%';
			hiddenInput.value = ( percentage / 100 ).toFixed( 2 );
		} );
	}

	if ( ! copyButton ) {
		return;
	}

	var copyText = function( text ) {
		if ( navigator.clipboard && navigator.clipboard.writeText ) {
			return navigator.clipboard.writeText( text );
		}

		var textarea = document.createElement( 'textarea' );
		textarea.value = text;
		textarea.setAttribute( 'readonly', '' );
		textarea.className = 'wps-advanced-copy-textarea';
		document.body.appendChild( textarea );
		textarea.select();

		try {
			document.execCommand( 'copy' );
			return Promise.resolve();
		} catch ( error ) {
			return Promise.reject( error );
		} finally {
			document.body.removeChild( textarea );
		}
	};

	copyButton.addEventListener( 'click', function() {
		var json = copyButton.getAttribute( 'data-system-info' ) || '{}';
		copyText( json )
			.then( function() {
				if ( copyStatus ) {
					copyStatus.textContent = wpsAdvancedSettingsPage.copiedText;
				}
			} )
			.catch( function() {
				if ( copyStatus ) {
					copyStatus.textContent = wpsAdvancedSettingsPage.copyFailedText;
				}
			} );
	} );
} )();
