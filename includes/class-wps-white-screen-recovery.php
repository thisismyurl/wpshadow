<?php
/**
 * White Screen Auto-Recovery - Automatic detection and recovery from fatal errors.
 *
 * Implements emergency toolkit functionality for handling white screen of death (WSoD)
 * situations by detecting fatal errors, identifying problematic plugins, and
 * automatically attempting recovery.
 *
 * @package WPSHADOW_wpshadow_THISISMYURL
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * White Screen Recovery Manager
 *
 * Handles automatic detection and recovery from fatal errors that cause
 * white screen of death (WSoD) situations.
 */
class WPSHADOW_White_Screen_Recovery {

	/**
	 * Recovery attempts option key.
	 */
	private const RECOVERY_ATTEMPTS_KEY = 'wpshadow_recovery_attempts';

	/**
	 * Recovery mode option key.
	 */
	private const RECOVERY_MODE_KEY = 'wpshadow_recovery_mode_active';

	/**
	 * Problematic plugins option key.
	 */
	private const PROBLEMATIC_PLUGINS_KEY = 'wpshadow_problematic_plugins';

	/**
	 * Maximum auto-recovery attempts before giving up.
	 */
	private const MAX_RECOVERY_ATTEMPTS = 3;

	/**
	 * Recovery endpoint slug.
	 */
	private const RECOVERY_ENDPOINT = 'wps-recovery';

	/**
	 * Initialize White Screen Recovery system.
	 *
	 * @return void
	 */
	public static function init(): void {
		// Register recovery mode handler early.
		add_action( 'muplugins_loaded', array( __CLASS__, 'handle_recovery_mode' ), 1 );

		// Register fatal error handler with higher priority.
		register_shutdown_function( array( __CLASS__, 'handle_fatal_error' ), 0 );

		// Add recovery endpoint for safe recovery.
		add_action( 'init', array( __CLASS__, 'register_recovery_endpoint' ) );

		// Add recovery admin notice.
		add_action( 'admin_notices', array( __CLASS__, 'display_recovery_notice' ) );

		// Add recovery metabox to emergency support dashboard.
		add_action( 'wpshadow_emergency_metaboxes', array( __CLASS__, 'register_recovery_metabox' ) );

		// Enqueue recovery mode script on emergency dashboard.
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_recovery_scripts' ) );

		// Handle AJAX exit recovery.
		add_action( 'wp_ajax_wpshadow_exit_recovery_ajax', array( __CLASS__, 'handle_exit_recovery_ajax' ) );
	}

	/**
	 * Enqueue recovery mode JavaScript.
	 *
	 * @return void
	 */
	public static function enqueue_recovery_scripts(): void {
		// Only enqueue if recovery mode is active (needed on all admin pages for the notice button)
		if ( ! self::is_recovery_mode_active() ) {
			return;
		}

		wp_enqueue_script(
			'wpshadow-recovery-mode',
			WPSHADOW_URL . 'assets/js/recovery-mode.js',
			array(),
			WPSHADOW_VERSION,
			false // Load in header so onclick handlers work on page load
		);
	}

