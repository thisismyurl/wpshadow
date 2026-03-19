<?php
/**
 * Cross-Training Program Diagnostic
 *
 * Tests if team members are cross-trained.
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
 * Cross-Training Program Diagnostic Class
 *
 * Verifies that responsibilities are shared and knowledge coverage exists.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Implements_Cross_Training extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'implements-cross-training';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Cross-Training Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if team members are cross-trained';

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
		$manual_flag = get_option( 'wpshadow_cross_training_program' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'cross training',
			'backup coverage',
			'skills matrix',
			'role rotation',
			'coverage plan',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No cross-training evidence found. Create a skills matrix and backup coverage plan to reduce single‑person risk.', 'wpshadow' ),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/cross-training-program',
			'persona'      => 'enterprise-corp',
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
}
