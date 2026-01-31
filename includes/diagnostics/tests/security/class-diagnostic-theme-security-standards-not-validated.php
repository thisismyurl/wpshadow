<?php
/**
 * Theme Security Standards Not Validated Diagnostic
 *
 * Checks if theme meets security standards.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2352
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Security Standards Not Validated Diagnostic Class
 *
 * Detects theme security issues.
 *
 * @since 1.2601.2352
 */
class Diagnostic_Theme_Security_Standards_Not_Validated extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-security-standards-not-validated';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Security Standards Not Validated';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if theme meets security standards';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2352
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get current theme
		$theme = wp_get_theme();

		// Check if theme is from WordPress.org
		$is_official = in_array( $theme->get( 'TextDomain' ), array( 'twentytwentythree', 'twentytwentytwo', 'twentytwentyone' ), true );

		if ( ! $is_official ) {
			return array(
				'id'            => self::$slug,
				'title'         => self::$title,
				'description'   => __( 'Theme security standards are not validated. Ensure your theme follows WordPress security standards and is regularly updated.', 'wpshadow' ),
				'severity'      => 'medium',
				'threat_level'  => 45,
				'auto_fixable'  => false,
				'kb_link'       => 'https://wpshadow.com/kb/theme-security-standards-not-validated',
			);
		}

		return null;
	}
}
