<?php
/**
 * Theme Coding Standards Compliance Diagnostic
 *
 * Checks active theme for basic coding standards compliance.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since 1.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Coding Standards Compliance Diagnostic
 *
 * Validates theme headers and text domain configuration.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Coding_Standards_Compliance extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-coding-standards-compliance';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Coding Standards Compliance';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks active theme for basic coding standards compliance';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'functionality';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();
		$issues = array();
		$details = array();

		if ( empty( $theme->get( 'Name' ) ) ) {
			$issues[] = __( 'Theme header missing Name field', 'wpshadow' );
		}

		if ( empty( $theme->get( 'Version' ) ) ) {
			$issues[] = __( 'Theme header missing Version field', 'wpshadow' );
		}

		if ( empty( $theme->get( 'TextDomain' ) ) ) {
			$issues[] = __( 'Theme header missing Text Domain', 'wpshadow' );
		}

		$functions_file = $theme->get_stylesheet_directory() . '/functions.php';
		if ( ! file_exists( $functions_file ) ) {
			$issues[] = __( 'Theme functions.php file missing', 'wpshadow' );
		}

		$details['theme'] = $theme->get( 'Name' );
		$details['version'] = $theme->get( 'Version' );
		$details['text_domain'] = $theme->get( 'TextDomain' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Theme coding standards issues detected', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/theme-coding-standards-compliance',
				'details'      => array(
					'issues'  => $issues,
					'info'    => $details,
				),
			);
		}

		return null;
	}
}
