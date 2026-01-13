<?php
/**
 * White Screen Auto-Recovery - Automatic detection and recovery from fatal errors.
 *
 * Implements emergency toolkit functionality for handling white screen of death (WSoD)
 * situations by detecting fatal errors, identifying problematic plugins, and
 * automatically attempting recovery.
 *
 * @package WPS_WP_SUPPORT_THISISMYURL
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * White Screen Recovery Manager
 *
 * Handles automatic detection and recovery from fatal errors that cause
 * white screen of death (WSoD) situations.
 */
class WPS_White_Screen_Recovery {

	/**
	 * Recovery attempts option key.
	 */
	private const RECOVERY_ATTEMPTS_KEY = 'WPS_recovery_attempts';

	/**
	 * Recovery mode option key.
	 */
	private const RECOVERY_MODE_KEY = 'WPS_recovery_mode_active';

	/**
	 * Problematic plugins option key.
	 */
	private const PROBLEMATIC_PLUGINS_KEY = 'WPS_problematic_plugins';

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
		add_action( 'WPS_emergency_metaboxes', array( __CLASS__, 'register_recovery_metabox' ) );
	}

	/**
	 * Handle recovery mode if activated.
	 *
	 * Recovery mode disables all plugins except WP Support to allow
	 * safe access to the admin area.
	 *
	 * @return void
	 */
	public static function handle_recovery_mode(): void {
		if ( ! get_option( self::RECOVERY_MODE_KEY, false ) ) {
			return;
		}

		// In recovery mode, we filter active plugins to only include WP Support.
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
	 * @return array Filtered plugins (only WP Support).
	 */
	public static function filter_plugins_in_recovery_mode( $plugins ): array {
		if ( ! is_array( $plugins ) ) {
			return array();
		}

		// Only keep WP Support plugin active.
		$wp_support_plugin = wp_support_BASENAME;
		return array_filter(
			$plugins,
			function( $plugin ) use ( $wp_support_plugin ) {
				return $plugin === $wp_support_plugin;
			}
		);
	}

	/**
	 * Filter network plugins in recovery mode for multisite.
	 *
	 * @param array $plugins Active network plugins.
	 * @return array Filtered plugins (only WP Support).
	 */
	public static function filter_network_plugins_in_recovery_mode( $plugins ): array {
		if ( ! is_array( $plugins ) ) {
			return array();
		}

		// Only keep WP Support plugin active.
		$wp_support_plugin = wp_support_BASENAME;
		$filtered = array();
		
		foreach ( $plugins as $plugin => $time ) {
			if ( $plugin === $wp_support_plugin ) {
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
			set_transient( 'WPS_fatal_error_needs_manual_recovery', $error, DAY_IN_SECONDS );
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
				'WPS_auto_recovery_performed',
				array(
					'plugin'   => $problematic_plugin,
					'error'    => $error,
					'attempt'  => $attempts + 1,
				),
				HOUR_IN_SECONDS
			);
		} else {
			// Can't identify plugin, activate recovery mode.
			self::activate_recovery_mode();
			
			// Store error for display.
			set_transient( 'WPS_fatal_error_recovery_mode', $error, HOUR_IN_SECONDS );
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
		$parts = explode( '/', $relative_path );

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
				// Don't deactivate WP Support itself.
				if ( $plugin_file === wp_support_BASENAME ) {
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
		error_log(
			sprintf(
				'[WPS_AUTO_RECOVERY] Deactivated problematic plugin: %s',
				$plugin
			)
		);
	}

	/**
	 * Activate recovery mode.
	 *
	 * @return void
	 */
	private static function activate_recovery_mode(): void {
		update_option( self::RECOVERY_MODE_KEY, true );

		error_log( '[WPS_AUTO_RECOVERY] Recovery mode activated' );
	}

	/**
	 * Deactivate recovery mode.
	 *
	 * @return void
	 */
	public static function deactivate_recovery_mode(): void {
		delete_option( self::RECOVERY_MODE_KEY );
		delete_option( self::RECOVERY_ATTEMPTS_KEY );

		error_log( '[WPS_AUTO_RECOVERY] Recovery mode deactivated' );
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
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Activity_Logger' ) ) {
			WPS_Activity_Logger::log(
				'error',
				sprintf(
					'Fatal error: %s in %s:%d',
					$error['message'] ?? 'Unknown error',
					$error['file'] ?? 'Unknown file',
					$error['line'] ?? 0
				),
				array(
					'error_type' => $error['type'] ?? 0,
					'error_message' => $error['message'] ?? '',
					'error_file' => $error['file'] ?? '',
					'error_line' => $error['line'] ?? 0,
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
		if ( class_exists( '\\WPS\\CoreSupport\\WPS_Activity_Logger' ) ) {
			WPS_Activity_Logger::log(
				'info',
				sprintf(
					'Auto-recovery attempt #%d: Deactivated plugin %s',
					$attempt,
					$plugin
				),
				array(
					'plugin' => $plugin,
					'attempt' => $attempt,
					'error' => $error,
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
		
		if ( ! empty( $nonce ) && ! wp_verify_nonce( $nonce, 'wps_recovery' ) ) {
			wp_die( esc_html__( 'Invalid recovery link.', 'plugin-wp-support-thisismyurl' ) );
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
		$recovery = get_transient( 'WPS_auto_recovery_performed' );
		
		if ( $recovery ) {
			delete_transient( 'WPS_auto_recovery_performed' );

			$plugin_name = $recovery['plugin'] ?? __( 'Unknown Plugin', 'plugin-wp-support-thisismyurl' );
			$attempt = $recovery['attempt'] ?? 1;

			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<strong><?php esc_html_e( 'WP Support Auto-Recovery:', 'plugin-wp-support-thisismyurl' ); ?></strong>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: Plugin name, 2: Attempt number */
							__( 'A fatal error was detected and plugin "%1$s" was automatically deactivated (attempt #%2$d). Please review the error details and contact the plugin author.', 'plugin-wp-support-thisismyurl' ),
							$plugin_name,
							$attempt
						)
					);
					?>
				</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ); ?>" class="button">
						<?php esc_html_e( 'View Error Details', 'plugin-wp-support-thisismyurl' ); ?>
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
					<strong><?php esc_html_e( '🚨 Recovery Mode Active', 'plugin-wp-support-thisismyurl' ); ?></strong>
				</p>
				<p>
					<?php esc_html_e( 'All plugins except WP Support have been temporarily disabled due to a critical error. Please review the error details and fix the issue before exiting recovery mode.', 'plugin-wp-support-thisismyurl' ); ?>
				</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'Emergency Dashboard', 'plugin-wp-support-thisismyurl' ); ?>
					</a>
					<a href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin.php?page=wps-emergency-support&action=exit-recovery' ), 'wps_exit_recovery' ) ); ?>" class="button">
						<?php esc_html_e( 'Exit Recovery Mode', 'plugin-wp-support-thisismyurl' ); ?>
					</a>
				</p>
			</div>
			<?php
		}

		// Check for manual recovery needed.
		$manual_recovery = get_transient( 'WPS_fatal_error_needs_manual_recovery' );
		
		if ( $manual_recovery ) {
			?>
			<div class="notice notice-error">
				<p>
					<strong><?php esc_html_e( '🚨 Critical Error - Manual Recovery Required', 'plugin-wp-support-thisismyurl' ); ?></strong>
				</p>
				<p>
					<?php esc_html_e( 'Multiple recovery attempts have been made. Manual intervention is required to fix this issue.', 'plugin-wp-support-thisismyurl' ); ?>
				</p>
				<p>
					<a href="<?php echo esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ); ?>" class="button button-primary">
						<?php esc_html_e( 'View Details & Get Help', 'plugin-wp-support-thisismyurl' ); ?>
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
			'WPS_recovery_status',
			__( 'Auto-Recovery Status', 'plugin-wp-support-thisismyurl' ),
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
		$attempts = get_option( self::RECOVERY_ATTEMPTS_KEY, 0 );
		$problematic = get_option( self::PROBLEMATIC_PLUGINS_KEY, array() );

		?>
		<div class="wps-recovery-status">
			<table class="form-table" role="presentation">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Recovery Mode:', 'plugin-wp-support-thisismyurl' ); ?></th>
						<td>
							<?php if ( $recovery_mode ) : ?>
								<span style="color: #c00; font-weight: bold;">⚠️ <?php esc_html_e( 'ACTIVE', 'plugin-wp-support-thisismyurl' ); ?></span>
							<?php else : ?>
								<span style="color: #090;">✓ <?php esc_html_e( 'Inactive', 'plugin-wp-support-thisismyurl' ); ?></span>
							<?php endif; ?>
						</td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Recovery Attempts:', 'plugin-wp-support-thisismyurl' ); ?></th>
						<td>
							<?php echo esc_html( $attempts ); ?> / <?php echo esc_html( self::MAX_RECOVERY_ATTEMPTS ); ?>
						</td>
					</tr>
					<?php if ( ! empty( $problematic ) ) : ?>
					<tr>
						<th scope="row"><?php esc_html_e( 'Problematic Plugins:', 'plugin-wp-support-thisismyurl' ); ?></th>
						<td>
							<ul style="list-style: disc; padding-left: 20px;">
								<?php foreach ( $problematic as $plugin ) : ?>
									<li><code><?php echo esc_html( $plugin ); ?></code></li>
								<?php endforeach; ?>
							</ul>
							<p class="description">
								<?php esc_html_e( 'These plugins were automatically deactivated due to fatal errors.', 'plugin-wp-support-thisismyurl' ); ?>
							</p>
						</td>
					</tr>
					<?php endif; ?>
				</tbody>
			</table>

			<?php if ( $recovery_mode ) : ?>
				<div style="margin-top: 20px; padding: 15px; background: #fee; border-left: 4px solid #c00;">
					<h4 style="margin-top: 0;"><?php esc_html_e( 'Recovery Mode Instructions', 'plugin-wp-support-thisismyurl' ); ?></h4>
					<ol>
						<li><?php esc_html_e( 'Review the error details above', 'plugin-wp-support-thisismyurl' ); ?></li>
						<li><?php esc_html_e( 'Fix the problematic plugin or remove it', 'plugin-wp-support-thisismyurl' ); ?></li>
						<li><?php esc_html_e( 'Exit recovery mode to restore normal operation', 'plugin-wp-support-thisismyurl' ); ?></li>
					</ol>
					<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ); ?>">
						<?php wp_nonce_field( 'wps_exit_recovery', 'wps_recovery_nonce' ); ?>
						<input type="hidden" name="action" value="exit_recovery" />
						<button type="submit" class="button button-primary">
							<?php esc_html_e( 'Exit Recovery Mode', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
						<button type="submit" name="clear_problematic" value="1" class="button">
							<?php esc_html_e( 'Clear Problematic Plugins List', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
					</form>
				</div>
			<?php else : ?>
				<div style="margin-top: 20px;">
					<form method="post" action="<?php echo esc_url( admin_url( 'admin.php?page=wps-emergency-support' ) ); ?>">
						<?php wp_nonce_field( 'wps_manual_recovery', 'wps_recovery_nonce' ); ?>
						<input type="hidden" name="action" value="activate_recovery" />
						<button type="submit" class="button">
							<?php esc_html_e( 'Manually Activate Recovery Mode', 'plugin-wp-support-thisismyurl' ); ?>
						</button>
						<?php if ( $attempts > 0 ) : ?>
							<button type="submit" name="reset_attempts" value="1" class="button">
								<?php esc_html_e( 'Reset Recovery Counter', 'plugin-wp-support-thisismyurl' ); ?>
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
			check_admin_referer( 'wps_exit_recovery', 'wps_recovery_nonce' );

			// Clear problematic plugins list if requested.
			// phpcs:ignore WordPress.Security.NonceVerification.Recommended
			if ( isset( $_POST['clear_problematic'] ) ) {
				delete_option( self::PROBLEMATIC_PLUGINS_KEY );
			}

			self::deactivate_recovery_mode();

			wp_safe_redirect( admin_url( 'admin.php?page=wps-emergency-support&recovery=exited' ) );
			exit;
		}

		// Check for activate recovery action.
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_POST['action'] ) && 'activate_recovery' === $_POST['action'] ) {
			check_admin_referer( 'wps_manual_recovery', 'wps_recovery_nonce' );

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
			'attempts' => get_option( self::RECOVERY_ATTEMPTS_KEY, 0 ),
			'max_attempts' => self::MAX_RECOVERY_ATTEMPTS,
			'problematic_plugins' => get_option( self::PROBLEMATIC_PLUGINS_KEY, array() ),
		);
	}
}
