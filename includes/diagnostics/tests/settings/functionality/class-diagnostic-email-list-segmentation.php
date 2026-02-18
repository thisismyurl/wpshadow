<?php
/**
 * Email List Segmentation Diagnostic
 *
 * Checks if email list is segmented by behavior, interest, and engagement.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1055
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Email List Segmentation Diagnostic Class
 *
 * Segmented campaigns get 3x higher click rates and generate 760% more revenue.
 * Generic emails get ignored or marked spam.
 *
 * @since 1.6035.1055
 */
class Diagnostic_Email_List_Segmentation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'email-list-segmentation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Email List Segmentation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if email list is segmented for targeted campaigns';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'engagement';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1055
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues             = array();
		$segmentation_score = 0;
		$max_score          = 5;

		// Check for behavioral segmentation.
		$has_behavioral = self::check_behavioral_segmentation();
		if ( $has_behavioral ) {
			++$segmentation_score;
		} else {
			$issues[] = 'behavior/interest segmentation';
		}

		// Check for purchase history segmentation.
		$has_purchase_history = self::check_purchase_history_segmentation();
		if ( $has_purchase_history ) {
			++$segmentation_score;
		} else {
			$issues[] = 'purchase history segmentation';
		}

		// Check for engagement level groups.
		$has_engagement = self::check_engagement_segmentation();
		if ( $has_engagement ) {
			++$segmentation_score;
		} else {
			$issues[] = 'engagement level groups (active/inactive)';
		}

		// Check for location-based segments.
		$has_location = self::check_location_segmentation();
		if ( $has_location ) {
			++$segmentation_score;
		} else {
			$issues[] = 'location-based segments';
		}

		// Check for preference center.
		$has_preference_center = self::check_preference_center();
		if ( $has_preference_center ) {
			++$segmentation_score;
		} else {
			$issues[] = 'preference center for subscribers';
		}

		$completion_percentage = ( $segmentation_score / $max_score ) * 100;

		if ( $completion_percentage >= 60 ) {
			return null; // Segmentation present.
		}

		$severity     = $completion_percentage < 40 ? 'medium' : 'low';
		$threat_level = $completion_percentage < 40 ? 50 : 30;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Email segmentation at %1$d%%. Missing: %2$s. Segmented campaigns get 3x higher click rates.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/email-list-segmentation',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
	}

	/**
	 * Check if behavioral segmentation exists.
	 *
	 * @since  1.6035.1055
	 * @return bool True if segmentation exists.
	 */
	private static function check_behavioral_segmentation(): bool {
		// Check for email marketing plugins with segmentation.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$email_plugins = array(
			'mailpoet/mailpoet.php',
			'newsletter/plugin.php',
			'mailchimp-for-wp/mailchimp-for-wp.php',
		);

		foreach ( $email_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if purchase history segmentation exists.
	 *
	 * @since  1.6035.1055
	 * @return bool True if segmentation exists.
	 */
	private static function check_purchase_history_segmentation(): bool {
		// Check for e-commerce and email integration.
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		$has_woocommerce = is_plugin_active( 'woocommerce/woocommerce.php' );
		$has_email       = self::check_behavioral_segmentation();

		return $has_woocommerce && $has_email;
	}

	/**
	 * Check if engagement segmentation exists.
	 *
	 * @since  1.6035.1055
	 * @return bool True if segmentation exists.
	 */
	private static function check_engagement_segmentation(): bool {
		// Engagement segmentation is part of email marketing platforms.
		return self::check_behavioral_segmentation();
	}

	/**
	 * Check if location segmentation exists.
	 *
	 * @since  1.6035.1055
	 * @return bool True if segmentation exists.
	 */
	private static function check_location_segmentation(): bool {
		// Location segmentation is part of advanced email platforms.
		return self::check_behavioral_segmentation();
	}

	/**
	 * Check if preference center exists.
	 *
	 * @since  1.6035.1055
	 * @return bool True if center exists.
	 */
	private static function check_preference_center(): bool {
		// Check for preference center page.
		$args = array(
			'post_type'      => 'page',
			'posts_per_page' => 1,
			's'              => 'email preferences subscription',
			'post_status'    => 'publish',
		);

		$preference_pages = get_posts( $args );
		return ! empty( $preference_pages );
	}
}
