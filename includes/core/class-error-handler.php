<?php
/**
 * Error Handler - Enhances WordPress fatal error pages
 *
 * @package WPShadow
 * @subpackage Core
 */

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enhances WordPress fatal error display
 */
class Error_Handler {

	/**
	 * Initialize error handling
	 */
	public static function init(): void {
		// Hook into WordPress fatal error handler
		add_filter( 'wp_php_error_message', array( __CLASS__, 'enhance_error_message' ), 10, 2 );
		add_filter( 'wp_php_error_args', array( __CLASS__, 'enhance_error_args' ), 10, 2 );
	}

	/**
	 * Enhance the error message with WPShadow help options
	 *
	 * @param string $message Error message
	 * @param array $error Error details
	 * @return string Enhanced message
	 */
	public static function enhance_error_message( string $message, array $error ): string {
		// Add WPShadow help button that opens consent modal
		$help_section = '<div style="margin-top: 20px; padding: 15px; background: #f0f7ff; border: 1px solid #0073aa; border-radius: 4px;">' . 
			'<p style="margin: 0 0 10px 0;">' . 
				esc_html__( 'For help resolving this issue, WPShadow can assist:', 'wpshadow' ) . 
			'</p>' .
			'<button 
				id="wpshadow-help-btn" 
				style="
					background: #0073aa; 
					color: white; 
					border: none; 
					padding: 10px 20px; 
					font-size: 14px; 
					border-radius: 3px; 
					cursor: pointer;
					font-weight: 600;
				"
				onclick="wpshadowShowHelpModal()"
			>' .
				esc_html__( 'Get Help with This Error', 'wpshadow' ) . 
			'</button>' .
			'</div>';
		
		$message .= $help_section;
		
		return $message;
	}

	/**
	 * Enhance error args to add WPShadow AI report button
	 *
	 * @param array $args wp_die arguments
	 * @param array $error Error details
	 * @return array Enhanced args
	 */
	public static function enhance_error_args( array $args, array $error ): array {
		// Add custom HTML to include AI report button
		if ( ! empty( $error['type'] ) && in_array( $error['type'], array( E_ERROR, E_PARSE, E_COMPILE_ERROR ), true ) ) {
			$report_button = self::get_report_button( $error );
			
			// Add to the existing message
			if ( isset( $args['additional_errors'] ) && is_array( $args['additional_errors'] ) ) {
				$args['additional_errors'][] = $report_button;
			} else {
				$args['additional_errors'] = array( $report_button );
			}
		}
		
		return $args;
	}

