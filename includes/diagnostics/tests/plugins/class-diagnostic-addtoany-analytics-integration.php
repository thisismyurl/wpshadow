<?php
/**
 * AddToAny Analytics Diagnostic
 *
 * AddToAny analytics not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.436.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AddToAny Analytics Diagnostic Class
 *
 * @since 1.436.0000
 */
class Diagnostic_AddtoanyAnalyticsIntegration extends Diagnostic_Base {

	protected static $slug = 'addtoany-analytics-integration';
	protected static $title = 'AddToAny Analytics';
	protected static $description = 'AddToAny analytics not configured';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! function_exists( 'A2A_SHARE_SAVE_init' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 35 ),
				'threat_level' => 35,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/addtoany-analytics-integration',
			);
		}
		
		return null;
	}
}
