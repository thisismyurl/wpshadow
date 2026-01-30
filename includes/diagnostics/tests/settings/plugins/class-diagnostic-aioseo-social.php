<?php
/**
 * AIOSEO Social Meta Diagnostic
 *
 * Validates Open Graph and Twitter Card settings.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1805
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AIOSEO Social Meta Class
 *
 * Checks social sharing configuration.
 *
 * @since 1.5029.1805
 */
class Diagnostic_AIOSEO_Social extends Diagnostic_Base {

	protected static $slug        = 'aioseo-social';
	protected static $title       = 'AIOSEO Social Media Settings';
	protected static $description = 'Validates social sharing setup';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_aioseo_social';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();
		$options = get_option( 'aioseo_options', array() );

		// Check Facebook Open Graph.
		$facebook_enabled = isset( $options['social']['facebook']['general']['enable'] ) 
			? $options['social']['facebook']['general']['enable'] 
			: true;

		if ( ! $facebook_enabled ) {
			$issues[] = 'Facebook Open Graph is disabled';
		} else {
			// Check default image.
			$default_image = isset( $options['social']['facebook']['general']['defaultImagePosts'] ) 
				? $options['social']['facebook']['general']['defaultImagePosts'] 
				: '';

			if ( empty( $default_image ) ) {
				$issues[] = 'No default Facebook image configured';
			}
		}

		// Check Twitter Cards.
		$twitter_enabled = isset( $options['social']['twitter']['general']['enable'] ) 
			? $options['social']['twitter']['general']['enable'] 
			: true;

		if ( ! $twitter_enabled ) {
			$issues[] = 'Twitter Cards are disabled';
		} else {
			// Check default card type.
			$card_type = isset( $options['social']['twitter']['general']['defaultCardType'] ) 
				? $options['social']['twitter']['general']['defaultCardType'] 
				: 'summary';

			if ( 'summary' !== $card_type && 'summary_large_image' !== $card_type ) {
				$issues[] = 'Invalid Twitter card type configured';
			}

			// Check default image.
			$twitter_image = isset( $options['social']['twitter']['general']['defaultImagePosts'] ) 
				? $options['social']['twitter']['general']['defaultImagePosts'] 
				: '';

			if ( empty( $twitter_image ) ) {
				$issues[] = 'No default Twitter image configured';
			}
		}

		// Check home page social meta.
		$home_og_title = get_option( '_aioseo_og_title', '' );
		if ( empty( $home_og_title ) ) {
			$issues[] = 'Homepage Open Graph title not configured';
		}

		$home_og_description = get_option( '_aioseo_og_description', '' );
		if ( empty( $home_og_description ) ) {
			$issues[] = 'Homepage Open Graph description not configured';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d social media configuration issues. Optimize for better social sharing.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-aioseo-social',
				'data'         => array(
					'social_issues' => $issues,
					'total_issues' => count( $issues ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
