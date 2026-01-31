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
		$configured = get_option('diagnostic_' . self::$slug, false);
		if (!$configured) {
			$issues[] = 'not configured';
		}
		$has_issue = !empty($issues);
		
		if ( $has_issue ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => self::$description,
				'severity'    => 40,
				'threat_level' => 40,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/polylang-taxonomy-sync',
			);
		}
		

		// Feature availability checks
		if ( ! function_exists( 'add_action' ) ) {
			$issues[] = __( 'WordPress hooks unavailable', 'wpshadow' );
		}
		if ( empty( $GLOBALS['wpdb'] ) ) {
			$issues[] = __( 'Database not initialized', 'wpshadow' );
		}
		// Verify core functionality
		if ( ! function_exists( 'get_post' ) ) {
			$issues[] = __( 'Post functionality not available', 'wpshadow' );
		}
		return null;
	}
}
