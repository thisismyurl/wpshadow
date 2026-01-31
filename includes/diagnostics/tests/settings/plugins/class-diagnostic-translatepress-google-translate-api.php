<?php
/**
 * Translatepress Google Translate Api Diagnostic
 *
 * Translatepress Google Translate Api misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1154.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translatepress Google Translate Api Diagnostic Class
 *
 * @since 1.1154.0000
 */
class Diagnostic_TranslatepressGoogleTranslateApi extends Diagnostic_Base {

	protected static $slug = 'translatepress-google-translate-api';
	protected static $title = 'Translatepress Google Translate Api';
	protected static $description = 'Translatepress Google Translate Api misconfigured';
	protected static $family = 'security';

	public static function check() {
		if ( ! defined( 'TRP_PLUGIN_VERSION' ) ) {
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
				'severity'    => 65,
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-google-translate-api',
			);
		}
		
		return null;
	}
}
