<?php
/**
 * WPML Media Translation Diagnostic
 *
 * WPML media not translated properly.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.300.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPML Media Translation Diagnostic Class
 *
 * @since 1.300.0000
 */
class Diagnostic_WpmlMediaTranslation extends Diagnostic_Base {

	protected static $slug = 'wpml-media-translation';
	protected static $title = 'WPML Media Translation';
	protected static $description = 'WPML media not translated properly';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'ICL_SITEPRESS_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-media-translation',
			);
		}
		
		return null;
	}
}
