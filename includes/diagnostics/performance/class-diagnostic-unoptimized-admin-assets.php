<?php
/**
 * Diagnostic: Unoptimized Admin Assets
 *
 * Detects admin scripts/styles loaded without defer/async or minification.
 *
 * Philosophy: Ridiculously Good (#7) - Faster admin = happier users
 * KB Link: https://wpshadow.com/kb/unoptimized-admin-assets
 * Training: https://wpshadow.com/training/unoptimized-admin-assets
 *
 * @package WPShadow
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Unoptimized Admin Assets diagnostic
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Unoptimized_Admin_Assets extends Diagnostic_Base {

	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Diagnostic result or null if no issue
	 */
	public static function check(): ?array {
		global $wp_scripts, $wp_styles;

		if ( ! is_admin() ) {
			// Simulate admin context
			if ( ! did_action( 'admin_enqueue_scripts' ) ) {
				return null;
			}
		}

		$issues = [];
		$total_size = 0;
		$unminified_count = 0;
		$blocking_count = 0;

		// Check scripts
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				if ( ! isset( $wp_scripts->registered[ $handle ] ) ) {
					continue;
				}

				$script = $wp_scripts->registered[ $handle ];
				
				// Check if script is minified
				if ( strpos( $script->src, '.min.js' ) === false && strpos( $script->src, '.min.css' ) === false ) {
					$unminified_count++;
				}

				// Check if blocking (in header without defer/async)
				if ( empty( $script->extra['group'] ) || $script->extra['group'] === 0 ) {
					if ( empty( $script->extra['defer'] ) && empty( $script->extra['async'] ) ) {
						$blocking_count++;
					}
				}
			}
		}

		// Check styles
		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				if ( ! isset( $wp_styles->registered[ $handle ] ) ) {
					continue;
				}

				$style = $wp_styles->registered[ $handle ];
				
				// Check if minified
				if ( strpos( $style->src, '.min.css' ) === false ) {
					$unminified_count++;
				}

				// Estimate size (rough guess)
				$total_size += 20; // Assume 20KB per file
			}
		}

		// Only flag if significant issues
		if ( $unminified_count < 5 && $blocking_count < 3 ) {
			return null;
		}

		$severity = ( $unminified_count > 10 || $blocking_count > 5 ) ? 'medium' : 'low';

		$description = sprintf(
			__( 'Your admin loads %d unminified assets and %d blocking scripts. This slows down the WordPress admin interface. WPShadow\'s Asset Optimizer can automatically defer non-critical scripts and add compression.', 'wpshadow' ),
			$unminified_count,
			$blocking_count
		);

		return [
			'id'                => 'unoptimized-admin-assets',
			'title'             => __( 'Unoptimized Admin Assets', 'wpshadow' ),
			'description'       => $description,
			'severity'          => $severity,
			'category'          => 'performance',
			'impact'            => 'medium',
			'effort'            => 'low',
			'kb_link'           => 'https://wpshadow.com/kb/unoptimized-admin-assets',
			'training_link'     => 'https://wpshadow.com/training/unoptimized-admin-assets',
			'affected_resource' => sprintf( '%d assets', $unminified_count + $blocking_count ),
			'metadata'          => [
				'unminified_count' => $unminified_count,
				'blocking_count'   => $blocking_count,
				'estimated_delay'  => sprintf( '%d ms', $blocking_count * 50 ),
			],
		];
	}

}