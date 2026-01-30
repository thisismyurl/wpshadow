<?php
/**
 * WP Rocket Cache Lifespan Diagnostic
 *
 * WP Rocket cache lifespan too short.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.438.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WP Rocket Cache Lifespan Diagnostic Class
 *
 * @since 1.438.0000
 */
class Diagnostic_WpRocketCacheLifespan extends Diagnostic_Base {

	protected static $slug = 'wp-rocket-cache-lifespan';
	protected static $title = 'WP Rocket Cache Lifespan';
	protected static $description = 'WP Rocket cache lifespan too short';
	protected static $family = 'performance';

	public static function check() {
		if ( ! defined( 'WP_ROCKET_VERSION' ) ) {
			return null;
		}

		$issues = array();
		$options = get_option( 'wp_rocket_settings', array() );

		// Check 1: Cache lifespan
		$lifespan = isset( $options['cache_lifespan'] ) ? $options['cache_lifespan'] : 0;
		if ( $lifespan < 600 ) {
			$issues[] = sprintf( __( 'Cache lifespan %d seconds (too short)', 'wpshadow' ), $lifespan );
		}

		// Check 2: Automatic cache clearing
		$auto_clear = isset( $options['automatic_cleanup'] ) ? $options['automatic_cleanup'] : 0;
		if ( $auto_clear < 1 ) {
			$issues[] = __( 'No automatic cache cleanup (stale content)', 'wpshadow' );
		}

		// Check 3: Preload enabled
		$preload = isset( $options['manual_preload'] ) ? $options['manual_preload'] : 0;
		if ( 0 === $preload ) {
			$issues[] = __( 'Preload disabled (slow first visits)', 'wpshadow' );
		}

		// Check 4: Separate mobile cache
		$mobile_cache = isset( $options['do_caching_mobile_files'] ) ? $options['do_caching_mobile_files'] : 0;
		if ( 0 === $mobile_cache ) {
			$issues[] = __( 'No mobile cache (slow mobile experience)', 'wpshadow' );
		}

		// Check 5: User cache
		$user_cache = isset( $options['cache_logged_user'] ) ? $options['cache_logged_user'] : 0;
		if ( 1 === $user_cache ) {
			$issues[] = __( 'Logged-in users cached (dynamic content issues)', 'wpshadow' );
		}

		// Check 6: Cache size monitoring
		$cache_dir = WP_CONTENT_DIR . '/cache/wp-rocket/';
		if ( is_dir( $cache_dir ) ) {
			$size = 0;
			$files = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator( $cache_dir ),
				\RecursiveIteratorIterator::LEAVES_ONLY
			);
			foreach ( $files as $file ) {
				if ( $file->isFile() ) {
					$size += $file->getSize();
				}
			}
			$size_mb = $size / 1024 / 1024;
			if ( $size_mb > 1000 ) {
				$issues[] = sprintf( __( '%d MB cache (excessive)', 'wpshadow' ), round( $size_mb ) );
			}
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 45;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 57;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 51;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'WP Rocket has %d cache lifespan issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/wp-rocket-cache-lifespan',
		);
	}
}
