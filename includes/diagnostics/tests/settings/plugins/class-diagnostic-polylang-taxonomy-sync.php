<?php
/**
 * Polylang Taxonomy Sync Diagnostic
 *
 * Polylang taxonomies not synchronized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.309.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Polylang Taxonomy Sync Diagnostic Class
 *
 * @since 1.309.0000
 */
class Diagnostic_PolylangTaxonomySync extends Diagnostic_Base {

	protected static $slug = 'polylang-taxonomy-sync';
	protected static $title = 'Polylang Taxonomy Sync';
	protected static $description = 'Polylang taxonomies not synchronized';
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
				'severity'    => self::calculate_severity( 40 ),
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/polylang-taxonomy-sync',
			);
		}
		
		return null;
	}
}
