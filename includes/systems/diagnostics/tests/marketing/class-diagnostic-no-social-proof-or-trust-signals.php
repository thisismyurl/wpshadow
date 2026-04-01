<?php
/**
 * No Social Proof or Trust Signals Diagnostic
 *
 * Detects when social proof is not displayed,
 * missing opportunity to build trust and increase conversions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Social Proof or Trust Signals
 *
 * Checks whether social proof elements are displayed
 * to build trust with visitors.
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Social_Proof_Or_Trust_Signals extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-social-proof-trust-signals';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Proof & Trust Signals';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether social proof is displayed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check homepage for social proof elements
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return null;
		}

		$body = wp_remote_retrieve_body( $homepage );

		// Look for common social proof patterns
		$has_testimonials = strpos( $body, 'testimonial' ) !== false;
		$has_reviews = strpos( $body, 'review' ) !== false || strpos( $body, 'rating' ) !== false;
		$has_trust_badges = strpos( $body, 'secure' ) !== false || strpos( $body, 'ssl' ) !== false;
		$has_customer_count = preg_match( '/\d+[,\d]*\s*(?:customers|users|clients)/i', $body );

		if ( ! $has_testimonials && ! $has_reviews && ! $has_trust_badges && ! $has_customer_count ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __(
					'Your site lacks social proof and trust signals, which are critical for conversions. People trust what others say more than what you say. Social proof includes: testimonials, reviews, customer counts ("Join 10,000+ users"), logos of clients/partners, trust badges (SSL, payment security, awards). Even small amounts of social proof can increase conversions by 15-30%. Without it, visitors question if you\'re legitimate.',
					'wpshadow'
				),
				'severity'      => 'high',
				'threat_level'  => 65,
				'auto_fixable'  => false,
				'business_impact' => array(
					'metric'         => 'Trust & Conversion Rate',
					'potential_gain' => '+15-30% conversion improvement',
					'roi_explanation' => 'Social proof builds trust, increasing conversions by 15-30%. People trust peer recommendations more than marketing.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/social-proof-trust-signals?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}
}
