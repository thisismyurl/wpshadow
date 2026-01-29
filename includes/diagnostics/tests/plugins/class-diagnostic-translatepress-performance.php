<?php
/**
 * TranslatePress Performance Diagnostic
 *
 * TranslatePress database queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.316.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * TranslatePress Performance Diagnostic Class
 *
 * @since 1.316.0000
 */
class Diagnostic_TranslatepressPerformance extends Diagnostic_Base {

	protected static $slug = 'translatepress-performance';
	protected static $title = 'TranslatePress Performance';
	protected static $description = 'TranslatePress database queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 55 ),
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-performance',
			);
		}
		
		return null;
	}
}
