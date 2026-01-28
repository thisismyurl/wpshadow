<?php
/**
 * UTM Link Manager
 *
 * Generates wpshadow.com links with UTM parameters while respecting privacy settings.
 *
 * @package WPShadow
 * @subpackage Core
 */

declare(strict_types=1);

namespace WPShadow\Core;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * UTM Link Manager
 *
 * Generates wpshadow.com links with appropriate UTM parameters based on user's privacy settings.
 * If user has not consented to telemetry, returns basic links without tracking parameters.
 */
class UTM_Link_Manager {

	/**
	 * Generate UTM link to wpshadow.com
	 *
	 * @param string $path       URL path (e.g., '/kb/my-article')
	 * @param string $source     UTM source (e.g., 'wp-plugin', 'dashboard')
	 * @param string $medium     UTM medium (e.g., 'link', 'button')
	 * @param string $campaign   UTM campaign (e.g., 'onboarding', 'error-fix')
	 * @return string Full URL with UTM parameters if user consented, otherwise basic URL
	 */
	public static function build_link( $path = '', $source = 'wp-plugin', $medium = 'link', $campaign = '' ) {
		$base_url = 'https://wpshadow.com' . $path;

		// Check if user has consented to telemetry
		$current_user = get_current_user_id();
		if ( ! $current_user ) {
			// No logged in user, return basic URL
			return $base_url;
		}

		// Get consent preferences
		if ( ! class_exists( 'WPShadow\\Privacy\\Consent_Preferences' ) ) {
			return $base_url;
		}

		$has_telemetry = \WPShadow\Privacy\Consent_Preferences::has_consented( $current_user, 'telemetry' );

		// If user hasn't consented to telemetry, return basic URL
		if ( ! $has_telemetry ) {
			return $base_url;
		}

		// Build UTM parameters
		$utm_params = array(
			'utm_source' => $source,
			'utm_medium' => $medium,
		);

		// Add campaign if provided
		if ( ! empty( $campaign ) ) {
			$utm_params['utm_campaign'] = $campaign;
		}

		// Add content (page/section context)
		$screen = get_current_screen();
		if ( $screen ) {
			$utm_params['utm_content'] = $screen->id;
		}

		// Append to base URL
		$separator = strpos( $base_url, '?' ) !== false ? '&' : '?';
		return $base_url . $separator . http_build_query( $utm_params );
	}

	/**
	 * Generate KB article link
	 *
	 * KB links always include UTM parameters as they track content effectiveness,
	 * not user behavior. This is considered essential analytics.
	 *
	 * @param string $slug     Article slug (without /kb/ prefix)
	 * @param string $campaign Optional campaign name
	 * @return string Full KB article URL with UTM parameters
	 */
	public static function kb_link( $slug, $campaign = 'kb-article' ) {
		$base_url = 'https://wpshadow.com/kb/' . $slug;
		
		// Build UTM parameters (always included for content tracking)
		$utm_params = array(
			'utm_source'   => 'wpshadow',
			'utm_medium'   => 'plugin',
			'utm_campaign' => $campaign,
		);
		
		// Append to base URL
		$separator = strpos( $base_url, '?' ) !== false ? '&' : '?';
		return $base_url . $separator . http_build_query( $utm_params );
	}

	/**
	 * Generate Academy training link
	 *
	 * @param string $slug     Training slug (without /academy/ prefix)
	 * @param string $campaign Optional campaign name
	 * @return string Full Academy URL with UTM parameters
	 */
	public static function academy_link( $slug, $campaign = 'training' ) {
		return self::build_link(
			'/academy/' . $slug,
			'wp-plugin',
			'training-link',
			$campaign
		);
	}

	/**
	 * Generate marketing/feature link
	 *
	 * @param string $path     URL path
	 * @param string $campaign Campaign name
	 * @return string Full URL with UTM parameters
	 */
	public static function feature_link( $path = '', $campaign = '' ) {
		return self::build_link(
			$path,
			'wp-plugin',
			'feature-link',
			$campaign
		);
	}

	/**
	 * Generate dashboard link
	 *
	 * @param string $path     URL path
	 * @param string $campaign Campaign name
	 * @return string Full URL with UTM parameters
	 */
	public static function dashboard_link( $path = '', $campaign = 'dashboard' ) {
		return self::build_link(
			$path,
			'wp-plugin',
			'dashboard-link',
			$campaign
		);
	}

	/**
	 * Generate support link
	 *
	 * @param string $path     URL path
	 * @param string $campaign Campaign name
	 * @return string Full URL with UTM parameters
	 */
	public static function support_link( $path = '', $campaign = 'support' ) {
		return self::build_link(
			$path,
			'wp-plugin',
			'support-link',
			$campaign
		);
	}
}

