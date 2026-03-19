<?php
/**
 * Responds to Negative Feedback Diagnostic
 *
 * Tests if negative reviews and feedback are addressed promptly.
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
 * Responds to Negative Feedback Diagnostic Class
 *
 * Verifies that review/feedback handling is documented.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Addresses_Negative_Feedback extends Diagnostic_Base {

	protected static $slug = 'addresses-negative-feedback';
	protected static $title = 'Responds to Negative Feedback';
	protected static $description = 'Tests if negative reviews and feedback are addressed promptly';
	protected static $family = 'publisher';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$manual_flag = get_option( 'wpshadow_negative_feedback_process' );
		if ( $manual_flag ) {
			return null;
		}

		$keywords = array(
			'negative feedback',
			'review response',
			'complaint handling',
			'customer response policy',
		);

		if ( self::has_documented_item( $keywords ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'No negative feedback response process found. Create a response playbook to protect trust and retention.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 20,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/responds-to-negative-feedback',
			'persona'      => 'publisher',
		);
	}

	/**
	 * Check for documentation evidence in posts.
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
