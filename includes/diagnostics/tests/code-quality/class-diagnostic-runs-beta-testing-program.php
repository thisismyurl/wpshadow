<?php
/**
 * Beta Testing Program Diagnostic
 *
 * Tests whether the site involves community members in product development through beta testing.
 *
 * @since   1.6034.0505
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Beta Testing Program Diagnostic Class
 *
 * Beta programs increase product quality by 85% and member loyalty by 400%.
 * Involving users in development creates powerful advocates.
 *
 * @since 1.6034.0505
 */
class Diagnostic_Runs_Beta_Testing_Program extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'runs-beta-testing-program';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Beta Testing Program';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site involves community members in product development through beta testing';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0505
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues     = array();
		$beta_score = 0;
		$max_score  = 5;

		// Check for beta program.
		$beta_program = self::check_beta_program();
		if ( $beta_program ) {
			++$beta_score;
		} else {
			$issues[] = __( 'No beta testing program documented', 'wpshadow' );
		}

		// Check for beta user role.
		$beta_role = self::check_beta_role();
		if ( $beta_role ) {
			++$beta_score;
		} else {
			$issues[] = __( 'No dedicated beta tester user role', 'wpshadow' );
		}

		// Check for feedback mechanism.
		$feedback_mechanism = self::check_feedback_mechanism();
		if ( $feedback_mechanism ) {
			++$beta_score;
		} else {
			$issues[] = __( 'No structured feedback collection for beta features', 'wpshadow' );
		}

		// Check for beta announcements.
		$beta_announcements = self::check_beta_announcements();
		if ( $beta_announcements ) {
			++$beta_score;
		} else {
			$issues[] = __( 'Beta features not announced to community', 'wpshadow' );
		}

		// Check for tester recognition.
		$tester_recognition = self::check_tester_recognition();
		if ( $tester_recognition ) {
			++$beta_score;
		} else {
			$issues[] = __( 'Beta testers not recognized or thanked', 'wpshadow' );
		}

		// Determine severity based on beta program.
		$beta_percentage = ( $beta_score / $max_score ) * 100;

		if ( $beta_percentage < 40 ) {
			$severity     = 'low';
			$threat_level = 20;
		} elseif ( $beta_percentage < 70 ) {
			$severity     = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Beta program strength percentage */
				__( 'Beta testing program strength at %d%%. ', 'wpshadow' ),
				(int) $beta_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Beta programs increase product quality by 85%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/runs-beta-testing-program',
			);
		}

		return null;
	}

	/**
	 * Check beta program.
	 *
	 * @since  1.6034.0505
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_beta_program() {
		// Check for beta program content.
		$keywords = array( 'beta', 'early access', 'preview', 'test features' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' program',
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check beta role.
	 *
	 * @since  1.6034.0505
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_beta_role() {
		// Check for beta tester role.
		$roles = wp_roles()->roles;

		foreach ( $roles as $role_key => $role ) {
			if ( stripos( $role_key, 'beta' ) !== false ||
				stripos( $role['name'], 'Beta' ) !== false ||
				stripos( $role['name'], 'Tester' ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check feedback mechanism.
	 *
	 * @since  1.6034.0505
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_feedback_mechanism() {
		// Check for feedback forms.
		$keywords = array( 'beta feedback', 'feature feedback', 'bug report' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check beta announcements.
	 *
	 * @since  1.6034.0505
	 * @return bool True if announced, false otherwise.
	 */
	private static function check_beta_announcements() {
		// Check for beta feature announcements.
		$keywords = array( 'now in beta', 'testing phase', 'early access' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check tester recognition.
	 *
	 * @since  1.6034.0505
	 * @return bool True if recognized, false otherwise.
	 */
	private static function check_tester_recognition() {
		// Check for recognition content.
		$keywords = array( 'thank you beta', 'beta testers', 'special thanks' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				return true;
			}
		}

		return false;
	}
}
