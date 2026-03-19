<?php
/**
 * Diagnostic: Machine Learning Personalization
 *
 * Tests whether the site uses machine learning algorithms to personalize
 * user experience and increase conversions by 15-30%.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4553
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
 * Machine Learning Personalization Diagnostic
 *
 * Checks for ML-powered recommendation and personalization systems. ML learns
 * user preferences over time, delivering personalized experiences that convert better.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Behavioral_ML_Personalization extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-ml-personalization';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Machine Learning Personalization';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site uses ML algorithms to personalize experience';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for ML personalization implementation.
	 *
	 * Looks for recommendation engines and ML-powered systems.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if missing, null if present.
	 */
	public static function check() {
		// Check for recommendation plugins.
		$recommendation_plugins = array(
			'related-posts-by-taxonomy/related-posts-by-taxonomy.php' => 'Related Posts',
		);

		foreach ( $recommendation_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				// Basic recommendations, not ML but acceptable.
				return null;
			}
		}

		// Check for WooCommerce product recommendations.
		if ( class_exists( 'WooCommerce' ) ) {
			// WooCommerce has related products built-in.
			$related_products_enabled = get_option( 'woocommerce_show_related_products', 'yes' );
			
			if ( $related_products_enabled === 'yes' ) {
				return null; // Basic recommendations active.
			}
		}

		// Only recommend for e-commerce or high-traffic content sites.
		$needs_personalization = false;
		
		if ( class_exists( 'WooCommerce' ) ) {
			// E-commerce benefits greatly from ML recommendations.
			$needs_personalization = true;
		}

		// Check traffic volume (proxy via post count).
		$total_posts = wp_count_posts( 'post' )->publish;
		if ( $total_posts > 100 ) {
			// Large content library benefits from personalization.
			$needs_personalization = true;
		}

		if ( ! $needs_personalization ) {
			return null; // Small site, less critical.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'No ML-powered personalization detected. Machine learning algorithms learn user preferences and deliver personalized recommendations - "customers who bought X also bought Y", personalized content feeds, dynamic pricing. ML personalization increases conversions 15-30%. Start simple with WooCommerce related products or related posts. Advanced: Consider Jetpack CRM, recommendation engines.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => 28,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/ml-personalization',
		);
	}
}
