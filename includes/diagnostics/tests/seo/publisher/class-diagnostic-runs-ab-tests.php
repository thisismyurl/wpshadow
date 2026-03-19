<?php
/**
 * AB Testing Program Diagnostic
 *
 * Tests for evidence of ongoing A/B testing program.
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
 * AB Testing Program Diagnostic Class
 *
 * Verifies A/B testing tools are configured.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Runs_Ab_Tests extends Diagnostic_Base {

	protected static $slug = 'runs-ab-tests';
	protected static $title = 'AB Testing Program';
	protected static $description = 'Tests for evidence of ongoing A/B testing program';
	protected static $family = 'publisher';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
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

		$manual_flag = get_option( 'wpshadow_ab_testing_program' );
		if ( $manual_flag ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No A/B testing program detected. Test headlines, layouts, and calls to action to improve conversions.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ab-testing-program',
			'persona'      => 'publisher',
		);
	}
}
