<?php
/**
 * SEO Strategy Documented Diagnostic
 *
 * Tests if written SEO strategy and goals are documented.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * SEO Strategy Documented Diagnostic Class
 *
 * Verifies that a written SEO strategy exists.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Has_Documented_SEO_Strategy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'has-documented-seo-strategy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'SEO Strategy Documented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if written SEO strategy and goals are documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_seo_strategy_documented' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'seo strategy',
			'search strategy',
			'seo plan',
			'search goals',
			'seo roadmap',
		);

		if ( self::has_documented_item( $keywords ) || self::has_strategy_files() ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No SEO strategy found. Document goals, target keywords, and content priorities to focus efforts.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/seo-strategy-documented',
			'persona'      => 'publisher',
		);
	}

	/**
	 * Check for documentation evidence in posts or attachments.
	 *
	 * @since 1.6093.1200
	 * @param  array $keywords Search terms.
	 * @return bool True if found.
	 */
	private static function has_documented_item( array $keywords ) {
		if ( ! function_exists( 'get_posts' ) ) {
			return false;
		}

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'page', 'post', 'documentation', 'kb' ),
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $posts ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check for SEO strategy files in site root.
	 *
	 * @since 1.6093.1200
	 * @return bool True if found.
	 */
	private static function has_strategy_files() {
		$paths = array(
			ABSPATH . 'SEO_STRATEGY.md',
			ABSPATH . 'docs/seo-strategy.md',
			ABSPATH . 'docs/seo-plan.md',
		);

		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				return true;
			}
		}

		return false;
	}
}
