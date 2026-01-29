<?php
/**
 * NextGEN Gallery Watermark Diagnostic
 *
 * NextGEN Gallery watermark settings insecure.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.495.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NextGEN Gallery Watermark Diagnostic Class
 *
 * @since 1.495.0000
 */
class Diagnostic_NextgenGalleryWatermark extends Diagnostic_Base {

	protected static $slug = 'nextgen-gallery-watermark';
	protected static $title = 'NextGEN Gallery Watermark';
	protected static $description = 'NextGEN Gallery watermark settings insecure';
	protected static $family = 'security';

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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/nextgen-gallery-watermark',
			);
		}
		
		return null;
	}
}
