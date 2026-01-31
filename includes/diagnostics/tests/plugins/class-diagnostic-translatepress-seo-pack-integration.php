<?php
/**
 * Translatepress Seo Pack Integration Diagnostic
 *
 * Translatepress Seo Pack Integration misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1152.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Translatepress Seo Pack Integration Diagnostic Class
 *
 * @since 1.1152.0000
 */
class Diagnostic_TranslatepressSeoPackIntegration extends Diagnostic_Base {

	protected static $slug = 'translatepress-seo-pack-integration';
	protected static $title = 'Translatepress Seo Pack Integration';
	protected static $description = 'Translatepress Seo Pack Integration misconfigured';
	protected static $family = 'functionality';

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
				'severity'    => self::calculate_severity( 50 ),
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/translatepress-seo-pack-integration',
			);
		}
		
		return null;
	}
}
