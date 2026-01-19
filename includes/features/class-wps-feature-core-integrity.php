<?php declare(strict_types=1);
/**
 * Feature: Core Integrity
 *
 * Verifies WordPress core file checksums against WordPress.org
 *
 * @package WPShadow\CoreSupport
 * @since 1.2601.75001
 */

namespace WPShadow\CoreSupport;

final class WPSHADOW_Feature_Core_Integrity extends WPSHADOW_Abstract_Feature {

	public function __construct() {
		parent::__construct( array(
			'id'          => 'core-integrity',
			'name'        => __( 'Core Integrity', 'wpshadow' ),
			'description' => __( 'Verify WordPress core file checksums.', 'wpshadow' ),
			'sub_features' => array(
				'verify_core_files'  => __( 'Verify Core Files', 'wpshadow' ),
				'check_permissions'  => __( 'Check File Permissions', 'wpshadow' ),
				'scan_modifications' => __( 'Scan for Modifications', 'wpshadow' ),
				'auto_report'        => __( 'Auto Report Issues', 'wpshadow' ),
			),
		) );

		$this->register_default_settings( array(
			'verify_core_files'  => true,
			'check_permissions'  => true,
			'scan_modifications' => true,
			'auto_report'        => false,
		) );
	}

	public function register(): void {
		if ( ! $this->is_enabled() ) {
			return;
		}

		// Schedule periodic integrity checks
		add_action( 'wp_scheduled_delete', array( $this, 'run_integrity_check' ) );
		add_filter( 'site_status_tests', array( $this, 'register_site_health_test' ) );
	}

	/**
	 * Run integrity check.
	 */
	public function run_integrity_check(): void {
		$results = $this->verify_core_integrity();
		set_transient( 'wpshadow_core_integrity_results', $results, WEEK_IN_SECONDS );
	}

	/**
	 * Verify WordPress core integrity.
	 */
	private function verify_core_integrity(): array {
		require_once ABSPATH . 'wp-admin/includes/file.php';

		$issues = array();

		if ( $this->is_sub_feature_enabled( 'verify_core_files', true ) ) {
			$issues = array_merge( $issues, $this->check_core_files() );
		}

		if ( $this->is_sub_feature_enabled( 'check_permissions', true ) ) {
			$issues = array_merge( $issues, $this->check_permissions() );
		}

		if ( $this->is_sub_feature_enabled( 'scan_modifications', true ) ) {
			$issues = array_merge( $issues, $this->scan_modifications() );
		}

		return array(
			'timestamp' => time(),
			'total_issues' => count( $issues ),
			'issues' => array_slice( $issues, 0, 20 ), // Limit to 20 for transient size
		);
	}

	/**
	 * Check WordPress core files.
	 */
	private function check_core_files(): array {
		$issues = array();

		// Get core version
		$version = get_bloginfo( 'version' );

		// Try to get checksums from WordPress.org API
		$response = wp_remote_get(
			'https://api.wordpress.org/core/checksums/1.0/?version=' . $version,
			array( 'timeout' => 10 )
		);

		if ( is_wp_error( $response ) ) {
			return array();
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! isset( $data['checksums'] ) ) {
			return array();
		}

		$checked = 0;
		$mismatches = 0;

		// Sample check - don't check all files to save time
		foreach ( array_slice( $data['checksums'], 0, 50 ) as $file => $checksum ) {
			$filepath = ABSPATH . $file;

			if ( ! file_exists( $filepath ) ) {
				$issues[] = array(
					'type' => 'missing_file',
					'file' => $file,
					'message' => __( 'Core file missing', 'wpshadow' ),
				);
				$mismatches++;
				continue;
			}

			$local_checksum = md5_file( $filepath );
			if ( $local_checksum !== $checksum ) {
				$issues[] = array(
					'type' => 'modified_file',
					'file' => $file,
					'message' => __( 'Core file modified', 'wpshadow' ),
				);
				$mismatches++;
			}

			$checked++;
		}

		if ( $checked > 0 && $mismatches === 0 ) {
			$issues[] = array(
				'type' => 'integrity_ok',
				'message' => sprintf( __( 'Checked %d core files - all OK.', 'wpshadow' ), $checked ),
			);
		}

		return $issues;
	}

