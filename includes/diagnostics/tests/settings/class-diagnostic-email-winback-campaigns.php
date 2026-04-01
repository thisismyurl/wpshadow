<?php
/**
 * Email Win-Back Campaigns Diagnostic
 *
 * Tests whether the site runs campaigns to re-engage inactive subscribers.
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
 * Email Win-Back Campaigns Diagnostic Class
 *
 * Win-back campaigns recover 12-15% of inactive subscribers at minimal cost.
 * Without them, you're losing valuable contacts permanently.
 *
 * @since 0.6093.1200
 */
class Diagnostic_Email_Winback_Campaigns extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-winback-campaigns';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Win-Back Campaigns';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site runs campaigns to re-engage inactive subscribers';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'email-marketing';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$winback_score = 0;
		$max_score = 5;

		// Check for automation capability.
		$automation = self::check_automation_capability();
		if ( $automation ) {
			$winback_score++;
		} else {
			$issues[] = __( 'No email automation for win-back campaigns', 'wpshadow' );
		}

		// Check for inactivity tracking.
		$inactivity_tracking = self::check_inactivity_tracking();
		if ( $inactivity_tracking ) {
			$winback_score++;
		} else {
			$issues[] = __( 'Not tracking inactive subscribers', 'wpshadow' );
		}

		// Check for win-back content.
		$winback_content = self::check_winback_content();
		if ( $winback_content ) {
			$winback_score++;
		} else {
			$issues[] = __( 'No win-back email content or templates', 'wpshadow' );
		}

		// Check for incentives.
		$incentives = self::check_incentives();
		if ( $incentives ) {
			$winback_score++;
		} else {
			$issues[] = __( 'No special incentives for returning subscribers', 'wpshadow' );
		}

		// Check for segmentation.
		$segmentation = self::check_segmentation();
		if ( $segmentation ) {
			$winback_score++;
		} else {
			$issues[] = __( 'Win-back campaigns not segmented by inactivity period', 'wpshadow' );
		}

		// Determine severity based on win-back implementation.
		$winback_percentage = ( $winback_score / $max_score ) * 100;

		if ( $winback_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $winback_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Win-back campaign percentage */
				__( 'Email win-back campaigns at %d%%. ', 'wpshadow' ),
				(int) $winback_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Win-back campaigns recover 12-15% of inactive subscribers', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-winback-campaigns?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			);
		}

		return null;
	}

	/**
	 * Check automation capability.
	 *
	 * @since 0.6093.1200
	 * @return bool True if capable, false otherwise.
	 */
	private static function check_automation_capability() {
		// MailPoet has automation.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		// Newsletter plugin supports sequences.
		if ( is_plugin_active( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_email_automation', false );
	}

	/**
	 * Check inactivity tracking.
	 *
	 * @since 0.6093.1200
	 * @return bool True if tracking, false otherwise.
	 */
	private static function check_inactivity_tracking() {
		// Professional platforms track engagement.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_tracks_subscriber_inactivity', false );
	}

	/**
	 * Check win-back content.
	 *
	 * @since 0.6093.1200
	 * @return bool True if content exists, false otherwise.
	 */
	private static function check_winback_content() {
		// Check for win-back documentation.
		$query = new \WP_Query(
			array(
				's'              => 'win back re-engage inactive subscribers',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check incentives.
	 *
	 * @since 0.6093.1200
	 * @return bool True if incentives exist, false otherwise.
	 */
	private static function check_incentives() {
		// Check for discount/offer content.
		$keywords = array( 'come back', 'we miss you', 'special offer', 'welcome back' );

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
	 * Check segmentation.
	 *
	 * @since 0.6093.1200
	 * @return bool True if segmented, false otherwise.
	 */
	private static function check_segmentation() {
		// Advanced platforms support segmentation.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_winback_segmented', false );
	}
}
