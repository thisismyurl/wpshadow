<?php
/**
 * No Landing Page Testing Or Conversion Optimization Diagnostic
 *
 * Checks if landing pages are being systematically optimized.
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
 * Landing Page Conversion Optimization Diagnostic
 *
 * Companies optimizing landing pages improve conversion rates by 20-40%
 * without increasing traffic. Direct impact to bottom line.
 *
 * @since 1.6093.1200
 */
class Diagnostic_No_Landing_Page_Testing_Or_Conversion_Optimization extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'no-landing-page-conversion-optimization';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Landing Page Testing/Conversion Optimization';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if landing pages are being systematically optimized';

	/**
	 * Diagnostic family.
	 *
	 * @var string
	 */
	protected static $family = 'marketing';

	/**
	 * Run diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		if ( ! self::has_optimization_program() ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => __( 'No landing page conversion optimization program detected. Doubling traffic is 10x harder than doubling conversion rates. Companies optimizing landing pages improve conversion 20-40% without spending more on ads. Optimize: 1) Clear value prop (why should I care?), 2) Relevant headline (match their intent), 3) Single CTA (confusing options kills conversion), 4) Remove navigation (focus = conversion), 5) Social proof (testimonials, numbers), 6) Mobile optimized (60% of traffic), 7) Fast loading (<3 seconds), 8) Form simplicity (fewer fields = higher conversion), 9) Trust signals (guarantee, security badge, contact info), 10) Compelling images (product, customer using it). The best optimization is removing friction.', 'wpshadow' ),
				'severity'    => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/landing-page-conversion-optimization',
				'details'     => array(
					'issue'               => __( 'No landing page conversion optimization detected', 'wpshadow' ),
					'recommendation'      => __( 'Implement landing page testing and optimization program', 'wpshadow' ),
					'business_impact'     => __( 'Missing 20-40% conversion improvement without increasing traffic', 'wpshadow' ),
					'optimization_areas'  => self::get_optimization_areas(),
					'friction_elements'   => self::get_friction_elements(),
				),
			);
		}

		return null;
	}

	/**
	 * Check if optimization program exists.
	 *
	 * @since 1.6093.1200
	 * @return bool True if program detected, false otherwise.
	 */
	private static function has_optimization_program() {
		// Check for landing page/optimization content
		$optimization_posts = self::count_posts_by_keywords(
			array(
				'landing page',
				'conversion',
				'optimization',
				'funnel',
				'signup',
			)
		);

		if ( $optimization_posts > 0 ) {
			return true;
		}

		// Check for optimization plugins
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$all_plugins = get_plugins();
		$optimization_keywords = array(
			'conversion',
			'optimize',
			'landing',
			'heat map',
			'form',
		);

		foreach ( $all_plugins as $plugin_file => $plugin_data ) {
			$plugin_name = strtolower( $plugin_data['Name'] );
			foreach ( $optimization_keywords as $keyword ) {
				if ( false !== strpos( $plugin_name, $keyword ) ) {
					if ( is_plugin_active( $plugin_file ) ) {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * Count posts containing specific keywords.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Keywords to search for.
	 * @return int Number of matching posts.
	 */
	private static function count_posts_by_keywords( $keywords ) {
		$total = 0;

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'posts_per_page' => 1,
					'post_type'      => array( 'post', 'page' ),
					'post_status'    => 'publish',
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				++$total;
			}
		}

		return $total;
	}

	/**
	 * Get key optimization areas.
	 *
	 * @since 1.6093.1200
	 * @return array Optimization areas with impact.
	 */
	private static function get_optimization_areas() {
		return array(
			'headline'        => array(
				'impact'      => __( '+3-5% conversion improvement', 'wpshadow' ),
				'variables'   => __( 'Benefit-driven vs Curiosity gap vs Direct vs Question', 'wpshadow' ),
				'example'     => __( '"Save 10 Hours/Week" vs "How to Triple Your Productivity"', 'wpshadow' ),
			),
			'cta_copy'        => array(
				'impact'      => __( '+2-4% conversion improvement', 'wpshadow' ),
				'variables'   => __( '"Sign Up Free" vs "Get Started Now" vs "Claim Your Free Trial"', 'wpshadow' ),
				'example'     => __( 'Action-oriented buttons outperform generic buttons', 'wpshadow' ),
			),
			'form_fields'     => array(
				'impact'      => __( '+5-10% conversion improvement', 'wpshadow' ),
				'variables'   => __( '1 field vs 3 fields vs 5 fields (each field = 5% drop)', 'wpshadow' ),
				'example'     => __( 'Email only vs Email + Name vs Email + Name + Company + Role + Budget', 'wpshadow' ),
			),
			'social_proof'    => array(
				'impact'      => __( '+3-7% conversion improvement', 'wpshadow' ),
				'variables'   => __( 'None vs Customer count vs Testimonials vs Case studies', 'wpshadow' ),
				'example'     => __( '"Join 10,000+ customers" or 5-star testimonial video', 'wpshadow' ),
			),
			'images'          => array(
				'impact'      => __( '+2-5% conversion improvement', 'wpshadow' ),
				'variables'   => __( 'Generic stock photo vs Product photo vs Customer success', 'wpshadow' ),
				'example'     => __( 'Actual customer using product outperforms stock photos', 'wpshadow' ),
			),
			'trust_signals'   => array(
				'impact'      => __( '+2-4% conversion improvement', 'wpshadow' ),
				'variables'   => __( 'None vs Money-back guarantee vs Security badge vs Refund policy', 'wpshadow' ),
				'example'     => __( '"30-Day Money-Back Guarantee" removes purchase risk', 'wpshadow' ),
			),
		);
	}

	/**
	 * Get friction elements to remove.
	 *
	 * @since 1.6093.1200
	 * @return array Friction elements and solutions.
	 */
	private static function get_friction_elements() {
		return array(
			'navigation'    => __( 'Navigation menu on signup page (removes focus)', 'wpshadow' ),
			'distractions'  => __( 'Unrelated links, ads, competing CTAs', 'wpshadow' ),
			'loading_time'  => __( 'Slow page load (lose 7% conversion per second)', 'wpshadow' ),
			'form_fields'   => __( 'Too many required form fields', 'wpshadow' ),
			'unclear_offer' => __( 'Unclear what\'s included or cost', 'wpshadow' ),
			'broken_links'  => __( '404 errors, broken images, typos', 'wpshadow' ),
			'mobile_issues' => __( 'Not optimized for mobile (60% of visitors)', 'wpshadow' ),
			'trust_gaps'    => __( 'No contact info, no security info, sketchy design', 'wpshadow' ),
		);
	}
}
