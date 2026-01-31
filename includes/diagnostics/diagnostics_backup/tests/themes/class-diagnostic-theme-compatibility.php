<?php
/**
 * Theme Compatibility Diagnostic
 *
 * Checks if the active theme is compatible with WordPress and PHP versions.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Themes
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Theme Compatibility Diagnostic Class
 *
 * Validates that the current theme is compatible with the
 * WordPress version and PHP version being used.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Theme_Compatibility extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'theme-compatibility';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Theme Compatibility';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if active theme is compatible with WordPress and PHP versions';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'themes';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$theme = wp_get_theme();

		if ( ! $theme->exists() ) {
			return null;
		}

		$issues = array();

		// Check WordPress version compatibility.
		$requires_wp = $theme->get( 'RequiresWP' );
		if ( ! empty( $requires_wp ) && version_compare( get_bloginfo( 'version' ), $requires_wp, '<' ) ) {
			$issues[] = sprintf(
				/* translators: 1: Theme name, 2: Required WP version, 3: Current WP version */
				__( 'Theme "%1$s" requires WordPress %2$s or higher, but you are running WordPress %3$s.', 'wpshadow' ),
				esc_html( $theme->get( 'Name' ) ),
				esc_html( $requires_wp ),
				esc_html( get_bloginfo( 'version' ) )
			);
		}

		// Check PHP version compatibility.
		$requires_php = $theme->get( 'RequiresPHP' );
		if ( ! empty( $requires_php ) && version_compare( PHP_VERSION, $requires_php, '<' ) ) {
			$issues[] = sprintf(
				/* translators: 1: Theme name, 2: Required PHP version, 3: Current PHP version */
				__( 'Theme "%1$s" requires PHP %2$s or higher, but you are running PHP %3$s.', 'wpshadow' ),
				esc_html( $theme->get( 'Name' ) ),
				esc_html( $requires_php ),
				PHP_VERSION
			);
		}

		// Check if theme is a child theme but parent is missing.
		if ( $theme->parent() && ! $theme->parent()->exists() ) {
			$issues[] = sprintf(
				/* translators: 1: Child theme name, 2: Parent theme name */
				__( 'Child theme "%1$s" requires parent theme "%2$s" which is not installed.', 'wpshadow' ),
				esc_html( $theme->get( 'Name' ) ),
				esc_html( $theme->get( 'Template' ) )
			);
		}

		// If issues found, return finding.
		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/themes-theme-compatibility',
			);
		}

		return null; // Theme is compatible.
	}
}
