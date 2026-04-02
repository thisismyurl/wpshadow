<?php
/**
 * Admin Excessive Inline Styles Diagnostic
 *
 * Scans the captured admin page HTML and counts inline <style> blocks.
 * A high number of inline style blocks indicates plugins are injecting CSS
 * directly into the HTML rather than enqueueing stylesheets, which prevents
 * browser caching, inflates raw HTML, and contributes to render-blocking.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;
use WPShadow\Diagnostics\Helpers\Diagnostic_Admin_Page_HTML_Helper as Admin_HTML;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Admin_Excessive_Inline_Styles Class
 *
 * @since 0.6093.1200
 */
class Diagnostic_Admin_Excessive_Inline_Styles extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'admin-excessive-inline-styles';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Excessive Inline Styles in Admin';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Counts inline <style> blocks on admin pages. Inline stylesheets cannot be cached separately by the browser, increase raw HTML weight, and may cause a parser blocking penalty inconsistent with WordPress core patterns. High counts are a signal of plugins that do not properly use wp_enqueue_style().';

	/**
	 * Gauge family/category.
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Number of inline style blocks above which a low finding is raised.
	 *
	 * WordPress core adds ~2–4 inline style blocks per admin page (dashicons,
	 * block-editor colours, admin bar responsive CSS). Anything above 8 is
	 * caused by plugins injecting raw CSS.
	 *
	 * @var int
	 */
	private const THRESHOLD_LOW = 8;

	/**
	 * Number of inline style blocks above which a medium finding is raised.
	 *
	 * @var int
	 */
	private const THRESHOLD_MEDIUM = 15;

	/**
	 * Run the diagnostic check.
	 *
	 * Counts standalone inline <style> blocks in the captured admin page HTML.
	 * Inline styles injected by plugins:
	 *   - Cannot be browser-cached between page loads.
	 *   - Inflate raw HTML document size.
	 *   - May inject large amounts of generated/dynamic CSS that could be
	 *     relegated to a properly versioned and cached external stylesheet.
	 *
	 * Common offenders in the user's HTML sample were Rank Math (colour vars),
	 * LearnDash (menu icon overrides), Google Site Kit (admin bar chip CSS),
	 * and WP Engine MU plugin (aggressively inlined styles).
	 *
	 * @since  0.6093.1200
	 * @return array|null Finding array when inline styles are excessive, null when healthy.
	 */
	public static function check(): ?array {
		$html = Admin_HTML::get_html();

		if ( null === $html ) {
			return null; // HTML not yet captured; skip gracefully.
		}

		$style_count = Admin_HTML::count_inline_styles( $html );

		if ( $style_count <= self::THRESHOLD_LOW ) {
			return null;
		}

		if ( $style_count > self::THRESHOLD_MEDIUM ) {
			$severity     = 'medium';
			$threat_level = 30;
		} else {
			$severity     = 'low';
			$threat_level = 18;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %d: count of inline style blocks */
				__(
					'%d inline <style> blocks were detected on this admin page. Unlike external stylesheets, inline CSS blocks cannot be cached by the browser across page loads, which means the full CSS payload must be downloaded and parsed on every admin page visit. Plugins that inject large inline stylesheets for things like custom admin-menu icon colours, dashboard widget chrome, or welcome-panel branding are common contributors.',
					'wpshadow'
				),
				$style_count
			),
			'severity'     => $severity,
			'threat_level' => $threat_level,
			'kb_link'      => 'https://wpshadow.com/kb/admin-excessive-inline-styles?utm_source=wpshadow&utm_medium=plugin&utm_campaign=kb_diagnostics',
			'details'      => array(
				'inline_style_count'  => $style_count,
				'threshold_low'       => self::THRESHOLD_LOW,
				'threshold_medium'    => self::THRESHOLD_MEDIUM,
				'note'                => __(
					'Review active plugins for those adding large <style> blocks to admin pages. Common culprits include page builders with custom admin CSS, SEO plugins with large inline variable sheets, and LMS plugins with per-menu-item icon overrides. Request that authors use wp_add_inline_style() only for truly dynamic values and otherwise enqueue a cacheable external stylesheet.',
					'wpshadow'
				),
			),
		);
	}
}
