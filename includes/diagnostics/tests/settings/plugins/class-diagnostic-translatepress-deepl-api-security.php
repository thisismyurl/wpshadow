<?php
/**
 * Translatepress Deepl Api Security Diagnostic
 *
 * Translatepress Deepl Api Security misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1153.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translatepress Deepl Api Security Diagnostic Class
 *
 * @since 1.1153.0000
 */
class Diagnostic_TranslatepressDeeplApiSecurity extends Diagnostic_Base {

	protected static $slug = 'translatepress-deepl-api-security';
	protected static $title = 'Translatepress Deepl Api Security';
	protected static $description = 'Translatepress Deepl Api Security misconfigured';
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
				'severity'    => self::calculate_severity( 65 ),
				'threat_level' => 65,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-deepl-api-security',
			);
		}
		
		return null;
	}
}
