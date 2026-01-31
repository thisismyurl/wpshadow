<?php
/**
 * AIOSEO Pro Features Diagnostic
 *
 * Checks Pro feature utilization.
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
 * AIOSEO Pro Features Class
 *
 * Validates Pro feature usage.
 *
 * @since 1.5029.1805
 */
class Diagnostic_AIOSEO_Pro_Features extends Diagnostic_Base {

	protected static $slug        = 'aioseo-pro-features';
	protected static $title       = 'AIOSEO Pro Features Usage';
	protected static $description = 'Validates Pro feature usage';
	protected static $family      = 'plugins';

	public static function check() {
		if ( ! function_exists( 'aioseo' ) ) {
			return null;
		}

		$cache_key = 'wpshadow_aioseo_pro';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		// Check if Pro version.
		$is_pro = defined( 'AIOSEO_VERSION' ) && function_exists( 'aioseo' ) && aioseo()->pro;

		if ( ! $is_pro ) {
			// Using free version - report missing features.
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Using AIOSEO Free. Consider Pro for advanced features.', 'wpshadow' ),
				'severity'     => 'low',
				'threat_level' => 20,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-aioseo-pro',
				'data'         => array(
					'version' => 'free',
					'missing_features' => array(
						'Local SEO',
						'Video Sitemap',
						'News Sitemap',
						'Smart Schema',
						'Redirect Manager',
						'Link Assistant',
						'Image SEO',
						'Local Business Schema',
					),
				),
			);

			set_transient( $cache_key, $result, 7 * DAY_IN_SECONDS );
			return $result;
		}

		// Pro version - check license and feature usage.
		$options = get_option( 'aioseo_options', array() );
		$license_key = isset( $options['general']['licenseKey'] ) ? $options['general']['licenseKey'] : '';

		if ( empty( $license_key ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'AIOSEO Pro installed but license key not activated!', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 60,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/plugins-aioseo-pro-license',
				'data'         => array(
					'version' => 'pro',
					'issue' => 'No license key configured',
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 7 * DAY_IN_SECONDS );
		return null;
	}
}
