<?php
/**
 * Landing Page Testing Diagnostic
 *
 * Tests if landing pages are tested upon creation.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Landing Page Testing Diagnostic Class
 *
 * Verifies that A/B or multivariate testing is used for landing pages.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Tests_Landing_Pages extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tests-landing-pages';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Landing Page Testing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if landing pages are tested upon creation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$testing_plugins = array(
			'nelio-ab-testing/nelio-ab-testing.php',
			'google-optimize/google-optimize.php',
			'ab-testing/ab-testing.php',
			'split-test-for-elementor/split-test-for-elementor.php',
		);

		foreach ( $testing_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null;
			}
		}

		$manual_flag = get_option( 'wpshadow_landing_page_testing_enabled' );
		if ( $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No landing page testing detected. Test headlines and layouts to improve conversions before scaling traffic.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/landing-page-testing',
			'persona'      => 'publisher',
		);
	}
}
