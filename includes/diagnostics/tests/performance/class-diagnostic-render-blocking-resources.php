<?php
/**
 * Render-Blocking Resources Diagnostic
 *
 * Identifies all render-blocking resources and quantifies their impact on
 * First Contentful Paint performance.
 *
 * @since   1.26033.2092
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Render-Blocking Resources Diagnostic Class
 *
 * Analyzes render-blocking resources:
 * - Blocking stylesheets count
 * - Blocking scripts count
 * - Total blocking size estimate
 * - Impact calculation
 *
 * @since 1.26033.2092
 */
class Diagnostic_Render_Blocking_Resources extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'render-blocking-resources';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Render-Blocking Resources';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Identifies render-blocking resources impacting FCP';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2092
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_styles, $wp_scripts;

		$blocking_resources = array(
			'stylesheets' => 0,
			'scripts'     => 0,
		);

		// Count blocking stylesheets
		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				$style = $wp_styles->registered[ $handle ] ?? null;
				if ( $style && in_array( $style->media ?? 'all', array( 'all', 'screen' ), true ) ) {
					$blocking_resources['stylesheets']++;
				}
			}
		}

		// Count blocking scripts (in head tag, before defer/async)
		if ( ! empty( $wp_scripts->queue ) ) {
			foreach ( $wp_scripts->queue as $handle ) {
				$script = $wp_scripts->registered[ $handle ] ?? null;
				if ( $script && ! isset( $script->extra['async'] ) && ! isset( $script->extra['defer'] ) ) {
					$blocking_resources['scripts']++;
				}
			}
		}

		$total_blocking = $blocking_resources['stylesheets'] + $blocking_resources['scripts'];

		if ( $total_blocking >= 3 ) {
			// Estimate impact: ~50-100ms per blocking resource
			$estimated_impact = $total_blocking * 75; // milliseconds

			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: total blocking resources, %d: estimated impact in ms */
					__( 'Found %d render-blocking resources. This could delay FCP by ~%dms.', 'wpshadow' ),
					$total_blocking,
					$estimated_impact
				),
				'severity'      => $total_blocking >= 5 ? 'high' : 'medium',
				'threat_level'  => $total_blocking >= 5 ? 70 : 50,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/render-blocking-resources',
				'meta'          => array(
					'blocking_stylesheets' => $blocking_resources['stylesheets'],
					'blocking_scripts'     => $blocking_resources['scripts'],
					'total_blocking'       => $total_blocking,
					'estimated_fcp_delay'  => $estimated_impact . 'ms',
					'recommendation'       => 'Inline critical CSS, defer non-critical CSS, use async/defer for scripts',
					'impact'               => 'Removing blocking resources could improve FCP by ' . $estimated_impact . 'ms',
			),
			);
		}

		return null;
	}
}
