<?php
/**
 * AIOSEO Setup Wizard Diagnostic
 *
 * Checks if AIOSEO setup wizard has been completed.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1805
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * AIOSEO Setup Wizard Class
 *
 * Validates basic AIOSEO configuration.
 *
 * @since 1.5029.1805
 */
class Diagnostic_AIOSEO_Setup extends Diagnostic_Base {

	protected static $slug        = 'aioseo-setup';
	protected static $title       = 'All in One SEO Setup';
	protected static $description = 'Validates AIOSEO configuration';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) || ! class_exists( 'AIOSEO\Plugin\AIOSEO' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_aioseo_setup';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check if setup wizard completed.
		$setup_wizard_completed = get_option( 'aioseo_options_setup_wizard_welcome', '' );
		if ( empty( $setup_wizard_completed ) ) {
			$issues[] = 'Setup wizard not completed';
		}

		// Check site title and description.
		$options = get_option( 'aioseo_options', array() );
		if ( empty( $options ) ) {
			$issues[] = 'No AIOSEO options configured';
		} else {
			if ( ! isset( $options['searchAppearance']['global']['siteTitle'] ) || empty( $options['searchAppearance']['global']['siteTitle'] ) ) {
				$issues[] = 'Site title not configured';
			}

			if ( ! isset( $options['searchAppearance']['global']['metaDescription'] ) || empty( $options['searchAppearance']['global']['metaDescription'] ) ) {
				$issues[] = 'Meta description not configured';
			}

			// Check if home page has SEO settings.
			$home_title = get_option( '_aioseo_title', '' );
			if ( empty( $home_title ) ) {
				$issues[] = 'Homepage SEO not configured';
			}
		}

		// Check webmaster tools verification.
		$webmaster_tools = isset( $options['webmasterTools'] ) ? $options['webmasterTools'] : array();
		$has_verification = ! empty( $webmaster_tools['googleVerify'] ) || ! empty( $webmaster_tools['bingVerify'] );

		if ( ! $has_verification ) {
			$issues[] = 'No webmaster tools verification configured';
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: %d: count */
					__( '%d AIOSEO setup issues. Complete configuration for better SEO.', 'wpshadow' ),
					count( $issues )
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-aioseo-setup',
				'data'         => array(
					'setup_issues' => $issues,
					'total_issues' => count( $issues ),
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
