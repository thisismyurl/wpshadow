<?php
/**
 * Conversion Tracking Setup Diagnostic
 *
 * Verifies conversion tracking (purchases, signups, form submissions)
 * configured to measure marketing ROI.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Conversion_Tracking_Setup Class
 *
 * Verifies conversion tracking configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Conversion_Tracking_Setup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'conversion-tracking-setup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Conversion Tracking Setup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies conversion tracking';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if tracking missing, null otherwise.
	 */
	public static function check() {
		$tracking_status = self::check_conversion_tracking();

		if ( ! $tracking_status['has_issue'] ) {
			return null; // Conversion tracking configured
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No conversion tracking detected. Can\'t measure marketing ROI. $1000 on Google Ads = how many sales? Unknown. Can\'t optimize campaigns without conversion data.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/conversion-tracking',
			'family'       => self::$family,
			'meta'         => array(
				'ecommerce_detected' => $tracking_status['has_ecommerce'] ? 'yes' : 'no',
				'forms_detected'     => $tracking_status['has_forms'] ? 'yes' : 'no',
			),
			'details'      => array(
				'what_are_conversions'         => array(
					'E-commerce' => __( 'Product purchases, add-to-cart, checkout started' ),
					'Lead Generation' => __( 'Contact form submissions, quote requests' ),
					'Email Signups' => __( 'Newsletter subscriptions' ),
					'Engagement' => __( 'Video plays, file downloads, outbound clicks' ),
					'Custom Goals' => __( 'Time on page, scroll depth, specific page views' ),
				),
				'types_of_conversion_tracking' => array(
					'Google Analytics 4 (GA4)' => array(
						'Setup: Analytics → Admin → Events',
						'E-commerce: Enable enhanced measurement',
						'Custom events: gtag(\'event\', \'purchase\')',
					),
					'Facebook Pixel' => array(
						'Purpose: Track Facebook ad conversions',
						'Events: ViewContent, AddToCart, Purchase',
						'Plugin: PixelYourSite (free)',
					),
					'Google Ads Conversion Tracking' => array(
						'Purpose: Track Google Ads clicks → conversions',
						'Setup: Google Ads → Tools → Conversions',
						'Tag: Global site tag + event snippet',
					),
					'WooCommerce Analytics' => array(
						'Built-in: WooCommerce → Analytics',
						'Tracks: Orders, revenue, products',
						'Enhanced: MonsterInsights WooCommerce addon',
					),
				),
				'setting_up_ecommerce_tracking' => array(
					'WooCommerce + GA4' => array(
						'Install: MonsterInsights',
						'Settings → E-commerce → Enable',
						'Auto-tracks: Purchases, revenue, products',
						'Verify: Analytics → Monetization → E-commerce purchases',
					),
					'Easy Digital Downloads + GA4' => array(
						'Plugin: MonsterInsights',
						'Settings → E-commerce → EDD',
						'Tracks digital product sales',
					),
					'Manual GA4 E-commerce' => array(
						'Code: gtag(\'event\', \'purchase\', {',
						'  transaction_id: "T12345",',
						'  value: 25.99,',
						'  currency: "USD",',
						'  items: [...]',
						'});',
					),
				),
				'setting_up_form_tracking'     => array(
					'Contact Form 7' => array(
						'Plugin: MonsterInsights',
						'Settings → Forms → Enable CF7 tracking',
						'Auto-tracks: Form submissions',
					),
					'Gravity Forms' => array(
						'Built-in: Form Settings → Google Analytics',
						'Event name: Submission',
						'Tracks: All form completions',
					),
					'Manual Form Tracking' => array(
						'On submit: gtag(\'event\', \'form_submit\', {',
						'  form_name: "Contact Form"',
						'});',
					),
				),
				'measuring_conversion_roi'     => array(
					__( 'Formula: (Revenue - Ad Spend) / Ad Spend × 100 = ROI%' ),
					__( 'Example: $5000 revenue - $1000 ads = $4000 profit' ),
					__( '$4000 / $1000 × 100 = 400% ROI' ),
					__( 'Track in Google Ads: Conversions / Cost = Cost per conversion' ),
					__( 'Optimize: Pause low-ROI campaigns, scale high-ROI' ),
				),
			),
		);
	}

	/**
	 * Check conversion tracking.
	 *
	 * @since  1.2601.2148
	 * @return array Conversion tracking status.
	 */
	private static function check_conversion_tracking() {
		$has_ecommerce = false;
		$has_forms = false;

		// Check for e-commerce platforms
		if ( class_exists( 'WooCommerce' ) || class_exists( 'Easy_Digital_Downloads' ) ) {
			$has_ecommerce = true;
		}

		// Check for form plugins
		if ( is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ||
			 is_plugin_active( 'gravityforms/gravityforms.php' ) ||
			 is_plugin_active( 'wpforms/wpforms.php' ) ) {
			$has_forms = true;
		}

		// Check for tracking plugins
		$has_tracking = is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ||
						is_plugin_active( 'google-site-kit/google-site-kit.php' ) ||
						is_plugin_active( 'pixelyoursite/pixelyoursite.php' );

		// Issue if have e-commerce/forms but no tracking
		$has_issue = ( $has_ecommerce || $has_forms ) && ! $has_tracking;

		return array(
			'has_issue'      => $has_issue,
			'has_ecommerce'  => $has_ecommerce,
			'has_forms'      => $has_forms,
		);
	}
}
