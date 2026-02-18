<?php
/**
 * Retrospectives Conducted Diagnostic
 *
 * Tests if team conducts regular retrospectives.
 *
 * @since   1.6050.0000
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Retrospectives Conducted Diagnostic Class
 *
 * Verifies that teams run retrospectives or post‑mortems regularly.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Conducts_Retrospectives extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'conducts-retrospectives';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Retrospectives Conducted';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if team conducts regular retrospectives';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6050.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_retrospectives_conducted' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'retrospective',
			'postmortem',
			'post-mortem',
			'lessons learned',
			'retro notes',
		);

		if ( self::has_recent_documented_item( $keywords, 180 ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No recent retrospectives found. Regular retros help teams learn from incidents and improve reliability.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/retrospectives-conducted',
			'persona'      => 'enterprise-corp',
		);
	}

	/**
	 * Check for recent documentation evidence in posts.
	 *
	 * @since  1.6050.0000
	 * @param  array $keywords Search terms.
	 * @param  int   $days     Lookback window.
	 * @return bool True if found.
	 */
	private static function has_recent_documented_item( array $keywords, $days ) {
		if ( ! function_exists( 'get_posts' ) ) {
			return false;
		}

		$after = gmdate( 'Y-m-d H:i:s', time() - ( $days * DAY_IN_SECONDS ) );

		foreach ( $keywords as $keyword ) {
			$posts = get_posts(
				array(
					's'              => $keyword,
					'post_type'      => array( 'post', 'page', 'documentation', 'kb' ),
					'post_status'    => array( 'publish', 'private' ),
					'posts_per_page' => 1,
					'fields'         => 'ids',
					'date_query'     => array(
						array(
							'after' => $after,
						),
					),
				)
			);

			if ( ! empty( $posts ) ) {
				return true;
			}
		}

		return false;
	}
}
