<?php
/**
 * Ewww Image Optimizer Lazy Load Diagnostic
 *
 * Ewww Image Optimizer Lazy Load detected.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.753.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Ewww Image Optimizer Lazy Load Diagnostic Class
 *
 * @since 1.753.0000
 */
class Diagnostic_EwwwImageOptimizerLazyLoad extends Diagnostic_Base {

	protected static $slug = 'ewww-image-optimizer-lazy-load';
	protected static $title = 'Ewww Image Optimizer Lazy Load';
	protected static $description = 'Ewww Image Optimizer Lazy Load detected';
	protected static $family = 'functionality';

	public static function check() {
		if ( ! get_option( 'ewww_image_optimizer_enabled', '' ) && ! defined( 'EWWW_IMAGE_OPTIMIZER_PLUGIN_FILE' ) ) {
			return null;
		}

		$issues = array();

		// Check 1: Lazy load enabled
		$lazy_enabled = get_option( 'ewww_image_optimizer_lazy_load', 0 );
		if ( ! $lazy_enabled ) {
			$issues[] = 'Lazy loading not enabled';
		}

		// Check 2: Native lazy load enabled
		$native_lazy = get_option( 'ewww_image_optimizer_native_lazy_load', 0 );
		if ( ! $native_lazy ) {
			$issues[] = 'Native lazy loading not enabled';
		}

		// Check 3: Placeholder generation
		$placeholder = get_option( 'ewww_image_optimizer_placeholder_generation', '' );
		if ( empty( $placeholder ) || 'none' === $placeholder ) {
			$issues[] = 'Placeholder generation not configured';
		}

		// Check 4: Lazy load fade-in enabled
		$fade_in = get_option( 'ewww_image_optimizer_lazy_load_fade_in', 0 );
		if ( ! $fade_in ) {
			$issues[] = 'Lazy load fade-in effect not enabled';
		}

		// Check 5: Skip image exclusion
		$skip_class = get_option( 'ewww_image_optimizer_skip_lazy_load_class', '' );
		if ( empty( $skip_class ) ) {
			$issues[] = 'Skip lazy load class not configured';
		}

		// Check 6: Lazy load threshold
		$threshold = absint( get_option( 'ewww_image_optimizer_lazy_load_threshold', 0 ) );
		if ( $threshold <= 0 ) {
			$issues[] = 'Lazy load viewport threshold not configured';
		}

		$issue_count = count( $issues );
		if ( $issue_count > 0 ) {
			$base_threat = 40;
			$threat_multiplier = 6;
			$max_threat = 70;
			$threat_level = min( $max_threat, $base_threat + ( $issue_count * $threat_multiplier ) );

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					'Found %d EWWW lazy load issue(s): %s',
					$issue_count,
					implode( ', ', $issues )
				),
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/ewww-image-optimizer-lazy-load',
			);
		}

		return null;
	}
}
