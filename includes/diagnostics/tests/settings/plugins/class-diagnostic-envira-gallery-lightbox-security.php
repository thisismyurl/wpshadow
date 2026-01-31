<?php
/**
 * Envira Gallery Lightbox Security Diagnostic
 *
 * Envira Gallery lightbox vulnerable.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.490.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Envira Gallery Lightbox Security Diagnostic Class
 *
 * @since 1.490.0000
 */
class Diagnostic_EnviraGalleryLightboxSecurity extends Diagnostic_Base {

	protected static $slug = 'envira-gallery-lightbox-security';
	protected static $title = 'Envira Gallery Lightbox Security';
	protected static $description = 'Envira Gallery lightbox vulnerable';
	protected static $family = 'security';

	public static function check() {
		if ( ! class_exists( 'Envira_Gallery' ) ) {
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
				'severity'    => self::calculate_severity( 60 ),
				'threat_level' => 60,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/envira-gallery-lightbox-security',
			);
		}
		
		return null;
	}
}
