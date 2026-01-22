<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Canvas/WebGL Hotspots (FE-333)
 *
 * Flags heavy canvas/WebGL usage impacting CPU/GPU.
 * Philosophy: Show value (#9) and educate (#5) with clear, actionable insights.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 1.2601.2200
 */
class Diagnostic_CanvasWebglHotspots extends Diagnostic_Base {
	/**
	 * Run the diagnostic check
	 *
	 * @return array|null Array with finding details or null if no issue found
	 */
	public static function check(): ?array {
		$hotspot_ms    = (int) get_transient( 'wpshadow_canvas_hotspot_ms' );
		$hotspot_count = (int) get_transient( 'wpshadow_canvas_hotspot_count' );

		if ( $hotspot_ms > 120 || $hotspot_count > 0 ) {
			return array(
				'id'            => 'canvas-webgl-hotspots',
				'title'         => __( 'Canvas/WebGL hotspots detected', 'wpshadow' ),
				'description'   => __( 'Heavy canvas/WebGL rendering is impacting CPU/GPU. Reduce draw calls, lower resolution, or throttle animation frame rates.', 'wpshadow' ),
				'severity'      => 'medium',
				'category'      => 'other',
				'kb_link'       => 'https://wpshadow.com/kb/canvas-performance/',
				'training_link' => 'https://wpshadow.com/training/webgl-performance/',
				'auto_fixable'  => false,
				'threat_level'  => 50,
				'hotspot_ms'    => $hotspot_ms,
			);
		}

		return null;
	}
}
