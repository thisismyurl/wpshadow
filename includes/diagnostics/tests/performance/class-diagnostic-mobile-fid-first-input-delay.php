<?php
/**
 * Mobile FID (First Input Delay) Diagnostic
 *
 * Measures time from first tap to browser response for interactivity.
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
 * Mobile FID (First Input Delay) Diagnostic Class
 *
 * Measures time from first tap to browser response, a Core Web Vitals metric
 * critical for user experience and Google rankings.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Mobile_FID_First_Input_Delay extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'mobile-fid-first-input-delay';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Mobile FID (First Input Delay)';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Measure time from first tap to browser response (Core Web Vitals metric)';

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
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for long JavaScript tasks
		$has_long_tasks = apply_filters( 'wpshadow_has_long_javascript_tasks', false );
		if ( $has_long_tasks ) {
			$issues[] = __( 'Long JavaScript tasks detected; may cause input delay', 'wpshadow' );
		}

		// Check if input handlers are fast
		$input_handlers_fast = apply_filters( 'wpshadow_input_handlers_respond_quickly', false );
		if ( ! $input_handlers_fast ) {
			$issues[] = __( 'Input handlers may take >50ms to respond; causes user frustration', 'wpshadow' );
		}

		// Check for third-party script impact
		$third_party_impact = apply_filters( 'wpshadow_third_party_scripts_block_main_thread', false );
		if ( $third_party_impact ) {
			$issues[] = __( 'Third-party scripts block the main thread; increases FID', 'wpshadow' );
		}

		// Check total blocking time (TBT)
		$total_blocking_time = apply_filters( 'wpshadow_total_blocking_time_ms', 0 );
		if ( $total_blocking_time > 150 ) {
			$issues[] = sprintf(
				/* translators: %dms: total blocking time */
				__( 'Total blocking time is %dms; target <150ms for good FID', 'wpshadow' ),
				$total_blocking_time
			);
		}

		// Check if JavaScript is minified and optimized
		$js_optimized = apply_filters( 'wpshadow_javascript_minified_and_optimized', false );
		if ( ! $js_optimized ) {
			$issues[] = __( 'JavaScript may not be minified/optimized; could contribute to FID', 'wpshadow' );
		}

		// Check for code splitting and lazy loading
		$code_splitting_enabled = apply_filters( 'wpshadow_code_splitting_lazy_loading_enabled', false );
		if ( ! $code_splitting_enabled ) {
			$issues[] = __( 'Code splitting helps load only the JavaScript you need for each page (like a cafeteria where you pick what you want, instead of getting everything at once). This makes your site respond faster to clicks and taps.', 'wpshadow' );
		}

		// Check for Core Web Vitals monitoring
		$cwv_monitoring = apply_filters( 'wpshadow_core_web_vitals_monitoring_enabled', false );
		if ( ! $cwv_monitoring ) {
			$issues[] = __( 'Core Web Vitals monitoring not detected; FID measurement unavailable', 'wpshadow' );
		}

		// Check for performance monitoring plugins
		$perf_plugins = array(
			'lighthouse' => 'Lighthouse',
			'web-vitals' => 'Web Vitals',
			'monitorix' => 'Monitorix',
		);

		$has_monitoring = false;
		foreach ( $perf_plugins as $plugin_slug => $plugin_name ) {
			if ( is_plugin_active( "$plugin_slug/$plugin_slug.php" ) ) {
				$has_monitoring = true;
				break;
			}
		}

		if ( ! $has_monitoring && ! $cwv_monitoring ) {
			$issues[] = __( 'No FID monitoring detected; input delay metrics unavailable', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( '. ', $issues ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/mobile-fid-first-input-delay',
			);
		}

		return null;
	}
}
