<?php
/**
 * No Customer Journey Mapping Diagnostic
 *
 * Checks if customer journey from awareness to advocacy is documented and optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\BusinessPerformance
 * @since      1.6035.2100
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Customer Journey Mapping Diagnostic
 *
 * Detects when customer journey isn't documented or optimized. Understanding the
 * complete customer journey (from first touch to repeat purchase) reveals drop-off
 * points and optimization opportunities. Companies with optimized journeys see
 * 20-40% revenue increases.
 *
 * @since 1.6035.2100
 */
class Diagnostic_No_Customer_Journey_Mapping extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-customer-journey-mapping';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Customer Journey Documented & Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if customer journey from awareness to advocacy is documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since  1.6035.2100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$journey_mapped = self::check_journey_mapping();

		if ( ! $journey_mapped ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'Customer journey isn\'t documented or optimized. You\'re flying blind on your biggest revenue opportunity. Mapping and optimizing customer journey reveals: 1) Where visitors drop off, 2) Which touchpoints convert best, 3) Pain points in experience, 4) Upsell/cross-sell moments. Document: Awareness → Interest → Consideration → Decision → Advocacy. Optimize each stage.', 'wpshadow' ),
				'severity'    => 'high',
				'threat_level' => 70,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/customer-journey-mapping',
				'details'     => array(
					'journey_stages'      => self::get_journey_stages(),
					'metrics_to_track'    => self::get_metrics(),
					'business_impact'     => '20-40% revenue increase from optimization',
					'recommendation'      => __( 'Map each journey stage, identify metrics, and create optimization plan', 'wpshadow' ),
				),
			);
		}

		return null;
	}

	/**
	 * Check if journey mapping exists
	 *
	 * @since  1.6035.2100
	 * @return bool True if journey documented
	 */
	private static function check_journey_mapping(): bool {
		// Check for marketing automation or CRM plugins
		$plugins = get_plugins();

		$journey_keywords = array( 'crm', 'automation', 'marketing automation', 'funnel', 'email marketing', 'lead nurture' );

		foreach ( $plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $journey_keywords as $keyword ) {
				if ( strpos( $plugin_name, $keyword ) !== false ) {
					return true;
				}
			}
		}

		// Check if there's documented journey structure
		if ( self::has_clear_funnel_structure() ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if website shows clear funnel/journey structure
	 *
	 * @since  1.6035.2100
	 * @return bool True if funnel structure evident
	 */
	private static function has_clear_funnel_structure(): bool {
		// Check for clear call-to-action hierarchy
		$response = wp_remote_get( home_url( '/' ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		// Look for structured progression (hero → benefits → cta → details → cta)
		$has_hero       = preg_match( '/<h1|<header|<hero/i', $body );
		$has_benefits   = preg_match( '/benefit|feature|advantage|why/i', $body );
		$has_cta        = preg_match( '/<button|class="[^"]*btn|class="[^"]*cta/i', $body );
		$has_form       = preg_match( '/<form|<input type="email"/i', $body );
		$has_social_proof = preg_match( '/testimonial|review|case.*study|success.*story/i', $body );

		// If at least 4 of these exist, likely has structure
		$elements = array_sum( array( $has_hero, $has_benefits, $has_cta, $has_form, $has_social_proof ) );

		return $elements >= 4;
	}

	/**
	 * Get customer journey stages
	 *
	 * @since  1.6035.2100
	 * @return array Array of journey stages with details
	 */
	private static function get_journey_stages(): array {
		return array(
			array(
				'stage'       => 'Awareness',
				'definition'  => 'Customer realizes they have a problem',
				'touchpoints' => 'Blog, ads, social media, referrals',
				'metrics'     => 'Traffic, impressions, brand mentions',
			),
			array(
				'stage'       => 'Interest',
				'definition'  => 'Customer researches potential solutions',
				'touchpoints' => 'Content, product pages, comparisons',
				'metrics'     => 'Time on site, pages/session, email signups',
			),
			array(
				'stage'       => 'Consideration',
				'definition'  => 'Customer evaluates specific options',
				'touchpoints' => 'Demo, trial, pricing page, reviews',
				'metrics'     => 'Demo requests, trial signups, pricing page views',
			),
			array(
				'stage'       => 'Decision',
				'definition'  => 'Customer makes purchase decision',
				'touchpoints' => 'Sales calls, proposals, checkout',
				'metrics'     => 'Conversion rate, average order value, CAC',
			),
			array(
				'stage'       => 'Retention',
				'definition'  => 'Customer successfully uses product',
				'touchpoints' => 'Onboarding, support, education',
				'metrics'     => 'Activation rate, feature adoption, churn',
			),
			array(
				'stage'       => 'Advocacy',
				'definition'  => 'Customer becomes promoter/advocate',
				'touchpoints' => 'Reviews, referrals, testimonials, community',
				'metrics'     => 'NPS, referral rate, retention rate',
			),
		);
	}

	/**
	 * Get metrics to track by stage
	 *
	 * @since  1.6035.2100
	 * @return array Array of key metrics
	 */
	private static function get_metrics(): array {
		return array(
			'Awareness'    => array( 'Website traffic', 'Brand search volume', 'Organic reach', 'Ad impressions' ),
			'Interest'     => array( 'Pages per session', 'Average session duration', 'Resource downloads', 'Email signups' ),
			'Consideration' => array( 'Product page views', 'Demo requests', 'Trial signups', 'Pricing page bounce rate' ),
			'Decision'     => array( 'Conversion rate', 'Cart abandonment rate', 'Average order value', 'Customer acquisition cost' ),
			'Retention'    => array( 'Feature adoption', 'Support tickets', 'User login frequency', 'Churn rate' ),
			'Advocacy'     => array( 'Net Promoter Score', 'Referral rate', 'Review ratings', 'Repeat purchase rate' ),
		);
	}
}
