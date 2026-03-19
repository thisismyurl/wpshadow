<?php
/**
 * Multisite Upload Path Configuration Diagnostic
 *
 * Validates upload directory structure and configuration for WordPress multisite installations.
 * Ensures proper /sites/X/ path generation and write permissions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Upload Path Configuration Diagnostic Class
 *
 * Verifies multisite upload paths are correctly configured and accessible.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Multisite_Upload_Path_Configuration extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-upload-path-configuration';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Upload Path Configuration';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates upload directory structure for multisite installations';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'multisite';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks:
	 * - Multisite is enabled
	 * - Upload paths follow /sites/X/ structure
	 * - Each site has proper upload directory
	 * - Directories are writable
	 * - No path conflicts or misconfigurations
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only applicable to multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$issues = array();
		$current_site_id = get_current_blog_id();
		$upload_dir = wp_upload_dir();

		// Check if upload directory is configured.
		if ( empty( $upload_dir['basedir'] ) || empty( $upload_dir['baseurl'] ) ) {
			$issues[] = __( 'Upload directory is not configured', 'wpshadow' );
		}

		// For non-main sites, verify /sites/X/ structure.
		if ( $current_site_id !== 1 && ! empty( $upload_dir['basedir'] ) ) {
			$expected_path_pattern = '/sites/' . $current_site_id . '/';
			
			if ( strpos( $upload_dir['basedir'], $expected_path_pattern ) === false ) {
				$issues[] = sprintf(
					/* translators: 1: current site ID, 2: actual path */
					__( 'Site %1$d does not use standard /sites/%1$d/ upload path. Current path: %2$s', 'wpshadow' ),
					$current_site_id,
					$upload_dir['basedir']
				);
			}
		}

		// Check if upload directory exists and is writable.
		if ( ! empty( $upload_dir['basedir'] ) ) {
			if ( ! file_exists( $upload_dir['basedir'] ) ) {
				$issues[] = sprintf(
					/* translators: %s: directory path */
					__( 'Upload directory does not exist: %s', 'wpshadow' ),
					$upload_dir['basedir']
				);
			} elseif ( ! wp_is_writable( $upload_dir['basedir'] ) ) {
				$issues[] = sprintf(
					/* translators: %s: directory path */
					__( 'Upload directory is not writable: %s', 'wpshadow' ),
					$upload_dir['basedir']
				);
			}
		}

		// Check for upload_path option (deprecated but still can cause issues).
		$upload_path = get_option( 'upload_path' );
		if ( ! empty( $upload_path ) ) {
			$issues[] = __( 'Deprecated upload_path option is set. This can cause multisite upload issues.', 'wpshadow' );
		}

		// Check for upload_url_path option.
		$upload_url_path = get_option( 'upload_url_path' );
		if ( ! empty( $upload_url_path ) && strpos( $upload_url_path, '/sites/' . $current_site_id ) === false && $current_site_id !== 1 ) {
			$issues[] = __( 'upload_url_path option may be incorrectly configured for this multisite subsite', 'wpshadow' );
		}

		// Verify main uploads directory exists (for main site).
		if ( $current_site_id === 1 ) {
			$main_uploads_path = WP_CONTENT_DIR . '/uploads';
			if ( ! file_exists( $main_uploads_path ) ) {
				$issues[] = sprintf(
					/* translators: %s: directory path */
					__( 'Main uploads directory does not exist: %s', 'wpshadow' ),
					$main_uploads_path
				);
			}
		}

		// Check for sites directory existence (multisite structure).
		$sites_dir = WP_CONTENT_DIR . '/uploads/sites';
		if ( ! file_exists( $sites_dir ) && get_blog_count() > 1 ) {
			$issues[] = sprintf(
				/* translators: %s: directory path */
				__( 'Multisite uploads/sites directory does not exist: %s', 'wpshadow' ),
				$sites_dir
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => implode( ' ', $issues ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false, // Directory creation requires server access
				'details'     => array(
					'current_site_id' => $current_site_id,
					'upload_basedir'  => $upload_dir['basedir'] ?? '',
					'upload_baseurl'  => $upload_dir['baseurl'] ?? '',
					'upload_path_option' => $upload_path ?? '',
					'issues_found'    => $issues,
				),
				'kb_link'     => 'https://wpshadow.com/kb/multisite-upload-path-configuration',
			);
		}

		return null;
	}
}
