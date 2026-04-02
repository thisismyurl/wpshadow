<?php
/**
 * Homepage Meta Diagnostic
 *
 * Checks whether the homepage has a unique meta title and description
 * configured in an SEO plugin rather than relying on WordPress site defaults.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Homepage_Meta Class
 *
 * Reads homepage meta title and description from Yoast SEO or Rank Math
 * post-meta and flags missing fields on the front page.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Homepage_Meta extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'homepage-meta';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Homepage Meta';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether the homepage has a unique meta title and description configured in an SEO plugin rather than relying on the WordPress site title defaults.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * Detects the active SEO plugin (Yoast or Rank Math), reads meta title
	 * and description from the front-page post meta, and returns a high-severity
	 * finding that lists any fields that are empty.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when homepage meta is incomplete, null when healthy.
	 */
	public static function check() {
		$active_plugins = (array) get_option( 'active_plugins', array() );

		$has_yoast    = in_array( 'wordpress-seo/wp-seo.php', $active_plugins, true )
		             || in_array( 'wordpress-seo-premium/wp-seo-premium.php', $active_plugins, true );
		$has_rankmath = in_array( 'seo-by-rank-math/rank-math.php', $active_plugins, true )
		             || in_array( 'seo-by-rank-math-pro/rank-math-pro.php', $active_plugins, true );

		if ( ! $has_yoast && ! $has_rankmath ) {
			return null; // Cannot determine without a supported SEO plugin.
		}

		$show_on_front  = get_option( 'show_on_front', 'posts' ); // 'posts' or 'page'
		$front_page_id  = (int) get_option( 'page_on_front', 0 );
		$missing_fields = array();

		if ( $has_yoast ) {
			if ( 'page' === $show_on_front && $front_page_id > 0 ) {
				$title = get_post_meta( $front_page_id, '_yoast_wpseo_title', true );
				$desc  = get_post_meta( $front_page_id, '_yoast_wpseo_metadesc', true );
				if ( empty( trim( $title ) ) ) {
					$missing_fields[] = 'meta title';
				}
				if ( empty( trim( $desc ) ) ) {
					$missing_fields[] = 'meta description';
				}
			} else {
				$titles = get_option( 'wpseo_titles', array() );
				if ( empty( $titles['title-home-wpseo'] ) ) {
					$missing_fields[] = 'meta title (home)';
				}
				if ( empty( $titles['metadesc-home-wpseo'] ) ) {
					$missing_fields[] = 'meta description (home)';
				}
			}
		} elseif ( $has_rankmath ) {
			if ( 'page' === $show_on_front && $front_page_id > 0 ) {
				$title = get_post_meta( $front_page_id, 'rank_math_title', true );
				$desc  = get_post_meta( $front_page_id, 'rank_math_description', true );
				if ( empty( trim( $title ) ) ) {
					$missing_fields[] = 'meta title';
				}
				if ( empty( trim( $desc ) ) ) {
					$missing_fields[] = 'meta description';
				}
			}
		}

		if ( empty( $missing_fields ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: list of missing meta fields */
				__( 'The homepage is missing the following SEO metadata: %s. The homepage is typically the highest-traffic page on your site and should have a carefully crafted meta title and description. Fill these in via your SEO plugin.', 'wpshadow' ),
				implode( ', ', $missing_fields )
			),
			'severity'     => 'high',
			'threat_level' => 60,
			'kb_link'      => 'https://wpshadow.com/kb/homepage-meta?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'missing_fields' => $missing_fields,
				'show_on_front'  => $show_on_front,
				'front_page_id'  => $front_page_id,
			),
		);
	}
}
