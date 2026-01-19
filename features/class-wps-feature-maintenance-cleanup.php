<?php declare(strict_types=1);
/**
 * Feature: Maintenance File Cleanup
 *
 * Automatically detect and remove stuck maintenance mode files after failed WordPress updates.
 *
 * @package    WPShadow\CoreSupport
 * @subpackage Features
 * @since      1.2601.75000
 */

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
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

	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'maintenance-cleanup',
				'name'               => __( 'Fix Stuck Maintenance Mode', 'wpshadow' ),
				'description'        => __( "If an update gets stuck in maintenance mode, we'll automatically get your site back online.", 'wpshadow' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'tools',
				'license_level'      => 1,
				'minimum_capability' => 'update_core',
				'icon'               => 'dashicons-admin-tools',
				'category'           => 'tools',
				'priority'           => 50,
				'sub_features'       => array(
					'auto_detect'         => array(
						'name'            => __( 'Auto-Detect Stuck Maintenance', 'wpshadow' ),
						'description'     => __( 'Scan for stuck maintenance files during admin init.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'admin_notices'       => array(
						'name'            => __( 'Show Admin Notice', 'wpshadow' ),
						'description'     => __( 'Display an admin notice when maintenance mode appears stuck.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'site_health'         => array(
						'name'            => __( 'Add Site Health Test', 'wpshadow' ),
						'description'     => __( 'Surface maintenance status in Site Health.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'cleanup_upgrade_dir' => array(
						'name'            => __( 'Clean Upgrade Temp Directories', 'wpshadow' ),
						'description'     => __( 'Remove stale upgrade and backup directories after cleanup.', 'wpshadow' ),
						'default_enabled' => true,
					),
					'ajax_cleanup'        => array(
						'name'            => __( 'Enable AJAX Cleanup', 'wpshadow' ),
						'description'     => __( 'Allow cleanup through AJAX and admin-post endpoints.', 'wpshadow' ),
						'default_enabled' => true,
					),
				),
			)
		);
	}

	public function has_details_page(): bool {
		return true;
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		if ( $this->is_sub_feature_enabled( 'auto_detect', true ) ) {
			add_action( 'admin_init', array( $this, 'check_maintenance_mode' ) );
		}

		if ( $this->is_sub_feature_enabled( 'admin_notices', true ) ) {
			add_action( 'admin_notices', array( $this, 'show_maintenance_notice' ) );
		}

		if ( $this->is_sub_feature_enabled( 'site_health', true ) ) {
			add_filter( 'site_status_tests', array( $this, 'add_site_health_test' ) );
		}

		if ( $this->is_sub_feature_enabled( 'ajax_cleanup', true ) ) {
			add_action( 'wp_ajax_wpshadow_cleanup_maintenance', array( $this, 'ajax_cleanup_maintenance' ) );
			add_action( 'admin_post_wpshadow_cleanup_maintenance_file', array( $this, 'handle_admin_post_cleanup' ) );
		}
	}

	/**
	 * Check if maintenance mode file exists and is stale.
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
	 * @return bool|\WP_Error
	 */
	private function remove_maintenance_file() {
		if ( ! file_exists( self::MAINTENANCE_FILE ) ) {
			return new \WP_Error( 'file_not_found', __( 'Maintenance file not found', 'wpshadow' ) );
		}

		if ( ! is_writable( self::MAINTENANCE_FILE ) ) {
			return new \WP_Error( 'file_not_writable', __( 'Maintenance file is not writable', 'wpshadow' ) );
		}

		$contents = file_get_contents( self::MAINTENANCE_FILE );
		if ( false !== $contents ) {
			set_transient( 'wpshadow_maintenance_file_backup', $contents, HOUR_IN_SECONDS );
		}

		$deleted = wp_delete_file( self::MAINTENANCE_FILE );

		if ( ! $deleted && file_exists( self::MAINTENANCE_FILE ) ) {
			return new \WP_Error( 'deletion_failed', __( "Couldn't delete the maintenance file", 'wpshadow' ) );
		}

		$this->log_cleanup_action( 'remove_maintenance_file', true );

		return true;
	}

	/**
	 * Get maintenance file information.
	 *
	 * @return array|false
	 */
	private function get_maintenance_info() {
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
	 */
	private function cleanup_upgrade_directories(): void {
		$wp_content = WP_CONTENT_DIR;
		$cleaned    = array();

		$upgrade_dir = $wp_content . '/upgrade';
		if ( is_dir( $upgrade_dir ) && $this->is_directory_stale( $upgrade_dir ) ) {
			if ( $this->remove_directory_recursive( $upgrade_dir ) ) {
				$cleaned[] = 'upgrade';
			}
		}

		$backup_dir = $wp_content . '/upgrade-temp-backup';
		if ( is_dir( $backup_dir ) && $this->is_directory_stale( $backup_dir ) ) {
			if ( $this->remove_directory_recursive( $backup_dir ) ) {
				$cleaned[] = 'upgrade-temp-backup';
			}
		}

		if ( ! empty( $cleaned ) ) {
			$this->log_cleanup_action( 'cleanup_upgrade_directories', true );
		}
	}

	/**
	 * Add Site Health test for maintenance mode.
	 *
	 * @param array<string, mixed> $tests Site Health tests.
	 * @return array<string, mixed>
	 */
	public function add_site_health_test( array $tests ): array {
		$tests['direct']['wpshadow_maintenance_mode'] = array(
			'label' => __( 'Maintenance mode status', 'wpshadow' ),
			'test'  => array( $this, 'site_health_test_callback' ),
		);

		return $tests;
	}

	/**
	 * Get Site Health test result.
	 *
	 * @return array<string, mixed>
	 */
	public function site_health_test_callback(): array {
		if ( ! $this->is_enabled() || ! $this->is_sub_feature_enabled( 'site_health', true ) ) {
			return array(
				'label'       => __( 'Maintenance mode status', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'gray',
				),
				'description' => sprintf( '<p>%s</p>', __( 'Maintenance monitoring is disabled. Enable it to catch stuck maintenance mode faster.', 'wpshadow' ) ),
				'test'        => 'wpshadow_maintenance_mode',
			);
		}

		$info = $this->get_maintenance_info();

		if ( false === $info ) {
			return array(
				'label'       => __( 'No maintenance mode issues', 'wpshadow' ),
				'status'      => 'good',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'blue',
				),
				'description' => sprintf( '<p>%s</p>', __( 'No maintenance mode file detected.', 'wpshadow' ) ),
				'test'        => 'wpshadow_maintenance_mode',
			);
		}

		if ( ! empty( $info['is_stuck'] ) ) {
			$action_url = wp_nonce_url( admin_url( 'admin-post.php?action=wpshadow_cleanup_maintenance_file' ), 'wpshadow_cleanup_maintenance' );

			return array(
				'label'       => __( 'Maintenance mode is stuck', 'wpshadow' ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'Performance', 'wpshadow' ),
					'color' => 'red',
				),
				'description' => sprintf(
					'<p>%s</p><p><a href="%s" class="button button-primary">%s</a></p>',
					sprintf(
						/* translators: %d: minutes since maintenance file was created */
						__( 'Your site has been in maintenance mode for %d minutes. This usually indicates a failed update.', 'wpshadow' ),
						(int) $info['age_minutes']
					),
					esc_url( $action_url ),
					esc_html__( 'Remove Maintenance Mode', 'wpshadow' )
				),
				'test'        => 'wpshadow_maintenance_mode',
				'actions'     => sprintf( '<a href="%s">%s</a>', esc_url( $action_url ), esc_html__( 'Remove Maintenance File', 'wpshadow' ) ),
			);
		}

		return array(
			'label'       => __( 'Maintenance mode active', 'wpshadow' ),
			'status'      => 'recommended',
			'badge'       => array(
				'label' => __( 'Performance', 'wpshadow' ),
				'color' => 'orange',
			),
			'description' => sprintf(
				'<p>%s</p>',
				sprintf(
					/* translators: %d: minutes since maintenance file was created */
					__( 'Your site is in maintenance mode (%d minutes). If this persists, you may need to remove it manually.', 'wpshadow' ),
					(int) $info['age_minutes']
				)
			),
			'test'        => 'wpshadow_maintenance_mode',
		);
	}

	/**
	 * Check maintenance mode on admin init.
	 */
	public function check_maintenance_mode(): void {
		if ( ! current_user_can( 'update_core' ) ) {
			return;
		}

		if ( $this->is_maintenance_stuck() ) {
			set_transient( 'wpshadow_maintenance_stuck', true, HOUR_IN_SECONDS );
		} else {
			delete_transient( 'wpshadow_maintenance_stuck' );
		}
	}

	/**
	 * Show admin notice for stuck maintenance mode.
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

		$nonce = wp_create_nonce( 'wpshadow_cleanup_maintenance' );
		?>
		<div class="notice notice-error is-dismissible">
			<p><strong><?php esc_html_e( 'WPShadow: Maintenance Mode Stuck', 'wpshadow' ); ?></strong></p>
			<p>
				<?php
				printf(
					/* translators: %d: minutes since maintenance file was created */
					esc_html__( 'Your site has been in maintenance mode for %d minutes. This usually means a WordPress update failed or was interrupted.', 'wpshadow' ),
					(int) $info['age_minutes']
				);
				?>
			</p>
			<p>
				<button type="button" class="button button-primary" id="wps-cleanup-maintenance">
					<?php esc_html_e( 'Remove Maintenance Mode', 'wpshadow' ); ?>
				</button>
				<span class="spinner" style="float: none; margin: 0 0 0 10px;"></span>
			</p>
		</div>
		<script>
		(function($){
			$(document).on('click', '#wps-cleanup-maintenance', function(){
				var $button = $(this);
				var $spinner = $button.next('.spinner');

				$button.prop('disabled', true);
				$spinner.addClass('is-active');

				$.post(ajaxurl, {
					action: 'wpshadow_cleanup_maintenance',
					_ajax_nonce: '<?php echo esc_js( $nonce ); ?>'
				}).done(function(response){
					if (response && response.success) {
						$button.closest('.notice').fadeOut(function(){ $(this).remove(); });
						location.reload();
					} else {
						alert((response && response.data && response.data.message) ? response.data.message : '<?php echo esc_js( __( 'Failed to remove maintenance file.', 'wpshadow' ) ); ?>');
						$button.prop('disabled', false);
						$spinner.removeClass('is-active');
					}
				}).fail(function(){
					alert('<?php echo esc_js( __( 'An error occurred. Please try again.', 'wpshadow' ) ); ?>');
					$button.prop('disabled', false);
					$spinner.removeClass('is-active');
				});
			});
		})(jQuery);
		</script>
		<?php
	}

	/**
	 * AJAX handler for manual cleanup.
	 */
	public function ajax_cleanup_maintenance(): void {
		check_ajax_referer( 'wpshadow_cleanup_maintenance' );

		if ( ! current_user_can( 'update_core' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'wpshadow' ) ) );
		}

		$result = $this->remove_maintenance_file();

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ) );
		}

		if ( $this->is_sub_feature_enabled( 'cleanup_upgrade_dir', true ) ) {
			$this->cleanup_upgrade_directories();
		}

		delete_transient( 'wpshadow_maintenance_stuck' );

		wp_send_json_success( array( 'message' => __( 'Maintenance mode has been removed successfully.', 'wpshadow' ) ) );
	}

	/**
	 * Admin-post handler for cleanup button in Site Health.
	 */
	public function handle_admin_post_cleanup(): void {
		check_admin_referer( 'wpshadow_cleanup_maintenance' );

		if ( ! current_user_can( 'update_core' ) ) {
			wp_die( esc_html__( 'You do not have permission to perform this action.', 'wpshadow' ), esc_html__( 'Unauthorized', 'wpshadow' ), array( 'response' => 403 ) );
		}

		$result = $this->remove_maintenance_file();

		if ( ! is_wp_error( $result ) && $this->is_sub_feature_enabled( 'cleanup_upgrade_dir', true ) ) {
			$this->cleanup_upgrade_directories();
		}

		delete_transient( 'wpshadow_maintenance_stuck' );

		$redirect = wp_get_referer();
		if ( ! $redirect ) {
			$redirect = admin_url();
		}

		wp_safe_redirect( $redirect );
		exit;
	}

	/**
	 * Log cleanup action.
	 */
	private function log_cleanup_action( string $action, bool $success ): void {
		$message = $success ? 'Maintenance cleanup completed' : 'Maintenance cleanup failed';
		$context = $success ? 'info' : 'warning';

		$this->log_activity( $action, $message, $context );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( sprintf( 'WPS Maintenance Cleanup: %s - %s', $action, $success ? 'success' : 'failed' ) );
		}
	}

	/**
	 * Check if directory is stale (older than threshold).
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
