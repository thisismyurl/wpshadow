<?php
/**
 * Paid Memberships Pro Member Directory Diagnostic
 *
 * PMPro member directory exposed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.335.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Paid Memberships Pro Member Directory Diagnostic Class
 *
 * @since 1.335.0000
 */
class Diagnostic_PaidMembershipsProMemberDirectory extends Diagnostic_Base {

	protected static $slug = 'paid-memberships-pro-member-directory';
	protected static $title = 'Paid Memberships Pro Member Directory';
	protected static $description = 'PMPro member directory exposed';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/paid-memberships-pro-member-directory',
			);
		}
		
		return null;
	}
}
