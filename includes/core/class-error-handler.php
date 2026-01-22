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
		// Add inline script that loads early
		add_action( 'wp_footer', array( __CLASS__, 'add_error_modal_script' ), 1 );
		add_action( 'admin_footer', array( __CLASS__, 'add_error_modal_script' ), 1 );
	}

	/**
	 * Add the modal script and HTML early so functions are available
	 */
	public static function add_error_modal_script(): void {
		?>
		<!-- WPShadow Error Handler Modal -->
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
				<h2 style="margin-top: 0; color: #0073aa;">
					<?php esc_html_e( 'How can WPShadow help?', 'wpshadow' ); ?>
				</h2>
				
				<p style="line-height: 1.6; color: #333;">
					<?php esc_html_e( 'We have two options to help you resolve this error:', 'wpshadow' ); ?>
				</p>
				
				<!-- Option 1: Send Anonymous Report -->
				<div style="margin: 20px 0; padding: 15px; background: #f0f7ff; border-left: 4px solid #0073aa; border-radius: 4px;">
					<h3 style="margin-top: 0; font-size: 16px; color: #0073aa;">
						<?php esc_html_e( '📊 Send Anonymous Report (Recommended)', 'wpshadow' ); ?>
					</h3>
					<p style="font-size: 14px; line-height: 1.5; margin: 10px 0;">
						<?php esc_html_e( 'Send error details to WPShadow for personalized suggestions. We collect:', 'wpshadow' ); ?>
					</p>
					<ul style="font-size: 13px; color: #666; margin: 10px 0; padding-left: 20px;">
						<li><?php esc_html_e( 'Error message and location', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'PHP version and WordPress version', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Active plugins list (names only)', 'wpshadow' ); ?></li>
					</ul>
					<p style="font-size: 12px; color: #666; margin: 10px 0 0 0; font-style: italic;">
						<?php esc_html_e( '✓ No personal data • No site URL • No content • Fully anonymous', 'wpshadow' ); ?>
					</p>
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
						onclick="wpshadowSendReport()"
					>
						<?php esc_html_e( 'Send Report & Get Help', 'wpshadow' ); ?>
					</button>
				</div>
				
				<!-- Option 2: Just Read KB -->
				<div style="margin: 20px 0; padding: 15px; background: #f9f9f9; border-left: 4px solid #999; border-radius: 4px;">
					<h3 style="margin-top: 0; font-size: 16px; color: #333;">
						<?php esc_html_e( '📚 Browse Knowledge Base', 'wpshadow' ); ?>
					</h3>
					<p style="font-size: 14px; line-height: 1.5; margin: 10px 0;">
						<?php esc_html_e( 'Read general troubleshooting guides without sending anything.', 'wpshadow' ); ?>
					</p>
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
					>
						<?php esc_html_e( 'Open Knowledge Base', 'wpshadow' ); ?>
					</a>
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
				>
					<?php esc_html_e( 'Close', 'wpshadow' ); ?>
				</button>
			</div>
		</div>

		<script>
		// Store error data globally so functions can access it
		window.wpshadowErrorData = {
			lastError: null
		};

		// Show modal
		function wpshadowShowHelpModal(errorData) {
			// Store error data if provided
			if (errorData) {
				window.wpshadowErrorData.lastError = errorData;
			}
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
		function wpshadowSendReport() {
			const btn = document.getElementById("wpshadow-send-report-btn");
			btn.disabled = true;
			btn.textContent = "<?php esc_attr_e( 'Sending...', 'wpshadow' ); ?>";
			
			const errorData = window.wpshadowErrorData.lastError || {};
			const formData = new FormData();
			formData.append("action", "wpshadow_send_error_report");
			formData.append("nonce", "<?php echo esc_attr( wp_create_nonce( 'wpshadow_error_report' ) ); ?>");
			formData.append("error_data", JSON.stringify(errorData));
			
			fetch("<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>", {
				method: "POST",
				body: formData
			})
			.then(response => response.json())
			.then(data => {
				if (data.success) {
					btn.textContent = "<?php esc_attr_e( '✓ Report Sent', 'wpshadow' ); ?>";
					btn.style.background = "#46b450";
					
					setTimeout(() => {
						wpshadowCloseModal();
					}, 1500);
				} else {
					btn.textContent = "<?php esc_attr_e( '✗ Error Sending', 'wpshadow' ); ?>";
					btn.style.background = "#dc3232";
					setTimeout(() => {
						btn.disabled = false;
						btn.textContent = "<?php esc_attr_e( 'Send Report & Get Help', 'wpshadow' ); ?>";
						btn.style.background = "#0073aa";
					}, 3000);
				}
			})
			.catch(() => {
				btn.textContent = "<?php esc_attr_e( '✗ Error Sending', 'wpshadow' ); ?>";
				btn.style.background = "#dc3232";
				setTimeout(() => {
					btn.disabled = false;
					btn.textContent = "<?php esc_attr_e( 'Send Report & Get Help', 'wpshadow' ); ?>";
					btn.style.background = "#0073aa";
				}, 3000);
			});
		}

		// Close modal on outside click
		(function() {
			const modal = document.getElementById("wpshadow-modal-overlay");
			if (modal) {
				modal.addEventListener("click", function(e) {
					if (e.target === this) {
						wpshadowCloseModal();
					}
				});
			}
		})();
		</script>
		<?php
	}

	/**
	 * Enhance the error message with WPShadow help options
	 *
	 * @param string $message Error message
	 * @param array $error Error details
	 * @return string Enhanced message
	 */
	public static function enhance_error_message( string $message, array $error ): string {
		// Prepare error data for modal
		$error_data = array(
			'message' => $error['message'] ?? '',
			'file' => basename( $error['file'] ?? '' ),
			'line' => $error['line'] ?? '',
			'type' => $error['type'] ?? '',
			'php_version' => phpversion(),
			'wp_version' => get_bloginfo( 'version' ),
			'active_plugins' => array_keys( get_option( 'active_plugins', array() ) ),
		);

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
				onclick="wpshadowShowHelpModal(' . wp_json_encode( $error_data ) . ')"
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
		// Modal is now always available via add_error_modal_script hook
		return $args;
	}
}
