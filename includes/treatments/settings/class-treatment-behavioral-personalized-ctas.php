<?php
/**
 * Treatment: Personalized CTAs
 *
 * Tests whether the site personalizes calls-to-action based on user context
 * to outperform generic CTAs by 202%.
 *
 * Issue: https://github.com/thisismyurl/wpshadow/issues/4537
 *
 * @package    WPShadow
 * @subpackage Treatments\Behavioral
 * @since      1.6034.1440
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Personalized CTAs Treatment
 *
 * Checks if CTAs adapt based on user behavior, location, referrer, or
 * previous visits. Personalized CTAs convert 202% better than generic ones.
 *
 * @since 1.6034.1440
 */
class Treatment_Behavioral_Personalized_CTAs extends Treatment_Base {

	/**
	 * Treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'personalizes-call-to-action';

	/**
	 * Treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Personalized CTAs';

	/**
	 * Treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Tests whether site personalizes CTAs based on user context';

	/**
	 * Treatment family
	 *
	 * @var string
	 */
	protected static $family = 'behavioral';

	/**
	 * Check for CTA personalization.
	 *
	 * Detects personalization engines, dynamic content plugins, and
	 * behavior-based customization.
	 *
	 * @since  1.6034.1440
	 * @return array|null Finding array if not implemented, null if present.
	 */
	public static function check() {
		// Check for personalization plugins.
		$personalization_plugins = array(
			'if-so/if-so.php'                                => 'If-So Dynamic Content',
			'thrive-ovation/thrive-ovation.php'              => 'Thrive Ovation',
			'optinmonster/optin-monster-wp-api.php'          => 'OptinMonster',
			'hustle/opt-in.php'                              => 'Hustle',
		);

		foreach ( $personalization_plugins as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				return null; // Has personalization.
			}
		}

		// Check for geolocation plugins.
		$geo_plugins = array(
			'geoip-detect/geoip-detect.php',
			'geoplugin/geoplugin.php',
		);

		foreach ( $geo_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				return null; // Can personalize by location.
			}
		}

		// Check for membership/login systems (enables personalization).
		$membership_plugins = array(
			'woocommerce/woocommerce.php',
			'paid-memberships-pro/paid-memberships-pro.php',
			'restrict-content-pro/restrict-content-pro.php',
		);

		$has_membership = false;
		foreach ( $membership_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$has_membership = true;
				break;
			}
		}

		// Check for dynamic content in theme.
		$theme      = wp_get_theme();
		$theme_root = get_theme_root();
		$theme_path = $theme_root . '/' . $theme->get_stylesheet();

		if ( file_exists( $theme_path . '/functions.php' ) ) {
			$content = file_get_contents( $theme_path . '/functions.php' ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
			
			// Check for personalization patterns.
			$patterns = array(
				'is_user_logged_in',
				'current_user_can',
				'wp_get_current_user',
				'get_user_meta',
			);

			foreach ( $patterns as $pattern ) {
				if ( strpos( $content, $pattern ) !== false ) {
					// Theme has user-aware code.
					return null;
				}
			}
		}

		// If has membership but no personalization.
		if ( $has_membership ) {
			$threat_level = 50; // Higher priority - already has user data.
		} else {
			$threat_level = 35; // Lower priority - no user system yet.
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __(
				'CTAs are not personalized. Generic CTAs work for everyone but excel for no one. Personalized CTAs based on user behavior, location, or previous visits convert 202% better. Consider dynamic content plugins or behavior-based customization.',
				'wpshadow'
			),
			'severity'     => 'low',
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/personalized-ctas',
		);
	}
}
