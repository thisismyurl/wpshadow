<?php
/**
 * API Key Management Diagnostic
 *
 * Detects insecure API key storage and management practices that
 * could lead to credential exposure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * API Key Management Diagnostic Class
 *
 * Checks for:
 * - Hardcoded API keys in theme/plugin files
 * - API keys committed to version control (.git exposure)
 * - Keys stored in wp-config.php vs environment variables
 * - Lack of API key rotation mechanism
 * - Keys visible in client-side JavaScript
 *
 * According to GitGuardian's 2024 State of Secrets Sprawl report,
 * over 10 million secrets are leaked on GitHub annually. Hardcoded
 * API keys are the #1 cause of cloud breaches (42% of incidents).
 *
 * @since 1.6093.1200
 */
class Diagnostic_API_Key_Management extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'api-key-management';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'API Key Management';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Detects insecure API key storage and management practices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * Performs comprehensive API key security analysis:
	 * 1. Scans code for hardcoded keys
	 * 2. Checks .git directory exposure
	 * 3. Verifies environment variable usage
	 * 4. Detects keys in JavaScript
	 * 5. Checks for key rotation metadata
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check 1: Look for hardcoded API keys in code.
		$hardcoded_keys = self::scan_for_hardcoded_keys();
		if ( ! empty( $hardcoded_keys ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				_n(
					'Found %d file with hardcoded API keys',
					'Found %d files with hardcoded API keys',
					count( $hardcoded_keys ),
					'wpshadow'
				),
				count( $hardcoded_keys )
			);
		}

		// Check 2: Verify .git directory is not web-accessible.
		$git_exposed = self::check_git_exposure();
		if ( $git_exposed ) {
			$issues[] = __( '.git directory is web-accessible (could expose API keys in commit history)', 'wpshadow' );
		}

		// Check 3: Check for API keys in wp-config.php.
		$wpconfig_keys = self::check_wpconfig_keys();
		if ( $wpconfig_keys > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d API keys defined as constants in wp-config.php (should use environment variables)', 'wpshadow' ),
				$wpconfig_keys
			);
		}

		// Check 4: Check for keys in JavaScript files.
		$js_keys = self::scan_javascript_for_keys();
		if ( ! empty( $js_keys ) ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				_n(
					'Found %d JavaScript file with exposed API keys',
					'Found %d JavaScript files with exposed API keys',
					count( $js_keys ),
					'wpshadow'
				),
				count( $js_keys )
			);
		}

		// Check 5: Look for API key rotation mechanism.
		$has_rotation = self::check_key_rotation_mechanism();
		if ( ! $has_rotation ) {
			$issues[] = __( 'No API key rotation mechanism detected', 'wpshadow' );
		}

		// Check 6: Check wp_options for plaintext API keys.
		$options_keys = self::scan_options_for_keys();
		if ( $options_keys > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: count */
				__( 'Found %d API keys stored in wp_options without encryption', 'wpshadow' ),
				$options_keys
			);
		}

		// If we found issues, return finding.
		if ( ! empty( $issues ) ) {
			$finding = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of issues */
					_n(
						'%d API key security issue detected',
						'%d API key security issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/api-key-management',
				'context'      => array(
					'issues' => $issues,
					'why'    => __(
						'Exposed API keys give attackers access to third-party services using your credentials. ' .
						'According to GitGuardian, over 10 million secrets leak on GitHub annually. ' .
						'Hardcoded keys are responsible for 42% of cloud breaches (Verizon DBIR). ' .
						'Once exposed, keys can be used to access paid services (racking up charges), ' .
						'steal data from integrated platforms, or pivot to other parts of your infrastructure.',
						'wpshadow'
					),
					'recommendation' => __(
						'Store API keys in environment variables, not code or database. ' .
						'Use wp-config.php with getenv() for sensitive keys. ' .
						'Never commit keys to version control (use .env files + .gitignore). ' .
						'Implement key rotation every 90 days. ' .
						'Use different keys for development, staging, and production. ' .
						'Monitor for leaked keys using services like GitGuardian.',
						'wpshadow'
					),
				),
			);

			$finding = Upgrade_Path_Helper::add_upgrade_path(
				$finding,
				'vault',
				'secrets-management',
				'api-key-guide'
			);

			return $finding;
		}

		return null;
	}

	/**
	 * Scan code for hardcoded API keys.
	 *
	 * @since 1.6093.1200
	 * @return array Files with hardcoded keys.
	 */
	private static function scan_for_hardcoded_keys() {
		$found_in_files = array();
		
		// Common API key patterns.
		$key_patterns = array(
			'/["\']api[_-]?key["\']\s*[=:]\s*["\'][a-zA-Z0-9]{20,}["\']/' => 'API key assignment',
			'/["\']secret[_-]?key["\']\s*[=:]\s*["\'][a-zA-Z0-9]{20,}["\']/' => 'Secret key assignment',
			'/["\']access[_-]?token["\']\s*[=:]\s*["\'][a-zA-Z0-9]{20,}["\']/' => 'Access token assignment',
			'/sk_live_[a-zA-Z0-9]{24,}/' => 'Stripe live secret key',
			'/sk_test_[a-zA-Z0-9]{24,}/' => 'Stripe test secret key',
			'/AIza[0-9A-Za-z-_]{35}/' => 'Google API key',
			'/AKIA[0-9A-Z]{16}/' => 'AWS access key',
		);

		$theme_dir = get_stylesheet_directory();
		$plugin_dir = WP_PLUGIN_DIR;

		// Scan theme.
		$theme_files = self::get_php_files( $theme_dir, 30 );
		foreach ( $theme_files as $file ) {
			if ( self::file_contains_key_pattern( $file, $key_patterns ) ) {
				$found_in_files[] = str_replace( ABSPATH, '', $file );
			}
		}

		// Scan top 5 active plugins.
		$active_plugins = array_slice( get_option( 'active_plugins', array() ), 0, 5 );
		foreach ( $active_plugins as $plugin ) {
			$plugin_path = $plugin_dir . '/' . dirname( $plugin );
			if ( is_dir( $plugin_path ) ) {
				$plugin_files = self::get_php_files( $plugin_path, 10 );
				foreach ( $plugin_files as $file ) {
					if ( self::file_contains_key_pattern( $file, $key_patterns ) ) {
						$found_in_files[] = str_replace( ABSPATH, '', $file );
					}
				}
			}
		}

		return $found_in_files;
	}

	/**
	 * Check if .git directory is web-accessible.
	 *
	 * @since 1.6093.1200
	 * @return bool True if exposed.
	 */
	private static function check_git_exposure() {
		$git_dir = ABSPATH . '.git';
		
		if ( ! is_dir( $git_dir ) ) {
			return false;
		}

		// Check if .git/config is readable from web.
		$git_config = $git_dir . '/config';
		if ( ! is_readable( $git_config ) ) {
			return false;
		}

		// If we can read it locally, check if .htaccess blocks it.
		$htaccess = ABSPATH . '.htaccess';
		if ( file_exists( $htaccess ) ) {
			// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			$htaccess_content = file_get_contents( $htaccess );
			if ( str_contains( $htaccess_content, 'RedirectMatch 404 /\.git' ) || 
			     str_contains( $htaccess_content, 'deny from all' ) && str_contains( $htaccess_content, '.git' ) ) {
				return false; // Blocked by .htaccess.
			}
		}

		return true; // Potentially exposed.
	}

	/**
	 * Check wp-config.php for API key constants.
	 *
	 * @since 1.6093.1200
	 * @return int Number of keys found.
	 */
	private static function check_wpconfig_keys() {
		$wpconfig = ABSPATH . 'wp-config.php';
		
		if ( ! is_readable( $wpconfig ) ) {
			return 0;
		}

		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $wpconfig );
		
		// Look for API key constants.
		$count = preg_match_all( '/define\s*\(\s*["\'][A-Z_]*(?:API|KEY|SECRET|TOKEN)[A-Z_]*["\']\s*,/', $content );
		
		return (int) $count;
	}

	/**
	 * Scan JavaScript files for exposed keys.
	 *
	 * @since 1.6093.1200
	 * @return array Files with exposed keys.
	 */
	private static function scan_javascript_for_keys() {
		$found = array();
		
		$js_dirs = array(
			get_stylesheet_directory() . '/js',
			get_stylesheet_directory() . '/assets/js',
		);

		$key_pattern = '/(?:api[_-]?key|secret|token)\s*[:=]\s*["\'][a-zA-Z0-9]{20,}["\']/i';

		foreach ( $js_dirs as $dir ) {
			if ( ! is_dir( $dir ) ) {
				continue;
			}

			$js_files = glob( $dir . '/*.js' );
			foreach ( $js_files as $file ) {
				// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				$content = file_get_contents( $file );
				if ( preg_match( $key_pattern, $content ) ) {
					$found[] = str_replace( ABSPATH, '', $file );
				}
			}
		}

		return $found;
	}

	/**
	 * Check for key rotation mechanism.
	 *
	 * @since 1.6093.1200
	 * @return bool True if mechanism exists.
	 */
	private static function check_key_rotation_mechanism() {
		global $wpdb;

		// Check for key rotation metadata.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$rotation_meta = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			WHERE option_name LIKE '%_key_rotated%' 
			OR option_name LIKE '%_key_expires%'"
		);

		return $rotation_meta > 0;
	}

	/**
	 * Scan wp_options for unencrypted API keys.
	 *
	 * @since 1.6093.1200
	 * @return int Number of keys found.
	 */
	private static function scan_options_for_keys() {
		global $wpdb;

		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$count = $wpdb->get_var(
			"SELECT COUNT(*) FROM {$wpdb->options} 
			WHERE (option_name LIKE '%api_key%' 
			   OR option_name LIKE '%secret%' 
			   OR option_name LIKE '%token%')
			AND option_value != '' 
			AND LENGTH(option_value) > 20 
			AND option_value NOT LIKE '%encrypted%'"
		);

		return (int) $count;
	}

	/**
	 * Get PHP files from directory.
	 *
	 * @since 1.6093.1200
	 * @param  string $dir Directory path.
	 * @param  int    $limit File limit.
	 * @return array File paths.
	 */
	private static function get_php_files( $dir, $limit = 50 ) {
		$files = array();
		$count = 0;

		if ( ! is_dir( $dir ) ) {
			return $files;
		}

		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator( $dir, \RecursiveDirectoryIterator::SKIP_DOTS )
		);

		foreach ( $iterator as $file ) {
			if ( $count >= $limit ) {
				break;
			}
			if ( $file->isFile() && 'php' === $file->getExtension() ) {
				$files[] = $file->getPathname();
				$count++;
			}
		}

		return $files;
	}

	/**
	 * Check if file contains key pattern.
	 *
	 * @since 1.6093.1200
	 * @param  string $file File path.
	 * @param  array  $patterns Key patterns.
	 * @return bool True if contains pattern.
	 */
	private static function file_contains_key_pattern( $file, $patterns ) {
		// phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
		$content = file_get_contents( $file );
		if ( false === $content ) {
			return false;
		}

		foreach ( $patterns as $pattern => $description ) {
			if ( preg_match( $pattern, $content ) ) {
				return true;
			}
		}

		return false;
	}
}
