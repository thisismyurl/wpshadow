<?php
/**
 * Product Page Load Time Diagnostic
 *
 * Checks if product pages load within acceptable time (<2.5 seconds).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Product Page Load Time Diagnostic Class
 *
 * Verifies that product pages load quickly enough to maintain
 * user engagement and reduce bounce rate.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Product_Page_Load_Time extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'product-page-load-time';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Product Page Load Time';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if product pages load within acceptable time (<2.5 seconds)';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the product page load time diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if product load time issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping product page check', 'wpshadow' );
			return null;
		}

		// Get a sample product.
		$products = wc_get_products( array(
			'limit'  => 5,
			'status' => 'publish',
		) );

		if ( empty( $products ) ) {
			$warnings[] = __( 'No products found - cannot check load time', 'wpshadow' );
			return null;
		}

		$load_times = array();

		// Test first 3 product pages.
		foreach ( array_slice( $products, 0, 3 ) as $product ) {
			$start_time = microtime( true );

			$response = wp_remote_get( $product->get_permalink(), array(
				'timeout'   => 10,
				'blocking'  => true,
				'sslverify' => false,
			) );

			$end_time = microtime( true );
			$load_time = ( $end_time - $start_time );
			$load_times[] = $load_time;
		}

		$avg_load_time = array_sum( $load_times ) / count( $load_times );
		$max_load_time = max( $load_times );

		$stats['avg_product_load_time'] = round( $avg_load_time, 2 );
		$stats['max_product_load_time'] = round( $max_load_time, 2 );

		if ( $avg_load_time > 3 ) {
			$issues[] = sprintf(
				/* translators: %s: seconds */
				__( 'Average product page load time is %s seconds (target: <2.5s)', 'wpshadow' ),
				round( $avg_load_time, 2 )
			);
		} elseif ( $avg_load_time > 2.5 ) {
			$warnings[] = sprintf(
				/* translators: %s: seconds */
				__( 'Average product page load time is %s seconds (target: <2.5s)', 'wpshadow' ),
				round( $avg_load_time, 2 )
			);
		}

		// Check for product image optimization.
		$image_optimization = is_plugin_active( 'optimole-wp/optimole-wp.php' ) ||
							  is_plugin_active( 'imagify/imagify.php' );

		$stats['image_optimization'] = $image_optimization;

		if ( ! $image_optimization ) {
			$warnings[] = __( 'No image optimization plugin - product images likely not optimized', 'wpshadow' );
		}

		// Check number of product images per page.
		$first_product = $products[0];
		$image_ids = $first_product->get_gallery_image_ids();
		$image_count = count( $image_ids ) + ( $first_product->get_image_id() ? 1 : 0 );

		$stats['avg_images_per_product'] = $image_count;

		if ( $image_count > 20 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'High number of product images (%d) - may slow down load time', 'wpshadow' ),
				$image_count
			);
		}

		// Check for lazy loading.
		$lazy_loading = get_option( 'woocommerce_lazy_load_images' );
		$stats['lazy_loading_enabled'] = boolval( $lazy_loading );

		if ( ! $lazy_loading ) {
			$warnings[] = __( 'Lazy loading not enabled for product images', 'wpshadow' );
		}

		// Check for product page caching.
		$cache_plugins = array(
			'wp-super-cache/wp-cache.php',
			'w3-total-cache/w3-total-cache.php',
		);

		$has_cache = false;
		foreach ( $cache_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_cache = true;
				break;
			}
		}

		$stats['page_cache_enabled'] = $has_cache;

		if ( ! $has_cache ) {
			$warnings[] = __( 'Page caching not enabled', 'wpshadow' );
		}

		// Check for JavaScript optimization.
		$js_minification = get_option( 'woocommerce_minify_javascript' );
		$stats['js_minification'] = boolval( $js_minification );

		if ( ! $js_minification ) {
			$warnings[] = __( 'JavaScript not minified', 'wpshadow' );
		}

		// Check for CSS optimization.
		$css_minification = get_option( 'woocommerce_minify_css' );
		$stats['css_minification'] = boolval( $css_minification );

		if ( ! $css_minification ) {
			$warnings[] = __( 'CSS not minified', 'wpshadow' );
		}

		// Check for CDN.
		$cdn_enabled = get_option( 'woocommerce_cdn_enabled' );
		$stats['cdn_enabled'] = boolval( $cdn_enabled );

		if ( ! $cdn_enabled ) {
			$warnings[] = __( 'CDN not enabled for static assets', 'wpshadow' );
		}

		// Check database query count.
		$query_count = get_num_queries();
		$stats['database_queries'] = $query_count;

		if ( $query_count > 100 ) {
			$warnings[] = sprintf(
				/* translators: %d: count */
				__( 'High database query count (%d) - optimize database queries', 'wpshadow' ),
				$query_count
			);
		}

		// Check for server response time.
		$ttfb = get_transient( 'woocommerce_product_page_ttfb' );
		if ( $ttfb ) {
			$stats['time_to_first_byte'] = round( $ttfb, 2 );

			if ( $ttfb > 1 ) {
				$warnings[] = sprintf(
					/* translators: %s: seconds */
					__( 'Time to first byte is %s seconds', 'wpshadow' ),
					round( $ttfb, 2 )
				);
			}
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Product page load time has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/product-page-load-time',
				'context'      => array(
					'stats'    => $stats,
					'issues'   => $issues,
					'warnings' => $warnings,
				),
			);
		}

		// If only warnings.
		if ( ! empty( $warnings ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Product page load time has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/product-page-load-time',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Product page load time is good.
	}
}
