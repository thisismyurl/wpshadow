<?php
/**
 * Business Directory Email Notifications Diagnostic
 *
 * Business Directory emails excessive.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.550.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Business Directory Email Notifications Diagnostic Class
 *
 * @since 1.550.0000
 */
class Diagnostic_BusinessDirectoryEmailNotifications extends Diagnostic_Base {

	protected static $slug = 'business-directory-email-notifications';
	protected static $title = 'Business Directory Email Notifications';
	protected static $description = 'Business Directory emails excessive';
	protected static $family = 'performance';

	public static function check() {
		if ( ! function_exists( 'wpbdp' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/business-directory-email-notifications',
			);
		}
		
		return null;
	}
}
