<?php
/**
 * HTML Detect Missing Mobile Friendly Meta Tags Diagnostic
 *
 * Detects missing mobile-friendly meta tags.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\HTML
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * HTML Detect Missing Mobile Friendly Meta Tags Diagnostic Class
 *
 * Identifies pages missing viewport and other mobile-friendly meta tags
 * that are essential for mobile responsiveness.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_Mobilefriendly_Meta_Tags extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-mobilefriendly-meta-tags';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Mobile-Friendly Meta Tags';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects missing viewport and mobile meta tags';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'mobile';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( is_admin() ) {
			return null;
		}

		$missing_meta = array();
		$has_viewport = false;

		// Check scripts for meta viewport tag.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Check for viewport meta.
					if ( preg_match( '/<meta[^>]*name=["\']?viewport["\']?[^>]*>/i', $data ) ) {
						$has_viewport = true;
						break;
					}
				}
			}
		}

		if ( ! $has_viewport ) {
			$missing_meta[] = array(
				'tag'         => 'viewport',
				'recommended' => '<meta name="viewport" content="width=device-width, initial-scale=1">',
				'issue'       => __( 'Missing viewport meta tag (required for mobile responsiveness)', 'wpshadow' ),
			);
		}

		// Check for mobile-friendly specific meta tags.
		$mobile_meta_patterns = array(
			'/apple-mobile-web-app-capable/i'   => 'apple-mobile-web-app-capable',
			'/apple-mobile-web-app-status-bar/i' => 'apple-mobile-web-app-status-bar-style',
			'/apple-mobile-web-app-title/i'     => 'apple-mobile-web-app-title',
		);

		$found_mobile_meta = array();

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					foreach ( $mobile_meta_patterns as $pattern => $name ) {
						if ( preg_match( $pattern, $data ) ) {
							$found_mobile_meta[] = $name;
						}
					}
				}
			}
		}

		// Missing mobile-specific meta tags isn't an error (viewport is the critical one).
		// But we can note if they're missing.

		if ( empty( $missing_meta ) && empty( $found_mobile_meta ) ) {
			return null;
		}

		$items_list = '';

		foreach ( $missing_meta as $item ) {
			$items_list .= sprintf(
				"\n- %s: %s",
				esc_html( $item['tag'] ),
				esc_html( $item['issue'] )
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: meta tags */
				__( 'Missing critical mobile-friendly meta tag(s). The <meta name="viewport"> tag is essential for mobile responsiveness. Without it, mobile browsers display the page at desktop width and scale it down, making it hard to read. Add this to your <head>: <meta name="viewport" content="width=device-width, initial-scale=1">%s', 'wpshadow' ),
				$items_list
			),
			'severity'     => 'high',
			'threat_level' => 30,
			'auto_fixable' => true,
			'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-mobilefriendly-meta-tags',
			'meta'         => array(
				'missing' => $missing_meta,
			),
		);
	}
}
