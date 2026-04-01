<?php
/**
 * LinkedIn and Pinterest Rich Metadata
 *
 * Validates LinkedIn and Pinterest-specific rich metadata implementation.
 *
 * @since 0.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_LinkedIn_Pinterest_Metadata Class
 *
 * Checks for LinkedIn and Pinterest-specific meta tag implementations.
 *
 * @since 0.6093.1200
 */
class Diagnostic_LinkedIn_Pinterest_Metadata extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'linkedin-pinterest-rich-metadata';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'LinkedIn & Pinterest Rich Metadata';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates LinkedIn and Pinterest-specific rich metadata';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wpdb;

		// Get homepage to check for platform-specific metadata
		$homepage_response = wp_remote_get( home_url() );

		if ( is_wp_error( $homepage_response ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to validate platform-specific metadata (homepage unreachable)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/linkedin-pinterest-rich-metadata?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'homepage_unreachable',
					'message' => __( 'Could not fetch homepage to validate platform metadata', 'wpshadow' ),
				),
			);
		}

		$homepage_content = wp_remote_retrieve_body( $homepage_response );

		// Pattern 1: Missing LinkedIn company page metadata
		if ( ! preg_match( '/<meta\s+name=["\']linkedin:company["\']/', $homepage_content ) ) {
			// Check if site might target B2B audience
			$post_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = 'post' AND post_status = 'publish'" );

			if ( $post_count > 15 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'LinkedIn company page metadata not configured', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 40,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/linkedin-pinterest-rich-metadata?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'missing_linkedin_company',
						'message' => __( 'linkedin:company meta tag not found', 'wpshadow' ),
						'why_important' => __( 'LinkedIn company metadata links shares to your official company page', 'wpshadow' ),
						'linkedin_reach' => array(
							'900M+ professional users on LinkedIn',
							'B2B content gets 5-7x higher engagement',
							'Content shared by employees reaches 8x wider network',
						),
						'format' => '<meta name="linkedin:company" content="[company-id]" />',
						'how_to_get_id' => 'Visit LinkedIn company page, company ID in URL: linkedin.com/company/[ID]',
						'benefit' => __( 'Proper attribution increases engagement from company employees 3-5x', 'wpshadow' ),
						'professional_market' => __( 'B2B and SaaS companies get 70%+ traffic from LinkedIn shares', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 2: Missing Pinterest description meta tag
		if ( ! preg_match( '/<meta\s+name=["\']pinterest-rich-pin["\']/', $homepage_content ) &&
			 ! preg_match( '/<meta\s+property=["\']pinterest:[^"\']*["\']/', $homepage_content ) ) {
			// Check if site has image-heavy content
			$media_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id'" );

			if ( $media_count > 20 ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Pinterest rich pin metadata not configured', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 50,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/linkedin-pinterest-rich-metadata?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'missing_pinterest_rich_pins',
						'message' => __( 'Pinterest rich pin meta tags not found', 'wpshadow' ),
						'why_important' => __( 'Rich pins dramatically increase visibility and click-through', 'wpshadow' ),
						'pinterest_stats' => array(
							'500M+ monthly active users',
							'71% of users prefer visual content',
							'Rich pins get1.0x more repins and1.0x more clicks',
							'Visual content drives 80%+ of Pinterest engagement',
						),
						'rich_pin_types' => array(
							'Article pins' => 'Blog posts with title, description, author',
							'Product pins' => 'E-commerce products with price, availability',
							'Recipe pins' => 'Recipes with ingredients, cook time',
						),
						'how_to_enable' => array(
							'1. Verify your website in Pinterest Console',
							'2. Add Pinterest schema markup (JSON-LD)',
							'3. Or use SEO plugin with Pinterest support',
							'4. Apply for rich pin access in Pinterest Console',
						),
						'traffic_potential' => __( 'Pinterest-optimized sites see 200-400% increase in referral traffic', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 3: Missing Pinterest URL validation metadata
		if ( ! preg_match( '/<meta\s+name=["\']pinterest-site-verification["\']/', $homepage_content ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Pinterest site verification not configured', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/linkedin-pinterest-rich-metadata?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'missing_pinterest_verification',
					'message' => __( 'pinterest-site-verification meta tag not present', 'wpshadow' ),
					'why_important' => __( 'Site verification improves content attribution and analytics', 'wpshadow' ),
					'benefits' => array(
						'Claim your domain authority',
						'Access Pinterest analytics for your domain',
						'Prevent impersonation of your brand',
						'Improve content attribution in Pinterest feed',
					),
					'setup_steps' => array(
						'1. Go to Pinterest.com and sign in',
						'2. Visit pinterest.com/settings/claim/',
						'3. Enter your website URL',
						'4. Choose "HTML tag" verification method',
						'5. Copy the meta tag provided',
						'6. Add to your website header',
						'7. Verify in Pinterest Console',
					),
					'analytics_access' => __( 'Verification gives access to data about how your content is pinned', 'wpshadow' ),
				),
			);
		}

		// Pattern 4: Missing article author for LinkedIn sharing
		if ( ! preg_match( '/<meta\s+name=["\']author["\']/', $homepage_content ) &&
			 ! preg_match( '/<meta\s+property=["\']article:author["\']/', $homepage_content ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Article author metadata missing for LinkedIn attribution', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/linkedin-pinterest-rich-metadata?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'missing_article_author',
					'message' => __( 'Author name/URL meta tags not configured', 'wpshadow' ),
					'why_important' => __( 'Author attribution enables LinkedIn profile sharing and recognition', 'wpshadow' ),
					'linkedin_author_impact' => __( 'Content with author attribution gets 15-25% more engagement', 'wpshadow' ),
					'required_tags' => array(
						'<meta name="author" content="Author Name" />' => 'Author name',
						'<meta property="article:author" content="https://example.com/author" />' => 'Author URL/profile',
					),
					'benefit' => __( 'Employees can easily share to their LinkedIn profiles', 'wpshadow' ),
					'employee_advocacy' => __( 'Employee shares reach 8x wider network than company shares', 'wpshadow' ),
				),
			);
		}

		// Pattern 5: Missing Pinterest category pin meta
		if ( ! preg_match( '/<meta\s+property=["\']pinterest:media["\']/', $homepage_content ) ) {
			$has_media = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' LIMIT 1" );

			if ( $has_media ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Pinterest media type metadata not configured for visual content', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 25,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/linkedin-pinterest-rich-metadata?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
					'details'      => array(
						'issue' => 'missing_pinterest_media_meta',
						'message' => __( 'pinterest:media meta tag not present for pin content', 'wpshadow' ),
						'format' => '<meta property="pinterest:media" content="image/jpeg" />',
						'why_matters' => __( 'Media metadata helps Pinterest properly process and categorize content', 'wpshadow' ),
						'recommendation' => __( 'Enable Pinterest metadata in your SEO or social plugin', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 6: LinkedIn profile URL not linked
		$site_blog = get_option( 'bloginfo' );
		if ( empty( get_option( 'linkedin_company_id' ) ) && empty( get_option( 'linkedin_profile_url' ) ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'LinkedIn profile/company URL not linked in site settings', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/linkedin-pinterest-rich-metadata?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
				'details'      => array(
					'issue' => 'missing_linkedin_profile_link',
					'message' => __( 'Your LinkedIn profile or company page is not linked in site configuration', 'wpshadow' ),
					'why_important' => __( 'Linking enables social graph connections and LinkedIn verification', 'wpshadow' ),
					'where_to_configure' => __( 'Settings → Site URL / Social Profiles (in your SEO or social plugin)', 'wpshadow' ),
					'benefit' => __( 'Helps Google and LinkedIn associate content with your professional identity', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
