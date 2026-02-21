<?php
/**
 * Heat Map & Session Recording Treatment
 *
 * Checks if heat mapping and session recording tools are configured.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since      1.6035.1030
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heat Map & Session Recording Treatment Class
 *
 * Heat maps show what users actually do vs. what you think they do. Often
 * reveals buttons are invisible or important content is ignored.
 *
 * @since 1.6035.1030
 */
class Treatment_Heat_Map_Session_Recording extends Treatment_Base {

	/**
	 * The treatment slug
	 *
	 * @var string
	 */
	protected static $slug = 'heat-map-session-recording';

	/**
	 * The treatment title
	 *
	 * @var string
	 */
	protected static $title = 'Heat Map & Session Recording';

	/**
	 * The treatment description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if heat mapping and session recording tools are active';

	/**
	 * The family this treatment belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the treatment check.
	 *
	 * @since  1.6035.1030
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Heat_Map_Session_Recording' );
	}

	/**
	 * Check if heat mapping tool is installed.
	 *
	 * @since  1.6035.1030
	 * @return bool True if tool exists.
	 */
	private static function check_heatmap_tool(): bool {
		// Check for heat mapping scripts.
		global $wp_scripts;

		if ( ! is_object( $wp_scripts ) ) {
			return false;
		}

		$heatmap_tools = array(
			'hotjar',
			'crazyegg',
			'mouseflow',
			'luckyorange',
			'smartlook',
		);

		foreach ( $heatmap_tools as $tool ) {
			if ( $wp_scripts->query( $tool ) ) {
				return true;
			}
		}

		// Check for tracking codes in options.
		$tracking_codes = array(
			'hotjar_tracking_code',
			'crazyegg_tracking_code',
			'mouseflow_tracking_code',
		);

		foreach ( $tracking_codes as $code ) {
			$value = get_option( $code, '' );
			if ( ! empty( $value ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Check if session recording is active.
	 *
	 * @since  1.6035.1030
	 * @return bool True if recording exists.
	 */
	private static function check_session_recording(): bool {
		// Session recording is often part of heat mapping tools.
		return self::check_heatmap_tool();
	}

	/**
	 * Check if click tracking exists.
	 *
	 * @since  1.6035.1030
	 * @return bool True if tracking exists.
	 */
	private static function check_click_tracking(): bool {
		// Check for Google Analytics events.
		global $wp_scripts;

		if ( ! is_object( $wp_scripts ) ) {
			return false;
		}

		// Check for GA tracking.
		if ( $wp_scripts->query( 'google-analytics' ) || $wp_scripts->query( 'gtag' ) ) {
			return true;
		}

		// Check for Google Tag Manager.
		$gtm_code = get_option( 'gtm_code', '' );
		if ( ! empty( $gtm_code ) ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if scroll depth tracking exists.
	 *
	 * @since  1.6035.1030
	 * @return bool True if tracking exists.
	 */
	private static function check_scroll_depth(): bool {
		// Scroll tracking is part of heat mapping tools.
		return self::check_heatmap_tool();
	}

	/**
	 * Check if privacy-compliant recording is configured.
	 *
	 * @since  1.6035.1030
	 * @return bool True if compliant.
	 */
	private static function check_privacy_compliance(): bool {
		// Check for privacy policy mentioning recording.
		$privacy_policy_id = get_option( 'wp_page_for_privacy_policy', 0 );
		if ( $privacy_policy_id > 0 ) {
			$policy_page = get_post( $privacy_policy_id );
			if ( $policy_page ) {
				$content = $policy_page->post_content;
				if ( false !== stripos( $content, 'recording' ) || false !== stripos( $content, 'tracking' ) ) {
					return true;
				}
			}
		}

		return false;
	}
}
