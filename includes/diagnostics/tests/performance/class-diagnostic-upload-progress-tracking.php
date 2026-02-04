<?php
/**
 * Upload Progress Tracking Diagnostic
 *
 * Verifies upload progress bar works correctly. Tests JavaScript upload handlers.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Media
 * @since      1.6030.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Upload_Progress_Tracking Class
 *
 * Validates upload progress tracking functionality. WordPress uses Plupload
 * for asynchronous uploads with progress indicators. Issues with JavaScript
 * handlers or session support can break progress tracking.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Upload_Progress_Tracking extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'upload-progress-tracking';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Upload Progress Tracking';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Verifies upload progress bar works correctly';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'media';

	/**
	 * Run the diagnostic check.
	 *
	 * Validates:
	 * - PHP session support for progress tracking
	 * - Plupload script enqueued
	 * - wp-ajax endpoint availability
	 * - JavaScript error logs
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check if Plupload is properly enqueued.
		global $wp_scripts;
		
		if ( ! wp_script_is( 'plupload-handlers', 'registered' ) ) {
			$issues[] = __( 'Plupload handlers script not registered', 'wpshadow' );
		}

		// Check Plupload dependencies.
		$required_scripts = array( 'plupload', 'plupload-html5', 'wp-plupload' );
		foreach ( $required_scripts as $script ) {
			if ( ! wp_script_is( $script, 'registered' ) ) {
				$issues[] = sprintf(
					/* translators: %s: script handle */
					__( 'Required script not registered: %s', 'wpshadow' ),
					$script
				);
			}
		}

		// Check for jQuery dependency.
		if ( ! wp_script_is( 'jquery', 'registered' ) ) {
			$issues[] = __( 'jQuery not registered - required for upload progress', 'wpshadow' );
		}

		// Check PHP session support (used for upload progress on some servers).
		if ( ! function_exists( 'session_status' ) || session_status() === PHP_SESSION_DISABLED ) {
			$issues[] = __( 'PHP sessions disabled - may affect upload progress tracking', 'wpshadow' );
		}

		// Check for upload progress tracking in PHP.
		$session_upload_progress = ini_get( 'session.upload_progress.enabled' );
		if ( $session_upload_progress === false || $session_upload_progress === '0' ) {
			$issues[] = __( 'PHP session.upload_progress.enabled is disabled', 'wpshadow' );
		}

		// Check if APC is available (alternative progress tracking method).
		$apc_available   = function_exists( 'apc_fetch' );
		$apcu_available  = function_exists( 'apcu_fetch' );

		if ( ! $apc_available && ! $apcu_available && ! $session_upload_progress ) {
			$issues[] = __( 'No PHP upload progress tracking method available (session/APC/APCu)', 'wpshadow' );
		}

		// Check for conflicting plugins that might interfere.
		$active_plugins = get_option( 'active_plugins', array() );
		$known_conflicts = array(
			'better-wp-security'      => __( 'iThemes Security - may block AJAX requests', 'wpshadow' ),
			'all-in-one-wp-security'  => __( 'All In One WP Security - may block AJAX', 'wpshadow' ),
			'wordfence'               => __( 'Wordfence - may block rapid AJAX calls', 'wpshadow' ),
		);

		foreach ( $known_conflicts as $plugin_slug => $message ) {
			foreach ( $active_plugins as $plugin ) {
				if ( false !== strpos( $plugin, $plugin_slug ) ) {
					$issues[] = $message;
					break;
				}
			}
		}

		// Check for JavaScript error logs in the database.
		global $wpdb;
		
		// Check for failed AJAX uploads (tracked in transients).
		$ajax_errors = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*)
				FROM {$wpdb->options}
				WHERE option_name LIKE %s
				AND option_name LIKE %s",
				$wpdb->esc_like( '_transient_' ) . '%',
				'%upload_error%'
			)
		);

		if ( $ajax_errors > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of errors */
				_n(
					'%d AJAX upload error found in transients',
					'%d AJAX upload errors found in transients',
					$ajax_errors,
					'wpshadow'
				),
				$ajax_errors
			);
		}

		// Check for admin-ajax.php availability.
		$ajax_url = admin_url( 'admin-ajax.php' );
		if ( empty( $ajax_url ) ) {
			$issues[] = __( 'admin-ajax.php URL not available', 'wpshadow' );
		}

		// Check for HTTPS mixed content issues.
		if ( is_ssl() ) {
			$site_url = get_site_url();
			if ( 0 !== strpos( $site_url, 'https://' ) ) {
				$issues[] = __( 'Site URL uses HTTP but page is HTTPS - may cause AJAX failures', 'wpshadow' );
			}
		}

		// Check for mod_security - can block AJAX requests.
		if ( function_exists( 'apache_get_modules' ) ) {
			$modules = apache_get_modules();
			if ( in_array( 'mod_security', $modules, true ) || in_array( 'mod_security2', $modules, true ) ) {
				$issues[] = __( 'ModSecurity detected - may block AJAX upload requests', 'wpshadow' );
			}
		}

		// Check for CloudFlare/CDN that might cache AJAX responses.
		$headers = headers_list();
		foreach ( $headers as $header ) {
			if ( false !== stripos( $header, 'cf-ray' ) ) {
				$issues[] = __( 'CloudFlare detected - ensure AJAX endpoints are not cached', 'wpshadow' );
				break;
			}
		}

		// Check PHP max_input_vars - affects large AJAX payloads.
		$max_input_vars = (int) ini_get( 'max_input_vars' );
		if ( $max_input_vars > 0 && $max_input_vars < 1000 ) {
			$issues[] = sprintf(
				/* translators: %d: max input vars */
				__( 'max_input_vars (%d) is low - may affect progress tracking data', 'wpshadow' ),
				$max_input_vars
			);
		}

		// Check for output buffering issues.
		$output_buffering = ini_get( 'output_buffering' );
		if ( $output_buffering && $output_buffering !== 'Off' ) {
			$buffer_size = wp_convert_hr_to_bytes( $output_buffering );
			if ( $buffer_size > 4096 ) {
				$issues[] = sprintf(
					/* translators: %s: buffer size */
					__( 'output_buffering (%s) is high - may delay progress updates', 'wpshadow' ),
					size_format( $buffer_size )
				);
			}
		}

		// Check for gzip compression on AJAX responses.
		if ( function_exists( 'gzencode' ) && ini_get( 'zlib.output_compression' ) ) {
			$issues[] = __( 'zlib.output_compression enabled - may delay progress updates', 'wpshadow' );
		}

		// Return finding if issues detected.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d issue detected with upload progress tracking',
						'%d issues detected with upload progress tracking',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/upload-progress-tracking',
				'details'      => array(
					'issues'            => $issues,
					'ajax_url'          => $ajax_url,
					'session_support'   => function_exists( 'session_status' ),
					'apc_available'     => $apc_available,
					'apcu_available'    => $apcu_available,
					'ajax_errors'       => $ajax_errors,
					'max_input_vars'    => $max_input_vars,
				),
			);
		}

		return null;
	}
}
