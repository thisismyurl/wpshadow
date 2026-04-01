<?php
/**
 * Social Proof on Key Pages Diagnostic
 *
 * Detects when key conversion pages lack social proof elements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Marketing
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Marketing;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Proof Key Pages Diagnostic Class
 *
 * Checks if important pages have social proof elements like testimonials, reviews, or trust badges.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Social_Proof_Key_Pages extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-proof-key-pages';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Social Proof on Key Pages';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when conversion pages lack testimonials, reviews, or trust signals';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run the diagnostic check
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		$has_social_proof    = false;
		$social_proof_types  = array();
		$pages_needing_proof = array();

		// Check for social proof plugins.
		$proof_plugins = array(
			'trustpulse-api/trustpulse.php'           => 'TrustPulse',
			'proven/proven.php'                        => 'Proven',
			'wp-testimonials/wp-testimonials.php'      => 'WP Testimonials',
			'strong-testimonials/strong-testimonials.php' => 'Strong Testimonials',
			'site-reviews/site-reviews.php'            => 'Site Reviews',
			'testimonial-rotator/testimonial-rotator.php' => 'Testimonial Rotator',
		);

		foreach ( $proof_plugins as $plugin => $name ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_social_proof   = true;
				$social_proof_types[] = $name;
			}
		}

		// Check for WooCommerce reviews.
		if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
			$enable_reviews = get_option( 'woocommerce_enable_reviews', 'yes' );
			if ( 'yes' === $enable_reviews ) {
				$has_social_proof   = true;
				$social_proof_types[] = __( 'WooCommerce Product Reviews', 'wpshadow' );
			} else {
				$pages_needing_proof[] = __( 'Product Pages (reviews disabled)', 'wpshadow' );
			}
		}

		// Check key pages for social proof keywords.
		$key_pages = array(
			'about'    => __( 'About Page', 'wpshadow' ),
			'contact'  => __( 'Contact Page', 'wpshadow' ),
			'services' => __( 'Services Page', 'wpshadow' ),
			'pricing'  => __( 'Pricing Page', 'wpshadow' ),
		);

		$proof_keywords = array( 'testimonial', 'review', 'client', 'customer', 'trusted', 'rating', 'stars', 'feedback' );

		foreach ( $key_pages as $slug => $name ) {
			$page = get_page_by_path( $slug );
			if ( ! $page ) {
				continue;
			}

			$content       = $page->post_content;
			$has_proof_keywords = false;

			foreach ( $proof_keywords as $keyword ) {
				if ( stripos( $content, $keyword ) !== false ) {
					$has_proof_keywords = true;
					break;
				}
			}

			if ( ! $has_proof_keywords ) {
				$pages_needing_proof[] = $name;
			} else {
				$has_social_proof = true;
			}
		}

		if ( $has_social_proof && empty( $pages_needing_proof ) ) {
			return null; // Social proof is adequately implemented.
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your important pages don\'t show testimonials, reviews, or trust signals. Visitors don\'t see proof that others trust and recommend you', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 55,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/social-proof?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'context'      => array(
				'has_proof'          => $has_social_proof,
				'proof_types'        => $social_proof_types,
				'pages_needing'      => $pages_needing_proof,
				'impact'             => __( '93% of consumers read online reviews before purchasing. Without social proof, visitors don\'t trust you enough to convert. Conversion rates drop 30-50% without testimonials.', 'wpshadow' ),
				'recommendation'     => array(
					__( 'Add customer testimonials to homepage and product/service pages', 'wpshadow' ),
					__( 'Display star ratings prominently', 'wpshadow' ),
					__( 'Show client logos (if B2B)', 'wpshadow' ),
					__( 'Add "X happy customers" counters', 'wpshadow' ),
					__( 'Include case studies or success stories', 'wpshadow' ),
					__( 'Display trust badges (SSL, payment security, industry certifications)', 'wpshadow' ),
					__( 'Show real-time purchase/signup notifications', 'wpshadow' ),
					__( 'Feature user-generated content (photos, reviews)', 'wpshadow' ),
				),
				'conversion_impact'  => __( 'Adding testimonials increases conversions by 34% on average', 'wpshadow' ),
				'trust_building'     => __( 'Social proof builds trust faster than any marketing copy', 'wpshadow' ),
			),
		);
	}
}
