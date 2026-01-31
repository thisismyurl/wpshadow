<?php
/**
 * Critical CSS Not Extracted Diagnostic
 *
 * Checks if critical CSS is optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2330
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Critical CSS Not Extracted Diagnostic Class
 *
 * Detects unoptimized CSS loading.
 *
 * @since 1.2601.2330
 */
class Diagnostic_Critical_CSS_Not_Extracted extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'critical-css-not-extracted';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Critical CSS Not Extracted';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if critical CSS is optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2330
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for critical CSS optimization plugins
		$critical_css_plugins = array(
			'critical-css/critical-css.php',
			'rocket-lazy-load/rocket-lazy-load.php',
		);

		$critical_active = false;
		foreach ( $critical_css_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$critical_active = true;
				break;
			}
		}

		if ( ! $critical_active ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Critical CSS is not optimized. Extract and inline critical CSS to improve initial page rendering.', 'wpshadow' ),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/critical-css-not-extracted',
			);
		}

		return null;
	}
}
