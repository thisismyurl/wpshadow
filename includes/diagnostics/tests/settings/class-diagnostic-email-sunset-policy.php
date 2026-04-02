<?php
/**
 * Email Sunset Policy Diagnostic
 *
 * Tests whether the site implements a documented policy for removing inactive subscribers.
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
 * Email Sunset Policy Diagnostic Class
 *
 * Sunset policies improve deliverability and reduce costs. Keeping inactive
 * subscribers damages sender reputation and wastes money.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Email_Sunset_Policy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-sunset-policy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Sunset Policy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site implements a documented policy for removing inactive subscribers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$sunset_score = 0;
		$max_score = 5;

		// Check for documented policy.
		$documented_policy = self::check_documented_policy();
		if ( $documented_policy ) {
			$sunset_score++;
		} else {
			$issues[] = __( 'No documented sunset policy for inactive subscribers', 'wpshadow' );
		}

		// Check for automation.
		$automated_removal = self::check_automated_removal();
		if ( $automated_removal ) {
			$sunset_score++;
		} else {
			$issues[] = __( 'No automated removal of inactive subscribers', 'wpshadow' );
		}

		// Check for warning sequence.
		$warning_sequence = self::check_warning_sequence();
		if ( $warning_sequence ) {
			$sunset_score++;
		} else {
			$issues[] = __( 'No warning sequence before removing subscribers', 'wpshadow' );
		}

		// Check for reactivation opportunity.
		$reactivation = self::check_reactivation_opportunity();
		if ( $reactivation ) {
			$sunset_score++;
		} else {
			$issues[] = __( 'No final reactivation opportunity before removal', 'wpshadow' );
		}

		// Check for timeline.
		$clear_timeline = self::check_clear_timeline();
		if ( $clear_timeline ) {
			$sunset_score++;
		} else {
			$issues[] = __( 'No clear timeline for sunset process (recommend 6-12 months)', 'wpshadow' );
		}

		// Determine severity based on sunset policy.
		$sunset_percentage = ( $sunset_score / $max_score ) * 100;

		if ( $sunset_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 20;
		} elseif ( $sunset_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Sunset policy implementation percentage */
				__( 'Email sunset policy at %d%%. ', 'wpshadow' ),
				(int) $sunset_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Sunset policies improve deliverability and reduce costs', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-sunset-policy',
			);
		}

		return null;
	}

	/**
	 * Check documented policy.
	 *
	 * @since 1.6093.1200
	 * @return bool True if documented, false otherwise.
	 */
	private static function check_documented_policy() {
		// Check for sunset policy documentation.
		$query = new \WP_Query(
			array(
				's'              => 'sunset policy inactive subscriber removal',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check automated removal.
	 *
	 * @since 1.6093.1200
	 * @return bool True if automated, false otherwise.
	 */
	private static function check_automated_removal() {
		// MailPoet can automate subscriber management.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_automated_sunset', false );
	}

	/**
	 * Check warning sequence.
	 *
	 * @since 1.6093.1200
	 * @return bool True if sequence exists, false otherwise.
	 */
	private static function check_warning_sequence() {
		// Check for warning content.
		$query = new \WP_Query(
			array(
				's'              => 'final email last chance stay subscribed',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check reactivation opportunity.
	 *
	 * @since 1.6093.1200
	 * @return bool True if opportunity exists, false otherwise.
	 */
	private static function check_reactivation_opportunity() {
		// Most sunset sequences include reactivation.
		$query = new \WP_Query(
			array(
				's'              => 'reactivate subscription stay on list',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check clear timeline.
	 *
	 * @since 1.6093.1200
	 * @return bool True if timeline exists, false otherwise.
	 */
	private static function check_clear_timeline() {
		// Check for timeline documentation.
		$keywords = array( '6 months', '90 days', '180 days', 'inactive period' );

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
