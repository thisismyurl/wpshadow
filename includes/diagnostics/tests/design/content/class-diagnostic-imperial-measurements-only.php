<?php
/**
 * Imperial Measurements Only Diagnostic
 *
 * Checks if content or store settings use only imperial measurements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Content
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Imperial Measurements Only Diagnostic Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Imperial_Measurements_Only extends Diagnostic_Base {

	/**
	 * The diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'imperial-measurements-only';

	/**
	 * The diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Unit Measurements Hardcoded as Imperial';

	/**
	 * The diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether measurements include metric equivalents';

	/**
	 * The family this diagnostic belongs to.
	 *
	 * @var string
	 */
	protected static $family = 'content';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$stats  = array(
			'imperial_only_posts' => array(),
			'imperial_hits'       => 0,
			'metric_hits'         => 0,
		);

		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$dimension_unit = (string) get_option( 'woocommerce_dimension_unit', '' );
			$weight_unit    = (string) get_option( 'woocommerce_weight_unit', '' );

			$stats['woocommerce_dimension_unit'] = $dimension_unit;
			$stats['woocommerce_weight_unit']    = $weight_unit;

			$imperial_dimension_units = array( 'in', 'ft', 'yd' );
			$imperial_weight_units    = array( 'lbs', 'oz' );

			if ( in_array( $dimension_unit, $imperial_dimension_units, true ) ) {
				$issues[] = __( 'WooCommerce dimensions use imperial units only', 'wpshadow' );
			}

			if ( in_array( $weight_unit, $imperial_weight_units, true ) ) {
				$issues[] = __( 'WooCommerce weight uses imperial units only', 'wpshadow' );
			}
		}

		$posts = get_posts(
			array(
				'post_type'      => array( 'post', 'page' ),
				'post_status'    => 'publish',
				'posts_per_page' => 10,
			)
		);

		$imperial_pattern = '/\b\d+(?:\.\d+)?\s*(?:ft|feet|foot|in|inch|inches|yd|yard|yards|mi|mile|miles|lb|lbs|pound|pounds|oz|ounce|ounces|fahrenheit)\b/i';
		$metric_pattern   = '/\b\d+(?:\.\d+)?\s*(?:m|meter|meters|cm|centimeter|centimeters|mm|millimeter|millimeters|km|kilometer|kilometers|kg|kilogram|kilograms|g|gram|grams|celsius)\b/i';

		foreach ( $posts as $post ) {
			$content = wp_strip_all_tags( $post->post_content );

			$has_imperial = (bool) preg_match( $imperial_pattern, $content );
			$has_metric   = (bool) preg_match( $metric_pattern, $content );

			if ( $has_imperial ) {
				$stats['imperial_hits']++;
			}

			if ( $has_metric ) {
				$stats['metric_hits']++;
			}

			if ( $has_imperial && ! $has_metric ) {
				$stats['imperial_only_posts'][] = get_the_title( $post );
			}
		}

		if ( ! empty( $stats['imperial_only_posts'] ) ) {
			$issues[] = __( 'Some pages show imperial measurements without metric equivalents', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Most of the world uses metric measurements. When measurements are shown only in feet or pounds, many visitors must convert them manually, which adds friction and confusion. Showing both units helps everyone understand instantly.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/metric-units',
			'context'      => array(
				'stats'  => $stats,
				'issues' => $issues,
			),
		);
	}
}
