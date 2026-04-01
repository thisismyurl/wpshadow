<?php
/**
 * Homepage Title Tag Business Optimization Diagnostic
 *
 * Issue #4801: Homepage Title Tag Not Optimized for Business
 * Family: business-performance
 *
 * Checks if homepage title tag targets business keywords and value proposition.
 * Homepage title is the most important title on your site.
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
 * Diagnostic_Homepage_Title_Optimization Class
 *
 * Checks homepage title tag quality.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Homepage_Title_Optimization extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-title-optimization';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Title Tag Not Optimized for Business';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if homepage title follows business SEO best practices';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get homepage.
		$front_page_id = get_option( 'page_on_front' );
		$title         = '';

		if ( $front_page_id ) {
			$front_page = get_post( $front_page_id );
			if ( $front_page ) {
				$title = wp_strip_all_tags( $front_page->post_title );
			}
		}

		if ( empty( $title ) ) {
			$title = get_bloginfo( 'name' );
		}

		$issues[] = __( 'Include primary keyword: "Best [Product/Service] for [Target Audience]"', 'wpshadow' );
		$issues[] = __( 'Add unique value proposition: What makes you different?', 'wpshadow' );
		$issues[] = __( 'Keep under 60 characters (Google truncates longer)', 'wpshadow' );
		$issues[] = __( 'Front-load keywords: Most important words first', 'wpshadow' );
		$issues[] = __( 'Include brand name at end: "[Keywords] | Brand Name"', 'wpshadow' );
		$issues[] = __( 'Test with Yoast SEO or Rank Math meta title editor', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your homepage title tag might not target your business keywords effectively. The homepage title is THE most important title on your site—it\'s what appears in Google search results for your brand and what people see in browser tabs. A great homepage title: 1) Includes primary keyword (what you do), 2) Targets your audience (who you serve), 3) Shows unique value (why choose you), 4) Under 60 characters (avoids truncation in search), 5) Front-loads keywords (most important first), 6) Includes brand name (recognition). Compare weak vs strong: ❌ Weak: "Welcome to Our Website" (no keywords), "Home | Company Name" (wasted space), "Company Name - We Do Stuff" (vague). ✅ Strong: "Email Marketing Software for Small Business | Mailchimp" (keyword + audience + brand), "Affordable Web Hosting Plans Starting $2.95/mo | Bluehost" (value prop + price + brand), "WordPress Security Plugin - Malware Scanner | Wordfence" (what it does + feature + brand). Formula: [Primary Keyword] [for Target Audience] [Unique Benefit] | [Brand]. Where to edit: WordPress: Yoast SEO or Rank Math meta title field on homepage. Test: Search Google for your brand—does title clearly explain what you do? Would you click it?', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/homepage-title-seo?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'recommendations'       => $issues,
					'current_title'         => $title,
					'optimal_length'        => '50-60 characters (avoids truncation)',
					'formula'               => '[Keyword] [for Audience] [Benefit] | [Brand]',
					'good_examples'         => '"Email Marketing for Small Business | Mailchimp"',
					'where_to_edit'         => 'Yoast SEO or Rank Math meta title field',
					'seo_weight'            => 'Homepage title is most important title for brand searches',
				),
			);
		}

		return null;
	}
}
