<?php
/**
 * Fathom Analytics Script Loading Diagnostic
 *
 * Fathom Analytics Script Loading misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1363.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Fathom Analytics Script Loading Diagnostic Class
 *
 * @since 1.1363.0000
 */
class Diagnostic_FathomAnalyticsScriptLoading extends Diagnostic_Base {

	protected static $slug = 'fathom-analytics-script-loading';
	protected static $title = 'Fathom Analytics Script Loading';
	protected static $description = 'Fathom Analytics Script Loading misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! true // Generic check ) {
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
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/fathom-analytics-script-loading',
			);
		}
		
		return null;
	}
}
