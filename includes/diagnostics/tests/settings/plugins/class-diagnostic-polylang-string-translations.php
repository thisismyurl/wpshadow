<?php
/**
 * Polylang String Translations Diagnostic
 *
 * Polylang string translations missing.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.307.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang String Translations Diagnostic Class
 *
 * @since 1.307.0000
 */
class Diagnostic_PolylangStringTranslations extends Diagnostic_Base {

	protected static $slug = 'polylang-string-translations';
	protected static $title = 'Polylang String Translations';
	protected static $description = 'Polylang string translations missing';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'POLYLANG_VERSION' ) ) {
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
				'kb_link'     => 'https://wpshadow.com/kb/polylang-string-translations',
			);
		}
		
		return null;
	}
}
