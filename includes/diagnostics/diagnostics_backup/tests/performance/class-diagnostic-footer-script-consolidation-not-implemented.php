<?php
/**
 * Footer Script Consolidation Not Implemented Diagnostic
 *
 * Checks if scripts are loaded in footer.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2315
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Footer Script Consolidation Not Implemented Diagnostic Class
 *
 * Detects scripts not consolidated in footer.
 *
 * @since 1.2601.2315
 */
class Diagnostic_Footer_Script_Consolidation_Not_Implemented extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'footer-script-consolidation-not-implemented';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Footer Script Consolidation Not Implemented';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if scripts are consolidated in footer';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'performance';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2315
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		global $wp_scripts;

		if ( ! $wp_scripts ) {
			return null;
		}

		// Count scripts loaded in header vs footer
		$header_scripts = 0;
		$footer_scripts = 0;

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( $script->extra && isset( $script->extra['in_footer'] ) && $script->extra['in_footer'] ) {
				$footer_scripts++;
			} else {
				$header_scripts++;
			}
		}

		if ( $header_scripts > $footer_scripts ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => sprintf(
					__( '%d scripts are loaded in header. Move them to footer to improve page rendering speed.', 'wpshadow' ),
					$header_scripts
				),
				'severity'      => 'low',
				'threat_level'  => 20,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/footer-script-consolidation-not-implemented',
			);
		}

		return null;
	}
}
