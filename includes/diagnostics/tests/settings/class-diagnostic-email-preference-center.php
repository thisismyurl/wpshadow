<?php
/**
 * Email Preference Center Diagnostic
 *
 * Tests whether the site provides subscribers control over email frequency and content types.
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
 * Email Preference Center Diagnostic Class
 *
 * Preference centers reduce unsubscribes by 30-40% by giving users control.
 * Without one, frustrated subscribers leave permanently.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Email_Preference_Center extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-preference-center';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email Preference Center';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site provides subscribers control over email frequency and content types';

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
		$preference_score = 0;
		$max_score = 5;

		// Check for preference center page.
		$has_center = self::check_preference_center();
		if ( $has_center ) {
			$preference_score++;
		} else {
			$issues[] = __( 'No email preference center page found', 'wpshadow' );
		}

		// Check for frequency options.
		$frequency_options = self::check_frequency_options();
		if ( $frequency_options ) {
			$preference_score++;
		} else {
			$issues[] = __( 'No email frequency control (daily, weekly, monthly)', 'wpshadow' );
		}

		// Check for content type selection.
		$content_types = self::check_content_type_selection();
		if ( $content_types ) {
			$preference_score++;
		} else {
			$issues[] = __( 'No content type preferences (newsletters, promotions, updates)', 'wpshadow' );
		}

		// Check for easy access.
		$easy_access = self::check_easy_access();
		if ( $easy_access ) {
			$preference_score++;
		} else {
			$issues[] = __( 'Preference center not easily accessible from emails', 'wpshadow' );
		}

		// Check for immediate updates.
		$immediate_updates = self::check_immediate_updates();
		if ( $immediate_updates ) {
			$preference_score++;
		} else {
			$issues[] = __( 'Preference changes not applied immediately', 'wpshadow' );
		}

		// Determine severity based on preference center.
		$preference_percentage = ( $preference_score / $max_score ) * 100;

		if ( $preference_percentage < 40 ) {
			$severity = 'low';
			$threat_level = 25;
		} elseif ( $preference_percentage < 70 ) {
			$severity = 'low';
			$threat_level = 15;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Preference center implementation percentage */
				__( 'Email preference center at %d%%. ', 'wpshadow' ),
				(int) $preference_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Preference centers reduce unsubscribes by 30-40%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/email-preference-center',
			);
		}

		return null;
	}

	/**
	 * Check preference center.
	 *
	 * @since 1.6093.1200
	 * @return bool True if center exists, false otherwise.
	 */
	private static function check_preference_center() {
		// Check for preference center page.
		$query = new \WP_Query(
			array(
				's'              => 'email preferences subscription manage',
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		if ( $query->have_posts() ) {
			return true;
		}

		// MailPoet has built-in preference center.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_preference_center', false );
	}

	/**
	 * Check frequency options.
	 *
	 * @since 1.6093.1200
	 * @return bool True if options exist, false otherwise.
	 */
	private static function check_frequency_options() {
		// Advanced email platforms support frequency.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		$query = new \WP_Query(
			array(
				's'              => 'daily weekly monthly frequency',
				'post_type'      => 'page',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check content type selection.
	 *
	 * @since 1.6093.1200
	 * @return bool True if selection exists, false otherwise.
	 */
	private static function check_content_type_selection() {
		// MailPoet supports list segmentation.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_content_type_preferences', false );
	}

	/**
	 * Check easy access.
	 *
	 * @since 1.6093.1200
	 * @return bool True if accessible, false otherwise.
	 */
	private static function check_easy_access() {
		// Most email platforms include preference links in footers.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ||
			 is_plugin_active( 'newsletter/newsletter.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_preference_center_accessible', false );
	}

	/**
	 * Check immediate updates.
	 *
	 * @since 1.6093.1200
	 * @return bool True if immediate, false otherwise.
	 */
	private static function check_immediate_updates() {
		// Professional platforms apply changes immediately.
		if ( is_plugin_active( 'mailpoet/mailpoet.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_immediate_preference_updates', false );
	}
}
