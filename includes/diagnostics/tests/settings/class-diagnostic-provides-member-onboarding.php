<?php
/**
 * Member Onboarding Process Diagnostic
 *
 * Tests whether the site provides structured onboarding to help new community members succeed.
 *
 * @since   1.6034.0520
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Member Onboarding Process Diagnostic Class
 *
 * Structured onboarding increases member activation by 500% and long-term retention
 * by 350%. First 24 hours determine member success.
 *
 * @since 1.6034.0520
 */
class Diagnostic_Provides_Member_Onboarding extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'provides-member-onboarding';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Member Onboarding Process';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site provides structured onboarding to help new community members succeed';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'community-building';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0520
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$onboarding_score = 0;
		$max_score = 6;

		// Check for welcome email.
		$welcome_email = self::check_welcome_email();
		if ( $welcome_email ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No automated welcome email for new members', 'wpshadow' );
		}

		// Check for getting started guide.
		$getting_started = self::check_getting_started_guide();
		if ( $getting_started ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No "getting started" or orientation guide', 'wpshadow' );
		}

		// Check for community introduction.
		$community_intro = self::check_community_introduction();
		if ( $community_intro ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No prompt for new members to introduce themselves', 'wpshadow' );
		}

		// Check for onboarding checklist.
		$onboarding_checklist = self::check_onboarding_checklist();
		if ( $onboarding_checklist ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No onboarding checklist or first actions guide', 'wpshadow' );
		}

		// Check for mentor/buddy system.
		$mentor_system = self::check_mentor_system();
		if ( $mentor_system ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No mentor or buddy system for new members', 'wpshadow' );
		}

		// Check for onboarding follow-up.
		$onboarding_followup = self::check_onboarding_followup();
		if ( $onboarding_followup ) {
			$onboarding_score++;
		} else {
			$issues[] = __( 'No follow-up sequence to ensure member success', 'wpshadow' );
		}

		// Determine severity based on onboarding.
		$onboarding_percentage = ( $onboarding_score / $max_score ) * 100;

		if ( $onboarding_percentage < 35 ) {
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $onboarding_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Onboarding completeness percentage */
				__( 'Member onboarding completeness at %d%%. ', 'wpshadow' ),
				(int) $onboarding_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Structured onboarding increases activation by 500%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/provides-member-onboarding',
			);
		}

		return null;
	}

	/**
	 * Check welcome email.
	 *
	 * @since  1.6034.0520
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_welcome_email() {
		// Check for email automation plugins.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ||
			 is_plugin_active( 'newsletter/plugin.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_welcome_email', false );
	}

	/**
	 * Check getting started guide.
	 *
	 * @since  1.6034.0520
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_getting_started_guide() {
		// Check for getting started page.
		$keywords = array( 'getting started', 'quick start', 'new member guide', 'orientation' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'page',
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
	 * Check community introduction.
	 *
	 * @since  1.6034.0520
	 * @return bool True if prompted, false otherwise.
	 */
	private static function check_community_introduction() {
		// Check for introduction prompts.
		$keywords = array( 'introduce yourself', 'new member introduction', 'say hello' );

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
	 * Check onboarding checklist.
	 *
	 * @since  1.6034.0520
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_onboarding_checklist() {
		// Check for checklist content.
		$keywords = array( 'checklist', 'first steps', 'to-do', 'action items' );

		foreach ( $keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword . ' new member',
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
	 * Check mentor system.
	 *
	 * @since  1.6034.0520
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_mentor_system() {
		// Check for mentor/buddy program.
		$keywords = array( 'mentor', 'buddy', 'onboarding buddy', 'welcome team' );

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
	 * Check onboarding follow-up.
	 *
	 * @since  1.6034.0520
	 * @return bool True if exists, false otherwise.
	 */
	private static function check_onboarding_followup() {
		// Difficult to detect automatically.
		return apply_filters( 'wpshadow_has_onboarding_followup', false );
	}
}
