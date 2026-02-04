<?php
/**
 * Missing Meta Descriptions Diagnostic
 *
 * Detects when meta descriptions are missing or not optimized,
 * reducing search result click-through rates.
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
 * Diagnostic: Missing Meta Descriptions
 *
 * Checks whether all pages have optimized meta descriptions
 * for search results.
 *
 * @since 1.6035.2148
 */
class Diagnostic_Missing_Meta_Descriptions extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'missing-meta-descriptions';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Meta Descriptions';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether meta descriptions are present and optimized';

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
		// Check for SEO plugins that auto-generate descriptions
		$has_seo_plugin = is_plugin_active( 'yoast-seo/wp-seo.php' ) ||
			is_plugin_active( 'rank-math-seo/rank-math.php' ) ||
			is_plugin_active( 'all-in-one-seo-pack/all_in_one_seo_pack.php' );

		// Check pages for meta descriptions
		$posts = get_posts( array(
			'post_type'      => array( 'post', 'page' ),
			'posts_per_page' => -1,
			'post_status'    => 'publish',
		) );

		$missing_descriptions = 0;
		$short_descriptions = 0;
		$total_pages = count( $posts );

		foreach ( $posts as $post ) {
			$meta_desc = get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ) ?:
				get_post_meta( $post->ID, '_rank_math_description', true ) ?:
				get_post_meta( $post->ID, '_aioseop_description', true );

			if ( empty( $meta_desc ) ) {
				$missing_descriptions++;
			} elseif ( strlen( $meta_desc ) < 120 ) {
				$short_descriptions++;
			}
		}

		$percentage = ( $total_pages > 0 ) ? round( ( $missing_descriptions / $total_pages ) * 100 ) : 0;

		if ( $percentage > 20 && ! $has_seo_plugin ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__(
						'About %d%% of your pages (%d of %d) are missing meta descriptions. Meta descriptions are the text that appears under your page title in Google search results. When Google shows your meta description, users can see what the page is about before clicking—influencing their decision to click. Descriptions should be 150-160 characters. Good descriptions can increase click-through rate by 20-30%% compared to auto-generated summaries.',
						'wpshadow'
					),
					$percentage,
					$missing_descriptions,
					$total_pages
				),
				'severity'      => 'medium',
				'threat_level'  => 50,
				'auto_fixable'  => false,
				'missing_count' => $missing_descriptions,
				'business_impact' => array(
					'metric'         => 'Search Result CTR',
					'potential_gain' => '+20-30% click-through rate',
					'roi_explanation' => 'Optimized meta descriptions increase search result CTR by 20-30% by helping users understand page content before clicking.',
				),
				'kb_link'       => 'https://wpshadow.com/kb/meta-descriptions',
			);
		}

		return null;
	}
}