	/**
	 * AJAX handler for exiting recovery mode without page reload.
	 *
	 * @return void
	 */
	public static function handle_exit_recovery_ajax(): void {
		// Disable caching for this request
		header( 'Cache-Control: no-cache, no-store, must-revalidate, private' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		// Verify nonce
		check_ajax_referer( 'wpshadow_exit_recovery', 'nonce' );

		// Verify capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( array( 'message' => esc_html__( 'Insufficient permissions.', 'plugin-wpshadow' ) ) );
		}

		// Clear problematic plugins list if requested
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST['clear_problematic'] ) ) {
			delete_option( self::PROBLEMATIC_PLUGINS_KEY );
		}

		// Deactivate recovery mode
		self::deactivate_recovery_mode();

		// Clear any relevant transients
		delete_transient( 'wpshadow_recovery_status' );
		delete_transient( 'wpshadow_fatal_error_needs_manual_recovery' );

		// Clear object cache if available
		if ( function_exists( 'wp_cache_flush' ) ) {
			wp_cache_flush();
		}

		wp_send_json_success( array( 
			'message' => esc_html__( 'Recovery mode exited successfully.', 'plugin-wpshadow' ),
			'timestamp' => current_time( 'mysql' ),
		) );
	}

	/**
	 * Handle recovery mode if activated.
	 *
	 * Recovery mode disables all plugins except WPShadow to allow
	 * safe access to the admin area.
	 *
	 * @return void
	 */
	public static function handle_recovery_mode(): void {
		if ( ! get_option( self::RECOVERY_MODE_KEY, false ) ) {
			return;
		}

		// In recovery mode, we filter active plugins to only include WPShadow.
		add_filter( 'option_active_plugins', array( __CLASS__, 'filter_plugins_in_recovery_mode' ) );

		// Network-wide recovery for multisite.
		if ( is_multisite() ) {
			add_filter( 'site_option_active_sitewide_plugins', array( __CLASS__, 'filter_network_plugins_in_recovery_mode' ) );
		}
	}

	/**
	 * Filter active plugins in recovery mode.
	 *
	 * @param array $plugins Active plugins.
	 * @return array Filtered plugins (only WPShadow).
	 */
	public static function filter_plugins_in_recovery_mode( $plugins ): array {
		if ( ! is_array( $plugins ) ) {
			return array();
		}

		// Only keep WPShadow plugin active.
		$wpshadow_plugin = WPSHADOW_BASENAME;
		return array_filter(
			$plugins,
			function ( $plugin ) use ( $wpshadow_plugin ) {
				return $plugin === $wpshadow_plugin;
			}
		);
	}

	/**
	 * Filter network plugins in recovery mode for multisite.
	 *
	 * @param array $plugins Active network plugins.
	 * @return array Filtered plugins (only WPShadow).
	 */
	public static function filter_network_plugins_in_recovery_mode( $plugins ): array {
		if ( ! is_array( $plugins ) ) {
			return array();
		}

		// Only keep WPShadow plugin active.
		$wpshadow_plugin = WPSHADOW_BASENAME;
		$filtered          = array();

		foreach ( $plugins as $plugin => $time ) {
			if ( $plugin === $wpshadow_plugin ) {
				$filtered[ $plugin ] = $time;
			}
		}

		return $filtered;
	}

	/**
	 * Handle fatal PHP errors and attempt recovery.
	 *
	 * @return void
	 */
	public static function handle_fatal_error(): void {
		$error = error_get_last();

		// Only handle fatal errors.
		if ( ! $error || ! ( $error['type'] & ( E_ERROR | E_PARSE | E_COMPILE_ERROR | E_CORE_ERROR | E_USER_ERROR ) ) ) {
			return;
		}

		// Get current recovery attempts.
		$attempts = get_option( self::RECOVERY_ATTEMPTS_KEY, 0 );

		// Log the error.
		self::log_fatal_error( $error );

		// If we've exceeded max attempts, don't auto-recover.
		if ( $attempts >= self::MAX_RECOVERY_ATTEMPTS ) {
			// Store error for manual recovery.
			set_transient( 'wpshadow_fatal_error_needs_manual_recovery', $error, DAY_IN_SECONDS );
			return;
		}

		// Try to identify the problematic plugin.
		$problematic_plugin = self::identify_problematic_plugin( $error );

		if ( $problematic_plugin ) {
			// Increment recovery attempts.
			update_option( self::RECOVERY_ATTEMPTS_KEY, $attempts + 1 );

			// Deactivate the problematic plugin.
			self::deactivate_plugin( $problematic_plugin );

			// Log recovery attempt.
			self::log_recovery_attempt( $error, $problematic_plugin, $attempts + 1 );

			// Store information for admin notice.
			set_transient(
				'wpshadow_auto_recovery_performed',
				array(
					'plugin'  => $problematic_plugin,
					'error'   => $error,
					'attempt' => $attempts + 1,
				),
				HOUR_IN_SECONDS
			);
		} else {
			// Can't identify plugin, activate recovery mode.
			self::activate_recovery_mode();

			// Store error for display.
			set_transient( 'wpshadow_fatal_error_recovery_mode', $error, HOUR_IN_SECONDS );
		}
	}

	/**
	 * Identify the problematic plugin from error information.
	 *
	 * @param array $error Error details.
	 * @return string|null Plugin basename or null if not identified.
	 */
	private static function identify_problematic_plugin( array $error ): ?string {
		$file = $error['file'] ?? '';

		if ( empty( $file ) ) {
			return null;
		}

		// Get WordPress plugin directory path.
		$plugin_dir = WP_PLUGIN_DIR;

		// Check if error is in a plugin file.
		if ( strpos( $file, $plugin_dir ) === false ) {
			return null;
		}

		// Extract plugin directory from path.
		$relative_path = str_replace( trailingslashit( $plugin_dir ), '', $file );
		$parts         = explode( '/', $relative_path );

		if ( empty( $parts[0] ) ) {
			return null;
		}

		$plugin_slug = $parts[0];

		// Find the actual plugin file.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			if ( strpos( $plugin_file, $plugin_slug . '/' ) === 0 ) {
				// Don't deactivate WPShadow itself.
				if ( $plugin_file === WPSHADOW_BASENAME ) {
					return null;
				}
				return $plugin_file;
			}
		}

		return null;
	}

	/**
	 * Deactivate a plugin.
	 *
	 * @param string $plugin Plugin basename.
	 * @return void
	 */
	private static function deactivate_plugin( string $plugin ): void {
		if ( ! function_exists( 'deactivate_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		deactivate_plugins( $plugin, true );

		// Mark as problematic.
		$problematic = get_option( self::PROBLEMATIC_PLUGINS_KEY, array() );

		if ( ! in_array( $plugin, $problematic, true ) ) {
			$problematic[] = $plugin;
			update_option( self::PROBLEMATIC_PLUGINS_KEY, $problematic );
		}

		// Log to error log.
	}

	/**
	 * Activate recovery mode.
	 *
	 * @return void
	 */
	private static function activate_recovery_mode(): void {
		update_option( self::RECOVERY_MODE_KEY, true );
	}

	/**
	 * Deactivate recovery mode.
	 *
	 * @return void
	 */
	public static function deactivate_recovery_mode(): void {
		delete_option( self::RECOVERY_MODE_KEY );
		delete_option( self::RECOVERY_ATTEMPTS_KEY );
	}

	/**
	 * Check if recovery mode is active.
	 *
	 * @return bool
	 */
	public static function is_recovery_mode_active(): bool {
		return (bool) get_option( self::RECOVERY_MODE_KEY, false );
	}

	/**
	 * Log fatal error to database.
	 *
	 * @param array $error Error details.
	 * @return void
	 */
	private static function log_fatal_error( array $error ): void {
		// Use existing emergency support logging.
		if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log(
				'error',
				sprintf(
					'Fatal error: %s in %s:%d',
					$error['message'] ?? 'Unknown error',
					$error['file'] ?? 'Unknown file',
					$error['line'] ?? 0
				),
				array(
					'error_type'    => $error['type'] ?? 0,
					'error_message' => $error['message'] ?? '',
					'error_file'    => $error['file'] ?? '',
					'error_line'    => $error['line'] ?? 0,
				)
			);
		}
	}

	/**
	 * Log recovery attempt.
	 *
	 * @param array  $error Error details.
	 * @param string $plugin Plugin that was deactivated.
	 * @param int    $attempt Attempt number.
	 * @return void
	 */
	private static function log_recovery_attempt( array $error, string $plugin, int $attempt ): void {
		if ( class_exists( '\\WPShadow\\WPSHADOW_Activity_Logger' ) ) {
			WPSHADOW_Activity_Logger::log(
				'info',
				sprintf(
					'Auto-recovery attempt #%d: Deactivated plugin %s',
					$attempt,
					$plugin
				),
				array(
					'plugin'  => $plugin,
					'attempt' => $attempt,
					'error'   => $error,
				)
			);
		}
	}

	/**
	 * Register recovery endpoint.
	 *
	 * @return void
	 */
	public static function register_recovery_endpoint(): void {
		// Check if we're on the recovery endpoint.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_GET[ self::RECOVERY_ENDPOINT ] ) ) {
			self::handle_recovery_endpoint();
		}
	}

	/**
	 * Handle recovery endpoint request.
	 *
	 * @return void
	 */
	private static function handle_recovery_endpoint(): void {
		// Verify nonce if provided.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : '';

		if ( ! empty( $nonce ) && ! wp_verify_nonce( $nonce, 'wpshadow_recovery' ) ) {
			wp_die( esc_html__( 'Invalid recovery link.', 'plugin-wpshadow' ) );
		}

		// Activate recovery mode.
		self::activate_recovery_mode();

		// Redirect to admin.
		wp_safe_redirect( admin_url( 'admin.php?page=wps-emergency-support' ) );
		exit;
	}

	/**
	 * Display recovery notice in admin.
	 *
	 * @return void
	 */
	public static function display_recovery_notice(): void {
		// Check for auto-recovery.
		$recovery = get_transient( 'wpshadow_auto_recovery_performed' );

		if ( $recovery ) {
			delete_transient( 'wpshadow_auto_recovery_performed' );

			$plugin_name = $recovery['plugin'] ?? __( 'Unknown Plugin', 'plugin-wpshadow' );
			$attempt     = $recovery['attempt'] ?? 1;

			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'WPShadow Auto-Recovery:', 'plugin-wpshadow' ); ?></strong>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: Plugin name, 2: Attempt number */
							__( 'A fatal error was detected and plugin "%1$s" was automatically deactivated (attempt #%2$d). Please review the error details and contact the plugin author.', 'plugin-wpshadow' ),
							$plugin_name,
							$attempt
						)
					);
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ); ?>" class="button">
						<?php esc_html_e( 'View Error Details', 'plugin-wpshadow' ); ?>
					</a>
				</p>
			</div>
			<?php
		}

		// Check for recovery mode.
		if ( self::is_recovery_mode_active() ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( '🚨 Recovery Mode Active', 'plugin-wpshadow' ); ?></strong>
				</p>
				<p>
					<?php
					printf(
						esc_html__( 'All plugins except WPShadow have been temporarily disabled due to a critical error. Please review the error details in the %s and fix the issue before exiting recovery mode.', 'plugin-wpshadow' ),
						'<a href="' . esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ) . '">' . esc_html__( 'Emergency Dashboard', 'plugin-wpshadow' ) . '</a>'
					);
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Emergency Dashboard', 'plugin-wpshadow' ); ?>
					</a>
					<form id="wpshadow-exit-recovery-notice-form" style="display: inline;">
						<?php wp_nonce_field( 'wpshadow_exit_recovery', 'wpshadow_recovery_nonce', false ); ?>
						<button type="button" class="button" onclick="WPShadowRecovery.exitRecoveryMode()">
							<?php esc_html_e( 'Exit Recovery Mode', 'plugin-wpshadow' ); ?>
						</button>
					</form>
				</p>
			</div>
			<?php
		}

		// Check for manual recovery needed.
		$manual_recovery = get_transient( 'wpshadow_fatal_error_needs_manual_recovery' );

		if ( $manual_recovery ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( '🚨 Critical Error - Manual Recovery Required', 'plugin-wpshadow' ); ?></strong>
				</p>
				<p>
					<?php esc_html_e( 'Multiple recovery attempts have been made. Manual intervention is required to fix this issue.', 'plugin-wpshadow' ); ?>
				</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'View Details & Get Help', 'plugin-wpshadow' ); ?>
					</a>
				</p>
			</div>
			<?php
		}
	}

	/**
	 * Register recovery metabox for emergency dashboard.
	 *
	 * @return void
	 */
	public static function register_recovery_metabox(): void {
		add_meta_box(
			'wpshadow_recovery_status',
			__( 'Auto-Recovery Status', 'plugin-wpshadow' ),
			array( __CLASS__, 'render_recovery_metabox' ),
			'wps-emergency-support',
			'normal',
			'high'
		);
	}

	/**
	 * Render recovery metabox.
	 *
	 * @return void
	 */
	public static function render_recovery_metabox(): void {
		$recovery_mode = self::is_recovery_mode_active();
		$attempts      = get_option( self::RECOVERY_ATTEMPTS_KEY, 0 );
		$problematic   = get_option( self::PROBLEMATIC_PLUGINS_KEY, array() );

		?>
		<div class="wps-recovery-status">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Recovery Mode:', 'plugin-wpshadow' ); ?></th>
						<td>
							<?php if ( $recovery_mode ) : ?>
								<span style="color: #c00; font-weight: bold;">⚠️ <?php esc_html_e( 'ACTIVE', 'plugin-wpshadow' ); ?></span>
							<?php else : ?>
								<span style="color: #090;">✓ <?php esc_html_e( 'Inactive', 'plugin-wpshadow' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Recovery Attempts:', 'plugin-wpshadow' ); ?></th>
						<td>
							<?php echo esc_html( $attempts ); ?> / <?php echo esc_html( self::MAX_RECOVERY_ATTEMPTS ); ?>
						</td>
					</tr>
					<?php if ( ! empty( $problematic ) ) : ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Problematic Plugins:', 'plugin-wpshadow' ); ?></th>
						<td>
							<ul style="list-style: disc; padding-left: 20px;">
								<?php foreach ( $problematic as $plugin ) : ?>
									<li><code><?php echo esc_html( $plugin ); ?></code></li>
								<?php endforeach; ?>
							</ul>
							<p class="description">
								<?php esc_html_e( 'These plugins were automatically deactivated due to fatal errors.', 'plugin-wpshadow' ); ?>
							</p>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $recovery_mode ) : ?>
				<div style="margin-top: 20px; padding: 15px; background: #fee; border-left: 4px solid #c00;">
					<h4 style="margin-top: 0;"><?php esc_html_e( 'Recovery Mode Instructions', 'plugin-wpshadow' ); ?></h4>
					<ol>
						<li><?php esc_html_e( 'Review the error details above', 'plugin-wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Fix the problematic plugin or remove it', 'plugin-wpshadow' ); ?></li>
						<li><?php esc_html_e( 'Exit recovery mode to restore normal operation', 'plugin-wpshadow' ); ?></li>
					</ol>
					<form id="wpshadow-exit-recovery-form" method="post">
						<?php wp_nonce_field( 'wpshadow_exit_recovery', 'wpshadow_recovery_nonce' ); ?>
						<input type="hidden" name="action" value="exit_recovery" />
						<button type="button" class="button button-primary" onclick="WPShadowRecovery.exitRecoveryMode()">
							<?php esc_html_e( 'Exit Recovery Mode', 'plugin-wpshadow' ); ?>
						</button>
						<button type="button" class="button" onclick="WPShadowRecovery.exitRecoveryMode( true )">
							<?php esc_html_e( 'Clear Problematic Plugins List', 'plugin-wpshadow' ); ?>
						</button>
					</form>
				</div>
			<?php else : ?>
				<div style="margin-top: 20px;">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ); ?>">
						<?php wp_nonce_field( 'wpshadow_manual_recovery', 'wpshadow_recovery_nonce' ); ?>
						<input type="hidden" name="action" value="activate_recovery" />
						<button type="submit" class="button">
							<?php esc_html_e( 'Manually Activate Recovery Mode', 'plugin-wpshadow' ); ?>
						</button>
						<?php if ( $attempts > 0 ) : ?>
							<button type="submit" name="reset_attempts" value="1" class="button">
								<?php esc_html_e( 'Reset Recovery Counter', 'plugin-wpshadow' ); ?>
							</button>
						<?php endif; ?>
					</form>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Handle recovery actions from emergency dashboard.
	 *
	 * @return void
	 */
	public static function handle_recovery_actions(): void {
		// Check for exit recovery action.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST['action'] ) && 'exit_recovery' === $_POST['action'] ) {
			check_admin_referer( 'wpshadow_exit_recovery', 'wpshadow_recovery_nonce' );

			// Clear problematic plugins list if requested.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_POST['clear_problematic'] ) ) {
				delete_option( self::PROBLEMATIC_PLUGINS_KEY );
			}

			self::deactivate_recovery_mode();

			// Stay on the current page - no redirect
			return;
		}

		// Check for activate recovery action.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST['action'] ) && 'activate_recovery' === $_POST['action'] ) {
			check_admin_referer( 'wpshadow_manual_recovery', 'wpshadow_recovery_nonce' );

			// Reset attempts if requested.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_POST['reset_attempts'] ) ) {
				delete_option( self::RECOVERY_ATTEMPTS_KEY );
			}

			self::activate_recovery_mode();

			wp_safe_redirect( admin_url( 'admin.php?page=wps-emergency-support&recovery=activated' ) );
			exit;
		}
	}

	/**
	 * Get recovery status summary.
	 *
	 * @return array Recovery status data.
	 */
	public static function get_recovery_status(): array {
		return array(
			'recovery_mode_active' => self::is_recovery_mode_active(),
			'attempts'             => get_option( self::RECOVERY_ATTEMPTS_KEY, 0 ),
			'max_attempts'         => self::MAX_RECOVERY_ATTEMPTS,
			'problematic_plugins'  => get_option( self::PROBLEMATIC_PLUGINS_KEY, array() ),
		);
	}
}
