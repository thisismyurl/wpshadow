<?php
/**
 * MemberPress Subscription Management Diagnostic
 *
 * MemberPress subscriptions not managed properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.321.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Subscription Management Diagnostic Class
 *
 * @since 1.321.0000
 */
class Diagnostic_MemberpressSubscriptionManagement extends Diagnostic_Base {

	protected static $slug = 'memberpress-subscription-management';
	protected static $title = 'MemberPress Subscription Management';
	protected static $description = 'MemberPress subscriptions not managed properly';
	protected static $family = 'functionality';

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
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => 55,
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-subscription-management',
			);
		}
		
		return null;
	}
}
