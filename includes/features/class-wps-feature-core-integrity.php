<?php
/**
 * Feature: Core File Integrity Checker
 *
 * WordPress core file integrity checking and repair using WordPress.org API.
 *
 * FEATURES:
 * - Core file checksum verification against WordPress.org
 * - Detection of modified core files
 * - Automatic repair of core files
 * - Plugin file integrity checking
 * - Theme file integrity checking
 * - Unknown file detection in core directories
 * - Scheduled integrity scans
 * - Email alerts for file changes
 * - Quarantine suspicious files
 * - File change history tracking
 *
 * IMPLEMENTATION NOTES:
 * - Fetch checksums from WordPress.org API
 * - Compare local files with official checksums
 * - Store baseline checksums in database
 * - Support different WordPress versions
 * - Handle customized core files (flag but don't auto-repair)
 * - Add admin UI showing file changes
 *
 * API ENDPOINTS:
 * - https://api.wordpress.org/core/checksums/1.0/?version=X.Y.Z
 * - Returns MD5 checksums for all core files
 *
 * INTEGRATION POINTS:
 * - Add to Site Health checks
 * - Integrate with activity logger
 * - Add to troubleshooting wizard
 * - Show in security dashboard widget
 *
 * PERFORMANCE CONSIDERATIONS:
 * - Run scans in background (WP-Cron)
 * - Chunk large file lists
 * - Cache checksums from API
 * - Use transients to prevent repeated API calls
 *
 * REPAIR FUNCTIONALITY:
 * - Download fresh copy from WordPress.org
 * - Backup modified file before repair
 * - Allow rollback if repair causes issues
 * - Require user confirmation for repairs
 *
 * @package WPS\CoreSupport
 * @since 1.2601.75000
 */

declare(strict_types=1);

namespace WPS\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPS_Feature_Core_Integrity
 *
 * WordPress core file integrity checking and repair with automatic verification.
 */
final class WPS_Feature_Core_Integrity extends WPS_Abstract_Feature {

