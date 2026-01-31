<?php
/**
 * Plugin Asset Loading Performance Diagnostic
 *
 * Detects plugins loading assets inefficiently.
 *
 * @since   1.4031.1939
 * @package WPShadow\Diagnostics
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Plugin_Asset_Loading Class
 *
 * Identifies plugins loading CSS/JS on all pages unnecessarily.
 */
class Diagnostic_Plugin_Asset_Loading extends Diagnostic_Base {

	/**
	 * Diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'plugin-asset-loading';

	/**
	 * Diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Plugin Asset Loading Performance';

	/**
	 * Diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects inefficient plugin asset loading patterns';

	/**
	 * Diagnostic family
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.4031.1939
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts, $wp_styles;

		$asset_concerns = array();

		// Count scripts and styles (too many slows page load)
		$script_count = 0;
		$style_count  = 0;

		if ( isset( $wp_scripts->queue ) ) {
			$script_count = count( $wp_scripts->queue );
		}

		if ( isset( $wp_styles->queue ) ) {
			$style_count = count( $wp_styles->queue );
		}

		if ( $script_count > 30 ) {
			$asset_concerns[] = sprintf(
				/* translators: %d: script count */
				__( '%d scripts enqueued. Over 30 scripts significantly slows page load.', 'wpshadow' ),
				$script_count
			);
		}

		if ( $style_count > 20 ) {
			$asset_concerns[] = sprintf(
				/* translators: %d: style count */
				__( '%d stylesheets enqueued. Over 20 stylesheets causes HTTP overhead.', 'wpshadow' ),
				$style_count
			);
		}

		// Check for inline styles/scripts (not minified)
		$inline_styles = 0;
		$inline_scripts = 0;

		if ( isset( $wp_styles->registered ) ) {
			foreach ( (array) $wp_styles->registered as $style ) {
				if ( empty( $style->src ) ) {
					$inline_styles++;
				}
			}
		}

		if ( isset( $wp_scripts->registered ) ) {
			foreach ( (array) $wp_scripts->registered as $script ) {
				if ( empty( $script->src ) ) {
					$inline_scripts++;
				}
			}
		}

		if ( $inline_scripts > 10 ) {
			$asset_concerns[] = sprintf(
				/* translators: %d: inline script count */
				__( '%d inline scripts. These block page rendering.', 'wpshadow' ),
				$inline_scripts
			);
		}

		if ( $inline_styles > 5 ) {
			$asset_concerns[] = sprintf(
				/* translators: %d: inline style count */
				__( '%d inline stylesheets. Consider external CSS files.', 'wpshadow' ),
				$inline_styles
			);
		}

		if ( ! empty( $asset_concerns ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $asset_concerns ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'details'      => array(
					'scripts_count'   => $script_count,
					'styles_count'    => $style_count,
					'inline_scripts'  => $inline_scripts,
					'inline_styles'   => $inline_styles,
				),
				'kb_link'      => 'https://wpshadow.com/kb/plugin-asset-loading',
			);
		}

		return null;
	}
}
