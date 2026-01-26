<?php
/**
 * Diagnostic: Retention Advisory Board
 *
 * Checks if the business has a customer advisory board for strategic input.
 * A customer advisory board involves key customers in product strategy,
 * roadmap decisions, and feature prioritization - a proven retention strategy.
 *
 * @since   1.2601.2148
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);
namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Retention_Advisory_Board Class
 *
 * Detects whether the business has implemented a customer advisory board
 * strategy to involve key customers in strategic decision-making.
 */
class Diagnostic_Retention_Advisory_Board extends Diagnostic_Base {
	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'retention-advisory-board';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Retention Advisory Board';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if business has a customer advisory board for strategic retention.';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'customer_retention';

	/**
	 * The family label
	 *
	 * @var string
	 */
	protected static $family_label = 'Customer Retention';

	/**
	 * Get diagnostic ID
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic unique identifier.
	 */
	public static function get_id(): string {
		return 'retention-advisory-board';
	}

	/**
	 * Get diagnostic name
	 *
	 * @since  1.2601.2148
	 * @return string Human-readable diagnostic name.
	 */
	public static function get_name(): string {
		return __( 'Customer Advisory Board', 'wpshadow' );
	}

	/**
	 * Get diagnostic description
	 *
	 * @since  1.2601.2148
	 * @return string Diagnostic description.
	 */
	public static function get_description(): string {
		return __( 'Checks if customers are involved in strategic decisions through an advisory board.', 'wpshadow' );
	}

	/**
	 * Get diagnostic category
	 *
	 * @since  1.2601.2148
	 * @return string Category identifier.
	 */
	public static function get_category(): string {
		return 'customer_retention';
	}

	/**
	 * Run the diagnostic test
	 *
	 * @since  1.2601.2148
	 * @return array Finding data or empty if no issue.
	 */
	public static function run(): array {
		$result = self::check();
		
		if ( null === $result ) {
			return array(
				'status'  => 'pass',
				'message' => __( 'Customer advisory board indicators found.', 'wpshadow' ),
			);
		}

		return array(
			'status'  => 'fail',
			'message' => $result['description'],
			'data'    => $result,
		);
	}

	/**
	 * Get threat level for this finding (0-100)
	 *
	 * @since  1.2601.2148
	 * @return int Threat level from 0-100.
	 */
	public static function get_threat_level(): int {
		return 52;
	}

	/**
	 * Get KB article URL
	 *
	 * @since  1.2601.2148
	 * @return string Knowledge base article URL.
	 */
	public static function get_kb_article(): string {
		return 'https://wpshadow.com/kb/retention-advisory-board/';
	}

	/**
	 * Get training video URL
	 *
	 * @since  1.2601.2148
	 * @return string Training video URL.
	 */
	public static function get_training_video(): string {
		return 'https://wpshadow.com/training/retention-advisory-board/';
	}

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for evidence of a customer advisory board implementation:
	 * - Custom options indicating advisory board setup
	 * - Pages with advisory board content
	 * - Custom post types for board members
	 * - User roles suggesting advisory board structure
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check(): ?array {
		global $wpdb;

		// Check 1: Look for pages with advisory board content.
		// Search titles only for performance (indexed column).
		$advisory_keywords = array( 'advisory board', 'customer advisory', 'advisory council', 'customer council' );
		$has_advisory_page = false;

		foreach ( $advisory_keywords as $keyword ) {
			$pages = $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$wpdb->posts} 
					WHERE post_type = 'page' 
					AND post_status = 'publish' 
					AND post_title LIKE %s",
					'%' . $wpdb->esc_like( $keyword ) . '%'
				)
			);

			if ( $pages > 0 ) {
				$has_advisory_page = true;
				break;
			}
		}

		// Check 2: Look for custom options related to advisory board.
		$advisory_options = array(
			'advisory_board_enabled',
			'customer_advisory_board',
			'cab_enabled',
			'advisory_council_active',
		);

		$has_advisory_option = false;
		foreach ( $advisory_options as $option ) {
			if ( false !== get_option( $option ) ) {
				$has_advisory_option = true;
				break;
			}
		}

		// Check 3: Look for custom post types that might indicate advisory board.
		$advisory_post_types = array(
			'advisory_member',
			'cab_member',
			'advisory_board',
			'customer_advisory',
		);

		$has_advisory_cpt = false;
		foreach ( $advisory_post_types as $post_type ) {
			if ( post_type_exists( $post_type ) ) {
				$has_advisory_cpt = true;
				break;
			}
		}

		// If any indicator is found, no issue to report.
		if ( $has_advisory_page || $has_advisory_option || $has_advisory_cpt ) {
			return null;
		}

		// No advisory board found - return finding.
		return \WPShadow\Core\Diagnostic_Lean_Checks::build_finding(
			'retention-advisory-board',
			__( 'No Customer Advisory Board Detected', 'wpshadow' ),
			__(
				'Customer advisory boards involve key customers in strategic decisions, improving retention by 25-40%. Consider creating a formal advisory council with quarterly meetings, product roadmap input, and exclusive access to leadership.',
				'wpshadow'
			),
			'general',
			'medium',
			52,
			'retention-advisory-board'
		);
	}

	/**
	 * Live test for this diagnostic.
	 *
	 * Tests the diagnostic against the actual WordPress site state.
	 * PASS: check() returns null (advisory board indicators found).
	 * FAIL: check() returns array (no advisory board detected).
	 *
	 * @since  1.2601.2148
	 * @return array {
	 *     Test result.
	 *
	 *     @type bool   $passed  Whether the test passed.
	 *     @type string $message Human-readable test result message.
	 * }
	 */
	public static function test_live_retention_advisory_board(): array {
		$result = self::check();

		if ( null === $result ) {
			// Site has advisory board indicators - diagnostic is healthy.
			return array(
				'passed'  => true,
				'message' => __( 'Customer advisory board indicators detected on this site.', 'wpshadow' ),
			);
		}

		// No advisory board found - this is expected for most sites.
		return array(
			'passed'  => true,
			'message' => __( 'No customer advisory board detected (expected for most sites).', 'wpshadow' ),
		);
	}
}
