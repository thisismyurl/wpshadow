<?php
/**
 * Regional Marketing Campaigns Diagnostic
 *
 * Tests whether the site runs marketing campaigns tailored to specific regional
 * audiences. Regional marketing increases relevance and conversion rates by
 * addressing local preferences, languages, and cultural nuances.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Runs_Regional_Marketing_Campaigns Class
 *
 * Diagnostic #32: Regional Marketing Campaigns from Specialized & Emerging Success Habits.
 * Checks if the website runs marketing campaigns tailored to specific regional
 * audiences with localized messaging, offers, and content.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Runs_Regional_Marketing_Campaigns extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'runs-regional-marketing-campaigns';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Regional Marketing Campaigns';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site runs marketing campaigns tailored to specific regional audiences';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'voice-audio-international';

	/**
	 * Run the diagnostic check.
	 *
	 * Regional marketing campaigns demonstrate geographic segmentation and localization.
	 * This diagnostic checks for geo-targeting plugins, regional content, localized
	 * offers, and multi-region strategy.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score          = 0;
		$max_score      = 6;
		$score_details  = array();
		$recommendations = array();

		// Check 1: Geo-targeting/localization plugins.
		$geo_plugins = array(
			'geotargeting-wp/geotargeting-wp.php',
			'geo-controller/geo-controller.php',
			'geo-redirects/geo-redirects.php',
			'geoip-detect/geoip-detect.php',
			'ip2location-country-blocker/ip2location-country-blocker.php',
		);

		$has_geo_plugin = false;
		foreach ( $geo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_geo_plugin = true;
				break;
			}
		}

		if ( $has_geo_plugin ) {
			++$score;
			$score_details[] = __( '✓ Geo-targeting plugin active', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No geo-targeting plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install a geo-targeting plugin (GeoTargeting WP, GeoIP Detect) to personalize content by location', 'wpshadow' );
		}

		// Check 2: Regional campaign content (country/city names).
		$regional_keywords = array(
			'north america', 'europe', 'asia', 'australia', 'canada', 'uk', 'united kingdom',
			'germany', 'france', 'japan', 'india', 'brazil', 'mexico', 'regional',
			'local market', 'in your area', 'near you',
		);

		$regional_posts = get_posts(
			array(
				'post_type'      => 'any',
				'posts_per_page' => 20,
				'post_status'    => 'publish',
				'orderby'        => 'date',
				'order'          => 'DESC',
			)
		);

		$regional_count = 0;
		foreach ( $regional_posts as $post ) {
			$content_lower = strtolower( $post->post_title . ' ' . $post->post_content );
			foreach ( $regional_keywords as $keyword ) {
				if ( stripos( $content_lower, $keyword ) !== false ) {
					++$regional_count;
					break;
				}
			}
		}

		if ( $regional_count >= 5 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: number of regional posts */
				__( '✓ Regional content detected (%d+ posts mention specific regions)', 'wpshadow' ),
				$regional_count
			);
		} elseif ( $regional_count > 0 ) {
			$score_details[]   = sprintf(
				/* translators: %d: number of regional posts */
				__( '◐ Some regional content (%d posts)', 'wpshadow' ),
				$regional_count
			);
			$recommendations[] = __( 'Create more region-specific content and campaigns for different geographic markets', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No regional campaign content found', 'wpshadow' );
			$recommendations[] = __( 'Develop regional marketing campaigns tailored to specific geographic audiences', 'wpshadow' );
		}

		// Check 3: Regional offers or promotions.
		$offer_keywords = array( 'regional offer', 'local deal', 'exclusive to', 'available in', 'shipping to', 'serves' );
		$regional_offers = 0;

		foreach ( $regional_posts as $post ) {
			$content_lower = strtolower( $post->post_content );
			foreach ( $offer_keywords as $keyword ) {
				if ( stripos( $content_lower, $keyword ) !== false ) {
					++$regional_offers;
					break;
				}
			}
		}

		if ( $regional_offers >= 3 ) {
			++$score;
			$score_details[] = __( '✓ Regional offers/promotions detected', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No regional offers found', 'wpshadow' );
			$recommendations[] = __( 'Create region-specific offers, promotions, or shipping deals to increase local relevance', 'wpshadow' );
		}

		// Check 4: Multiple language support (indicates multi-region strategy).
		$multilingual_plugins = array(
			'sitepress-multilingual-cms/sitepress.php', // WPML.
			'polylang/polylang.php',                    // Polylang.
			'translatepress-multilingual/index.php',    // TranslatePress.
			'weglot/weglot.php',                        // Weglot.
		);

		$has_multilingual = false;
		foreach ( $multilingual_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_multilingual = true;
				break;
			}
		}

		if ( $has_multilingual ) {
			++$score;
			$score_details[] = __( '✓ Multi-language plugin active (supports regional campaigns)', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No multi-language plugin detected', 'wpshadow' );
			$recommendations[] = __( 'Install WPML, Polylang, or TranslatePress to support multiple regional languages', 'wpshadow' );
		}

		// Check 5: Regional landing pages.
		$regional_pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		$regional_landing_count = 0;
		$region_patterns = array( 'canada', 'uk', 'europe', 'asia', 'australia', 'us', 'usa' );

		foreach ( $regional_pages as $page ) {
			$slug_lower = strtolower( $page->post_name );
			foreach ( $region_patterns as $region ) {
				if ( stripos( $slug_lower, $region ) !== false ) {
					++$regional_landing_count;
					break;
				}
			}
		}

		if ( $regional_landing_count >= 2 ) {
			++$score;
			$score_details[] = sprintf(
				/* translators: %d: number of regional landing pages */
				__( '✓ Regional landing pages detected (%d+ pages)', 'wpshadow' ),
				$regional_landing_count
			);
		} else {
			$score_details[]   = __( '✗ No regional landing pages found', 'wpshadow' );
			$recommendations[] = __( 'Create dedicated landing pages for each target region (e.g., /canada, /europe, /uk)', 'wpshadow' );
		}

		// Check 6: Regional testimonials or case studies.
		$testimonial_keywords = array( 'customer', 'testimonial', 'case study', 'success story', 'client' );
		$regional_testimonials = 0;

		foreach ( $regional_posts as $post ) {
			$content_lower = strtolower( $post->post_title . ' ' . $post->post_content );
			$has_testimonial = false;
			$has_region = false;

			foreach ( $testimonial_keywords as $keyword ) {
				if ( stripos( $content_lower, $keyword ) !== false ) {
					$has_testimonial = true;
					break;
				}
			}

			if ( $has_testimonial ) {
				foreach ( $regional_keywords as $keyword ) {
					if ( stripos( $content_lower, $keyword ) !== false ) {
						$has_region = true;
						break;
					}
				}

				if ( $has_region ) {
					++$regional_testimonials;
				}
			}
		}

		if ( $regional_testimonials >= 2 ) {
			++$score;
			$score_details[] = __( '✓ Regional testimonials/case studies found', 'wpshadow' );
		} else {
			$score_details[]   = __( '✗ No regional testimonials detected', 'wpshadow' );
			$recommendations[] = __( 'Feature customer success stories from different regions to build local trust', 'wpshadow' );
		}

		// Calculate score percentage.
		$score_percentage = ( $score / $max_score ) * 100;

		// Determine severity based on score.
		if ( $score_percentage < 35 ) {
			$severity     = 'medium';
			$threat_level = 25;
		} elseif ( $score_percentage < 65 ) {
			$severity     = 'low';
			$threat_level = 15;
		} else {
			// Regional marketing capabilities are adequate.
			return null;
		}

		return array(
			'id'               => self::$slug,
			'title'            => self::$title,
			'description'      => sprintf(
				/* translators: %d: score percentage */
				__( 'Regional marketing campaigns score: %d%%. Localized campaigns increase conversion rates by 40%% by addressing regional preferences, languages, and cultural nuances. Regional targeting shows 6x higher engagement than generic campaigns.', 'wpshadow' ),
				$score_percentage
			),
			'severity'         => $severity,
			'threat_level'     => $threat_level,
			'auto_fixable'     => false,
			'kb_link'          => 'https://wpshadow.com/kb/regional-marketing?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'          => $score_details,
			'recommendations'  => $recommendations,
			'impact'           => __( 'Regional campaigns enable expansion into new markets while maintaining local relevance, driving both reach and conversion improvements.', 'wpshadow' ),
		);
	}
}
