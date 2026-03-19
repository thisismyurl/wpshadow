<?php
/**
 * Annual Business Goals Diagnostic
 *
 * Tests if clear annual objectives are documented.
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
 * Annual Business Goals Diagnostic Class
 *
 * Verifies that annual objectives (OKRs, strategic plans, or goals) are
 * documented and discoverable.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Sets_Annual_Goals extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'sets-annual-goals';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Annual Business Goals';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if clear annual objectives are documented';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_annual_goals_documented' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'annual goals',
			'annual objectives',
			'objectives and key results',
			'okrs',
			'strategic plan',
			'annual plan',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No documented annual objectives found. Define and document yearly goals to align priorities and measure success.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 40,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/annual-business-goals',
			'persona'      => 'enterprise-corp',
			'meta'         => array(
				'manual_flag' => (bool) $manual_flag,
			),
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

			$attachments = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => 'attachment',
					'post_status'    => 'inherit',
					'posts_per_page' => 1,
					'fields'         => 'ids',
				)
			);

			if ( ! empty( $attachments ) ) {
				return true;
			}
		}

		return false;
	}
}
