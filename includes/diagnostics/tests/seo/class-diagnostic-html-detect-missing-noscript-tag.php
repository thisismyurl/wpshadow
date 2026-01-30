<?php
/**
 * HTML Detect Missing Noscript Tag Diagnostic
 *
 * Detects pages missing noscript tag for JavaScript-dependent content.
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
 * HTML Detect Missing Noscript Tag Diagnostic Class
 *
 * Identifies pages that use JavaScript but lack <noscript> fallback
 * content, which means users without JavaScript get a broken experience.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Html_Detect_Missing_Noscript_Tag extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'html-detect-missing-noscript-tag';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Missing Noscript Fallback Content';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects pages with heavy JavaScript but no noscript fallback';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'accessibility';

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

		$script_count = 0;
		$has_noscript = false;

		// Check script count.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->queue ) ) {
			$script_count = count( $wp_scripts->queue );
		}

		// Check for noscript tag.
		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					$data = (string) $script_obj->extra['data'];

					// Look for noscript tag.
					if ( preg_match( '/<noscript[^>]*>.*?<\/noscript>/is', $data ) ) {
						$has_noscript = true;
						break;
					}
				}
			}
		}

		// If no noscript and heavy JavaScript usage.
		if ( $script_count >= 5 && ! $has_noscript ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: script count */
					__( 'This page uses %d JavaScript files but has no <noscript> fallback content. Users with JavaScript disabled or blocked will see a broken page. Provide a <noscript> message explaining that JavaScript is required, or offer a non-JS alternative.', 'wpshadow' ),
					$script_count
				),
				'severity'     => 'medium',
				'threat_level' => 30,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/html-detect-missing-noscript-tag',
				'meta'         => array(
					'script_count' => $script_count,
					'has_noscript' => false,
				),
			);
		}

		return null;
	}
}
