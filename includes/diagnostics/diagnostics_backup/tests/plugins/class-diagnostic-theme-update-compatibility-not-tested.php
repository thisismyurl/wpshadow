<?php
/**
 * Theme Update Compatibility Not Tested Diagnostic
 *
 * Checks if theme updates have been tested.
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
 * Theme Update Compatibility Not Tested Diagnostic Class
 *
 * Detects theme update compatibility issues.
 *
 * @since 1.2601.2310
 */
class Diagnostic_Theme_Update_Compatibility_Not_Tested extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-update-compatibility-not-tested';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Update Compatibility Not Tested';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme updates are tested';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'plugins';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2310
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get current theme
		$theme = wp_get_theme();

		// Check if theme has updates available
		$updates = get_transient( 'update_themes' );

		if ( $updates && isset( $updates->response ) ) {
			$theme_name = $theme->get( 'Name' );
			if ( isset( $updates->response[ $theme->get_stylesheet() ] ) ) {
				return array(
					'id'            => self::$slug,
					'title'         => self::$title,
					'description'   => sprintf(
						__( 'Theme "%s" has an available update. Test updates on a staging site before applying to production.', 'wpshadow' ),
						esc_html( $theme_name )
					),
					'severity'      => 'medium',
					'threat_level'  => 35,
					'auto_fixable'  => false,
					'kb_link'       => 'https://wpshadow.com/kb/theme-update-compatibility-not-tested',
				);
			}
		}

		return null;
	}
}
