<?php
/**
 * Envira Gallery Performance Diagnostic
 *
 * Envira Gallery slowing page loads.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.488.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Envira Gallery Performance Diagnostic Class
 *
 * @since 1.488.0000
 */
class Diagnostic_EnviraGalleryPerformance extends Diagnostic_Base {

	protected static $slug = 'envira-gallery-performance';
	protected static $title = 'Envira Gallery Performance';
	protected static $description = 'Envira Gallery slowing page loads';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'Envira_Gallery' ) ) {
			return null;
		}
		
		$has_issue = false;
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/envira-gallery-performance',
			);
		}
		
		return null;
	}
}
