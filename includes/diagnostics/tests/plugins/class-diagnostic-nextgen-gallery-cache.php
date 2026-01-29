<?php
/**
 * NextGEN Gallery Cache Diagnostic
 *
 * NextGEN Gallery cache not configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.494.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NextGEN Gallery Cache Diagnostic Class
 *
 * @since 1.494.0000
 */
class Diagnostic_NextgenGalleryCache extends Diagnostic_Base {

	protected static $slug = 'nextgen-gallery-cache';
	protected static $title = 'NextGEN Gallery Cache';
	protected static $description = 'NextGEN Gallery cache not configured';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'C_NextGEN_Bootstrap' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/nextgen-gallery-cache',
			);
		}
		
		return null;
	}
}
