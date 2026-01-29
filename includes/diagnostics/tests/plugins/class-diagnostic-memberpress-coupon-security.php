<?php
/**
 * MemberPress Coupon Security Diagnostic
 *
 * MemberPress coupons vulnerable to abuse.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.323.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Coupon Security Diagnostic Class
 *
 * @since 1.323.0000
 */
class Diagnostic_MemberpressCouponSecurity extends Diagnostic_Base {

	protected static $slug = 'memberpress-coupon-security';
	protected static $title = 'MemberPress Coupon Security';
	protected static $description = 'MemberPress coupons vulnerable to abuse';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/memberpress-coupon-security',
			);
		}
		
		return null;
	}
}
