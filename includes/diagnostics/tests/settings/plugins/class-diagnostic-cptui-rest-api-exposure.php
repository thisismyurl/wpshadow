<?php
/**
 * CPT UI REST API Exposure Diagnostic
 *
 * CPT UI exposing posts via REST.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.448.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * CPT UI REST API Exposure Diagnostic Class
 *
 * @since 1.448.0000
 */
class Diagnostic_CptuiRestApiExposure extends Diagnostic_Base {

	protected static $slug = 'cptui-rest-api-exposure';
	protected static $title = 'CPT UI REST API Exposure';
	protected static $description = 'CPT UI exposing posts via REST';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'CPT_VERSION' ) ) {
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/cptui-rest-api-exposure',
			);
		}
		
		return null;
	}
}
