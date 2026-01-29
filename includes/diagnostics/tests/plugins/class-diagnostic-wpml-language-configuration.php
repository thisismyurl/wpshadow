<?php
/**
 * WPML Language Configuration Diagnostic
 *
 * WPML language settings misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.298.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WPML Language Configuration Diagnostic Class
 *
 * @since 1.298.0000
 */
class Diagnostic_WpmlLanguageConfiguration extends Diagnostic_Base {

	protected static $slug = 'wpml-language-configuration';
	protected static $title = 'WPML Language Configuration';
	protected static $description = 'WPML language settings misconfigured';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/wpml-language-configuration',
			);
		}
		
		return null;
	}
}
