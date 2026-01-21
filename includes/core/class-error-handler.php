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
	 * Enhance the error message with WPShadow KB link
	 *
	 * @param string $message Error message
	 * @param array $error Error details
	 * @return string Enhanced message
	 */
	public static function enhance_error_message( string $message, array $error ): string {
		// Add WPShadow KB link after the default message
		$kb_link = '<p style="margin-top: 20px;">' . 
			sprintf(
				__( 'For help resolving this issue, check the %s.', 'wpshadow' ),
				'<a href="https://wpshadow.com/kb/fatal-errors" target="_blank" rel="noopener">WPShadow Knowledge Base</a>'
			) . 
			'</p>';
		
		$message .= $kb_link;
		
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
	 * Get the HTML for the AI report button
	 *
	 * @param array $error Error details
	 * @return string Button HTML
	 */
	private static function get_report_button( array $error ): string {
		$nonce = wp_create_nonce( 'wpshadow_error_report' );
		$error_hash = md5( serialize( $error ) );
		
		$button_html = '
		<div style="margin: 20px 0; padding: 15px; background: #f0f7ff; border: 1px solid #0073aa; border-radius: 4px;">
			<p style="margin: 0 0 10px 0; font-weight: bold;">' . 
				esc_html__( 'Get immediate help from WPShadow AI', 'wpshadow' ) . 
			'</p>
			<button 
				id="wpshadow-ai-report-btn" 
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
				onclick="wpshadowSendErrorReport(\'' . esc_js( $error_hash ) . '\', \'' . esc_js( $nonce ) . '\')"
			>
				' . esc_html__( 'Send Anonymous Report to WPShadow AI for Immediate Suggestions', 'wpshadow' ) . '
			</button>
			<p style="margin: 10px 0 0 0; font-size: 12px; color: #666;">' . 
				esc_html__( 'Your report will be sent anonymously to help you resolve this error faster.', 'wpshadow' ) . 
			'</p>
		</div>
		
		<script>
		function wpshadowSendErrorReport(errorHash, nonce) {
			const btn = document.getElementById("wpshadow-ai-report-btn");
			btn.disabled = true;
			btn.textContent = "' . esc_js( __( 'Sending...', 'wpshadow' ) ) . '";
			
			// Send error report via AJAX
			const formData = new FormData();
			formData.append("action", "wpshadow_send_error_report");
			formData.append("error_hash", errorHash);
			formData.append("nonce", nonce);
			formData.append("error_data", JSON.stringify({
				message: "' . esc_js( $error['message'] ?? '' ) . '",
				file: "' . esc_js( $error['file'] ?? '' ) . '",
				line: "' . esc_js( $error['line'] ?? '' ) . '",
				type: "' . esc_js( $error['type'] ?? '' ) . '"
			}));
			
			fetch(ajaxurl || "/wp-admin/admin-ajax.php", {
				method: "POST",
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					btn.textContent = "' . esc_js( __( '✓ Report Sent Successfully', 'wpshadow' ) ) . '";
					btn.style.background = "#46b450";
					
					// Show suggestions if available
					if (data.data && data.data.suggestions) {
						const suggestionsDiv = document.createElement("div");
						suggestionsDiv.style.marginTop = "15px";
						suggestionsDiv.style.padding = "15px";
						suggestionsDiv.style.background = "#fff";
						suggestionsDiv.style.border = "1px solid #46b450";
						suggestionsDiv.style.borderRadius = "4px";
						suggestionsDiv.innerHTML = "<h4 style=\"margin-top:0\">' . 
							esc_js( __( 'WPShadow AI Suggestions:', 'wpshadow' ) ) . 
						'</h4>" + data.data.suggestions;
						btn.parentElement.appendChild(suggestionsDiv);
					}
				} else {
					btn.textContent = "' . esc_js( __( 'Error Sending Report', 'wpshadow' ) ) . '";
					btn.style.background = "#dc3232";
				}
			})
			.catch(() => {
				btn.textContent = "' . esc_js( __( 'Error Sending Report', 'wpshadow' ) ) . '";
				btn.style.background = "#dc3232";
			});
		}
		</script>
		';
		
		return $button_html;
	}
}
