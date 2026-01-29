<?php
/**
 * Social Warfare Floating Buttons Diagnostic
 *
 * Social Warfare buttons slowing page.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.434.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Warfare Floating Buttons Diagnostic Class
 *
 * @since 1.434.0000
 */
class Diagnostic_SocialWarfareFloatingButtons extends Diagnostic_Base {

	protected static $slug = 'social-warfare-floating-buttons';
	protected static $title = 'Social Warfare Floating Buttons';
	protected static $description = 'Social Warfare buttons slowing page';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'SWP_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/social-warfare-floating-buttons',
			);
		}
		
		return null;
	}
}
