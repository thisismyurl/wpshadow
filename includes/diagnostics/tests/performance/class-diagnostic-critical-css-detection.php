<?php
/**
 * Critical CSS Detection Diagnostic
 *
 * Identifies critical CSS extraction and inlining opportunities.
 *
 * @since   1.26033.2110
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Critical CSS Detection Diagnostic
 *
 * Detects critical CSS extraction for above-the-fold optimization.
 *
 * @since 1.26033.2110
 */
class Diagnostic_Critical_CSS_Detection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'critical-css-detection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Critical CSS Detection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects critical CSS extraction and above-the-fold optimization';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26033.2110
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_styles;

		// Check for critical CSS plugins
		$critical_css_plugins = array(
			'wp-rocket/wp-rocket.php',
			'w3-total-cache/w3-total-cache.php',
			'autoptimize/autoptimize.php',
			'nitropack/main.php',
			'perfmatrix/perfmatrix.php',
		);

		$plugin_detected = false;
		foreach ( $critical_css_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$plugin_detected = true;
				break;
			}
		}

		// Count total stylesheets
		$stylesheet_count = 0;
		if ( isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style ) {
				if ( $wp_styles->is_enqueued( $handle ) ) {
					$stylesheet_count++;
				}
			}
		}

		// Check for inline critical CSS in custom CSS
		$custom_css = wp_get_custom_css();
		$has_inline_css = strlen( $custom_css ) > 500;

		// Generate findings if issues detected
		if ( ! $plugin_detected && $stylesheet_count > 5 && ! $has_inline_css ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of stylesheets */
					__( '%d stylesheets detected without critical CSS extraction. Inline above-the-fold CSS to improve FCP.', 'wpshadow' ),
					$stylesheet_count
				),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/critical-css-detection',
				'meta'         => array(
					'stylesheet_count'    => $stylesheet_count,
					'plugin_configured'   => $plugin_detected,
					'inline_css_found'    => $has_inline_css,
					'recommendation'      => 'Use WP Rocket or Autoptimize for critical CSS extraction',
					'impact_estimate'     => '150-350ms FCP improvement',
					'typical_size_kb'     => '15-30',
				),
			);
		}

		if ( $stylesheet_count > 10 && ! $plugin_detected ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: number of stylesheets */
					__( 'High stylesheet count (%d). Consolidate and extract critical CSS.', 'wpshadow' ),
					$stylesheet_count
				),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/critical-css-detection',
				'meta'         => array(
					'stylesheet_count'  => $stylesheet_count,
					'recommendation'    => 'Consolidate stylesheets and extract critical CSS',
					'impact_estimate'   => '200-400ms FCP improvement',
					'typical_size_kb'   => '20-40',
				),
			);
		}

		return null;
	}
}
