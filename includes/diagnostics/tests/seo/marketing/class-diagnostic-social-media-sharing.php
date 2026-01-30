<?php
/**
 * Social Media Sharing Diagnostic
 *
 * Verifies Open Graph and Twitter Card meta tags for proper
 * social media sharing with images and descriptions.
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
 * Diagnostic_Social_Media_Sharing Class
 *
 * Verifies social sharing meta tags.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Social_Media_Sharing extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'social-media-sharing';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Social Media Sharing';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies Open Graph and Twitter Card meta tags';

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
	 * @return array|null Finding array if meta tags missing, null otherwise.
	 */
	public static function check() {
		$social_check = self::check_social_meta_tags();

		if ( ! $social_check['has_issue'] ) {
			return null; // Social meta tags present
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Missing social media meta tags. Shares on Facebook/Twitter show no image, generic text. Posts with images = 2.3x more engagement. Bad shares = lost traffic.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 50,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/social-sharing',
			'family'       => self::$family,
			'meta'         => array(
				'og_tags_found'      => $social_check['has_og'] ? 'yes' : 'no',
				'twitter_tags_found' => $social_check['has_twitter'] ? 'yes' : 'no',
			),
			'details'      => array(
				'why_social_meta_tags_matter' => array(
					__( 'Control how links appear on Facebook, Twitter, LinkedIn' ),
					__( 'Posts with images = 2.3x more engagement (Buffer)' ),
					__( 'Proper titles/descriptions increase click-through' ),
					__( 'Professional appearance builds trust' ),
					__( 'Without tags: Generic site description, no image' ),
				),
				'open_graph_protocol'         => array(
					'What It Is' => __( 'Facebook/LinkedIn meta tags' ),
					'Required Tags' => array(
						'og:title → Page title',
						'og:description → Brief description',
						'og:image → Featured image URL',
						'og:url → Canonical page URL',
						'og:type → "article" or "website"',
					),
					'Image Requirements' => array(
						'Dimensions: 1200×630px (recommended)',
						'Minimum: 600×315px',
						'Format: JPG or PNG',
						'Max size: 8MB',
					),
				),
				'twitter_cards'               => array(
					'What It Is' => __( 'Twitter-specific meta tags' ),
					'Card Types' => array(
						'summary_large_image: Large image (preferred)',
						'summary: Small thumbnail',
					),
					'Required Tags' => array(
						'twitter:card → "summary_large_image"',
						'twitter:title → Page title',
						'twitter:description → Brief description',
						'twitter:image → Featured image URL',
					),
					'Image Requirements' => array(
						'Dimensions: 1200×675px or 2:1 ratio',
						'Format: JPG, PNG, WEBP, GIF',
						'Max size: 5MB',
					),
				),
				'implementing_social_tags'    => array(
					'Via SEO Plugin (Easiest)' => array(
						'Yoast SEO: Settings → Social',
						'Rank Math: Settings → Social Meta',
						'All in One SEO: Social Networks',
						'Auto-generates: Tags from post data',
					),
					'Manual Implementation' => array(
						'File: header.php (before </head>)',
						'Open Graph:',
						'<meta property="og:title" content="<?php the_title(); ?>">',
						'<meta property="og:description" content="...">',
						'<meta property="og:image" content="<?php echo get_the_post_thumbnail_url(); ?>">',
						'Twitter Cards:',
						'<meta name="twitter:card" content="summary_large_image">',
					),
				),
				'testing_social_sharing'      => array(
					'Facebook Sharing Debugger' => array(
						'URL: developers.facebook.com/tools/debug',
						'Enter page URL',
						'Shows: Preview of how link appears',
						'Scrape Again: Updates cached data',
					),
					'Twitter Card Validator' => array(
						'URL: cards-dev.twitter.com/validator',
						'Enter page URL',
						'Shows: Card preview',
						'Log in required',
					),
					'LinkedIn Post Inspector' => array(
						'URL: linkedin.com/post-inspector',
						'Enter page URL',
						'Clear cache if not updating',
					),
				),
				'common_issues'               => array(
					'Image Not Showing' => array(
						'Cause: Wrong URL (relative vs absolute)',
						'Fix: Use full URL (https://example.com/image.jpg)',
					),
					'Wrong Image' => array(
						'Cause: Cached old image',
						'Fix: Use Facebook debugger "Scrape Again"',
					),
					'Generic Description' => array(
						'Cause: No custom excerpt',
						'Fix: Set post excerpt or SEO description',
					),
				),
			),
		);
	}

	/**
	 * Check social meta tags.
	 *
	 * @since  1.2601.2148
	 * @return array Social meta tag status.
	 */
	private static function check_social_meta_tags() {
		// Check for SEO plugins (typically handle social tags)
		$has_seo_plugin = is_plugin_active( 'wordpress-seo/wp-seo.php' ) ||
						  is_plugin_active( 'seo-by-rank-math/rank-math.php' ) ||
						  is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' );

		if ( $has_seo_plugin ) {
			return array(
				'has_issue'   => false,
				'has_og'      => true,
				'has_twitter' => true,
			);
		}

		// Check homepage for meta tags
		$homepage = wp_remote_get( home_url() );
		if ( is_wp_error( $homepage ) ) {
			return array(
				'has_issue'   => true,
				'has_og'      => false,
				'has_twitter' => false,
			);
		}

		$body = wp_remote_retrieve_body( $homepage );
		$has_og = strpos( $body, 'og:image' ) !== false;
		$has_twitter = strpos( $body, 'twitter:card' ) !== false;

		return array(
			'has_issue'   => ! $has_og && ! $has_twitter,
			'has_og'      => $has_og,
			'has_twitter' => $has_twitter,
		);
	}
}
