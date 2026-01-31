<?php
/**
 * Rank Math Internal Linking Diagnostic
 *
 * Rank Math Internal Linking configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.699.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Rank Math Internal Linking Diagnostic Class
 *
 * @since 1.699.0000
 */
class Diagnostic_RankMathInternalLinking extends Diagnostic_Base {

	protected static $slug = 'rank-math-internal-linking';
	protected static $title = 'Rank Math Internal Linking';
	protected static $description = 'Rank Math Internal Linking configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! defined( 'RANK_MATH_VERSION' ) ) {
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
				'severity'    => 50,
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/rank-math-internal-linking',
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
