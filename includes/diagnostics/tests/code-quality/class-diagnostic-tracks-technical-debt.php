<?php
/**
 * Technical Debt Tracked Diagnostic
 *
 * Tests if improvements and debt are tracked.
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
 * Technical Debt Tracked Diagnostic Class
 *
 * Verifies technical debt is documented and prioritized.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Tracks_Technical_Debt extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'tracks-technical-debt';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Technical Debt Tracked';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if improvements and debt are tracked';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'code-quality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_technical_debt_log' );
		if ( $manual_flag ) {
			return null;
		}

		$paths = array(
			ABSPATH . 'TECHNICAL_DEBT.md',
			ABSPATH . 'docs/technical-debt.md',
			ABSPATH . 'docs/tech-debt.md',
		);

		foreach ( $paths as $path ) {
			if ( file_exists( $path ) ) {
				return null;
			}
		}

		$keywords = array(
			'technical debt',
			'tech debt',
			'debt backlog',
			'refactor plan',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No technical debt tracking found. Document debt so fixes are planned and risk stays visible.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/technical-debt-tracked?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'persona'      => 'developer',
		);
	}

	/**
	 * Check for documentation evidence in posts.
	 *
	 * @since 0.6093.1200
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
}
