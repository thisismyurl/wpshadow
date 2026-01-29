<?php
/**
 * All In One Seo Social Meta Diagnostic
 *
 * All In One Seo Social Meta configuration issues.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.701.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * All In One Seo Social Meta Diagnostic Class
 *
 * @since 1.701.0000
 */
class Diagnostic_AllInOneSeoSocialMeta extends Diagnostic_Base {

	protected static $slug = 'all-in-one-seo-social-meta';
	protected static $title = 'All In One Seo Social Meta';
	protected static $description = 'All In One Seo Social Meta configuration issues';
	protected static $family = 'functionality';

	public static function check() {
		// Check if AIOSEO is installed
		if ( ! function_exists( 'aioseo' ) && ! defined( 'AIOSEO_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$threat_level = 0;

		// Get AIOSEO social settings
		$aioseo_options = get_option( 'aioseo_options', array() );
		$social_settings = isset( $aioseo_options['social'] ) ? $aioseo_options['social'] : array();

		// Check Open Graph
		$og_enabled = isset( $social_settings['facebook']['general']['enable'] ) ? $social_settings['facebook']['general']['enable'] : false;
		if ( ! $og_enabled ) {
			$issues[] = 'open_graph_disabled';
			$threat_level += 25;
		}

		// Check Twitter Cards
		$twitter_enabled = isset( $social_settings['twitter']['general']['enable'] ) ? $social_settings['twitter']['general']['enable'] : false;
		if ( ! $twitter_enabled ) {
			$issues[] = 'twitter_cards_disabled';
			$threat_level += 20;
		}

		// Check default image
		$default_image = isset( $social_settings['facebook']['general']['defaultImageSourcePosts'] ) ? $social_settings['facebook']['general']['defaultImageSourcePosts'] : '';
		if ( empty( $default_image ) ) {
			$issues[] = 'no_default_social_image';
			$threat_level += 15;
		}

		// Check Facebook App ID
		$fb_app_id = isset( $social_settings['facebook']['general']['appId'] ) ? $social_settings['facebook']['general']['appId'] : '';
		if ( empty( $fb_app_id ) && $og_enabled ) {
			$issues[] = 'facebook_app_id_missing';
			$threat_level += 10;
		}

		// Check social profiles
		$profiles = isset( $social_settings['profiles']['sameUsername'] ) ? $social_settings['profiles']['sameUsername'] : array();
		if ( empty( $profiles['username'] ) ) {
			$issues[] = 'social_profiles_not_configured';
			$threat_level += 15;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of social meta issues */
				__( 'All-in-One SEO social meta has configuration issues: %s. This reduces social media visibility and click-through rates.', 'wpshadow' ),
				implode( ', ', array_map( function( $issue ) {
					return ucwords( str_replace( '_', ' ', $issue ) );
				}, $issues ) )
			);

			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => $description,
				'severity'    => self::calculate_severity( $threat_level ),
				'threat_level' => $threat_level,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/all-in-one-seo-social-meta',
			);
		}
		
		return null;
	}
}
