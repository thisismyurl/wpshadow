<?php
/**
 * Theme Capability Checks Diagnostic
 *
 * Verifies theme code includes basic capability checks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2240
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Capability Checks Diagnostic
 *
 * Checks theme files for capability checks in admin hooks.
 *
 * @since 1.2601.2240
 */
class Diagnostic_Theme_Capability_Checks extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-capability-checks';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Capability Checks';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies theme code includes basic capability checks';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2240
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme_dir = wp_get_theme()->get_stylesheet_directory();
		$functions_file = $theme_dir . '/functions.php';

		if ( ! file_exists( $functions_file ) ) {
			return null;
		}

		$content = file_get_contents( $functions_file, false, null, 0, 50000 );
		$issues = array();

		if ( false !== strpos( $content, 'admin_init' ) || false !== strpos( $content, 'admin_post' ) ) {
			if ( false === strpos( $content, 'current_user_can' ) ) {
				$issues[] = __( 'Theme registers admin hooks without capability checks', 'wpshadow' );
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme capability checks may be missing in admin actions', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 75,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-capability-checks',
				'details'      => array(
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
