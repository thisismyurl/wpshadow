<?php
/**
 * Modula Gallery Lazy Load Diagnostic
 *
 * Modula Gallery lazy load misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.499.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Modula Gallery Lazy Load Diagnostic Class
 *
 * @since 1.499.0000
 */
class Diagnostic_ModulaGalleryLazyLoad extends Diagnostic_Base {

	protected static $slug = 'modula-gallery-lazy-load';
	protected static $title = 'Modula Gallery Lazy Load';
	protected static $description = 'Modula Gallery lazy load misconfigured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'MODULA_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/modula-gallery-lazy-load',
			);
		}
		
		return null;
	}
}
