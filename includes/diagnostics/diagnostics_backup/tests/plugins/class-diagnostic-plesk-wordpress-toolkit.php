<?php
/**
 * Plesk Wordpress Toolkit Diagnostic
 *
 * Plesk Wordpress Toolkit needs attention.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1033.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Plesk Wordpress Toolkit Diagnostic Class
 *
 * @since 1.1033.0000
 */
class Diagnostic_PleskWordpressToolkit extends Diagnostic_Base {

	protected static $slug = 'plesk-wordpress-toolkit';
	protected static $title = 'Plesk Wordpress Toolkit';
	protected static $description = 'Plesk Wordpress Toolkit needs attention';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'PLESK_WORDPRESS_TOOLKIT' ) && ! get_option( 'plesk_wp_toolkit', false ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Verify smart updates are enabled
		$smart_updates = get_option( 'plesk_wp_toolkit_smart_updates', 0 );
		if ( ! $smart_updates ) {
			$issues[] = 'Smart updates not enabled';
		}

		// Check 2: Check for security hardening
		$hardening = get_option( 'plesk_wp_toolkit_security_hardening', 0 );
		if ( ! $hardening ) {
			$issues[] = 'Security hardening not enabled';
		}

		// Check 3: Verify auto updates
		$auto_updates = get_option( 'plesk_wp_toolkit_auto_updates', 0 );
		if ( ! $auto_updates ) {
			$issues[] = 'Auto updates not enabled';
		}

		// Check 4: Check for staging environment
		$staging = get_option( 'plesk_wp_toolkit_staging', 0 );
		if ( ! $staging ) {
			$issues[] = 'Staging environment not configured';
		}

		// Check 5: Verify backup management
		$backups = get_option( 'plesk_wp_toolkit_backups', 0 );
		if ( ! $backups ) {
			$issues[] = 'Backup management not enabled';
		}

		// Check 6: Check for file permissions scan
		$file_permissions = get_option( 'plesk_wp_toolkit_file_permissions_scan', 0 );
		if ( ! $file_permissions ) {
			$issues[] = 'File permissions scan not enabled';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d Plesk WordPress Toolkit issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/plesk-wordpress-toolkit',
			);
		}

		return null;
	}
}