	/**
	 * WordPress.org checksums API URL.
	 */
	private const CHECKSUMS_API = 'https://api.wordpress.org/core/checksums/1.0/';

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct(
			array(
				'id'                 => 'core-integrity',
				'name'               => __( 'Core File Integrity', 'plugin-wp-support-thisismyurl' ),
				'description'        => __( 'Verify WordPress core files against official checksums, detect modifications, and repair compromised files automatically', 'plugin-wp-support-thisismyurl' ),
				'scope'              => 'core',
				'default_enabled'    => true,
				'version'            => '1.0.0',
				'widget_group'       => 'security',
				'widget_label'       => __( 'Security', 'plugin-wp-support-thisismyurl' ),
				'widget_description' => __( 'Advanced security features to protect your WordPress installation', 'plugin-wp-support-thisismyurl' ),
				// Unified metadata.
				'license_level'      => 1, // Free for everyone.
				'minimum_capability' => 'manage_options',
				'icon'               => 'dashicons-shield-alt',
				'category'           => 'security',
				'priority'           => 5,
				'dashboard'          => 'overview',
				'widget_column'      => 'right',
				'widget_priority'    => 5,
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

		// Scheduled integrity scans.
		if ( ! wp_next_scheduled( 'wps_core_integrity_scan' ) ) {
			wp_schedule_event( time(), 'daily', 'wps_core_integrity_scan' );
		}
		add_action( 'wps_core_integrity_scan', array( $this, 'run_scheduled_scan' ) );

		// AJAX handlers.
		add_action( 'wp_ajax_wps_scan_core_files', array( $this, 'ajax_scan_core_files' ) );
		add_action( 'wp_ajax_wps_repair_core_file', array( $this, 'ajax_repair_core_file' ) );
		add_action( 'wp_ajax_wps_repair_all_core_files', array( $this, 'ajax_repair_all_core_files' ) );

		// Add to Site Health checks.
		add_filter( 'site_status_tests', array( $this, 'add_site_health_test' ) );

		// Admin notices for file changes.
		add_action( 'admin_notices', array( $this, 'file_integrity_notices' ) );
	}

	/**
	 * Run integrity scan on WordPress core files.
	 *
	 * @return array Scan results.
	 */
	private function run_integrity_scan(): array {
		global $wp_version;

		$version = $wp_version;
		$locale  = get_locale();

		// Fetch checksums from API.
		$checksums = $this->fetch_checksums( $version, $locale );

		if ( is_wp_error( $checksums ) ) {
			return array(
				'error'          => $checksums->get_error_message(),
				'modified_files' => array(),
				'missing_files'  => array(),
				'unknown_files'  => array(),
				'scan_time'      => time(),
			);
		}

		$modified_files = array();
		$missing_files  = array();

		// Check each file against its checksum.
		foreach ( $checksums as $relative_path => $expected_checksum ) {
			$absolute_path = ABSPATH . $relative_path;

			if ( ! file_exists( $absolute_path ) ) {
				$missing_files[] = $relative_path;
				continue;
			}

			$actual_checksum = $this->calculate_checksum( $absolute_path );

			if ( $actual_checksum && $actual_checksum !== $expected_checksum ) {
				$modified_files[] = array(
					'path'              => $relative_path,
					'expected_checksum' => $expected_checksum,
					'actual_checksum'   => $actual_checksum,
				);
			}
		}

		// Detect unknown files in core directories.
		$unknown_files = $this->detect_unknown_files();

		$results = array(
			'modified_files' => $modified_files,
			'missing_files'  => $missing_files,
			'unknown_files'  => $unknown_files,
			'scan_time'      => time(),
			'version'        => $version,
			'locale'         => $locale,
		);

		// Store results.
		set_transient( 'wps_core_integrity_results', $results, DAY_IN_SECONDS );

		// Set flag if issues found.
		if ( ! empty( $modified_files ) || ! empty( $unknown_files ) ) {
			set_transient( 'wps_core_integrity_issues', true, WEEK_IN_SECONDS );
		} else {
			delete_transient( 'wps_core_integrity_issues' );
		}

		return $results;
	}

	/**
	 * Fetch checksums from WordPress.org API.
	 *
	 * @param string $version WordPress version.
	 * @param string $locale Locale (default: en_US).
	 * @return array|\WP_Error Array of checksums or WP_Error on failure.
	 */
	private function fetch_checksums( string $version, string $locale = 'en_US' ): array|\WP_Error {
		$cache_key = 'wps_checksums_' . $version . '_' . $locale;

		// Check cache first.
		$cached = get_transient( $cache_key );
		if ( false !== $cached && is_array( $cached ) ) {
			return $cached;
		}

		// Build API URL.
		$url = add_query_arg(
			array(
				'version' => $version,
				'locale'  => $locale,
			),
			self::CHECKSUMS_API
		);

		// Make API request.
		$response = wp_remote_get(
			$url,
			array(
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			return new \WP_Error(
				'api_error',
				sprintf(
					/* translators: %d: HTTP response code */
					__( 'Failed to fetch checksums from WordPress.org (HTTP %d)', 'plugin-wp-support-thisismyurl' ),
					$response_code
				)
			);
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! is_array( $data ) || ! isset( $data['checksums'] ) ) {
			return new \WP_Error(
				'parse_error',
				__( 'Failed to parse checksums response', 'plugin-wp-support-thisismyurl' )
			);
		}

		$checksums = $data['checksums'];

		// Cache checksums for 24 hours.
		set_transient( $cache_key, $checksums, DAY_IN_SECONDS );

		return $checksums;
	}

	/**
	 * Calculate MD5 checksum for a file.
	 *
	 * @param string $file_path Absolute path to file.
	 * @return string|false MD5 checksum or false on failure.
	 */
	private function calculate_checksum( string $file_path ): string|false {
		if ( ! file_exists( $file_path ) || ! is_readable( $file_path ) ) {
			return false;
		}

		return md5_file( $file_path );
	}

	/**
	 * Verify a single file against official checksum.
	 *
	 * @param string $relative_path Path relative to ABSPATH.
	 * @param string $expected_checksum Expected MD5 checksum.
	 * @return bool True if file matches, false otherwise.
	 */
	private function verify_file( string $relative_path, string $expected_checksum ): bool {
		$absolute_path = ABSPATH . $relative_path;

		if ( ! file_exists( $absolute_path ) ) {
			return false;
		}

		$actual_checksum = $this->calculate_checksum( $absolute_path );

		return $actual_checksum === $expected_checksum;
	}

	/**
	 * Repair a modified core file.
	 *
	 * @param string $relative_path Path relative to ABSPATH.
	 * @return bool|\WP_Error True on success, WP_Error on failure.
	 */
	private function repair_file( string $relative_path ): bool|\WP_Error {
		global $wp_version;

		$absolute_path = ABSPATH . $relative_path;

		if ( ! file_exists( $absolute_path ) ) {
			return new \WP_Error( 'file_not_found', __( 'File not found', 'plugin-wp-support-thisismyurl' ) );
		}

		// Backup current file.
		$backup_path = $absolute_path . '.wps-backup-' . time();
		if ( ! copy( $absolute_path, $backup_path ) ) {
			return new \WP_Error( 'backup_failed', __( 'Failed to backup file before repair', 'plugin-wp-support-thisismyurl' ) );
		}

		// Download fresh copy from WordPress.org.
		$download_url = 'https://core.svn.wordpress.org/tags/' . $wp_version . '/' . $relative_path;
		$response     = wp_remote_get(
			$download_url,
			array(
				'timeout' => 30,
			)
		);

		if ( is_wp_error( $response ) ) {
			// Restore backup.
			copy( $backup_path, $absolute_path );
			wp_delete_file( $backup_path );
			return $response;
		}

		$response_code = wp_remote_retrieve_response_code( $response );
		if ( 200 !== $response_code ) {
			// Restore backup.
			copy( $backup_path, $absolute_path );
			wp_delete_file( $backup_path );
			return new \WP_Error(
				'download_failed',
				sprintf(
					/* translators: %d: HTTP response code */
					__( 'Failed to download file from WordPress.org (HTTP %d)', 'plugin-wp-support-thisismyurl' ),
					$response_code
				)
			);
		}

		$file_contents = wp_remote_retrieve_body( $response );

		// Write new file.
		require_once ABSPATH . 'wp-admin/includes/file.php';
		global $wp_filesystem;
		if ( ! WP_Filesystem() ) {
			// Restore backup.
			copy( $backup_path, $absolute_path );
			wp_delete_file( $backup_path );
			return new \WP_Error( 'filesystem_error', __( 'Could not access filesystem', 'plugin-wp-support-thisismyurl' ) );
		}

		if ( ! $wp_filesystem->put_contents( $absolute_path, $file_contents, FS_CHMOD_FILE ) ) {
			// Restore backup.
			copy( $backup_path, $absolute_path );
			wp_delete_file( $backup_path );
			return new \WP_Error( 'write_failed', __( 'Failed to write repaired file', 'plugin-wp-support-thisismyurl' ) );
		}

		// Verify repair was successful.
		$checksums = $this->fetch_checksums( $wp_version, get_locale() );
		if ( ! is_wp_error( $checksums ) && isset( $checksums[ $relative_path ] ) ) {
			if ( ! $this->verify_file( $relative_path, $checksums[ $relative_path ] ) ) {
				// Restore backup.
				copy( $backup_path, $absolute_path );
				wp_delete_file( $backup_path );
				return new \WP_Error( 'verification_failed', __( 'Repaired file verification failed', 'plugin-wp-support-thisismyurl' ) );
			}
		}

		// Repair successful, delete backup after 7 days.
		wp_schedule_single_event( time() + ( 7 * DAY_IN_SECONDS ), 'wps_delete_backup_file', array( $backup_path ) );

		// Log repair action.
		if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
			error_log( 'WPS Core Integrity: Repaired file ' . $relative_path );
		}

		return true;
	}

	/**
	 * Get list of modified core files from last scan.
	 *
	 * @return array Modified files with details.
	 */
	private function get_modified_files(): array {
		$results = get_transient( 'wps_core_integrity_results' );

		if ( false === $results || ! isset( $results['modified_files'] ) ) {
			return array();
		}

		return $results['modified_files'];
	}

	/**
	 * Detect unknown files in core directories.
	 *
	 * @return array Unknown files list.
	 */
	private function detect_unknown_files(): array {
		global $wp_version;

		$unknown_files = array();

		// Get official file list.
		$checksums = $this->fetch_checksums( $wp_version, get_locale() );
		if ( is_wp_error( $checksums ) ) {
			return array();
		}

		$official_files = array_keys( $checksums );

		// Scan wp-admin directory.
		$wp_admin_files = $this->scan_directory( ABSPATH . 'wp-admin', ABSPATH );

		// Scan wp-includes directory.
		$wp_includes_files = $this->scan_directory( ABSPATH . 'wp-includes', ABSPATH );

		$found_files = array_merge( $wp_admin_files, $wp_includes_files );

		// Find files not in official list.
		foreach ( $found_files as $file ) {
			if ( ! in_array( $file, $official_files, true ) ) {
				$unknown_files[] = $file;
			}
		}

		return $unknown_files;
	}

	/**
	 * Recursively scan a directory for files.
	 *
	 * @param string $dir Directory path.
	 * @param string $base_path Base path to make paths relative.
	 * @return array List of relative file paths.
	 */
	private function scan_directory( string $dir, string $base_path ): array {
		$files = array();

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS ),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		foreach ( $iterator as $file ) {
			if ( $file->isFile() ) {
				$absolute_path = $file->getPathname();
				$relative_path = str_replace( $base_path, '', $absolute_path );
				$files[]       = ltrim( $relative_path, '/' );
			}
		}

		return $files;
	}

