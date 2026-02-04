<?php
/**
 * Training Budget Allocated Diagnostic
 *
 * Tests if professional development is prioritized.
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
 * Training Budget Allocated Diagnostic Class
 *
 * Verifies that a training budget or learning plan is documented.
 *
 * @since 1.6050.0000
 */
class Diagnostic_Invests_In_Training extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'invests-in-training';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Training Budget Allocated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests if professional development is prioritized';

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
		$budget_amount = (float) get_option( 'wpshadow_training_budget_amount', 0 );
		$budget_flag   = get_option( 'wpshadow_training_budget_enabled' );

		if ( $budget_amount > 0 || $budget_flag ) {
			return null;
		}

		$keywords = array(
			'training budget',
			'learning budget',
			'professional development',
			'certification plan',
			'learning plan',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No training budget evidence found. Invest in professional development to reduce operational risk and improve delivery quality.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/training-budget-allocated',
			'persona'      => 'enterprise-corp',
			'meta'         => array(
				'budget_amount' => $budget_amount,
			),
		);
	}

	/**
	 * Check for documentation evidence in posts or attachments.
	 *
	 * @since  1.6050.0000
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
