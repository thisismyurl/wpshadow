<?php
/**
 * Paid Memberships Pro Email Settings Diagnostic
 *
 * PMPro email configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.339.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paid Memberships Pro Email Settings Diagnostic Class
 *
 * @since 1.339.0000
 */
class Diagnostic_PaidMembershipsProEmailSettings extends Diagnostic_Base {

	protected static $slug = 'paid-memberships-pro-email-settings';
	protected static $title = 'Paid Memberships Pro Email Settings';
	protected static $description = 'PMPro email configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'PMPRO_VERSION' ) ) {
			return null;
		}
		
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
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/paid-memberships-pro-email-settings',
			);
		}
		
		return null;
	}
}
