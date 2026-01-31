<?php
/**
 * Third-Party Script Usage Not Monitored Diagnostic
 *
 * Checks if third-party scripts are monitored.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2335
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Third-Party Script Usage Not Monitored Diagnostic Class
 *
 * Detects unmonitored third-party scripts.
 *
 * @since 1.2601.2335
 */
class Diagnostic_Third_Party_Script_Usage_Not_Monitored extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'third-party-script-usage-not-monitored';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Third-Party Script Usage Not Monitored';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if third-party scripts are monitored';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'privacy';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2335
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! $wp_scripts ) {
			return null;
		}

		// Count external scripts
		$external_scripts = 0;
		$site_url = site_url();

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( $script->src && strpos( $script->src, 'http' ) === 0 && strpos( $script->src, $site_url ) === false ) {
				$external_scripts++;
			}
		}

		if ( $external_scripts > 10 ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d external scripts are loaded. Monitor and audit these for privacy concerns and performance impact.', 'wpshadow' ),
					$external_scripts
				),
				'severity'      => 'medium',
				'threat_level'  => 40,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/third-party-script-usage-not-monitored',
			);
		}

		return null;
	}
}
