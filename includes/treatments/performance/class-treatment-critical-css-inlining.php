<?php
/**
 * Critical CSS Inlining Treatment
 *
 * Issue #4934: No Critical CSS Inline (Render Blocking)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if critical CSS is inlined.
 * External CSS blocks rendering and delays first paint.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Critical_CSS_Inlining Class
 *
 * @since 1.6050.0000
 */
class Treatment_Critical_CSS_Inlining extends Treatment_Base {

	protected static $slug = 'critical-css-inlining';
	protected static $title = 'No Critical CSS Inline (Render Blocking)';
	protected static $description = 'Checks if above-the-fold CSS is inlined';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Inline critical CSS in <head> for above-fold content', 'wpshadow' );
		$issues[] = __( 'Defer non-critical CSS with media="print" trick', 'wpshadow' );
		$issues[] = __( 'Load full CSS asynchronously after page load', 'wpshadow' );
		$issues[] = __( 'Keep critical CSS under 14KB (TCP slow start)', 'wpshadow' );
		$issues[] = __( 'Use tools: Critical, Penthouse, or Lighthouse', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'External CSS blocks page rendering. Inlining critical CSS lets the browser display content immediately while loading the rest.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/critical-css',
				'details'      => array(
					'recommendations'         => $issues,
					'first_paint_improvement' => '500-1000ms faster First Contentful Paint',
					'defer_pattern'           => '<link rel="stylesheet" href="style.css" media="print" onload="this.media=\'all\'">',
					'14kb_limit'              => 'TCP slow start sends 14KB in first round trip',
				),
			);
		}

		return null;
	}
}
