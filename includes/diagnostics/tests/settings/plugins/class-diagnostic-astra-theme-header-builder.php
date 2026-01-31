<?php
/**
 * Astra Theme Header Builder Diagnostic
 *
 * Astra Theme Header Builder needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1292.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Astra Theme Header Builder Diagnostic Class
 *
 * @since 1.1292.0000
 */
class Diagnostic_AstraThemeHeaderBuilder extends Diagnostic_Base {

	protected static $slug = 'astra-theme-header-builder';
	protected static $title = 'Astra Theme Header Builder';
	protected static $description = 'Astra Theme Header Builder needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/astra-theme-header-builder',
			);
		}
		
		return null;
	}
}
