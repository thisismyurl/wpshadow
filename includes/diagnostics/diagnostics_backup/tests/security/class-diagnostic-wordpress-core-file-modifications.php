<?php
/**
 * WordPress Core File Modifications Diagnostic
 *
 * Detects unauthorized modifications, deletions, or additions to WordPress core files
 * by comparing against official WordPress.org checksums.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_WordPress_Core_File_Modifications Class
 *
 * Scans WordPress core files for unauthorized modifications that may indicate
 * a compromised installation or backdoor injection.
 *
 * @since 1.2601.2148
 */
class Diagnostic_WordPress_Core_File_Modifications extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-core-file-modifications';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Core File Integrity';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects modifications to WordPress core files';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Cache key for checksums
	 *
	 * @var string
	 */
	const CHECKSUMS_CACHE_KEY = 'wpshadow_core_checksums';

	/**
	 * Cache duration in seconds (24 hours)
	 *
	 * @var int
	 */
	const CHECKSUMS_CACHE_TTL = 86400;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_version;

		// Get checksums from WordPress.org or cache
		$checksums = self::get_core_checksums( $wp_version );

		if ( empty( $checksums ) ) {
			// Unable to verify checksums
			return null;
		}

		// Check for modified, deleted, or added files
		$modifications = self::scan_core_files( $checksums );

		if ( empty( $modifications['modified'] ) && empty( $modifications['deleted'] ) && empty( $modifications['added'] ) ) {
			// All core files intact
			return null;
		}

		// Files have been modified - critical security issue
		$modified_count = count( $modifications['modified'] );
		$deleted_count  = count( $modifications['deleted'] );
		$added_count    = count( $modifications['added'] );
		$total_issues   = $modified_count + $deleted_count + $added_count;

		$description = sprintf(
			/* translators: %d: number of files affected */
			__( 'Found %d WordPress core file %s. This may indicate malware, unauthorized modifications, or a corrupted installation.', 'wpshadow' ),
			$total_issues,
			( $total_issues === 1 ? __( 'anomaly', 'wpshadow' ) : __( 'anomalies', 'wpshadow' ) )
		);

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => $description,
			'severity'      => 'critical',
			'threat_level'  => 95,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/wordpress-core-file-integrity',
			'family'        => self::$family,
			'meta'          => array(
				'wordpress_version'   => $wp_version,
				'modified_files'      => $modified_count,
				'deleted_files'       => $deleted_count,
				'added_files'         => $added_count,
				'total_issues'        => $total_issues,
				'modified_list'       => array_slice( $modifications['modified'], 0, 10 ), // Show first 10
				'deleted_list'        => array_slice( $modifications['deleted'], 0, 10 ),
				'added_list'          => array_slice( $modifications['added'], 0, 10 ),
				'immediate_actions'   => array(
					__( 'Restore WordPress from backup', 'wpshadow' ),
					__( 'Run security scan for malware', 'wpshadow' ),
					__( 'Change all passwords', 'wpshadow' ),
					__( 'Review access logs for suspicious activity', 'wpshadow' ),
					__( 'Check for injected code in wp-config.php', 'wpshadow' ),
				),
			),
			'details'       => array(
				'issue'           => sprintf(
					/* translators: %d: number of files */
					__( '%d WordPress core files show anomalies.', 'wpshadow' ),
					$total_issues
				),
				'security_impact' => __( 'CRITICAL - Your site may be compromised. Core file modifications indicate potential malware or backdoor injection.', 'wpshadow' ),
				'investigation'   => array(
					__( 'Modified files may indicate:' ) => array(
						__( 'Unauthorized code injection (backdoor)' ),
						__( 'Malware infection' ),
						__( 'Failed or incomplete updates' ),
						__( 'Plugin/theme conflict' ),
					),
					__( 'Deleted files may indicate:' ) => array(
						__( 'Incomplete/corrupted WordPress installation' ),
						__( 'Malicious file removal covering tracks' ),
					),
					__( 'Added files may indicate:' ) => array(
						__( 'Backdoor/webshell injection' ),
						__( 'Malware or exploit scripts' ),
					),
				),
			),
		);
	}

	/**
	 * Get WordPress core file checksums from WordPress.org.
	 *
	 * @since  1.2601.2148
	 * @param  string $version WordPress version to check.
	 * @return array|null Array of checksums keyed by filename, or null if unable to fetch.
	 */
	private static function get_core_checksums( $version ) {
		// Check cache first
		$cached_checksums = get_transient( self::CHECKSUMS_CACHE_KEY );
		if ( is_array( $cached_checksums ) ) {
			return $cached_checksums;
		}

		// Fetch checksums from WordPress.org API
		$url = "https://api.wordpress.org/core/checksums/1.0/?version={$version}&locale=en_US";

		$response = wp_remote_get(
			$url,
			array(
				'timeout'   => 10,
				'sslverify' => true,
			)
		);

		if ( is_wp_error( $response ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( empty( $body ) ) {
			return null;
		}

		$data = json_decode( $body, true );

		if ( ! isset( $data['checksums'] ) || ! is_array( $data['checksums'] ) ) {
			return null;
		}

		// Cache the checksums
		set_transient( self::CHECKSUMS_CACHE_KEY, $data['checksums'], self::CHECKSUMS_CACHE_TTL );

		return $data['checksums'];
	}

	/**
	 * Scan WordPress core files against expected checksums.
	 *
	 * @since  1.2601.2148
	 * @param  array $checksums Expected checksums from WordPress.org.
	 * @return array {
	 *     Array of file anomalies.
	 *
	 *     @type array $modified Files with different checksums.
	 *     @type array $deleted  Expected files that don't exist.
	 *     @type array $added    Unexpected files in core directories.
	 * }
	 */
	private static function scan_core_files( array $checksums ) {
		$modified = array();
		$deleted  = array();
		$added    = array();

		// Check each expected file
		foreach ( $checksums as $file => $expected_checksum ) {
			$file_path = ABSPATH . $file;

			if ( ! file_exists( $file_path ) ) {
				// File doesn't exist
				$deleted[] = $file;
				continue;
			}

			// Calculate actual checksum
			$actual_checksum = md5_file( $file_path );

			if ( $actual_checksum !== $expected_checksum ) {
				// File has been modified
				$modified[] = $file;
			}
		}

		// Check for suspicious added files in wp-admin and wp-includes
		$suspicious_additions = self::find_suspicious_additions();
		$added                = array_merge( $added, $suspicious_additions );

		return array(
			'modified' => $modified,
			'deleted'  => $deleted,
			'added'    => $added,
		);
	}

	/**
	 * Find suspicious files added to core directories.
	 *
	 * @since  1.2601.2148
	 * @return array List of suspicious files.
	 */
	private static function find_suspicious_additions() {
		$suspicious = array();
		$dirs       = array( ABSPATH . 'wp-admin', ABSPATH . 'wp-includes' );

		foreach ( $dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			// Look for recently modified PHP files
			$files = glob( $dir . '/*.php' );

			if ( ! is_array( $files ) ) {
				continue;
			}

			foreach ( $files as $file ) {
				// Skip files we know should be there
				$basename = basename( $file );

				if ( strpos( $basename, '.php' ) === false ) {
					continue;
				}

				// Check if file is unusually recent (modified within last 24 hours)
				if ( filemtime( $file ) > ( time() - 86400 ) ) {
					// Check if it's not a known WordPress file
					if ( ! self::is_known_core_file( $basename ) ) {
						$suspicious[] = str_replace( ABSPATH, '', $file );
					}
				}
			}
		}

		return $suspicious;
	}

	/**
	 * Check if file is a known WordPress core file.
	 *
	 * @since  1.2601.2148
	 * @param  string $filename Filename to check.
	 * @return bool True if known core file.
	 */
	private static function is_known_core_file( $filename ) {
		$known_files = array(
			'index.php',
			'wp-blog-header.php',
			'wp-config-sample.php',
			'wp-settings.php',
			'wp-load.php',
			'wp-activate.php',
			'wp-mail.php',
			'wp-signup.php',
			'wp-comments-post.php',
			'wp-cron.php',
			'wp-login.php',
			'wp-links-opml.php',
			'wp-traceback.php',
			'xmlrpc.php',
		);

		return in_array( $filename, $known_files, true );
	}
}
