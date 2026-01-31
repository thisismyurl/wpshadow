<?php
/**
 * Conversion Goal Configuration Status Diagnostic
 *
 * Checks if analytics goals/events are configured for key business actions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Conversion Goal Configuration Status Class
 *
 * Tests conversion tracking.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Conversion_Goal_Configuration_Status extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'conversion-goal-configuration-status';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Conversion Goal Configuration Status';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if analytics goals/events are configured for key business actions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$conversion_check = self::check_conversion_tracking();
		
		if ( ! $conversion_check['tracking_detected'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'No conversion tracking detected (measuring traffic but not business value)', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/conversion-goal-configuration-status',
				'meta'         => array(
					'form_tracking'        => $conversion_check['form_tracking'],
					'ecommerce_tracking'   => $conversion_check['ecommerce_tracking'],
					'event_tracking'       => $conversion_check['event_tracking'],
				),
			);
		}

		return null;
	}

	/**
	 * Check conversion tracking setup.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_conversion_tracking() {
		$check = array(
			'tracking_detected'    => false,
			'form_tracking'        => false,
			'ecommerce_tracking'   => false,
			'event_tracking'       => false,
		);

		// Check for common conversion tracking plugins.
		$tracking_plugins = array(
			'google-analytics-for-wordpress/googleanalytics.php',
			'google-analytics-dashboard-for-wp/gadwp.php',
			'google-site-kit/google-site-kit.php',
			'enhanced-e-commerce-for-woocommerce-store/enhanced-ecommerce-google-analytics.php',
			'woocommerce-google-analytics-integration/woocommerce-google-analytics-integration.php',
		);

		foreach ( $tracking_plugins as $plugin_file ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$check['tracking_detected'] = true;
				
				// Enhanced e-commerce plugins indicate ecommerce tracking.
				if ( false !== strpos( $plugin_file, 'ecommerce' ) || 
				     false !== strpos( $plugin_file, 'woocommerce-google-analytics' ) ) {
					$check['ecommerce_tracking'] = true;
				}
				break;
			}
		}

		// Check homepage HTML for conversion tracking code.
		$response = wp_remote_get( home_url(), array(
			'timeout' => 10,
		) );

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );

			// Check for GTM dataLayer (conversion events).
			if ( false !== strpos( $body, 'dataLayer' ) ) {
				$check['tracking_detected'] = true;
				$check['event_tracking'] = true;
			}

			// Check for GA event tracking.
			if ( preg_match( '/gtag\s*\(\s*[\'"]event[\'"]/i', $body ) ) {
				$check['tracking_detected'] = true;
				$check['event_tracking'] = true;
			}

			// Check for form tracking code.
			if ( false !== strpos( $body, 'form_submit' ) || 
			     false !== strpos( $body, 'formSubmit' ) ||
			     preg_match( '/on(submit|click).*gtag/i', $body ) ) {
				$check['tracking_detected'] = true;
				$check['form_tracking'] = true;
			}

			// Check for e-commerce tracking.
			if ( false !== strpos( $body, 'ecommerce' ) || 
			     false !== strpos( $body, 'purchase' ) ||
			     false !== strpos( $body, 'add_to_cart' ) ) {
				$check['tracking_detected'] = true;
				$check['ecommerce_tracking'] = true;
			}
		}

		return $check;
	}
}
