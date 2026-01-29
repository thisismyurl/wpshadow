<?php
/**
 * Social Warfare Floating Buttons Diagnostic
 *
 * Social Warfare buttons slowing page.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.434.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Social Warfare Floating Buttons Diagnostic Class
 *
 * @since 1.434.0000
 */
class Diagnostic_SocialWarfareFloatingButtons extends Diagnostic_Base {

	protected static $slug = 'social-warfare-floating-buttons';
	protected static $title = 'Social Warfare Floating Buttons';
	protected static $description = 'Social Warfare buttons slowing page';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'SWP_VERSION' ) ) {
			return null;
		}
		
		$issues = array();
		$threat_level = 0;

		// Get Social Warfare settings
		$swp_options = get_option( 'social_warfare_settings', array() );

		// Check button loading method
		$button_location = isset( $swp_options['location_post'] ) ? $swp_options['location_post'] : 'both';
		if ( $button_location === 'both' ) {
			$issues[] = 'buttons_load_twice';
			$threat_level += 15;
		}

		// Check cache method
		$cache_method = isset( $swp_options['cache_method'] ) ? $swp_options['cache_method'] : 'none';
		if ( $cache_method === 'none' ) {
			$issues[] = 'share_counts_not_cached';
			$threat_level += 20;
		}

		// Check floating buttons
		$float_location = isset( $swp_options['float_location'] ) ? $swp_options['float_location'] : 'none';
		if ( $float_location !== 'none' ) {
			$issues[] = 'floating_buttons_enabled';
			$threat_level += 15;
		}

		// Check script loading
		$script_load = isset( $swp_options['script_load'] ) ? $swp_options['script_load'] : 'header';
		if ( $script_load === 'header' ) {
			$issues[] = 'scripts_load_in_header';
			$threat_level += 10;
		}

		// Check button count
		$button_count = 0;
		$networks = array( 'facebook', 'twitter', 'pinterest', 'linkedin', 'google_plus' );
		foreach ( $networks as $network ) {
			if ( isset( $swp_options[ $network . '_shares' ] ) && $swp_options[ $network . '_shares' ] ) {
				$button_count++;
			}
		}
		if ( $button_count > 5 ) {
			$issues[] = 'excessive_button_count';
			$threat_level += 10;
		}

		if ( ! empty( $issues ) ) {
			$description = sprintf(
				/* translators: %s: list of performance issues */
				__( 'Social Warfare has performance issues: %s. This slows page loads and increases server requests.', 'wpshadow' ),
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
				'kb_link'     => 'https://wpshadow.com/kb/social-warfare-floating-buttons',
			);
		}
		
		return null;
	}
}
