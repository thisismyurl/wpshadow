<?php
/**
 * Page Scroll Behavior Diagnostic
 *
 * Checks for smooth scrolling, scroll-to-top support, and avoids scroll hijacking.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\UX
 * @since      1.6035.0900
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\UX;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_HTML_Helper;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Page Scroll Behavior Diagnostic Class
 *
 * Detects confusing or broken scrolling behavior on the homepage.
 *
 * @since 1.6035.0900
 */
class Diagnostic_Page_Scroll_Behavior extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'page-scroll-behavior';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Page Scrolling Behavior Confusing or Broken';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks for smooth scrolling, scroll-to-top support, and scroll hijacking risks';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'ux-optimization';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6035.0900
	 * @return array|null Finding array or null if no issues found.
	 */
	public static function check() {
		$html = Diagnostic_HTML_Helper::fetch_homepage_html_cached( 'wpshadow_scroll_behavior_homepage', 300 );
		if ( null === $html ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'We could not analyze scrolling behavior because the homepage HTML could not be fetched.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/scroll-behavior',
			);
		}

		$issues = array();
		$flags  = self::detect_scroll_flags( $html );

		if ( ! $flags['has_smooth_scroll'] ) {
			$issues[] = __( 'Smooth scrolling not detected; jumpy anchor navigation can feel broken.', 'wpshadow' );
		}

		if ( ! $flags['has_scroll_to_top'] ) {
			$issues[] = __( 'No scroll-to-top button detected for long pages.', 'wpshadow' );
		}

		if ( $flags['has_fixed_header'] && ! $flags['has_scroll_offset'] ) {
			$issues[] = __( 'Fixed header detected without scroll offset; content may be hidden after jumps.', 'wpshadow' );
		}

		if ( $flags['has_scroll_hijack'] ) {
			$issues[] = __( 'Scroll hijacking libraries detected; forced scrolling can confuse visitors.', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = min( 60, 35 + ( count( $issues ) * 8 ) );
		$severity = $threat_level >= 50 ? 'medium' : 'low';

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: number of scroll issues */
				__( 'Found %d scrolling behavior issue(s) that can frustrate visitors.', 'wpshadow' ),
				count( $issues )
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/scroll-behavior',
			'meta'         => array(
				'issues' => $issues,
				'flags'  => $flags,
			),
		);
	}

	/**
	 * Detect scroll behavior flags from HTML.
	 *
	 * @since  1.6035.0900
	 * @param  string $html Homepage HTML.
	 * @return array Flag values.
	 */
	private static function detect_scroll_flags( string $html ): array {
		$has_smooth_scroll = (bool) preg_match( '/scroll-behavior\s*:\s*smooth/i', $html )
			|| (bool) preg_match( '/behavior\s*:\s*["\']smooth["\']/', $html );

		$has_scroll_to_top = (bool) preg_match( '/back[-_ ]?to[-_ ]?top|scroll[-_ ]?top|to[-_ ]?top/i', $html );

		$has_fixed_header = (bool) preg_match( '/position\s*:\s*(fixed|sticky)/i', $html )
			|| (bool) preg_match( '/class=["\'][^"\']*(sticky|fixed)[^"\']*/i', $html );

		$has_scroll_offset = (bool) preg_match( '/scroll-padding-top|scroll-margin-top/i', $html );

		$has_scroll_hijack = (bool) preg_match( '/fullpage\.js|scrollify|locomotive-scroll|smooth-scrollbar|lenis|skrollr|scrollmagic/i', $html );

		return array(
			'has_smooth_scroll' => $has_smooth_scroll,
			'has_scroll_to_top' => $has_scroll_to_top,
			'has_fixed_header'  => $has_fixed_header,
			'has_scroll_offset' => $has_scroll_offset,
			'has_scroll_hijack' => $has_scroll_hijack,
		);
	}
}
