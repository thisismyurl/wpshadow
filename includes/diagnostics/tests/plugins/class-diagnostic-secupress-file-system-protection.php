<?php
/**
 * Secupress File System Protection Diagnostic
 *
 * Secupress File System Protection misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.871.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Secupress File System Protection Diagnostic Class
 *
 * @since 1.871.0000
 */
class Diagnostic_SecupressFileSystemProtection extends Diagnostic_Base {

	protected static $slug = 'secupress-file-system-protection';
	protected static $title = 'Secupress File System Protection';
	protected static $description = 'Secupress File System Protection misconfiguration';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'SECUPRESS_VERSION' ) && ! function_exists( 'secupress_get_option' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify file editor is disabled
		$file_editor = get_option( 'secupress_disallow_file_edit', 0 );
		if ( ! $file_editor ) {
			$issues[] = 'File editor not disabled';
		}

		// Check 2: Check for file permissions hardening
		$file_permissions = get_option( 'secupress_file_permissions', 0 );
		if ( ! $file_permissions ) {
			$issues[] = 'File permissions hardening not enabled';
		}

		// Check 3: Verify sensitive files protection
		$protect_files = get_option( 'secupress_protect_files', 0 );
		if ( ! $protect_files ) {
			$issues[] = 'Sensitive file protection not enabled';
		}

		// Check 4: Check for directory browsing prevention
		$directory_listing = get_option( 'secupress_directory_listing', 0 );
		if ( ! $directory_listing ) {
			$issues[] = 'Directory listing prevention not enabled';
		}

		// Check 5: Verify file scanner
		$file_scanner = get_option( 'secupress_file_scanner', 0 );
		if ( ! $file_scanner ) {
			$issues[] = 'File scanner not enabled';
		}

		// Check 6: Check for wp-config protection
		$config_protection = get_option( 'secupress_wpconfig_protection', 0 );
		if ( ! $config_protection ) {
			$issues[] = 'wp-config.php protection not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 70;
			$threat_multiplier = 5;
			$max_threat = 95;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d SecuPress file system protection issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/secupress-file-system-protection',
			);
		}

		return null;
	}
}
