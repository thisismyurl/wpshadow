<?php
/**
 * Checkout Speed Diagnostic (Batch 7)
 *
 * Checks if checkout pages load within acceptable time (<2 seconds).
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1415
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Checkout Speed Diagnostic Class
 *
 * Verifies that the checkout page loads quickly to minimize cart
 * abandonment and improve conversion rates.
 *
 * @since 1.6035.1415
 */
class Diagnostic_Checkout_Speed_Extended extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'checkout-speed-extended';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Checkout Speed Extended';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Advanced checkout performance analysis';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ecommerce';

	/**
	 * Run the checkout speed diagnostic check.
	 *
	 * @since  1.6035.1415
	 * @return array|null Finding array if checkout speed issues detected, null otherwise.
	 */
	public static function check() {
		$issues    = array();
		$warnings  = array();
		$stats     = array();

		// Check for WooCommerce.
		if ( ! function_exists( 'wc' ) || ! class_exists( 'WooCommerce' ) ) {
			$warnings[] = __( 'WooCommerce not active - skipping checkout speed check', 'wpshadow' );
			return null;
		}

		// Get checkout URL.
		$checkout_url = wc_get_checkout_url();
		$stats['checkout_url'] = $checkout_url;

		// Measure checkout page load time.
		$start_time = microtime( true );

		$response = wp_remote_get( $checkout_url, array(
			'timeout'   => 10,
			'blocking'  => true,
			'sslverify' => false,
			'user-agent' => 'WPShadow Diagnostic',
		) );

		$end_time = microtime( true );
		$checkout_load_time = $end_time - $start_time;

		$stats['checkout_load_time_seconds'] = round( $checkout_load_time, 2 );

		if ( $checkout_load_time > 3 ) {
			$issues[] = sprintf(
				/* translators: %s: seconds */
				__( 'Checkout page load time is %s seconds (target: <2s)', 'wpshadow' ),
				round( $checkout_load_time, 2 )
			);
		} elseif ( $checkout_load_time > 2 ) {
			$warnings[] = sprintf(
				/* translators: %s: seconds */
				__( 'Checkout page load time is %s seconds (target: <2s)', 'wpshadow' ),
				round( $checkout_load_time, 2 )
			);
		}

		// Check response headers.
		$content_length = wp_remote_retrieve_header( $response, 'content-length' );
		$stats['checkout_page_size'] = ! empty( $content_length ) ? intval( $content_length ) : 'Unknown';

		if ( ! empty( $content_length ) && $content_length > 1000000 ) {
			$warnings[] = sprintf(
				/* translators: %d: KB */
				__( 'Checkout page size is %dKB - very large for slow connections', 'wpshadow' ),
				intval( $content_length / 1024 )
			);
		}

		// Check for caching headers.
		$cache_control = wp_remote_retrieve_header( $response, 'cache-control' );
		$stats['cache_control'] = $cache_control ?: 'Not set';

		if ( ! $cache_control || strpos( $cache_control, 'no-cache' ) !== false ) {
			$warnings[] = __( 'Checkout page caching disabled - may impact performance', 'wpshadow' );
		}

		// Check for gzip compression.
		$content_encoding = wp_remote_retrieve_header( $response, 'content-encoding' );
		$stats['gzip_compression'] = ( $content_encoding === 'gzip' );

		if ( $content_encoding !== 'gzip' ) {
			$warnings[] = __( 'Gzip compression not enabled on checkout page', 'wpshadow' );
		}

		// Check for HTTP/2.
		$server_protocol = wp_remote_retrieve_header( $response, 'alt-svc' );
		$stats['http2_available'] = ! empty( $server_protocol );

		// Check for CSS/JS optimization.
		$minify_css = get_option( 'woocommerce_minify_checkout_css' );
		$minify_js = get_option( 'woocommerce_minify_checkout_js' );

		$stats['css_minified'] = boolval( $minify_css );
		$stats['js_minified'] = boolval( $minify_js );

		if ( ! $minify_css || ! $minify_js ) {
			$warnings[] = __( 'CSS/JS not minified on checkout page', 'wpshadow' );
		}

		// If critical issues found.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Checkout speed has critical issues: ', 'wpshadow' ) . implode( ', ', $issues ),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/checkout-speed',
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
				'description'  => __( 'Checkout speed has recommendations: ', 'wpshadow' ) . implode( ', ', $warnings ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/checkout-speed',
				'context'      => array(
					'stats'    => $stats,
					'warnings' => $warnings,
				),
			);
		}

		return null; // Checkout speed is optimal.
	}
}
