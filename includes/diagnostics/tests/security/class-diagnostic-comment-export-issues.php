<?php
/**
 * Comment Export Security Diagnostic
 *
 * Detects security and privacy issues with comment data export functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5049.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Comment Export Security Diagnostic Class
 *
 * Checks for insecure comment export functionality that could expose
 * sensitive data or allow unauthorized access.
 *
 * @since 1.5049.1200
 */
class Diagnostic_Comment_Export_Issues extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'comment-export-issues';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Comment Export Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for secure comment data export handling';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.5049.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for export-related plugins that might be insecure.
		$export_plugins = array(
			'export-all-urls/export-all-urls.php',
			'wp-all-export/wpae.php',
			'really-simple-csv-importer/rs-csv-importer.php',
		);

		$active_export_plugins = array();
		foreach ( $export_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
				$active_export_plugins[] = $plugin_data['Name'];
			}
		}

		// Check if export functionality exists without proper protection.
		$export_actions = array(
			'export_comments',
			'download_comments',
			'comments_export',
		);

		global $wp_filter;
		foreach ( $export_actions as $action ) {
			if ( isset( $wp_filter[ $action ] ) && ! empty( $wp_filter[ $action ] ) ) {
				$issues[] = sprintf(
					/* translators: %s: action name */
					__( 'Unprotected export action detected: %s', 'wpshadow' ),
					$action
				);
			}
		}

		// Check for comment export files in uploads directory.
		$upload_dir = wp_upload_dir();
		$export_files = glob( $upload_dir['basedir'] . '/*comment*export*.{csv,xml,json}', GLOB_BRACE );
		
		if ( ! empty( $export_files ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of export files */
				_n(
					'%d comment export file found in uploads directory',
					'%d comment export files found in uploads directory',
					count( $export_files ),
					'wpshadow'
				),
				count( $export_files )
			);
		}

		if ( ! empty( $issues ) || ! empty( $active_export_plugins ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Comment export functionality may expose sensitive data without proper access controls', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'details'     => array(
					'active_export_plugins' => $active_export_plugins,
					'issues'                => $issues,
					'export_files_count'    => ! empty( $export_files ) ? count( $export_files ) : 0,
				),
				'kb_link'     => 'https://wpshadow.com/kb/comment-export-issues',
			);
		}

		return null;
	}
}
