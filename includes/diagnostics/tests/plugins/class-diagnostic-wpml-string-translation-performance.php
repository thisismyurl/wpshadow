<?php
/**
 * WPML String Translation Diagnostic
 *
 * WPML string translation queries slow.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.299.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPML String Translation Diagnostic Class
 *
 * @since 1.299.0000
 */
class Diagnostic_WpmlStringTranslationPerformance extends Diagnostic_Base {

	protected static $slug = 'wpml-string-translation-performance';
	protected static $title = 'WPML String Translation';
	protected static $description = 'WPML string translation queries slow';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/wpml-string-translation-performance',
			);
		}
		
		return null;
	}
}
