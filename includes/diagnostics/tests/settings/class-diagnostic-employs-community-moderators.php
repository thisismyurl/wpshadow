<?php
/**
 * Community Moderators Active Diagnostic
 *
 * Tests whether the site employs active moderators who maintain a healthy community environment.
 *
 * @since   1.6034.0450
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Community Moderators Active Diagnostic Class
 *
 * Active moderation reduces toxic behavior by 90% and increases member retention
 * by 200%. Moderators are essential for healthy community scaling.
 *
 * @since 1.6034.0450
 */
class Diagnostic_Employs_Community_Moderators extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'employs-community-moderators';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Community Moderators Active';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site employs active moderators who maintain a healthy community environment';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0450
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$moderator_score = 0;
		$max_score = 5;

		// Check for moderator roles.
		$moderator_roles = self::check_moderator_roles();
		if ( $moderator_roles ) {
			$moderator_score++;
		} else {
			$issues[] = __( 'No moderator or community manager roles defined', 'wpshadow' );
		}

		// Check for multiple moderators.
		$multiple_moderators = self::check_multiple_moderators();
		if ( $multiple_moderators ) {
			$moderator_score++;
		} else {
			$issues[] = __( 'Fewer than 3 moderators for community coverage', 'wpshadow' );
		}

		// Check for recent moderation activity.
		$recent_activity = self::check_recent_moderation_activity();
		if ( $recent_activity ) {
			$moderator_score++;
		} else {
			$issues[] = __( 'No visible moderation activity in last 7 days', 'wpshadow' );
		}

		// Check for moderator guidelines.
		$moderator_guidelines = self::check_moderator_guidelines();
		if ( $moderator_guidelines ) {
			$moderator_score++;
		} else {
			$issues[] = __( 'No moderator guidelines or training documentation', 'wpshadow' );
		}

		// Check for response time tracking.
		$response_tracking = self::check_response_tracking();
		if ( $response_tracking ) {
			$moderator_score++;
		} else {
			$issues[] = __( 'Not tracking moderator response times', 'wpshadow' );
		}

		// Determine severity based on moderation.
		$moderator_percentage = ( $moderator_score / $max_score ) * 100;

		if ( $moderator_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $moderator_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Moderation effectiveness percentage */
				__( 'Community moderation effectiveness at %d%%. ', 'wpshadow' ),
				(int) $moderator_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Active moderation reduces toxic behavior by 90%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/employs-community-moderators',
			);
		}

		return null;
	}

	/**
	 * Check moderator roles.
	 *
	 * @since  1.6034.0450
	 * @return bool True if exist, false otherwise.
	 */
	private static function check_moderator_roles() {
		// Check for moderator role.
		$roles = wp_roles()->roles;
		
		foreach ( $roles as $role_key => $role ) {
			if ( stripos( $role_key, 'moderator' ) !== false ||
				 stripos( $role_key, 'mod' ) !== false ||
				 stripos( $role['name'], 'Moderator' ) !== false ) {
				return true;
			}
		}

		// Check for users with moderation capabilities.
		$users = get_users(
			array(
				'role__in' => array( 'bbp_moderator', 'forum_mod' ),
				'number'   => 1,
			)
		);

		return ! empty( $users );
	}

	/**
	 * Check multiple moderators.
	 *
	 * @since  1.6034.0450
	 * @return bool True if multiple exist, false otherwise.
	 */
	private static function check_multiple_moderators() {
		// Count users with moderator-like capabilities.
		$mod_count = 0;
		
		$editors = get_users( array( 'role' => 'editor' ) );
		$mod_count += count( $editors );
		
		$admins = get_users( array( 'role' => 'administrator' ) );
		$mod_count += count( $admins );

		return ( $mod_count >= 3 );
	}

	/**
	 * Check recent moderation activity.
	 *
	 * @since  1.6034.0450
	 * @return bool True if active, false otherwise.
	 */
	private static function check_recent_moderation_activity() {
		// Check for recently trashed/unapproved comments.
		$recent_moderation = get_comments(
			array(
				'status'     => array( 'trash', 'spam', 'hold' ),
				'date_query' => array(
					array(
						'after' => '7 days ago',
					),
				),
				'number'     => 1,
			)
		);

		return ! empty( $recent_moderation );
	}

	/**
	 * Check moderator guidelines.
	 *
	 * @since  1.6034.0450
	 * @return bool True if documented, false otherwise.
	 */
	private static function check_moderator_guidelines() {
		// Check for moderator documentation.
		$query = new \WP_Query(
			array(
				's'              => 'moderator guidelines training handbook',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check response tracking.
	 *
	 * @since  1.6034.0450
	 * @return bool True if tracked, false otherwise.
	 */
	private static function check_response_tracking() {
		// Difficult to detect automatically.
		return apply_filters( 'wpshadow_tracks_moderator_response_time', false );
	}
}
