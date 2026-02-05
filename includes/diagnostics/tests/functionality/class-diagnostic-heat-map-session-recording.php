<?php
/**
 * Heat Map & Session Recording Diagnostic
 *
 * Checks if heat mapping and session recording tools are configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6035.1030
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Heat Map & Session Recording Diagnostic Class
 *
 * Heat maps show what users actually do vs. what you think they do. Often
 * reveals buttons are invisible or important content is ignored.
 *
 * @since 1.6035.1030
 */
class Diagnostic_Heat_Map_Session_Recording extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'heat-map-session-recording';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Heat Map & Session Recording';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if heat mapping and session recording tools are active';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'analytics';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.1030
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues         = array();
		$tracking_score = 0;
		$max_score      = 5;

		// Check for heat mapping tool.
		$has_heatmap = self::check_heatmap_tool();
		if ( $has_heatmap ) {
			++$tracking_score;
		} else {
			$issues[] = 'heat mapping tool';
		}

		// Check for session recording.
		$has_recording = self::check_session_recording();
		if ( $has_recording ) {
			++$tracking_score;
		} else {
			$issues[] = 'session recording';
		}

		// Check for click tracking.
		$has_click_tracking = self::check_click_tracking();
		if ( $has_click_tracking ) {
			++$tracking_score;
		} else {
			$issues[] = 'click tracking on key pages';
		}

		// Check for scroll depth analysis.
		$has_scroll_tracking = self::check_scroll_depth();
		if ( $has_scroll_tracking ) {
			++$tracking_score;
		} else {
			$issues[] = 'scroll depth analysis';
		}

		// Check for privacy-compliant recording.
		$has_privacy_compliance = self::check_privacy_compliance();
		if ( $has_privacy_compliance ) {
			++$tracking_score;
		} else {
			$issues[] = 'privacy-compliant recording (masked sensitive data)';
		}

		$completion_percentage = ( $tracking_score / $max_score ) * 100;

		if ( $completion_percentage >= 40 ) {
			return null; // Tracking tools present.
		}

		$severity     = $completion_percentage < 20 ? 'medium' : 'low';
		$threat_level = $completion_percentage < 20 ? 45 : 25;

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: completion percentage, 2: missing features */
				__( 'Visual analytics at %1$d%%. Missing: %2$s. Heat maps reveal where users click, scroll, and struggle.', 'wpshadow' ),
				(int) $completion_percentage,
				implode( ', ', $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/heat-map-session-recording',
			'meta'         => array(
				'completion_percentage' => $completion_percentage,
				'missing_features'      => $issues,
			),
		);
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
