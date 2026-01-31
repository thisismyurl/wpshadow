<?php
/**
 * All In One Wp Security File Permissions Diagnostic
 *
 * All In One Wp Security File Permissions misconfiguration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.865.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Wp Security File Permissions Diagnostic Class
 *
 * @since 1.865.0000
 */
class Diagnostic_AllInOneWpSecurityFilePermissions extends Diagnostic_Base {

	protected static $slug = 'all-in-one-wp-security-file-permissions';
	protected static $title = 'All In One Wp Security File Permissions';
	protected static $description = 'All In One Wp Security File Permissions misconfiguration';
	protected static $family = 'security';

	public static function check() {
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 70 ),
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-wp-security-file-permissions',
			);
		}
		
		return null;
	}
}
