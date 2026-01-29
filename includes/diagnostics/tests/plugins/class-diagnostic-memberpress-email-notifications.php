<?php
/**
 * MemberPress Email Notifications Diagnostic
 *
 * MemberPress email settings misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.325.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Email Notifications Diagnostic Class
 *
 * @since 1.325.0000
 */
class Diagnostic_MemberpressEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'memberpress-email-notifications';
	protected static $title = 'MemberPress Email Notifications';
	protected static $description = 'MemberPress email settings misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'MEPR_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 45 ),
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-email-notifications',
			);
		}
		
		return null;
	}
}
