<?php
/**
 * Buttons Links Not Distinguishable Diagnostic
 *
 * Checks if buttons and links are semantically and visually distinct.
 *
 * @since 1.6093.1200
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Buttons Links Diagnostic Class
 *
 * Validates that buttons and links use correct semantics and are visually distinct.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Buttons_Links_Not_Distinguishable extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'buttons-links-not-distinguishable';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Buttons and Links Not Distinguishable';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if buttons and links use correct semantics';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check theme templates for link/button patterns.
		$templates = array(
			get_template_directory() . '/header.php',
			get_template_directory() . '/footer.php',
			get_template_directory() . '/index.php',
			get_template_directory() . '/template-parts',
		);

		$links_as_buttons = 0;
		$buttons_as_links = 0;

		foreach ( $templates as $template ) {
			if ( is_dir( $template ) ) {
				$files = glob( $template . '/*.php' );
				if ( ! is_array( $files ) ) {
					continue;
				}
				$templates = array_merge( $templates, $files );
				continue;
			}

			if ( ! file_exists( $template ) ) {
				continue;
			}

			$content = file_get_contents( $template );

			// Check for links styled as buttons (href="#" or onclick handlers).
			if ( preg_match_all( '/<a[^>]+href=["\']#["\'][^>]*>/i', $content, $matches ) ) {
				$links_as_buttons += count( $matches[0] );
			}

			if ( preg_match_all( '/<a[^>]+onclick=/i', $content, $matches ) ) {
				$links_as_buttons += count( $matches[0] );
			}

			// Check for buttons with href (semantic misuse).
			if ( preg_match( '/<button[^>]+href=/i', $content ) ) {
				$buttons_as_links++;
			}
		}

		if ( $links_as_buttons > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of links styled as buttons */
				__( 'Found %d links (href="#" or onclick) that should be <button> elements', 'wpshadow' ),
				$links_as_buttons
			);
		}

		if ( $buttons_as_links > 0 ) {
			$issues[] = sprintf(
				/* translators: %d: number of buttons with href */
				__( 'Found %d <button> elements with href attributes (semantic error)', 'wpshadow' ),
				$buttons_as_links
			);
		}

		// Check CSS for styling issues.
		$css_files = array(
			get_template_directory() . '/style.css',
			get_template_directory() . '/assets/css/main.css',
		);

		$links_look_like_buttons = false;
		$buttons_look_like_links = false;

		foreach ( $css_files as $css_file ) {
			if ( ! file_exists( $css_file ) ) {
				continue;
			}

			$content = file_get_contents( $css_file );

			// Check if links are styled like buttons.
			if ( preg_match( '/a[^{]*\{[^}]*(?:background|border|padding)[^}]*\}/i', $content ) ) {
				$links_look_like_buttons = true;
			}

			// Check if button styling is minimal.
			if ( preg_match( '/button[^{]*\{[^}]*(?:background\s*:\s*transparent|border\s*:\s*none)/i', $content ) ) {
				$buttons_look_like_links = true;
			}
		}

		if ( $links_look_like_buttons && $buttons_look_like_links ) {
			$issues[] = __( 'Links and buttons may not be visually distinguishable to users', 'wpshadow' );
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Your buttons and links look the same—like two doors where one opens a new room (link) and the other activates a machine (button), but both have identical handles. Links navigate to different pages or sections (think "go somewhere"). Buttons perform actions like submitting forms or opening modals (think "do something"). Screen reader users rely on these distinctions to set expectations. A link should use <a href>, a button should use <button> or <input type="submit">.', 'wpshadow' ) . ' ' . implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 55,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/buttons-links-semantics',
			);
		}

		return null;
	}
}
