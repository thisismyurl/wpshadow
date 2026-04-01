<?php
/**
 * Diagnostic: No Skip Links
 *
 * Detects missing skip-to-content links for keyboard navigation.
 * Keyboard users forced to tab through entire header without skip links.
 *
 * @package    WPShadow
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
 * No Skip Links Diagnostic Class
 *
 * Checks for accessibility skip navigation links.
 *
 * Detection methods:
 * - Skip link presence in header
 * - Anchor target verification (#main, #content)
 * - Screen reader compatibility
 *
 * @since 0.6093.1200
 */
class Diagnostic_No_Skip_Links extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'no-skip-links';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'No Skip Links';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Keyboard users forced to tab through entire header = accessibility violation';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'readability';

	/**
	 * Run the diagnostic check.
	 *
	 * Scoring system (3 points):
	 * - 2 points: Skip link present in header
	 * - 1 point: Target anchor exists (#main or #content)
	 *
	 * @since 0.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$score     = 0;
		$max_score = 3;

		// Capture homepage header output.
		ob_start();
		do_action( 'wp_head' );
		get_header();
		$header_html = ob_get_clean();

		// Check for skip link patterns.
		$has_skip_link = (
			stripos( $header_html, 'skip-to-content' ) !== false ||
			stripos( $header_html, 'skip-link' ) !== false ||
			stripos( $header_html, 'skip to content' ) !== false ||
			stripos( $header_html, 'skip navigation' ) !== false ||
			preg_match( '/<a[^>]+href=["\']#(?:main|content|main-content)["\'][^>]*>/i', $header_html )
		);

		if ( $has_skip_link ) {
			$score += 2;
		}

		// Check for target anchor in body.
		ob_start();
		// Capture a sample page.
		$sample_post = get_posts( array( 'posts_per_page' => 1 ) );
		if ( ! empty( $sample_post ) ) {
			global $post;
			$post = $sample_post[0];
			setup_postdata( $post );
			the_content();
			wp_reset_postdata();
		}
		$body_html = ob_get_clean();

		// Check for target IDs.
		$has_main_target = (
			stripos( $body_html, 'id="main"' ) !== false ||
			stripos( $body_html, 'id="content"' ) !== false ||
			stripos( $body_html, 'id="main-content"' ) !== false
		);

		if ( $has_main_target ) {
			$score++;
		}

		// Pass if score is high.
		if ( $score >= ( $max_score * 0.67 ) ) {
			return null;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => __( 'Skip links allow keyboard users to bypass repetitive navigation and jump directly to main content. Without skip links = keyboard users tab through every header link (logo, nav menu items, search, etc.) on EVERY page. WCAG 2.1 Level A requirement: Bypass Blocks (Success Criterion 2.4.1). Why it matters: Screen reader users (navigate by keyboard), Keyboard-only users (motor disabilities, power users), Voice control users (Dragon NaturallySpeaking), Mobile keyboard users (Bluetooth keyboards). Implementation: Add skip link as first focusable element in <body>, Link to main content ID (href="#main"), Visible on focus (hidden until Tab pressed), Screen reader friendly text. Code example: <a href="#main" class="skip-link screen-reader-text">Skip to content</a> with CSS: .skip-link { position: absolute; top: -40px; left: 0; } .skip-link:focus { top: 0; }. Target anchor: <main id="main" class="site-main">. Popular themes with skip links: Twenty Twenty-One, Twenty Twenty-Two (block themes have built-in), Astra, GeneratePress, Kadence. Testing: Press Tab on your homepage (skip link should appear), Press Enter (should jump to content), Use WAVE tool (webaim.org/wave/), Check with NVDA/JAWS screen readers.', 'wpshadow' ),
			'severity'    => 'medium',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/no-skip-links?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'stats'       => array(
				'has_skip_link'   => $has_skip_link,
				'has_main_target' => $has_main_target,
			),
			'recommendation' => __( 'Add skip link as first element in header.php: <a href="#main" class="skip-link">Skip to content</a>. Add CSS to hide until focused. Ensure <main id="main"> exists in content area. Test by pressing Tab on homepage. Use modern accessibility-ready theme (Twenty Twenty-Two, Astra, Kadence).', 'wpshadow' ),
		);
	}
}
