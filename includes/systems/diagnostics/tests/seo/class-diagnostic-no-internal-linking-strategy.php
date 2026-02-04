<?php
/**
 * No Internal Linking Strategy Diagnostic
 *
 * Detects when internal linking is sparse,
 * weakening SEO and user engagement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since      1.6035.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: No Internal Linking Strategy
 *
 * Checks whether internal links are used
 * strategically throughout content.
 *
 * @since 1.6035.2148
 */
class Diagnostic_No_Internal_Linking_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-internal-linking-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Internal Linking Strategy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether internal linking is strategic';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Whether this diagnostic is auto-fixable
	 *
	 * @var bool
	 */
	protected static $auto_fixable = false;

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check recent posts for internal links
		$posts = get_posts( array(
			'post_type'      => 'post',
			'posts_per_page' => 10,
			'post_status'    => 'publish',
		) );

		$posts_with_internal_links = 0;
		$site_url = home_url();

		foreach ( $posts as $post ) {
			$content = $post->post_content;
			// Check for links to own domain
			if ( preg_match( '/<a[^>]*href=["\']' . preg_quote( $site_url, '/' ) . '[^"\']*["\'][^>]*>/i', $content ) ) {
				$posts_with_internal_links++;
			}
		}

		$percentage_with_links = count( $posts ) > 0 ? ( $posts_with_internal_links / count( $posts ) ) * 100 : 0;

		if ( $percentage_with_links < 50 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'Only %d%% of your content has internal links, which weakens SEO and user engagement. Internal links: help Google discover and rank content, pass "link juice" (SEO authority) between pages, keep users on site longer, guide users to important pages. Best practice: 3-5 internal links per article, linking to related content. Sites with strong internal linking see 40-50%% more pages indexed and 20-30%% higher time on site.',
						'wpshadow'
					),
					round( $percentage_with_links )
				),
				'severity'      => 'high',
				'threat_level'  => 60,
				'auto_fixable'  => false,
				'internal_link_percentage' => $percentage_with_links,
				'business_impact' => array(
					'metric'         => 'SEO Authority & User Engagement',
					'potential_gain' => '+40-50% more pages indexed, +20-30% time on site',
					'roi_explanation' => 'Strategic internal linking distributes SEO authority and keeps users engaged longer.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/internal-linking-strategy',
			);
		}

		return null;
	}
}
