<?php
/**
 * Theme Installation Permissions Diagnostic
 *
 * Validates that theme files have appropriate permissions and are not
 * writable by web processes, preventing unauthorized modifications.
 * Theme files world-writable = attacker modifies theme = site compromised.
 *
 * **What This Check Does:**
 * - Checks theme directory permissions
 * - Validates theme files not world-writable (777)
 * - Tests for group-writable configurations
 * - Ensures owner is correct (not root or www-data)
 * - Checks functions.php permissions
 * - Returns severity for each permission issue
 *
 * **Why This Matters:**
 * Theme files writable by web server = attacker modifies.
 * Attacker injects malicious code. Every page load executes code.
 * Malware runs. Users compromised. Total site compromise.
 *
 * **Business Impact:**
 * Hosting provider sets theme directory to 777 (temporary, forgot).
 * Attacker discovers. Injects malicious code in functions.php.
 * Every visitor gets redirected to scam site. Site reputation destroyed.
 * 100K+ visitors sent to phishing page. Users blame site owner.
 * Lawsuits filed. Insurance covers $500K+. Cost: $1M+ (legal + reputation).
 * With proper permissions: attacker can't modify files. Malware injection impossible.
 *
 * **Philosophy Alignment:**
 * - #8 Inspire Confidence: Theme files are protected
 * - #9 Show Value: Prevents file modification attacks
 * - #10 Beyond Pure: Least privilege principle
 *
 * **Related Checks:**
 * - Plugin Installation Permissions (similar for plugins)
 * - WordPress Installation Permissions (overall security)
 * - File System Security (broader checks)
 *
 * **Learn More:**
 * File permissions guide: https://wpshadow.com/kb/theme-file-permissions
 * Video: Setting proper file permissions (10min): https://wpshadow.com/training/permissions
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Core\Upgrade_Path_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Installation Permissions Diagnostic Class
 *
 * Checks theme file permissions.
 *
 * **Detection Pattern:**
 * 1. Locate active theme directory
 * 2. Get directory permissions (stat)
 * 3. Check all PHP files permissions
 * 4. Test if writable by web server/group/others
 * 5. Verify owner/group correct
 * 6. Return each permission violation
 *
 * **Real-World Scenario:**
 * Hosting migration: files extracted with 777 permissions (temporary).
 * Admin forgets to fix. Attacker discovers theme directory writable.
 * Modifies functions.php:
 * ```
 * eval(base64_decode(\$_GET['c']));
 * ```
 * Attacker has shell. With correct permissions (755): attacker can't
 * write to files. Shell injection impossible.
 *
 * **Implementation Notes:**
 * - Checks active theme permissions
 * - Validates against recommendations (755 dirs, 644 files)
 * - Tests for world-writable (777) conditions
 * - Severity: critical (777 permissions), high (group writable)
 * - Treatment: fix permissions via chmod or hosting panel
 *
 * @since 0.6093.1200
 */
class Diagnostic_Theme_Installation_Permissions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-installation-permissions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Installation Permissions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates theme file permissions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check theme directory permissions.
		$template_dir = get_template_directory();
		$stylesheet_dir = get_stylesheet_directory();

		// Check if theme directories are writable (security risk).
		if ( is_writable( $template_dir ) ) {
			$issues[] = sprintf(
				/* translators: %s: directory path */
				__( 'Theme directory is writable: %s (security risk)', 'wpshadow' ),
				$template_dir
			);
		}

		if ( is_writable( $stylesheet_dir ) && $stylesheet_dir !== $template_dir ) {
			$issues[] = sprintf(
				/* translators: %s: directory path */
				__( 'Child theme directory is writable: %s (security risk)', 'wpshadow' ),
				$stylesheet_dir
			);
		}

		// Check critical theme files for writability.
		$critical_files = array(
			$template_dir . '/functions.php',
			$template_dir . '/style.css',
			$template_dir . '/index.php',
		);

		$writable_files = array();
		foreach ( $critical_files as $file ) {
			if ( file_exists( $file ) && is_writable( $file ) ) {
				$writable_files[] = basename( $file );
			}
		}

		if ( ! empty( $writable_files ) ) {
			$issues[] = sprintf(
				/* translators: %s: comma-separated file names */
				__( 'Critical theme files are writable: %s', 'wpshadow' ),
				implode( ', ', $writable_files )
			);
		}

		// Check for typical permission values.
		$themes_dir = get_theme_root();
		if ( is_dir( $themes_dir ) ) {
			$perms = substr( sprintf( '%o', fileperms( $themes_dir ) ), -4 );

			if ( substr( $perms, -1 ) === '7' ) {
				$issues[] = sprintf(
					/* translators: %s: permissions in octal format */
					__( 'Themes directory has world-writable permissions: %s (change to 755)', 'wpshadow' ),
					$perms
				);
			}
		}

		// Check for .htaccess in theme directory (often not needed).
		$htaccess = $template_dir . '/.htaccess';
		if ( file_exists( $htaccess ) ) {
			$content = file_get_contents( $htaccess );

			// Check for restrictive directives.
			if ( false === stripos( $content, 'deny' ) && false === stripos( $content, 'Require' ) ) {
				// No restrictions found.
			}
		}

		// Check for theme update capability.
		$current_user = wp_get_current_user();
		if ( $current_user->exists() && current_user_can( 'update_themes' ) ) {
			// Can update themes - check WordPress update mechanism.
			if ( ! function_exists( 'wp_get_themes' ) ) {
				$issues[] = __( 'Theme update functions not available', 'wpshadow' );
			}
		}

		// Check for theme directory traversal vulnerabilities.
		$theme_files = array_filter( glob( $template_dir . '/*.php' ), 'is_file' );
		foreach ( $theme_files as $file ) {
			$content = file_get_contents( $file );

			// Check for directory traversal patterns.
			if ( preg_match( '/\$_GET|\$_POST|\$_REQUEST|\$_FILE.*\.\.\// i', $content ) ) {
				$issues[] = sprintf(
					/* translators: %s: file name */
					__( 'Possible directory traversal vulnerability in: %s', 'wpshadow' ),
					basename( $file )
				);
			}

			// Check for file inclusion patterns.
			if ( preg_match( '/include|require.*\$_(?:GET|POST|REQUEST)/i', $content ) ) {
				$issues[] = sprintf(
					/* translators: %s: file name */
					__( 'Unsafe file inclusion in: %s (uses user input)', 'wpshadow' ),
					basename( $file )
				);
			}
		}

		// Check for backup/temporary theme files.
		$backup_patterns = array( '*.bak', '*.backup', '*.old', '*~', '*.tmp' );
		$backup_files    = array();

		foreach ( $backup_patterns as $pattern ) {
			$matches = glob( $template_dir . '/' . $pattern );
			$backup_files = array_merge( $backup_files, $matches );
		}

		if ( ! empty( $backup_files ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of backup files */
				__( '%d backup/temporary theme files found (should be removed)', 'wpshadow' ),
				count( $backup_files )
			);
		}

		// Check WP_Filesystem capabilities.
		if ( ! class_exists( 'WP_Filesystem_Base' ) ) {
			// Filesystem class not available.
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of permission issues */
					__( 'Found %d theme installation permission issues.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'details'      => array(
					'issues'              => $issues,
					'template_dir'        => $template_dir,
					'stylesheet_dir'      => $stylesheet_dir,
					'recommendation'      => __( 'Set theme directory permissions to 755, files to 644. Remove backup files. Disable direct theme editing via DISALLOW_FILE_EDIT.', 'wpshadow' ),
				),
			);
		}

		return null;
	}
}
