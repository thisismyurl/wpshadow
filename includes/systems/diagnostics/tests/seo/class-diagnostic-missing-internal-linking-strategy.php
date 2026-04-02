<?php
/**
 * Missing Internal Linking Strategy Diagnostic
 *
 * Detects when internal linking strategy is not implemented,
 * missing SEO benefits and user navigation improvements.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\SEO
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic: Missing Internal Linking Strategy
 *
 * Checks whether the site implements strategic internal linking
 * for SEO and user navigation purposes.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Missing_Internal_Linking_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-internal-linking-strategy';

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
	protected static $description = 'Checks whether internal linking strategy is implemented';

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
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get all published posts
		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		$total_links = 0;
		$total_internal_links = 0;
		$site_url = wp_parse_url( home_url() );
		$site_domain = $site_url['host'];

		foreach ( $posts as $post ) {
			// Count all links
			preg_match_all( '/<a\s+[^>]*href\s*=\s*["\']?([^"\'\s>]+)["\']?/i', $post->post_content, $links );
			$total_links += count( $links[1] );

			// Count internal links
			foreach ( $links[1] as $link ) {
				$link_host = wp_parse_url( $link );
				if ( isset( $link_host['host'] ) && $link_host['host'] === $site_domain ) {
					$total_internal_links++;
				} elseif ( strpos( $link, '/' ) === 0 && strpos( $link, 'http' ) === false ) {
					// Relative link (internal)
					$total_internal_links++;
				}
			}
		}

		// Internal links should be 30-50% of total links
		$internal_link_ratio = ( $total_links > 0 ) ? round( ( $total_internal_links / $total_links ) * 100 ) : 0;

		if ( $internal_link_ratio < 20 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'Only %d%% of your links are internal (%d of %d), which is below the recommended 30-50%%. Internal links tell Google which pages are important on your site. They also help visitors discover more of your content. A good strategy targets keywords from high-authority pages to pages you want to rank for: write a long article, then link to related articles using anchor text with target keywords. This distributes link authority and helps Google understand your site structure.',
						'wpshadow'
					),
					$internal_link_ratio,
					$total_internal_links,
					$total_links
				),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'internal_link_ratio' => $internal_link_ratio,
				'business_impact' => array(
					'metric'         => 'SEO & User Navigation',
					'potential_gain' => 'Better keyword rankings',
					'roi_explanation' => 'Strategic internal linking improves keyword rankings by distributing authority from strong pages to pages you want to rank for.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/internal-linking-strategy',
			);
		}

		return null;
	}
}