	/**
	 * Get the HTML for the consent modal and error reporting functionality
	 *
	 * @param array $error Error details
	 * @return string Modal HTML and JavaScript
	 */
	private static function get_report_button( array $error ): string {
		$nonce = wp_create_nonce( 'wpshadow_error_report' );
		$error_hash = md5( serialize( $error ) );
		
		$modal_html = '
		<!-- WPShadow Consent Modal -->
		<div id="wpshadow-modal-overlay" style="
			display: none;
			position: fixed;
			top: 0;
			left: 0;
			width: 100%;
			height: 100%;
			background: rgba(0,0,0,0.6);
			z-index: 999999;
			align-items: center;
			justify-content: center;
		">
			<div style="
				background: white;
				padding: 30px;
				border-radius: 8px;
				max-width: 500px;
				width: 90%;
				box-shadow: 0 4px 20px rgba(0,0,0,0.3);
			">
				<h2 style="margin-top: 0; color: #0073aa;">' . 
					esc_html__( 'How can WPShadow help?', 'wpshadow' ) . 
				'</h2>
				
				<p style="line-height: 1.6; color: #333;">' . 
					esc_html__( 'We have two options to help you resolve this error:', 'wpshadow' ) . 
				'</p>
				
				<!-- Option 1: Send Anonymous Report -->
				<div style="margin: 20px 0; padding: 15px; background: #f0f7ff; border-left: 4px solid #0073aa; border-radius: 4px;">
					<h3 style="margin-top: 0; font-size: 16px; color: #0073aa;">' . 
						esc_html__( '📊 Send Anonymous Report (Recommended)', 'wpshadow' ) . 
					'</h3>
					<p style="font-size: 14px; line-height: 1.5; margin: 10px 0;">' . 
						esc_html__( 'Send error details to WPShadow for personalized suggestions. We collect:', 'wpshadow' ) . 
					'</p>
					<ul style="font-size: 13px; color: #666; margin: 10px 0; padding-left: 20px;">
						<li>' . esc_html__( 'Error message and location', 'wpshadow' ) . '</li>
						<li>' . esc_html__( 'PHP version and WordPress version', 'wpshadow' ) . '</li>
						<li>' . esc_html__( 'Active plugins list (names only)', 'wpshadow' ) . '</li>
					</ul>
					<p style="font-size: 12px; color: #666; margin: 10px 0 0 0; font-style: italic;">' . 
						esc_html__( '✓ No personal data • No site URL • No content • Fully anonymous', 'wpshadow' ) . 
					'</p>
					<button 
						id="wpshadow-send-report-btn"
						style="
							background: #0073aa; 
							color: white; 
							border: none; 
							padding: 12px 24px; 
							font-size: 14px; 
							border-radius: 4px; 
							cursor: pointer;
							font-weight: 600;
							width: 100%;
							margin-top: 10px;
						"
						onclick="wpshadowSendReport(\'' . esc_js( $error_hash ) . '\', \'' . esc_js( $nonce ) . '\')"
					>' .
						esc_html__( 'Send Report & Get Help', 'wpshadow' ) . 
					'</button>
				</div>
				
				<!-- Option 2: Just Read KB -->
				<div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #999; border-radius: 4px;">
					<h3 style="margin-top: 0; font-size: 16px; color: #333;">' . 
						esc_html__( '📚 Browse Knowledge Base', 'wpshadow' ) . 
					'</h3>
					<p style="font-size: 14px; line-height: 1.5; margin: 10px 0;">' . 
						esc_html__( "Read general troubleshooting guides without sending anything.", 'wpshadow' ) . 
					'</p>
					<a 
						href="https://wpshadow.com/kb/fatal-errors" 
						target="_blank" 
						rel="noopener"
						style="
							display: inline-block;
							background: #666; 
							color: white; 
							border: none; 
							padding: 12px 24px; 
							font-size: 14px; 
							border-radius: 4px; 
							cursor: pointer;
							text-decoration: none;
							width: 100%;
							text-align: center;
							box-sizing: border-box;
							margin-top: 10px;
						"
					>' .
						esc_html__( 'Open Knowledge Base', 'wpshadow' ) . 
					'</a>
				</div>
				
				<!-- Close button -->
				<button 
					onclick="wpshadowCloseModal()"
					style="
						background: transparent;
						border: 1px solid #ccc;
						padding: 10px 20px;
						font-size: 14px;
						border-radius: 4px;
						cursor: pointer;
						width: 100%;
						margin-top: 10px;
					"
				>' .
					esc_html__( 'Close', 'wpshadow' ) . 
				'</button>
			</div>
		</div>
		
		<!-- Success message container (hidden initially) -->
		<div id="wpshadow-success-message" style="display: none; margin-top: 20px;"></div>
		
		<script>
		// Show modal
		function wpshadowShowHelpModal() {
			const modal = document.getElementById("wpshadow-modal-overlay");
			if (modal) {
				modal.style.display = "flex";
			}
		}
		
		// Close modal
		function wpshadowCloseModal() {
			const modal = document.getElementById("wpshadow-modal-overlay");
			if (modal) {
				modal.style.display = "none";
			}
		}
		
		// Send anonymous report
		function wpshadowSendReport(errorHash, nonce) {
			const btn = document.getElementById("wpshadow-send-report-btn");
			btn.disabled = true;
			btn.textContent = "' . esc_js( __( 'Sending...', 'wpshadow' ) ) . '";
			
			// Collect error data
			const formData = new FormData();
			formData.append("action", "wpshadow_send_error_report");
			formData.append("error_hash", errorHash);
			formData.append("nonce", nonce);
			formData.append("error_data", JSON.stringify({
				message: "' . esc_js( $error['message'] ?? '' ) . '",
				file: "' . esc_js( basename( $error['file'] ?? '' ) ) . '", // Only filename, not full path
				line: "' . esc_js( $error['line'] ?? '' ) . '",
				type: "' . esc_js( $error['type'] ?? '' ) . '",
				php_version: "' . esc_js( phpversion() ) . '",
				wp_version: "' . esc_js( get_bloginfo( 'version' ) ) . '",
				active_plugins: ' . wp_json_encode( array_keys( get_option( 'active_plugins', array() ) ) ) . '
			}));
			
			fetch(ajaxurl || "/wp-admin/admin-ajax.php", {
				method: "POST",
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					btn.textContent = "' . esc_js( __( '✓ Report Sent', 'wpshadow' ) ) . '";
					btn.style.background = "#46b450";
					
					// Close modal
					wpshadowCloseModal();
					
					// Show success message with suggestions
					const successDiv = document.getElementById("wpshadow-success-message");
					if (successDiv) {
						let html = "<div style=\"padding: 15px; background: #d4edda; border: 1px solid #46b450; border-radius: 4px;\">";
						html += "<h4 style=\"margin-top: 0; color: #155724;\">' . 
							esc_js( __( '✓ Report Sent Successfully', 'wpshadow' ) ) . 
						'</h4>";
						html += "<p style=\"margin: 10px 0; color: #155724;\">' . 
							esc_js( __( 'Thank you! We\'re analyzing this error and will add solutions to our Knowledge Base.', 'wpshadow' ) ) . 
						'</p>";
						
						// Show suggestions if available
						if (data.data && data.data.suggestions) {
							html += "<div style=\"margin-top: 15px; padding: 15px; background: white; border-left: 4px solid #0073aa; border-radius: 4px;\">";
							html += "<h5 style=\"margin-top: 0; color: #0073aa;\">' . 
								esc_js( __( 'Immediate Suggestions:', 'wpshadow' ) ) . 
							'</h5>";
							html += data.data.suggestions;
							html += "</div>";
						}
						
						html += "</div>";
						successDiv.innerHTML = html;
						successDiv.style.display = "block";
					}
				} else {
					btn.textContent = "' . esc_js( __( '✗ Error Sending', 'wpshadow' ) ) . '";
					btn.style.background = "#dc3232";
					setTimeout(() => {
						btn.disabled = false;
						btn.textContent = "' . esc_js( __( 'Send Report & Get Help', 'wpshadow' ) ) . '";
						btn.style.background = "#0073aa";
					}, 3000);
				}
			})
			.catch(() => {
				btn.textContent = "' . esc_js( __( '✗ Error Sending', 'wpshadow' ) ) . '";
				btn.style.background = "#dc3232";
				setTimeout(() => {
					btn.disabled = false;
					btn.textContent = "' . esc_js( __( 'Send Report & Get Help', 'wpshadow' ) ) . '";
					btn.style.background = "#0073aa";
				}, 3000);
			});
		}
		
		// Close modal on outside click
		document.getElementById("wpshadow-modal-overlay")?.addEventListener("click", function(e) {
			if (e.target === this) {
				wpshadowCloseModal();
			}
		});
		</script>
		';
		
		return $modal_html;
	}
}