	/**
	 * Check file permissions.
	 */
	private function check_permissions(): array {
		$issues = array();

		// Check wp-config.php
		$wp_config = ABSPATH . 'wp-config.php';
		if ( file_exists( $wp_config ) ) {
			$perms = fileperms( $wp_config );
			if ( ( $perms & 0020 ) || ( $perms & 0002 ) ) {
				$issues[] = array(
					'type' => 'insecure_perms',
					'file' => 'wp-config.php',
					'message' => __( 'wp-config.php has insecure permissions.', 'wpshadow' ),
				);
			}
		}

		// Check wp-content writable
		if ( ! is_writable( WP_CONTENT_DIR ) ) {
			$issues[] = array(
				'type' => 'not_writable',
				'file' => 'wp-content',
				'message' => __( 'wp-content directory is not writable.', 'wpshadow' ),
			);
		}

		return $issues;
	}

	/**
	 * Scan for suspicious modifications.
	 */
	private function scan_modifications(): array {
		$issues = array();

		// Look for suspicious files in root
		$suspicious_files = array( 'shell.php', 'c99.php', 'x.php', 'wp.php' );

		foreach ( $suspicious_files as $file ) {
			if ( file_exists( ABSPATH . $file ) ) {
				$issues[] = array(
					'type' => 'suspicious_file',
					'file' => $file,
					'message' => __( 'Suspicious file detected.', 'wpshadow' ),
				);
			}
		}

		// Check for recently modified core files (last 24 hours)
		$core_files = array( 'wp-config.php', 'wp-settings.php', 'index.php' );
		$now = time();

		foreach ( $core_files as $file ) {
			$path = ABSPATH . $file;
			if ( file_exists( $path ) ) {
				$modified = filemtime( $path );
				if ( ( $now - $modified ) < DAY_IN_SECONDS ) {
					$issues[] = array(
						'type' => 'recently_modified',
						'file' => $file,
						'message' => __( 'Core file recently modified.', 'wpshadow' ),
					);
				}
			}
		}

		return $issues;
	}

	public function register_site_health_test( array $tests ): array {
		$tests['direct']['core_integrity'] = array(
			'label'  => __( 'Core Integrity', 'wpshadow' ),
			'test'   => array( $this, 'test_integrity' ),
		);

		return $tests;
	}

	public function test_integrity(): array {
		if ( ! $this->is_enabled() ) {
			return array(
				'label'       => __( 'Core Integrity', 'wpshadow' ),
				'status'      => 'recommended',
				'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
				'description' => __( 'Enable core integrity checking to detect file modifications.', 'wpshadow' ),
				'actions'     => '',
				'test'        => 'core_integrity',
			);
		}

		$results = get_transient( 'wpshadow_core_integrity_results' );
		if ( false === $results ) {
			$results = $this->verify_core_integrity();
		}

		$status = $results['total_issues'] === 0 ? 'good' : 'recommended';
		if ( ! empty( $results['issues'] ) ) {
			foreach ( $results['issues'] as $issue ) {
				if ( 'suspicious_file' === $issue['type'] ) {
					$status = 'critical';
					break;
				}
			}
		}

		return array(
			'label'       => __( 'Core Integrity', 'wpshadow' ),
			'status'      => $status,
			'badge'       => array( 'label' => __( 'WPShadow', 'wpshadow' ) ),
			'description' => sprintf(
				__( '%d integrity issue(s) detected.', 'wpshadow' ),
				$results['total_issues']
			),
			'actions'     => '',
			'test'        => 'core_integrity',
		);
	}
}
