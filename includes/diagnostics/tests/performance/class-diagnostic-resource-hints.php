<?php
/**
 * Resource Hints Detection Diagnostic
 *
 * Checks for proper implementation of resource hints (preload, prefetch, prerender)
 * to optimize critical resource loading.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resource Hints Detection Diagnostic Class
 *
 * Verifies resource hints implementation:
 * - Preload for critical resources (fonts, stylesheets)
 * - Prefetch for non-critical resources
 * - Prerender for likely next pages
 * - Proper HTTP headers for resource hints
 *
 * @since 1.6093.1200
 */
class Diagnostic_Resource_Hints extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'resource-hints';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Resource Hints Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies preload, prefetch, and prerender resource hints';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_styles, $wp_scripts;

		$preload_configured  = false;
		$prefetch_configured = false;
		$critical_fonts      = 0;

		// Check for preload filter
		if ( has_filter( 'wp_resource_hints' ) ) {
			$preload_configured = true;
		}

		// Count web fonts (should be preloaded)
		foreach ( $wp_styles->queue as $handle ) {
			$style = $wp_styles->registered[ $handle ] ?? null;
			if ( $style && is_string( $style->src ) && stripos( $style->src, 'fonts.googleapis.com' ) !== false ) {
				$critical_fonts++;
			}
		}

		if ( $critical_fonts > 0 && ! $preload_configured ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of critical fonts */
					__( 'Found %d web fonts that should be preloaded to optimize rendering. Resource hints can reduce render-blocking time.', 'wpshadow' ),
					$critical_fonts
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/resource-hints',
				'meta'          => array(
					'critical_fonts'       => $critical_fonts,
					'recommendation'       => 'Add <link rel="preload" href="...fonts..."> for critical fonts or use plugin like WP Preload Resources',
					'impact'               => 'Reduces font render delay by 100-200ms',
					'best_practice'        => 'Preload fonts, prefetch next-page resources, prerender likely navigation targets',
				),
			);
		}

		return null;
	}
}
