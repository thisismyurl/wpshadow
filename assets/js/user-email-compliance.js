/**
 * Uncheck user notification email for privacy law compliance
 *
 * When WPShadow compliance settings require the "Send user notification email"
 * checkbox to be unchecked by default, this script handles that on user-new.php
 */

( function() {
	document.addEventListener( 'DOMContentLoaded', function() {
		const notificationCheckbox = document.querySelector( 'input[name="send_user_notification"]' );
		
		if ( notificationCheckbox && notificationCheckbox.type === 'checkbox' ) {
			// Uncheck the notification box for compliance
			notificationCheckbox.checked = false;
			
			// Add a compliance notice
			const sendPasswordDiv = document.querySelector( '.send_password' );
			if ( sendPasswordDiv ) {
				const notice = document.createElement( 'div' );
				notice.className = 'notice notice-info inline';
				notice.style.margin = '10px 0';
				notice.innerHTML = '<p><strong>Privacy Compliance Notice:</strong> The user notification email checkbox is unchecked by default per privacy law requirements (CASL/GDPR/CCPA). Check the box if you have explicit consent to send the email.</p>';
				sendPasswordDiv.parentNode.insertBefore( notice, sendPasswordDiv );
			}
		}
	} );
})();
