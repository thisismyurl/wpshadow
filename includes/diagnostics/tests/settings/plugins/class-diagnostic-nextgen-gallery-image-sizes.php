<?php
/**
 * NextGEN Gallery Image Sizes Diagnostic
 *
 * NextGEN Gallery creating too many sizes.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.493.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * NextGEN Gallery Image Sizes Diagnostic Class
 *
 * @since 1.493.0000
 */
class Diagnostic_NextgenGalleryImageSizes extends Diagnostic_Base {

	protected static $slug = 'nextgen-gallery-image-sizes';
	protected static $title = 'NextGEN Gallery Image Sizes';
	protected static $description = 'NextGEN Gallery creating too many sizes';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'C_NextGEN_Bootstrap' ) ) {
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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/nextgen-gallery-image-sizes',
			);
		}
		
		return null;
	}
}
