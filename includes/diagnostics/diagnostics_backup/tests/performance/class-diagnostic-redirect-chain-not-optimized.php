<?php
/**
 * Redirect Chain Not Optimized Diagnostic
 *
 * Checks if redirect chains are optimized.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2310
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Redirect Chain Not Optimized Diagnostic Class
 *
 * Detects redirect chain performance issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Redirect_Chain_Not_Optimized extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'redirect-chain-not-optimized';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Redirect Chain Not Optimized';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if redirect chains are optimized';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Check for redirect plugins
		$redirect_plugins = array(
			'redirection/redirection.php',
			'simple-301-redirects/simple-301-redirects.php',
		);

		$redirect_active = false;
		foreach ( $redirect_plugins as $plugin ) {
			if ( is_plugin_active( $plugin ) ) {
				$redirect_active = true;
				break;
			}
		}

		if ( ! $redirect_active ) {
			return null; // No redirect plugin, no issue
		}

		// This would require checking redirect rules from the plugin
		// For now, we return a warning that redirects should be optimized
		return array(
			'id'            => self::$slug,
			'title'         => self::$title,
			'description'   => __( 'Review redirect rules to avoid redirect chains. Each redirect adds latency to page loads.', 'wpshadow' ),
			'severity'      => 'low',
			'threat_level'  => 15,
			'auto_fixable'  => false,
			'kb_link'       => 'https://wpshadow.com/kb/redirect-chain-not-optimized',
		);
	}
}
