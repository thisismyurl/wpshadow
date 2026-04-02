<?php
/**
 * Exit Intent Popups Strategic Diagnostic
 *
 * Tests whether the site uses exit-intent popups strategically to recover abandoning visitors without being annoying.
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
 * Exit Intent Popups Strategic Diagnostic Class
 *
 * Well-designed exit-intent popups recover 10-15% of abandoning visitors.
 * Poor implementation damages user experience and brand perception.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Exit_Intent_Popups_Strategic extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'exit-intent-popups-strategic';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Exit Intent Popups Strategic';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site uses exit-intent popups strategically to recover abandoning visitors';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues            = array();
		$exit_intent_score = 0;
		$max_score         = 6;

		// Check for exit-intent technology.
		$exit_tech = self::check_exit_intent_tech();
		if ( $exit_tech ) {
			++$exit_intent_score;
		} else {
			$issues[] = __( 'No exit-intent popup technology installed', 'wpshadow' );
		}

		// Check for valuable offers.
		$valuable_offers = self::check_valuable_offers();
		if ( $valuable_offers ) {
			++$exit_intent_score;
		} else {
			$issues[] = __( 'Exit popups lack compelling offers (discounts, free shipping)', 'wpshadow' );
		}

		// Check for frequency controls.
		$frequency_control = self::check_frequency_control();
		if ( $frequency_control ) {
			++$exit_intent_score;
		} else {
			$issues[] = __( 'No frequency limits (users see same popup repeatedly)', 'wpshadow' );
		}

		// Check for page targeting.
		$page_targeting = self::check_page_targeting();
		if ( $page_targeting ) {
			++$exit_intent_score;
		} else {
			$issues[] = __( 'Exit popups not targeted by page type or value', 'wpshadow' );
		}

		// Check for mobile experience.
		$mobile_friendly = self::check_mobile_friendly();
		if ( $mobile_friendly ) {
			++$exit_intent_score;
		} else {
			$issues[] = __( 'Exit popups may interfere with mobile experience', 'wpshadow' );
		}

		// Check for A/B testing.
		$ab_testing = self::check_ab_testing();
		if ( $ab_testing ) {
			++$exit_intent_score;
		} else {
			$issues[] = __( 'Exit popups not tested for effectiveness', 'wpshadow' );
		}

		// Determine severity based on exit-intent implementation.
		$exit_intent_percentage = ( $exit_intent_score / $max_score ) * 100;

		if ( $exit_intent_percentage < 30 ) {
			$severity     = 'low';
			$threat_level = 20;
		} elseif ( $exit_intent_percentage < 60 ) {
			$severity     = 'low';
			$threat_level = 10;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: Exit-intent strategy percentage */
				__( 'Exit-intent strategy at %d%%. ', 'wpshadow' ),
				(int) $exit_intent_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Strategic exit popups recover 10-15% of abandoning visitors', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/exit-intent-popups-strategic',
			);
		}

		return null;
	}

	/**
	 * Check exit-intent technology.
	 *
	 * @since 1.6093.1200
	 * @return bool True if exit-intent exists, false otherwise.
	 */
	private static function check_exit_intent_tech() {
		// Check for popup plugins with exit-intent.
		$popup_plugins = array(
			'optinmonster/optin-monster-wp-api.php',
			'popup-maker/popup-maker.php',
			'convertpro/convertpro.php',
			'thrive-leads/thrive-leads.php',
		);

		foreach ( $popup_plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_exit_intent', false );
	}

	/**
	 * Check valuable offers.
	 *
	 * @since 1.6093.1200
	 * @return bool True if offers exist, false otherwise.
	 */
	private static function check_valuable_offers() {
		$offer_keywords = array( 'discount', 'coupon', 'free shipping', 'special offer', 'save' );
		$offers_found   = 0;

		foreach ( $offer_keywords as $keyword ) {
			$query = new \WP_Query(
				array(
					's'              => $keyword,
					'post_type'      => 'any',
					'posts_per_page' => 1,
					'post_status'    => 'publish',
				)
			);
			if ( $query->have_posts() ) {
				++$offers_found;
			}
		}

		return ( $offers_found >= 2 );
	}

	/**
	 * Check frequency control.
	 *
	 * @since 1.6093.1200
	 * @return bool True if frequency control exists, false otherwise.
	 */
	private static function check_frequency_control() {
		// Most popup plugins include frequency controls.
		if ( is_plugin_active( 'optinmonster/optin-monster-wp-api.php' ) ||
			is_plugin_active( 'popup-maker/popup-maker.php' ) ) {
			return true; // Assume properly configured.
		}

		return apply_filters( 'wpshadow_popup_frequency_controlled', false );
	}

	/**
	 * Check page targeting.
	 *
	 * @since 1.6093.1200
	 * @return bool True if targeting exists, false otherwise.
	 */
	private static function check_page_targeting() {
		// Advanced popup plugins support targeting.
		if ( is_plugin_active( 'optinmonster/optin-monster-wp-api.php' ) ||
			is_plugin_active( 'convertpro/convertpro.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_popup_targeted', false );
	}

	/**
	 * Check mobile friendly.
	 *
	 * @since 1.6093.1200
	 * @return bool True if mobile optimized, false otherwise.
	 */
	private static function check_mobile_friendly() {
		// Modern popup plugins are mobile-friendly.
		if ( is_plugin_active( 'popup-maker/popup-maker.php' ) ||
			is_plugin_active( 'optinmonster/optin-monster-wp-api.php' ) ) {
			return true;
		}

		// Check if theme is responsive.
		return current_theme_supports( 'responsive-embeds' );
	}

	/**
	 * Check A/B testing.
	 *
	 * @since 1.6093.1200
	 * @return bool True if testing capability exists, false otherwise.
	 */
	private static function check_ab_testing() {
		// Check for A/B testing plugins.
		if ( is_plugin_active( 'google-analytics-for-wordpress/googleanalytics.php' ) ||
			is_plugin_active( 'optinmonster/optin-monster-wp-api.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_popup_tested', false );
	}
}
