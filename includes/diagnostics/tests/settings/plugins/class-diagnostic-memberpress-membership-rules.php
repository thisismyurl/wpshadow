<?php
/**
 * MemberPress Membership Rules Diagnostic
 *
 * MemberPress access rules misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.320.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Membership Rules Diagnostic Class
 *
 * @since 1.320.0000
 */
class Diagnostic_MemberpressMembershipRules extends Diagnostic_Base {

	protected static $slug = 'memberpress-membership-rules';
	protected static $title = 'MemberPress Membership Rules';
	protected static $description = 'MemberPress access rules misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		// Check if feature is configured
		$option_prefix = 'diagnostic_' . str_replace('-', '_', self::$slug);
		$configured = get_option($option_prefix, false);
		if (!$configured) {
			$issues[] = 'feature not configured';
		}
		$has_issue = !empty($issues)
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-membership-rules',
			);
		}
		
		return null;
	}
}
