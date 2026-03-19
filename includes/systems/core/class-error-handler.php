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
	 *
	 * Why do programmers prefer dark mode? Because light attracts bugs!
	 */
	public static function init(): void {
		self::enable_screen_error_output();

		// Hook into WordPress PHP error handler
		add_filter( 'wp_php_error_message', array( __CLASS__, 'enhance_error_message' ), 10, 2 );
		add_filter( 'wp_php_error_args', array( __CLASS__, 'enhance_error_args' ), 10, 2 );
		// Only add error modal script when there's an actual error
		// (removed from wp_footer/admin_footer as it was showing on ALL pages)
	}

	/**
	 * Ensure errors show on screen and are logged.
	 *
	 * @since 1.6093.1200
	 * @return void
	 */
	private static function enable_screen_error_output(): void {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return;
		}

		@ini_set( 'display_errors', '1' );
		@ini_set( 'display_startup_errors', '1' );
		@ini_set( 'log_errors', '1' );
		@ini_set( 'html_errors', '1' );
		error_reporting( E_ALL );

		if ( ! defined( 'WP_DEBUG_LOG' ) || true === WP_DEBUG_LOG ) {
			if ( defined( 'WP_CONTENT_DIR' ) ) {
				@ini_set( 'error_log', trailingslashit( WP_CONTENT_DIR ) . 'debug.log' );
			}
		}
	}

	/**
	 * Add the modal script and HTML early so functions are available
	 */
	public static function add_error_modal_script(): void {
		?>
		<!-- WPShadow Error Handler Modal -->
		<div id="wpshadow-modal-overlay" class="wpshadow-modal-overlay wps-none" role="dialog" aria-modal="true" aria-labelledby="wpshadow-error-modal-title" aria-hidden="true" data-wpshadow-modal="static" data-overlay-close="true" data-esc-close="true">
			<div class="wpshadow-modal wps-p-30-rounded-8" role="document">
				<h2 id="wpshadow-error-modal-title" style="margin-top: 0; color: #0073aa;">
					<?php esc_html_e( 'How can WPShadow help?', 'wpshadow' ); ?>
				</h2>
				
				<p style="line-height:1.0; color: #333;">
					<?php esc_html_e( 'We have two options to help you resolve this error:', 'wpshadow' ); ?>
				</p>
				
				<!-- Option 1: Send Anonymous Report -->
				<div class="wps-m-20-p-15-rounded-4">
					<h3 style="margin-top: 0; font-size: 16px; color: #0073aa;">
						<?php esc_html_e( '📊 Send Anonymous Report (Recommended)', 'wpshadow' ); ?>
					</h3>
					<p class="wps-m-10">
						<?php esc_html_e( 'Send error details to WPShadow for personalized suggestions. We collect:', 'wpshadow' ); ?>
					</p>
					<ul class="wps-m-10">
						<li><?php esc_html_e( 'Error message and location', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'PHP version and WordPress version', 'wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Active plugins list (names only)', 'wpshadow' ); ?></li>
					</ul>
					<p class="wps-m-10">
						<?php esc_html_e( '✓ No personal data • No site URL • No content • Fully anonymous', 'wpshadow' ); ?>
					</p>
					<button 
						id="wpshadow-send-report-btn"
						class="wps-p-12-rounded-4"
						onclick="wpshadowSendReport()"
					>
						<?php esc_html_e( 'Send Report & Get Help', 'wpshadow' ); ?>
					</button>
				</div>
				
				<!-- Option 2: Just Read KB -->
				<div class="wps-m-20-p-15-rounded-4">
					<h3 style="margin-top: 0; font-size: 16px; color: #333;">
						<?php esc_html_e( '📚 Browse Knowledge Base', 'wpshadow' ); ?>
					</h3>
					<p class="wps-m-10">
						<?php esc_html_e( 'Read general troubleshooting guides without sending anything.', 'wpshadow' ); ?>
					</p>
					<a 
					href="<?php echo esc_url( \WPShadow\Core\UTM_Link_Manager::kb_link( 'fatal-errors', 'error-handler' ) ); ?>" 
				
				<!-- Close button -->
				<button 
					onclick="wpshadowCloseModal()"
					class="wps-p-10-rounded-4"
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
			if (window.WPShadowModal && typeof window.WPShadowModal.openStatic === 'function') {
				window.WPShadowModal.openStatic('wpshadow-modal-overlay', { returnFocus: document.activeElement });
				return;
			}
			const modal = document.getElementById("wpshadow-modal-overlay");
			if (modal) {
				modal.classList.remove('wps-none');
				modal.style.display = "flex";
				modal.setAttribute('aria-hidden', 'false');
			}
		}

		// Close modal
		function wpshadowCloseModal() {
			if (window.WPShadowModal && typeof window.WPShadowModal.closeStatic === 'function') {
				window.WPShadowModal.closeStatic('wpshadow-modal-overlay');
				return;
			}
			const modal = document.getElementById("wpshadow-modal-overlay");
			if (modal) {
				modal.style.display = "none";
				modal.setAttribute('aria-hidden', 'true');
				modal.classList.add('wps-none');
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
			'message'        => $error['message'] ?? '',
			'file'           => basename( $error['file'] ?? '' ),
			'line'           => $error['line'] ?? '',
			'type'           => $error['type'] ?? '',
			'php_version'    => phpversion(),
			'wp_version'     => get_bloginfo( 'version' ),
			'active_plugins' => array_keys( get_option( 'active_plugins', array() ) ),
		);

		// Add WPShadow help button and modal (only shown on actual errors)
		$help_section = '<div class="wps-p-15-rounded-4">' .
			'<p class="wps-m-0">' .
				esc_html__( 'For help resolving this issue, WPShadow can assist:', 'wpshadow' ) .
			'</p>' .
			'<button 
				id="wpshadow-help-btn" 
				class="wps-p-10-rounded-3"
				onclick="wpshadowShowHelpModal(' . wp_json_encode( $error_data ) . ')"
			>' .
				esc_html__( 'Get Help with This Error', 'wpshadow' ) .
			'</button>' .
			'</div>';

		$message .= $help_section;

		// Add modal HTML and scripts (only when error occurs)
		ob_start();
		self::add_error_modal_script();
		$message .= ob_get_clean();

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
