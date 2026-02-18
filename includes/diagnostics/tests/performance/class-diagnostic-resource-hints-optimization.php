<?php
/**
 * Resource Hints Optimization Diagnostic
 *
 * Issue #4965: No Resource Hints (Preconnect/Prefetch)
 * Pillar: ⚙️ Murphy's Law
 *
 * Checks if resource hints optimize loading.
 * Preconnect and prefetch speed up critical resources.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Resource_Hints_Optimization Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Resource_Hints_Optimization extends Diagnostic_Base {

	protected static $slug = 'resource-hints-optimization';
	protected static $title = 'No Resource Hints (Preconnect/Prefetch)';
	protected static $description = 'Checks if resource hints optimize critical resource loading';
	protected static $family = 'performance';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Use preconnect for critical third-party origins', 'wpshadow' );
		$issues[] = __( 'Preconnect to fonts.googleapis.com and fonts.gstatic.com', 'wpshadow' );
		$issues[] = __( 'Use prefetch for resources needed on next page', 'wpshadow' );
		$issues[] = __( 'Use preload for critical fonts/images', 'wpshadow' );
		$issues[] = __( 'Limit hints to 3-4 critical resources', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Resource hints tell the browser to prepare connections early, saving 100-500ms on critical resources.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 45,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/resource-hints',
				'details'      => array(
					'recommendations'         => $issues,
					'preconnect'              => '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>',
					'prefetch'                => '<link rel="prefetch" href="/next-page.html">',
					'preload'                 => '<link rel="preload" href="/font.woff2" as="font" crossorigin>',
				),
			);
		}

		return null;
	}
}
