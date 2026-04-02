<?php
/**
 * Diagnostic: Friction Analysis Conducted
 *
 * Tests whether the site regularly analyzes and reduces barriers to conversion.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4534
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Behavioral
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Friction Analysis Diagnostic
 *
 * Checks for tools and processes that identify conversion barriers.
 * Regular friction analysis can increase conversions by 20-50%.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_Friction_Analysis extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'analyzes-conversion-friction';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Friction Analysis Conducted';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site analyzes and reduces conversion barriers';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for friction analysis tools.
	 *
	 * Detects heatmaps, session recording, form analytics, and optimization plugins.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if not implemented, null if present.
	 */
	public static function check() {
		$has_analytics_tools = 0;

		// Check for heatmap/session recording plugins.
		$analytics_plugins = array(
			'hotjar/hotjar.php'                              => 'Hotjar',
			'crazy-egg/crazy-egg.php'                        => 'Crazy Egg',
			'wp-user-frontend/wp-user-frontend.php'          => 'User Analytics',
			'clarity/clarity.php'                            => 'Microsoft Clarity',
		);

		foreach ( $analytics_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				++$has_analytics_tools;
			}
		}

		// Check for form analytics.
		$form_plugins = array(
			'wpforms-lite/wpforms.php',
			'gravityforms/gravityforms.php',
		);

		foreach ( $form_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				// These have built-in analytics.
				++$has_analytics_tools;
				break;
			}
		}

		// Check for A/B testing plugins.
		$testing_plugins = array(
			'nelio-ab-testing/nelio-ab-testing.php',
			'google-optimize/google-optimize.php',
		);

		foreach ( $testing_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				++$has_analytics_tools;
				break;
			}
		}

		// Check for Google Analytics with enhanced ecommerce.
		if ( class_exists( 'WooCommerce' ) ) {
			$ga_plugins = array(
				'google-analytics-for-wordpress/googleanalytics.php',
				'ga-google-analytics/ga-google-analytics.php',
			);

			foreach ( $ga_plugins as $plugin ) {
				if ( is_plugin_active( $plugin ) ) {
					++$has_analytics_tools;
					break;
				}
			}
		}

		// Minimum 1 friction analysis tool recommended.
		if ( $has_analytics_tools >= 1 ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No friction analysis tools detected. Identifying and removing conversion barriers can increase conversions by 20-50%. Implement heatmaps (Hotjar, Microsoft Clarity), session recording, or form analytics to understand where users struggle.',
				'wpshadow'
			),
			'severity'     => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/friction-analysis',
		);
	}
}