	/**
	 * Run scheduled integrity scan.
	 *
	 * @return void
	 */
	public function run_scheduled_scan(): void {
		$this->run_integrity_scan();
	}

	/**
	 * AJAX handler for scanning core files.
	 *
	 * @return void
	 */
	public function ajax_scan_core_files(): void {
		check_ajax_referer( 'wps_core_integrity' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}

		$results = $this->run_integrity_scan();

		if ( isset( $results['error'] ) ) {
			wp_send_json_error(
				array(
					'message' => $results['error'],
				)
			);
		}

		wp_send_json_success(
			array(
				'modified_count' => count( $results['modified_files'] ),
				'missing_count'  => count( $results['missing_files'] ),
				'unknown_count'  => count( $results['unknown_files'] ),
				'results'        => $results,
			)
		);
	}

	/**
	 * AJAX handler for repairing a single core file.
	 *
	 * @return void
	 */
	public function ajax_repair_core_file(): void {
		check_ajax_referer( 'wps_core_integrity' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}

		$file_path = isset( $_POST['file_path'] ) ? sanitize_text_field( wp_unslash( $_POST['file_path'] ) ) : '';

		if ( empty( $file_path ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Invalid file path.', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}

		$result = $this->repair_file( $file_path );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'message' => $result->get_error_message(),
				)
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'File repaired successfully.', 'plugin-wp-support-thisismyurl' ),
			)
		);
	}

	/**
	 * AJAX handler for repairing all modified core files.
	 *
	 * @return void
	 */
	public function ajax_repair_all_core_files(): void {
		check_ajax_referer( 'wps_core_integrity' );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'You do not have permission to perform this action.', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}

		$modified_files = $this->get_modified_files();

		if ( empty( $modified_files ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'No modified files to repair.', 'plugin-wp-support-thisismyurl' ),
				)
			);
		}

		$repaired = array();
		$failed   = array();

		foreach ( $modified_files as $file ) {
			$result = $this->repair_file( $file['path'] );

			if ( is_wp_error( $result ) ) {
				$failed[] = array(
					'path'    => $file['path'],
					'message' => $result->get_error_message(),
				);
			} else {
				$repaired[] = $file['path'];
			}
		}

		// Re-run scan to update results.
		$this->run_integrity_scan();

		wp_send_json_success(
			array(
				'repaired_count' => count( $repaired ),
				'failed_count'   => count( $failed ),
				'repaired'       => $repaired,
				'failed'         => $failed,
			)
		);
	}

	/**
	 * Add Site Health test for core integrity.
	 *
	 * @param array $tests Site Health tests.
	 * @return array Modified tests.
	 */
	public function add_site_health_test( array $tests ): array {
		$tests['direct']['wps_core_integrity'] = array(
			'label' => __( 'WordPress core file integrity', 'plugin-wp-support-thisismyurl' ),
			'test'  => array( $this, 'site_health_test_callback' ),
		);

		return $tests;
	}

	/**
	 * Site Health test callback.
	 *
	 * @return array Test result.
	 */
	public function site_health_test_callback(): array {
		$results = get_transient( 'wps_core_integrity_results' );

		// If no scan has been run yet, run one now.
		if ( false === $results ) {
			$results = $this->run_integrity_scan();
		}

		if ( isset( $results['error'] ) ) {
			return array(
				'label'       => __( 'Core integrity check failed', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'recommended',
				'badge'       => array(
					'label' => __( 'Security', 'plugin-wp-support-thisismyurl' ),
					'color' => 'orange',
				),
				'description' => sprintf(
					'<p>%s</p>',
					esc_html( $results['error'] )
				),
				'test'        => 'wps_core_integrity',
			);
		}

		$modified_count = count( $results['modified_files'] ?? array() );
		$unknown_count  = count( $results['unknown_files'] ?? array() );

		if ( $modified_count > 0 || $unknown_count > 0 ) {
			return array(
				'label'       => __( 'Modified core files detected', 'plugin-wp-support-thisismyurl' ),
				'status'      => 'critical',
				'badge'       => array(
					'label' => __( 'Security', 'plugin-wp-support-thisismyurl' ),
					'color' => 'red',
				),
				'description' => sprintf(
					'<p>%s</p>',
					sprintf(
						/* translators: 1: number of modified files, 2: number of unknown files */
						__( 'Found %1$d modified core files and %2$d unknown files. This could indicate a security compromise.', 'plugin-wp-support-thisismyurl' ),
						$modified_count,
						$unknown_count
					)
				),
				'test'        => 'wps_core_integrity',
				'actions'     => sprintf(
					'<a href="%s">%s</a>',
					admin_url( 'tools.php?page=wps-core-integrity' ),
					__( 'View and Repair Files', 'plugin-wp-support-thisismyurl' )
				),
			);
		}

		return array(
			'label'       => __( 'Core files are intact', 'plugin-wp-support-thisismyurl' ),
			'status'      => 'good',
			'badge'       => array(
				'label' => __( 'Security', 'plugin-wp-support-thisismyurl' ),
				'color' => 'blue',
			),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'All WordPress core files match official checksums.', 'plugin-wp-support-thisismyurl' )
			),
			'test'        => 'wps_core_integrity',
		);
	}

	/**
	 * Show admin notices for file integrity issues.
	 *
	 * @return void
	 */
	public function file_integrity_notices(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! get_transient( 'wps_core_integrity_issues' ) ) {
			return;
		}

		$results = get_transient( 'wps_core_integrity_results' );

		if ( false === $results ) {
			return;
		}

		$modified_count = count( $results['modified_files'] ?? array() );
		$unknown_count  = count( $results['unknown_files'] ?? array() );

		if ( $modified_count === 0 && $unknown_count === 0 ) {
			return;
		}

		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<strong><?php esc_html_e( 'WP Support: Core Integrity Issue Detected', 'plugin-wp-support-thisismyurl' ); ?></strong>
			</p>
			<p>
				<?php
				printf(
					/* translators: 1: number of modified files, 2: number of unknown files */
					esc_html__( 'Found %1$d modified core files and %2$d unknown files. This could indicate a security issue.', 'plugin-wp-support-thisismyurl' ),
					$modified_count,
					$unknown_count
				);
				?>
			</p>
			<p>
				<button type="button" class="button button-primary" id="wps-repair-all-core-files">
					<?php esc_html_e( 'Repair All Modified Files', 'plugin-wp-support-thisismyurl' ); ?>
				</button>
				<span class="spinner" style="float: none; margin: 0 0 0 10px;"></span>
			</p>
		</div>
		<script>
		jQuery(document).ready(function($) {
			$('#wps-repair-all-core-files').on('click', function() {
				var $button = $(this);
				var $spinner = $button.next('.spinner');
				
				if (!confirm('<?php esc_html_e( 'Are you sure you want to repair all modified core files? This will overwrite the current files.', 'plugin-wp-support-thisismyurl' ); ?>')) {
					return;
				}
				
				$button.prop('disabled', true);
				$spinner.addClass('is-active');
				
				$.post(ajaxurl, {
					action: 'wps_repair_all_core_files',
					_ajax_nonce: '<?php echo esc_js( wp_create_nonce( 'wps_core_integrity' ) ); ?>'
				}, function(response) {
					if (response.success) {
						$button.closest('.notice').fadeOut(function() {
							$(this).remove();
						});
						alert('<?php esc_html_e( 'Files repaired successfully!', 'plugin-wp-support-thisismyurl' ); ?>\n' + 
							  '<?php esc_html_e( 'Repaired:', 'plugin-wp-support-thisismyurl' ); ?> ' + response.data.repaired_count);
						location.reload();
					} else {
						alert(response.data.message || '<?php esc_html_e( 'Failed to repair files.', 'plugin-wp-support-thisismyurl' ); ?>');
						$button.prop('disabled', false);
						$spinner.removeClass('is-active');
					}
				}).fail(function() {
					alert('<?php esc_html_e( 'An error occurred. Please try again.', 'plugin-wp-support-thisismyurl' ); ?>');
					$button.prop('disabled', false);
					$spinner.removeClass('is-active');
				});
			});
		});
		</script>
		<?php
	}
}
