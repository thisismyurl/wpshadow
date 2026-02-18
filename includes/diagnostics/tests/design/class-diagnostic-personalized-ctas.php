<?php
/**
 * Personalized CTAs Diagnostic
 *
 * Tests whether the site personalizes calls-to-action based on user context to outperform generic CTAs.
 *
 * @since   1.6034.0230
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personalized CTAs Diagnostic Class
 *
 * Personalized CTAs can outperform generic CTAs by 202%. Tailoring messages
 * to user behavior, source, or stage in the journey dramatically improves conversion.
 *
 * @since 1.6034.0230
 */
class Diagnostic_Personalized_Ctas extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'personalized-ctas';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Personalized CTAs';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether the site personalizes calls-to-action based on user context to outperform generic CTAs';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'cro';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6034.0230
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();
		$personalization_score = 0;
		$max_score = 6;

		// Check for personalization plugins.
		$personalization_plugins = self::check_personalization_plugins();
		if ( $personalization_plugins ) {
			$personalization_score++;
		} else {
			$issues[] = __( 'No content personalization plugins installed', 'wpshadow' );
		}

		// Check for dynamic content.
		$dynamic_content = self::check_dynamic_content();
		if ( $dynamic_content ) {
			$personalization_score++;
		} else {
			$issues[] = __( 'No dynamic content based on user behavior', 'wpshadow' );
		}

		// Check for member-specific CTAs.
		$member_ctas = self::check_member_ctas();
		if ( $member_ctas ) {
			$personalization_score++;
		} else {
			$issues[] = __( 'CTAs not tailored for logged-in vs guest users', 'wpshadow' );
		}

		// Check for location-based personalization.
		$location_based = self::check_location_personalization();
		if ( $location_based ) {
			$personalization_score++;
		} else {
			$issues[] = __( 'No geographic personalization of offers', 'wpshadow' );
		}

		// Check for source-based messaging.
		$source_based = self::check_source_personalization();
		if ( $source_based ) {
			$personalization_score++;
		} else {
			$issues[] = __( 'CTAs not customized by traffic source', 'wpshadow' );
		}

		// Check for behavior-triggered CTAs.
		$behavior_triggers = self::check_behavior_triggers();
		if ( $behavior_triggers ) {
			$personalization_score++;
		} else {
			$issues[] = __( 'No CTAs triggered by specific user behaviors', 'wpshadow' );
		}

		// Determine severity based on personalization implementation.
		$personalization_percentage = ( $personalization_score / $max_score ) * 100;

		if ( $personalization_percentage < 30 ) {
			$severity = 'low';
			$threat_level = 30;
		} elseif ( $personalization_percentage < 60 ) {
			$severity = 'low';
			$threat_level = 20;
		} else {
			return null;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %d: CTA personalization percentage */
				__( 'CTA personalization at %d%%. ', 'wpshadow' ),
				(int) $personalization_percentage
			) . implode( '. ', $issues ) . ' ' . __( 'Personalized CTAs can outperform generic by 202%', 'wpshadow' );

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => $description,
				'severity'     => $severity,
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/personalized-ctas',
			);
		}

		return null;
	}

	/**
	 * Check for personalization plugins.
	 *
	 * @since  1.6034.0230
	 * @return bool True if plugins exist, false otherwise.
	 */
	private static function check_personalization_plugins() {
		$plugins = array(
			'if-so/if-so.php',
			'wp-optimize/wp-optimize.php',
			'nelio-content/nelio-content.php',
		);

		foreach ( $plugins as $plugin_path ) {
			if ( is_plugin_active( $plugin_path ) ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_personalization', false );
	}

	/**
	 * Check for dynamic content.
	 *
	 * @since  1.6034.0230
	 * @return bool True if dynamic content exists, false otherwise.
	 */
	private static function check_dynamic_content() {
		if ( is_plugin_active( 'if-so/if-so.php' ) ) {
			return true;
		}

		$query = new \WP_Query(
			array(
				's'              => 'dynamic content personalized',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'publish',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for member-specific CTAs.
	 *
	 * @since  1.6034.0230
	 * @return bool True if member CTAs exist, false otherwise.
	 */
	private static function check_member_ctas() {
		// Check for membership plugins that support this.
		if ( is_plugin_active( 'paid-memberships-pro/paid-memberships-pro.php' ) ||
			 is_plugin_active( 'memberpress/memberpress.php' ) ) {
			return true;
		}

		// Check for shortcodes that show different content to members.
		$pages = get_posts(
			array(
				'post_type'      => 'page',
				'posts_per_page' => 10,
				'post_status'    => 'publish',
			)
		);

		foreach ( $pages as $page ) {
			if ( has_shortcode( $page->post_content, 'if' ) ||
				 strpos( $page->post_content, 'is_user_logged_in' ) !== false ) {
				return true;
			}
		}

		return apply_filters( 'wpshadow_has_member_ctas', false );
	}

	/**
	 * Check for location personalization.
	 *
	 * @since  1.6034.0230
	 * @return bool True if location-based exists, false otherwise.
	 */
	private static function check_location_personalization() {
		// Check for geolocation plugins.
		if ( is_plugin_active( 'geoip-detect/geoip-detect.php' ) ||
			 is_plugin_active( 'cloudflare/cloudflare.php' ) ) {
			return true;
		}

		$query = new \WP_Query(
			array(
				's'              => 'geographic geolocation country',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for source personalization.
	 *
	 * @since  1.6034.0230
	 * @return bool True if source-based exists, false otherwise.
	 */
	private static function check_source_personalization() {
		// If-So plugin supports this.
		if ( is_plugin_active( 'if-so/if-so.php' ) ) {
			return true;
		}

		// Check for UTM or referrer-based content.
		$query = new \WP_Query(
			array(
				's'              => 'utm source referrer',
				'post_type'      => 'any',
				'posts_per_page' => 1,
				'post_status'    => 'any',
			)
		);

		return $query->have_posts();
	}

	/**
	 * Check for behavior triggers.
	 *
	 * @since  1.6034.0230
	 * @return bool True if behavior triggers exist, false otherwise.
	 */
	private static function check_behavior_triggers() {
		// Check for popup/modal plugins with triggers.
		if ( is_plugin_active( 'popup-maker/popup-maker.php' ) ||
			 is_plugin_active( 'elementor/elementor.php' ) ) {
			return true;
		}

		return apply_filters( 'wpshadow_has_behavior_triggers', false );
	}
}
