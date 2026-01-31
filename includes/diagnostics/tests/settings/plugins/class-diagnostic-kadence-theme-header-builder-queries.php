<?php
/**
 * Kadence Theme Header Builder Queries Diagnostic
 *
 * Kadence Theme Header Builder Queries needs optimization.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1301.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Kadence Theme Header Builder Queries Diagnostic Class
 *
 * @since 1.1301.0000
 */
class Diagnostic_KadenceThemeHeaderBuilderQueries extends Diagnostic_Base {

	protected static $slug = 'kadence-theme-header-builder-queries';
	protected static $title = 'Kadence Theme Header Builder Queries';
	protected static $description = 'Kadence Theme Header Builder Queries needs optimization';
	protected static $family = 'functionality';

	public static function check() {
		
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
				'kb_link'     => 'https://wpshadow.com/kb/kadence-theme-header-builder-queries',
			);
		}
		
		return null;
	}
}
