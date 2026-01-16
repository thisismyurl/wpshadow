<?php
/**
 * Feature: Maintenance File Cleanup
 *
 * Automatically detect and remove stuck maintenance mode files after failed WordPress updates.
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPSHADOW_Feature_Maintenance_Cleanup
 *
 * Detect and remove stuck maintenance mode files.
 */
final class WPSHADOW_Feature_Maintenance_Cleanup extends WPSHADOW_Abstract_Feature {

	/**
	 * Maintenance file path.
	 */
	private const MAINTENANCE_FILE = ABSPATH . '.maintenance';

	/**
	 * Maximum maintenance mode duration (minutes).
	 */
	private const MAX_MAINTENANCE_DURATION = 10;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'maintenance-cleanup',
			'name'               => __( 'Fix Stuck Maintenance Mode', 'plugin-wpshadow' ),
			'description'        => __( 'If an update gets stuck in maintenance mode, we'll automatically get your site back online.', 'plugin-wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true, // Safe to enable by default.
				'version'            => '1.0.0',
				'widget_group'       => 'tools',
				// Unified metadata.
				'license_level'      => 1, // Free for everyone.
				'minimum_capability' => 'update_core',
				'icon'               => 'dashicons-admin-tools',
				'category'           => 'tools',
				'priority'           => 50,

			)
		);
	}

	/**
	 * Register hooks when feature is enabled.
	 *
	 * @return void
	 */
	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Check for stuck maintenance mode on admin init if auto-detect is enabled.
		if ( get_option( 'wpshadow_maintenance-cleanup_auto_detect', true ) ) {
			add_action( 'admin_init', array( $this, 'check_maintenance_mode' ) );
		}

		// Admin notice if enabled.
		if ( get_option( 'wpshadow_maintenance-cleanup_admin_notices', true ) ) {
			add_action( 'admin_notices', array( $this, 'show_maintenance_notice' ) );
		}

		// Add to Site Health tests if enabled.
		if ( get_option( 'wpshadow_maintenance-cleanup_site_health', true ) ) {
			add_filter( 'site_status_tests', array( $this, 'add_site_health_test' ) );
		}

		// AJAX handler for manual cleanup.
		add_action( 'wp_ajax_WPSHADOW_cleanup_maintenance', array( $this, 'ajax_cleanup_maintenance' ) );
	}

	/**
	 * Check if maintenance mode file exists and is stale.
	 *
	 * @return bool True if maintenance mode is stuck, false otherwise.
	 */
	private function is_maintenance_stuck(): bool {
		if ( ! file_exists( self::MAINTENANCE_FILE ) ) {
			return false;
		}

		$file_time = filemtime( self::MAINTENANCE_FILE );
		if ( false === $file_time ) {
			return false;
		}

		$age_minutes = ( time() - $file_time ) / 60;

		return $age_minutes > self::MAX_MAINTENANCE_DURATION;
	}

	/**
	 * Remove maintenance mode file.
	 *
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	private function remove_maintenance_file(): bool|\WP_Error {
		if ( ! file_exists( self::MAINTENANCE_FILE ) ) {
			return new \WP_Error(
				'file_not_found',
				__( 'Maintenance file not found', 'plugin-wpshadow' )
			);
		}

		if ( ! is_writable( self::MAINTENANCE_FILE ) ) {
			return new \WP_Error(
				'file_not_writable',
				__( 'Maintenance file is not writable', 'plugin-wpshadow' )
			);
		}

		// Backup file contents before deletion.
		$contents = file_get_contents( self::MAINTENANCE_FILE );
		if ( false !== $contents ) {
			set_transient( 'wpshadow_maintenance_file_backup', $contents, HOUR_IN_SECONDS );
		}

		// Delete the file.
		$deleted = wp_delete_file( self::MAINTENANCE_FILE );

		if ( ! $deleted && file_exists( self::MAINTENANCE_FILE ) ) {
			return new \WP_Error(
				'deletion_failed',
				__( 'Couldn\'t delete the maintenance file', 'plugin-wpshadow' )
			);
		}

		$this->log_cleanup_action( 'remove_maintenance_file', true );

		return true;
	}

	/**
	 * Get maintenance file information.
	 *
	 * @return array|false File information or false if not exists.
	 */
	private function get_maintenance_info(): array|false {
		if ( ! file_exists( self::MAINTENANCE_FILE ) ) {
			return false;
		}

		$file_time = filemtime( self::MAINTENANCE_FILE );
		$file_size = filesize( self::MAINTENANCE_FILE );

		if ( false === $file_time || false === $file_size ) {
			return false;
		}

		$age_seconds = time() - $file_time;
		$age_minutes = (int) ( $age_seconds / 60 );

		return array(
			'path'         => self::MAINTENANCE_FILE,
			'modified'     => $file_time,
			'size'         => $file_size,
			'age_seconds'  => $age_seconds,
			'age_minutes'  => $age_minutes,
			'is_stuck'     => $age_minutes > self::MAX_MAINTENANCE_DURATION,
			'readable'     => is_readable( self::MAINTENANCE_FILE ),
			'writable'     => is_writable( self::MAINTENANCE_FILE ),
		);
	}

	/**
	 * Clean up upgrade temporary directories.
	 *
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	private function cleanup_upgrade_directories(): bool|\WP_Error {
		$wp_content = WP_CONTENT_DIR;
		$cleaned    = array();

		// Check upgrade directory.
		$upgrade_dir = $wp_content . '/upgrade';
		if ( is_dir( $upgrade_dir ) && $this->is_directory_stale( $upgrade_dir ) ) {
			if ( $this->remove_directory_recursive( $upgrade_dir ) ) {
				$cleaned[] = 'upgrade';
			}
		}

		// Check upgrade-temp-backup directory.
		$backup_dir = $wp_content . '/upgrade-temp-backup';
		if ( is_dir( $backup_dir ) && $this->is_directory_stale( $backup_dir ) ) {
			if ( $this->remove_directory_recursive( $backup_dir ) ) {
				$cleaned[] = 'upgrade-temp-backup';
			}
		}

		if ( ! empty( $cleaned ) ) {
			$this->log_cleanup_action( 'cleanup_upgrade_directories', true );
		}

		return true;
	}

	/**
	 * Add Site Health test for maintenance mode.
	 *
	 * @param array $tests Site Health tests.
	 * @return array Modified tests.
	 */
	public function add_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_maintenance_mode'] = array(
			'label' => __( 'Maintenance mode status', 'plugin-wpshadow' ),
			'test'  => array( $this, 'site_health_test_callback' ),
		);

		return $tests;
	}

	/**
	 * Get Site Health test result.
	 *
	 * @return array Test result.
	 */
	public function site_health_test_callback(): array {
		$info = $this->get_maintenance_info();

		if ( false === $info ) {
			return array(
				'label'       => __( 'No maintenance mode issues', 'plugin-wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'blue',
				),
				'description' => sprintf(
					'<p>%s</p>',
					__( 'No maintenance mode file detected.', 'plugin-wpshadow' )
				),
				'test'        => 'wpshadow_maintenance_mode',
			);
		}

		if ( $info['is_stuck'] ) {
			return array(
				'label'       => __( 'Maintenance mode is stuck', 'plugin-wpshadow' ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'Performance', 'plugin-wpshadow' ),
					'color' => 'red',
				),
				'description' => sprintf(
					'<p>%s</p><p><a href="%s" class="button button-primary">%s</a></p>',
					sprintf(
						/* translators: %d: minutes since maintenance file was created */
						__( 'Your site has been in maintenance mode for %d minutes. This usually indicates a failed update.', 'plugin-wpshadow' ),
						$info['age_minutes']
					),
					wp_nonce_url( admin_url( 'admin-post.php?action=WPSHADOW_cleanup_maintenance_file' ), 'wpshadow_cleanup_maintenance' ),
					__( 'Remove Maintenance Mode', 'plugin-wpshadow' )
				),
				'test'        => 'wpshadow_maintenance_mode',
				'actions'     => sprintf(
					'<a href="%s">%s</a>',
					wp_nonce_url( admin_url( 'admin-post.php?action=WPSHADOW_cleanup_maintenance_file' ), 'wpshadow_cleanup_maintenance' ),
					__( 'Remove Maintenance File', 'plugin-wpshadow' )
				),
			);
		}

		return array(
			'label'       => __( 'Maintenance mode active', 'plugin-wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Performance', 'plugin-wpshadow' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: minutes since maintenance file was created */
					__( 'Your site is in maintenance mode (%d minutes). If this persists, you may need to remove it manually.', 'plugin-wpshadow' ),
					$info['age_minutes']
				)
			),
			'test'        => 'wpshadow_maintenance_mode',
		);
	}

	/**
	 * Check maintenance mode on admin init.
	 *
	 * @return void
	 */
	public function check_maintenance_mode(): void {
		if ( ! current_user_can( 'update_core' ) ) {
			return;
		}

		// Store maintenance mode status in transient for notice.
		if ( $this->is_maintenance_stuck() ) {
			set_transient( 'wpshadow_maintenance_stuck', true, HOUR_IN_SECONDS );
		} else {
			delete_transient( 'wpshadow_maintenance_stuck' );
		}
	}

	/**
	 * Show admin notice for stuck maintenance mode.
	 *
	 * @return void
	 */
	public function show_maintenance_notice(): void {
		if ( ! current_user_can( 'update_core' ) ) {
			return;
		}

		if ( ! get_transient( 'wpshadow_maintenance_stuck' ) ) {
			return;
		}

		$info = $this->get_maintenance_info();
		if ( false === $info ) {
			return;
		}

		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<strong><?php esc_html_e( 'WPShadow: Maintenance Mode Stuck', 'plugin-wpshadow' ); ?></strong>
			</p>
			<p>
				<?php
				printf(
					/* translators: %d: minutes since maintenance file was created */
					esc_html__( 'Your site has been in maintenance mode for %d minutes. This usually means a WordPress update failed or was interrupted.', 'plugin-wpshadow' ),
					(int) $info['age_minutes']
				);
				?>
			</p>
			<p>
				<button type="button" class="button button-primary" id="wps-cleanup-maintenance">
					<?php esc_html_e( 'Remove Maintenance Mode', 'plugin-wpshadow' ); ?>
				</button>
				<span class="spinner" style="float: none; margin: 0 0 0 10px;"></span>
			</p>
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('#wps-cleanup-maintenance').on('click', function() {
				var $button = $(this);
				var $spinner = $button.next('.spinner');
				
				$button.prop('disabled', true);
				$spinner.addClass('is-active');
				
				$.post(ajaxurl, {
					action: 'wpshadow_cleanup_maintenance',
					_ajax_nonce: '<?php echo esc_js( wp_create_nonce( 'wpshadow_cleanup_maintenance' ) ); ?>'
				}, function(response) {
					if (response.success) {
						$button.closest('.notice').fadeOut(function() {
							$(this).remove();
						});
						location.reload();
					} else {
						alert(response.data.message || '<?php esc_html_e( 'Failed to remove maintenance file.', 'plugin-wpshadow' ); ?>');
						$button.prop('disabled', false);
						$spinner.removeClass('is-active');
					}
				}).fail(function() {
					alert('<?php esc_html_e( 'An error occurred. Please try again.', 'plugin-wpshadow' ); ?>');
					$button.prop('disabled', false);
					$spinner.removeClass('is-active');
				});
			});
		});
		</script>
		<?php
	}

	/**
	 * AJAX handler for manual cleanup.
	 *
	 * @return void
	 */
	public function ajax_cleanup_maintenance(): void {
		check_ajax_referer( 'wpshadow_cleanup_maintenance' );

		if ( ! current_user_can( 'update_core' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'plugin-wpshadow' ),
				)
			);
		}

		$result = $this->remove_maintenance_file();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		// Also clean up upgrade directories.
		$this->cleanup_upgrade_directories();

		delete_transient( 'wpshadow_maintenance_stuck' );

		wp_send_json_success(
			array(
				'message' => __( 'Maintenance mode has been removed successfully.', 'plugin-wpshadow' ),
			)
		);
	}

	/**
	 * Log cleanup action.
	 *
	 * @param string $action Action performed.
	 * @param bool   $success Whether action was successful.
	 * @return void
	 */
	private function log_cleanup_action( string $action, bool $success ): void {
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log(
				sprintf(
					'WPS Maintenance Cleanup: %s - %s',
					$action,
					$success ? 'success' : 'failed'
				)
			);
		}

		// Log to activity logger if available.
		if ( class_exists( 'wpshadow_Activity_Logger' ) ) {
			\WPSHADOW_Activity_Logger::log(
				$action,
				$success ? 'success' : 'error',
				array(
					'feature' => 'maintenance-cleanup',
				)
			);
		}
	}

	/**
	 * Check if directory is stale (older than threshold).
	 *
	 * @param string $dir Directory path.
	 * @return bool True if stale, false otherwise.
	 */
	private function is_directory_stale( string $dir ): bool {
		if ( ! is_dir( $dir ) ) {
			return false;
		}

		$modified = filemtime( $dir );
		if ( false === $modified ) {
			return false;
		}

		$age_minutes = ( time() - $modified ) / 60;

		return $age_minutes > self::MAX_MAINTENANCE_DURATION;
	}

	/**
	 * Remove directory recursively.
	 *
	 * @param string $dir Directory path.
	 * @return bool True on success, false on failure.
	 */
	private function remove_directory_recursive( string $dir ): bool {
		if ( ! is_dir( $dir ) ) {
			return false;
		}

		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
		require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';

		$filesystem = new \WP_Filesystem_Direct( null );

		return $filesystem->rmdir( $dir, true );
	}
}
