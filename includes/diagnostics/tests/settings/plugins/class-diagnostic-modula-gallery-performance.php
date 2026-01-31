<?php
/**
 * Modula Gallery Performance Diagnostic
 *
 * Modula Gallery slowing frontend.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.498.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modula Gallery Performance Diagnostic Class
 *
 * @since 1.498.0000
 */
class Diagnostic_ModulaGalleryPerformance extends Diagnostic_Base {

	protected static $slug = 'modula-gallery-performance';
	protected static $title = 'Modula Gallery Performance';
	protected static $description = 'Modula Gallery slowing frontend';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'MODULA_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/modula-gallery-performance',
			);
		}
		
		return null;
	}
}
