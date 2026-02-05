<?php
/**
 * Critical CSS Inline Detection Treatment
 *
 * Checks if critical CSS is inlined in the document head to improve
 * First Contentful Paint and reduce render-blocking resources.
 *
 * @since   1.6033.2080
 * @package WPShadow\Treatments
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Critical CSS Inline Detection Treatment Class
 *
 * Verifies critical CSS implementation:
 * - Inline critical CSS in head
 * - Defer non-critical CSS
 * - Proper head tag optimization
 *
 * @since 1.6033.2080
 */
class Treatment_Critical_Css_Inline extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'critical-css-inline';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Critical CSS Inline Detection';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if critical CSS is inlined to optimize FCP';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6033.2080
	 * @return array|null Finding array if issues found, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		$critical_css_plugin = false;
		$render_blocking_css = 0;

		// Check for critical CSS plugins
		$critical_css_plugins = array(
			'critical/critical.php'                                    => 'Critical CSS',
			'perfmatrix-critical-css/perfmatrix-critical-css.php'      => 'PerfMatrix Critical CSS',
			'wp-critical-css/wp-critical-css.php'                      => 'WP Critical CSS',
			'autoptimize/autoptimize.php'                              => 'Autoptimize (with inline)',
			'wp-rocket/wp-rocket.php'                                  => 'WP Rocket (with CCSS)',
		);

		foreach ( $critical_css_plugins as $plugin_path => $plugin_name ) {
			if ( is_plugin_active( $plugin_path ) ) {
				$critical_css_plugin = true;
				break;
			}
		}

		// Count render-blocking stylesheets
		if ( ! empty( $wp_styles->queue ) ) {
			foreach ( $wp_styles->queue as $handle ) {
				$style = $wp_styles->registered[ $handle ] ?? null;
				if ( $style && in_array( $style->media ?? 'all', array( 'all', 'screen' ), true ) ) {
					$render_blocking_css++;
				}
			}
		}

		if ( ! $critical_css_plugin && $render_blocking_css > 3 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					/* translators: %d: number of render-blocking stylesheets */
					__( 'Found %d render-blocking stylesheets. Inlining critical CSS can improve FCP by 15-25%%.', 'wpshadow' ),
					$render_blocking_css
				),
				'severity'      => 'medium',
				'threat_level'  => 35,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/critical-css',
				'meta'          => array(
					'render_blocking_css'  => $render_blocking_css,
					'plugin_detected'      => $critical_css_plugin,
					'recommendation'       => 'Install a critical CSS plugin (WP Rocket, Autoptimize, or Perfmatrix) to automatically inline critical CSS',
					'impact'               => 'Improves FCP by 15-25%, reduces render-blocking time by 100-300ms',
					'best_practice'        => 'Inline critical CSS in head, defer non-critical CSS',
				),
			);
		}

		return null;
	}
}
