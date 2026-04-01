<?php
/**
 * Personal Data Exposed in Temp Files Diagnostic
 *
 * Tests whether GDPR export process leaves sensitive data in temporary files accessible to others.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Privacy
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Personal_Data_Exposed_In_Temp_Files Class
 *
 * Verifies that temporary files don't leak personal data.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Personal_Data_Exposed_In_Temp_Files extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'personal-data-exposed-in-temp-files';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Temporary File Security';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if personal data export leaves sensitive information in temp files';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// 1. Check system temp directory.
		$temp_dir = sys_get_temp_dir();

		if ( empty( $temp_dir ) ) {
			$issues[] = __( 'System temp directory not configured - may use insecure fallback', 'wpshadow' );
		} else {
			// Check permissions.
			if ( is_writable( $temp_dir ) ) {
				// Look for WordPress temp files.
				$wp_temp_files = glob( $temp_dir . '/wp-*' );

				if ( ! empty( $wp_temp_files ) ) {
					$old_files = 0;
					$now       = time();

					foreach ( $wp_temp_files as $file ) {
						$age = $now - filemtime( $file );

						// Temp files older than 24 hours.
						if ( $age > DAY_IN_SECONDS ) {
							$old_files++;
						}
					}

					if ( $old_files > 0 ) {
						$issues[] = sprintf(
							/* translators: %d: number of files */
							_n(
								'%d old WordPress temp file found in system directory',
								'%d old WordPress temp files found in system directory',
								$old_files,
								'wpshadow'
							),
							$old_files
						);
					}
				}
			}

			// Check temp directory permissions.
			$perms = fileperms( $temp_dir );
			if ( $perms && ( $perms & 0x0004 ) ) {
				$issues[] = __( 'System temp directory is world-readable - other users can access temp files', 'wpshadow' );
			}
		}

		// 2. Check WordPress upload temp directory.
		$upload_dir = wp_upload_dir();
		$wp_temp    = $upload_dir['basedir'] . '/tmp/';

		if ( file_exists( $wp_temp ) && is_dir( $wp_temp ) ) {
			$temp_files = glob( $wp_temp . '*' );

			if ( ! empty( $temp_files ) ) {
				$issues[] = sprintf(
					/* translators: %d: number of files */
					__( '%d file(s) in WordPress temp directory - should be cleaned automatically', 'wpshadow' ),
					count( $temp_files )
				);
			}

			// Check if protected.
			if ( ! file_exists( $wp_temp . '.htaccess' ) ) {
				$issues[] = __( 'WordPress temp directory not protected with .htaccess', 'wpshadow' );
			}
		}

		// 3. Check PHP session storage.
		$session_path = session_save_path();

		if ( ! empty( $session_path ) && is_dir( $session_path ) ) {
			// Look for session files with recent activity.
			$session_files = glob( $session_path . '/sess_*' );

			if ( ! empty( $session_files ) ) {
				$recent_sessions = 0;
				$now             = time();

				foreach ( $session_files as $file ) {
					$age = $now - filemtime( $file );

					// Sessions active in last hour.
					if ( $age < HOUR_IN_SECONDS ) {
						$recent_sessions++;

						// Sample first few bytes to check for personal data.
						$sample = file_get_contents( $file, false, null, 0, 500 );

						if ( false !== stripos( $sample, 'email' ) ||
						     false !== stripos( $sample, 'address' ) ||
						     false !== stripos( $sample, 'phone' ) ) {
							$issues[] = __( 'PHP sessions may contain personal data - verify secure storage', 'wpshadow' );
							break;
						}
					}
				}
			}
		}

		// 4. Check for insecure temp file creation.
		// WordPress should use wp_tempnam().
		if ( ! function_exists( 'wp_tempnam' ) ) {
			$issues[] = __( 'WordPress temp file function not available - may use insecure fallback', 'wpshadow' );
		}

		// 5. Check error_log files.
		$error_logs = array(
			ABSPATH . 'error_log',
			ABSPATH . 'wp-content/debug.log',
			WP_CONTENT_DIR . '/error_log',
		);

		foreach ( $error_logs as $log_file ) {
			if ( file_exists( $log_file ) ) {
				$log_size = filesize( $log_file );

				if ( $log_size > ( 1024 * 1024 ) ) {
					// Log over 1MB - sample for personal data.
					$sample = file_get_contents( $log_file, false, null, 0, 5000 );

					if ( preg_match( '/[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}/i', $sample ) ) {
						$issues[] = __( 'Error log contains email addresses - personal data may be exposed', 'wpshadow' );
						break;
					}
				}
			}
		}

		// 6. Check cache directories.
		$cache_dirs = array(
			WP_CONTENT_DIR . '/cache/',
			WP_CONTENT_DIR . '/wp-cache/',
			WP_CONTENT_DIR . '/object-cache/',
		);

		foreach ( $cache_dirs as $cache_dir ) {
			if ( file_exists( $cache_dir ) && is_dir( $cache_dir ) ) {
				$cache_files = glob( $cache_dir . '*', GLOB_NOSORT );

				if ( count( $cache_files ) > 1000 ) {
					$issues[] = sprintf(
						/* translators: %s: directory name */
						__( 'Cache directory %s has excessive files - may contain stale personal data', 'wpshadow' ),
						basename( $cache_dir )
					);
				}
			}
		}

		// 7. Check WordPress transients.
		global $wpdb;
		$transient_count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options}
			WHERE option_name LIKE '_transient_%'
			OR option_name LIKE '_site_transient_%'"
		);

		if ( (int) $transient_count > 500 ) {
			$issues[] = sprintf(
				/* translators: %d: number of transients */
				__( '%d transients stored - may contain personal data without expiration', 'wpshadow' ),
				$transient_count
			);
		}

		// 8. Check for plugin-specific temp files.
		$plugin_temp_patterns = array(
			WP_CONTENT_DIR . '/plugins/*/tmp/',
			WP_CONTENT_DIR . '/plugins/*/temp/',
			WP_CONTENT_DIR . '/plugins/*/cache/',
		);

		foreach ( $plugin_temp_patterns as $pattern ) {
			$dirs = glob( $pattern, GLOB_ONLYDIR );

			if ( ! empty( $dirs ) ) {
				foreach ( $dirs as $dir ) {
					$files = glob( $dir . '*' );

					if ( count( $files ) > 50 ) {
						$issues[] = sprintf(
							/* translators: %s: directory path */
							__( 'Plugin temp directory %s has accumulated files', 'wpshadow' ),
							str_replace( WP_CONTENT_DIR, 'wp-content', $dir )
						);
					}
				}
			}
		}

		// 9. Verify secure deletion practices.
		if ( ! function_exists( 'wp_delete_file' ) ) {
			$issues[] = __( 'WordPress secure file deletion function not available', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Temporary file security issues: %s', 'wpshadow' ),
				implode( '; ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 85,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/temp-file-security?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'issues'    => $issues,
				'temp_dir'  => $temp_dir,
			),
		);
	}
}
