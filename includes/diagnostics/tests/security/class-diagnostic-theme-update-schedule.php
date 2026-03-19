<?php
/**
 * Theme Update Schedule Diagnostic
 *
 * Issue #4909: Theme Not Updated Regularly
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if theme is kept up to date.
 * Outdated themes have security vulnerabilities.
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
 * Diagnostic_Theme_Update_Schedule Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Theme_Update_Schedule extends Diagnostic_Base {

	protected static $slug = 'theme-update-schedule';
	protected static $title = 'Theme Not Updated Regularly';
	protected static $description = 'Checks if active theme has pending updates';
	protected static $family = 'security';

	public static function check() {
		$current_theme = wp_get_theme();
		$updates = get_site_transient( 'update_themes' );

		$theme_slug = $current_theme->get_stylesheet();
		$has_update = isset( $updates->response[ $theme_slug ] );

		if ( $has_update ) {
			$update_info = $updates->response[ $theme_slug ];
			$current_version = $current_theme->get( 'Version' );
			$new_version = $update_info['new_version'];

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: theme name, 2: current version, 3: new version */
					__( '%1$s has an update available: %2$s → %3$s. Theme updates often include security patches.', 'wpshadow' ),
					$current_theme->get( 'Name' ),
					$current_version,
					$new_version
				),
				'severity'     => 'high',
				'threat_level' => 70,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/theme-updates',
				'details'      => array(
					'theme_name'              => $current_theme->get( 'Name' ),
					'current_version'         => $current_version,
					'new_version'             => $new_version,
					'security_risk'           => 'Themes handle user input and display output (XSS risk)',
				),
			);
		}

		return null;
	}
}
