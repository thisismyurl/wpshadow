<?php
/**
 * Social Profile Links Reviewed Diagnostic (Stub)
 *
 * TODO stub mapped to the seo gauge.
 *
 * @package WPShadow
 * @subpackage Diagnostics
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Social_Profile_Links_Reviewed Class
 *
 * TODO: Implement full test logic and remediation guidance.
 */
class Diagnostic_Social_Profile_Links extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'social-profile-links';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Social Profile Links';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks whether social media profile URLs are connected to the site via an SEO plugin to enable Knowledge Graph enrichment and social sharing metadata.';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'seo';

	/**
	 * Run the diagnostic check.
	 *
	 * TODO Test Plan:
	 * - Inspect menus, widgets, or SEO schema settings for intended social profile links.
	 *
	 * TODO Fix Plan:
	 * - Add verified social links to reinforce brand trust and discoverability.
	 * - Use WordPress hooks, filters, settings, DB fixes, PHP config, or accessible server settings.
	 * - Do not modify WordPress core files.
	 * - Ensure performance/security/success impact and align with WPShadow commandments.
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array if issue exists, null if healthy.
	 */
	public static function check() {
		// Yoast SEO stores social profile URLs in the wpseo_social option.
		$yoast_social = get_option( 'wpseo_social', array() );
		if ( is_array( $yoast_social ) && ! empty( $yoast_social ) ) {
			$social_url_keys = array( 'facebook_site', 'twitter_site', 'instagram_url', 'linkedin_url', 'youtube_url' );
			foreach ( $social_url_keys as $key ) {
				if ( ! empty( $yoast_social[ $key ] ) ) {
					return null;
				}
			}
		}

		// Rank Math stores organization social profiles.
		$rank_math_social = get_option( 'rank_math_social_options', array() );
		if ( is_array( $rank_math_social ) && ! empty( $rank_math_social ) ) {
			foreach ( $rank_math_social as $value ) {
				if ( ! empty( $value ) ) {
					return null;
				}
			}
		}

		// All in One SEO social settings.
		$aioseo_social = get_option( 'aioseo_social_networks', array() );
		if ( is_array( $aioseo_social ) ) {
			$aioseo = is_string( $aioseo_social ) ? json_decode( $aioseo_social, true ) : $aioseo_social;
			if ( is_array( $aioseo ) && ! empty( array_filter( $aioseo ) ) ) {
				return null;
			}
		}

		// If no SEO plugin is active at all, this isn't actionable without one.
		$seo_plugins = array( 'wpseo_social', 'rank_math_modules', 'aioseo_settings', 'seopress_titles' );
		$any_seo_active = false;
		foreach ( $seo_plugins as $option ) {
			if ( false !== get_option( $option, false ) ) {
				$any_seo_active = true;
				break;
			}
		}

		if ( ! $any_seo_active ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => __( 'Your SEO plugin has no social profile URLs configured. Linking your site\'s schema to verified social profiles strengthens brand signals for search engines, enables sitelinks in Google Search, and helps platforms attribute content correctly. Add your social profile URLs in your SEO plugin\'s social settings.', 'wpshadow' ),
			'severity'     => 'low',
			'threat_level' => 15,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/social-profile-links',
			'details'      => array(
				'social_profiles_found' => false,
			),
		);
	}
}
