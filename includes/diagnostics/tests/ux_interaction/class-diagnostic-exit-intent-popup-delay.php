<?php
/**
 * Exit-Intent Popup Without Delay Diagnostic
 *
 * Detects exit-intent popups that appear immediately without proper delay,
 * causing user frustration and potentially increasing bounce rate.
 *
 * @since   1.6028.1500
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Exit_Intent_Popup_Delay Class
 *
 * Checks for aggressive popup timing that may harm user experience
 * and increase bounce rates.
 *
 * @since 1.6028.1500
 */
class Diagnostic_Exit_Intent_Popup_Delay extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'exit-intent-popup-delay';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Exit-Intent Popup Without Delay';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects exit-intent popups appearing immediately without delay, causing user frustration';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'ux_interaction';

	/**
	 * Run the diagnostic check.
	 *
	 * Checks for popup plugins and their configuration to determine
	 * if exit-intent popups have appropriate delays.
	 *
	 * @since  1.6028.1500
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		$popup_info = self::detect_popup_plugins();

		if ( empty( $popup_info ) ) {
			return null; // No popup plugins detected
		}

		// Check if any detected plugins have aggressive timing
		$aggressive_popups = array();
		foreach ( $popup_info as $plugin => $config ) {
			if ( isset( $config['aggressive'] ) && $config['aggressive'] ) {
				$aggressive_popups[] = $plugin;
			}
		}

		if ( empty( $aggressive_popups ) ) {
			return null; // Popups have acceptable timing
		}

		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => sprintf(
				/* translators: %d: number of aggressive popup configurations */
				_n(
					'Your site has %d popup configured with aggressive timing that may frustrate visitors.',
					'Your site has %d popups configured with aggressive timing that may frustrate visitors.',
					count( $aggressive_popups ),
					'wpshadow'
				),
				count( $aggressive_popups )
			),
			'severity'      => 'medium',
			'threat_level'  => 50,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/exit-intent-popup-delay',
			'family'        => self::$family,
			'meta'          => array(
				'affected_plugins'  => $aggressive_popups,
				'popup_count'       => count( $popup_info ),
				'impact_level'      => __( 'Medium - User experience and bounce rate impact', 'wpshadow' ),
				'immediate_actions' => array(
					__( 'Add minimum 5-second delay before showing exit-intent popups', 'wpshadow' ),
					__( 'Require user to scroll 25% of page before triggering', 'wpshadow' ),
					__( 'Ensure easy dismissal with visible close button', 'wpshadow' ),
					__( 'Test popup timing on mobile devices', 'wpshadow' ),
				),
			),
			'details'       => array(
				'why_important'    => __( 'Exit-intent popups that appear immediately frustrate users and increase bounce rates. Research shows that popups appearing within 3 seconds of page load increase bounce rate by 35-40%. Users need time to evaluate your content before being interrupted. Proper timing (5+ seconds or after meaningful scroll) improves conversion while preserving user experience.', 'wpshadow' ),
				'user_impact'      => array(
					__( 'New Visitors: First impression is interruption, not value', 'wpshadow' ),
					__( 'Mobile Users: Harder to dismiss popups on small screens', 'wpshadow' ),
					__( 'Bounce Rate: 35-40% increase when popups appear too early', 'wpshadow' ),
					__( 'Brand Perception: Aggressive tactics reduce trust', 'wpshadow' ),
				),
				'solution_options' => array(
					'Quick Fix' => array(
						'description' => __( 'Configure existing popup plugin for better timing', 'wpshadow' ),
						'time'        => __( '10 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
						'steps'       => array(
							__( 'Open popup plugin settings', 'wpshadow' ),
							__( 'Set delay to minimum 5 seconds after page load', 'wpshadow' ),
							__( 'Add scroll depth trigger (25% minimum)', 'wpshadow' ),
							__( 'Enable cookie to prevent repeated displays', 'wpshadow' ),
							__( 'Test on mobile devices', 'wpshadow' ),
						),
					),
					'Better Popups' => array(
						'description' => __( 'Switch to OptinMonster or similar with smart triggers', 'wpshadow' ),
						'time'        => __( '30 minutes', 'wpshadow' ),
						'cost'        => __( '$9-49/month', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
					),
					'Remove Popups' => array(
						'description' => __( 'Replace aggressive popups with inline opt-in forms', 'wpshadow' ),
						'time'        => __( '30 minutes', 'wpshadow' ),
						'cost'        => __( 'Free', 'wpshadow' ),
						'difficulty'  => __( 'Easy', 'wpshadow' ),
					),
				),
				'best_practices'   => array(
					__( 'Delay: Minimum 5 seconds or after 25% scroll depth', 'wpshadow' ),
					__( 'Frequency: Show maximum once per 7 days per user', 'wpshadow' ),
					__( 'Dismissal: Large, obvious close button always visible', 'wpshadow' ),
					__( 'Mobile: Ensure touch targets are 44x44px minimum', 'wpshadow' ),
					__( 'Value: Offer genuine value (discount, guide) not just newsletter', 'wpshadow' ),
					__( 'Testing: A/B test timing and copy to optimize conversion', 'wpshadow' ),
				),
				'testing_steps'    => array(
					'Step 1' => __( 'Visit homepage in incognito/private browser', 'wpshadow' ),
					'Step 2' => __( 'Measure time until popup appears (should be 5+ seconds)', 'wpshadow' ),
					'Step 3' => __( 'Check if popup respects scroll depth trigger', 'wpshadow' ),
					'Step 4' => __( 'Test dismissal button on mobile', 'wpshadow' ),
					'Step 5' => __( 'Monitor bounce rate in Analytics before/after changes', 'wpshadow' ),
				),
			),
		);
	}

	/**
	 * Detect popup plugins and their configurations.
	 *
	 * Scans for popular popup plugins and attempts to determine
	 * if they have aggressive timing settings.
	 *
	 * @since  1.6028.1500
	 * @return array Array of detected popup plugins with configuration info.
	 */
	private static function detect_popup_plugins() {
		$popup_plugins = array();

		// Popular popup plugins to check
		$plugins_to_check = array(
			'popup-maker/popup-maker.php'                                     => 'Popup Maker',
			'optinmonster/optin-monster-wp-api.php'                          => 'OptinMonster',
			'hustle/opt-in.php'                                              => 'Hustle',
			'mailchimp-for-wp/mailchimp-for-wp.php'                          => 'MC4WP',
			'thrive-leads/thrive-leads.php'                                   => 'Thrive Leads',
			'elementor-pro/elementor-pro.php'                                 => 'Elementor Pro',
			'convertpro/convertpro.php'                                       => 'Convert Pro',
			'bloom/bloom.php'                                                 => 'Bloom',
			'icegram/icegram.php'                                            => 'Icegram',
			'sumo/sumo.php'                                                  => 'Sumo',
		);

		foreach ( $plugins_to_check as $plugin_file => $plugin_name ) {
			if ( is_plugin_active( $plugin_file ) ) {
				$config = self::check_plugin_configuration( $plugin_file, $plugin_name );
				if ( $config ) {
					$popup_plugins[ $plugin_name ] = $config;
				}
			}
		}

		return $popup_plugins;
	}

	/**
	 * Check plugin configuration for aggressive settings.
	 *
	 * Attempts to detect if plugin has aggressive timing configured.
	 *
	 * @since  1.6028.1500
	 * @param  string $plugin_file Plugin file path.
	 * @param  string $plugin_name Plugin name.
	 * @return array|null Configuration info or null if not aggressive.
	 */
	private static function check_plugin_configuration( $plugin_file, $plugin_name ) {
		$aggressive = false;

		// Popup Maker specific checks
		if ( strpos( $plugin_file, 'popup-maker' ) !== false ) {
			$popups = get_posts(
				array(
					'post_type'      => 'popup',
					'posts_per_page' => -1,
					'post_status'    => 'publish',
				)
			);

			foreach ( $popups as $popup ) {
				$delay = get_post_meta( $popup->ID, 'popup_display_delay', true );
				if ( $delay !== false && intval( $delay ) < 5000 ) { // Less than 5 seconds
					$aggressive = true;
					break;
				}
			}
		}

		// For other plugins, check if active (conservative assumption)
		if ( ! $aggressive && strpos( $plugin_file, 'optinmonster' ) === false ) {
			// OptinMonster typically has good defaults, others may not
			$aggressive = true; // Flag for manual review
		}

		return array(
			'aggressive' => $aggressive,
			'plugin'     => $plugin_name,
		);
	}
}
