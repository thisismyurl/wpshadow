<?php
/**
 * Twitter Card Implementation for Social Sharing
 *
 * Validates Twitter Card meta tags for optimal Twitter thread sharing.
 *
 * @since   1.6030.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;
use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Twitter_Card_Implementation Class
 *
 * Checks for proper Twitter Card implementation which controls content previews on Twitter/X.
 *
 * @since 1.6030.2148
 */
class Diagnostic_Twitter_Card_Implementation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'twitter-card-implementation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Twitter Card Implementation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Validates Twitter Card setup for social sharing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'social-media';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6030.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get homepage content
		$homepage_content = Diagnostic_HTML_Helper::fetch_html( home_url() );

		if ( null === $homepage_content ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Unable to validate Twitter Cards (homepage unreachable)', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/twitter-card-implementation',
				'details'      => array(
					'issue' => 'homepage_unreachable',
					'message' => __( 'Could not fetch homepage to validate Twitter Cards', 'wpshadow' ),
				),
			);
		}

		// Pattern 1: No Twitter Card tags at all
		if ( ! preg_match( '/<meta\s+name=["\']twitter:card/', $homepage_content ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Twitter Card meta tags not detected', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/twitter-card-implementation',
				'details'      => array(
					'issue' => 'missing_twitter_card',
					'message' => __( 'No Twitter Card meta tags found on your site', 'wpshadow' ),
					'recommendation' => __( 'Install a social media or SEO plugin with Twitter Card support', 'wpshadow' ),
					'why_important' => __( 'Twitter Cards control how your content appears when shared on Twitter/X', 'wpshadow' ),
					'card_types' => array(
						'summary' => 'Title, description, image, URL (default)',
						'summary_large_image' => 'Large featured image format',
						'player' => 'For audio/video content',
						'app' => 'For mobile app promotion',
					),
					'twitter_audience' => __( '336M+ monthly active Twitter users could see your content', 'wpshadow' ),
					'engagement_impact' => __( 'Proper Twitter Cards increase engagement 20-35%', 'wpshadow' ),
					'solutions' => array(
						'Yoast SEO - Full Twitter Card support',
						'Rank Math - Comprehensive Twitter integration',
						'Social Warfare - Twitter-focused optimization',
						'Jetpack - Built-in social sharing',
					),
				),
			);
		}

		// Pattern 2: Missing Twitter handle/creator attribution
		if ( ! preg_match( '/<meta\s+name=["\']twitter:creator/', $homepage_content ) && 
			 ! preg_match( '/<meta\s+name=["\']twitter:site/', $homepage_content ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Twitter Card missing creator or site attribution', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 40,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/twitter-card-implementation',
				'details'      => array(
					'issue' => 'missing_twitter_attribution',
					'message' => __( 'twitter:creator and/or twitter:site meta tags not configured', 'wpshadow' ),
					'recommendation' => __( 'Add Twitter handle for proper creator attribution and analytics', 'wpshadow' ),
					'tags' => array(
						'twitter:creator' => 'Individual author\'s Twitter handle (@username)',
						'twitter:site' => 'Brand/website Twitter handle',
					),
					'benefit' => __( 'Attribution helps Twitter connect shares to your account for analytics', 'wpshadow' ),
					'analytics_impact' => __( 'Enables Twitter Analytics to track engagement on shared content', 'wpshadow' ),
					'example_format' => array(
						'<meta name="twitter:creator" content="@yourhandle" />',
						'<meta name="twitter:site" content="@yourbrand" />',
					),
				),
			);
		}

		// Pattern 3: Card type mismatch for content (blog should use summary, large_image uses images wrong)
		if ( preg_match( '/<meta\s+name=["\']twitter:card["\'].*?content=["\']([^"\']+)["\']/', $homepage_content, $matches ) ) {
			$card_type = $matches[1];

			// Large image card requires image but check for presence
			if ( $card_type === 'summary_large_image' ) {
				if ( ! preg_match( '/<meta\s+name=["\']twitter:image/', $homepage_content ) ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => __( 'Twitter Card type mismatch: large image card without image', 'wpshadow' ),
						'severity'     => 'medium',
						'threat_level' => 50,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/twitter-card-implementation',
						'details'      => array(
							'issue' => 'card_type_mismatch',
							'message' => __( 'Using summary_large_image card type but no twitter:image meta tag', 'wpshadow' ),
							'current_card_type' => $card_type,
							'required_for_type' => 'twitter:image (minimum 506x506px, recommended 1200x630px)',
							'fix_options' => array(
								'Option 1: Add twitter:image meta tag',
								'Option 2: Change to summary card type',
								'Option 3: Use plugin to manage Twitter Cards automatically',
							),
						),
					);
				}
			}
		}

		// Pattern 4: Image dimension issues
		if ( preg_match( '/<meta\s+name=["\']twitter:image["\']/', $homepage_content ) ) {
			// Check for image width/height specifications
			if ( ! preg_match( '/<meta\s+name=["\']twitter:image:width/', $homepage_content ) ) {
				return array(
					'id'           => self::$slug,
					'title'        => self::$title,
					'description'  => __( 'Twitter image lacking dimension metadata', 'wpshadow' ),
					'severity'     => 'low',
					'threat_level' => 35,
					'auto_fixable' => false,
					'kb_link'      => 'https://wpshadow.com/kb/twitter-card-implementation',
					'details'      => array(
						'issue' => 'missing_image_dimensions',
						'message' => __( 'twitter:image present but missing width/height specifications', 'wpshadow' ),
						'image_specs' => array(
							'minimum_size' => '506x506px',
							'recommended_size' => '1200x630px',
							'aspect_ratio' => '2:1 to 1:1',
							'max_size' => '5000x5000px',
						),
						'recommendation' => __( 'Add twitter:image:width and twitter:image:height meta tags', 'wpshadow' ),
						'benefit' => __( 'Dimensions prevent Twitter from cropping or distorting your image', 'wpshadow' ),
					),
				);
			}
		}

		// Pattern 5: ALT text missing for accessibility
		if ( ! preg_match( '/<meta\s+name=["\']twitter:image:alt/', $homepage_content ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Twitter Card image missing alt text for accessibility', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/twitter-card-implementation',
				'details'      => array(
					'issue' => 'missing_image_alt_text',
					'message' => __( 'twitter:image:alt tag not found', 'wpshadow' ),
					'accessibility_impact' => __( 'Blind and visually impaired users on Twitter cannot understand image content', 'wpshadow' ),
					'inclusive_design' => __( 'Alt text makes your content accessible to 1B+ visually impaired internet users', 'wpshadow' ),
					'example_format' => '<meta name="twitter:image:alt" content="Description of image" />',
					'best_practice' => __( 'Alt text should be descriptive but concise (125 characters max)', 'wpshadow' ),
					'wcag_compliance' => 'WCAG 2.1 Level A - 1.1.1 Non-text Content',
				),
			);
		}

		// Pattern 6: Description mismatch or not optimized for Twitter
		if ( ! preg_match( '/<meta\s+name=["\']twitter:description/', $homepage_content ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Twitter Card missing dedicated description', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/twitter-card-implementation',
				'details'      => array(
					'issue' => 'missing_twitter_description',
					'message' => __( 'twitter:description tag not configured', 'wpshadow' ),
					'recommendation' => __( 'Use twitter:description for Twitter-specific optimization', 'wpshadow' ),
					'why_important' => __( 'Allows different description from Open Graph for platform optimization', 'wpshadow' ),
					'character_limit' => '200 characters (160 recommended for mobile display)',
					'optimization_tips' => array(
						'Include call-to-action (Read more, Learn how, etc.)',
						'Use Twitter-friendly language (conversational, engaging)',
						'Mention key topics users search for',
						'Avoid generic descriptions',
					),
					'impact' => __( 'Optimized descriptions increase click-through rate by 15-25%', 'wpshadow' ),
				),
			);
		}

		return null; // No issues found
	}
}
