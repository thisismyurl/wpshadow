<?php
/**
 * Mobile-First Indexing Diagnostic
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.5029.1730
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Diagnostic_Mobile_First_Indexing extends Diagnostic_Base {

	protected static $slug        = 'mobile-first-indexing';
	protected static $title       = 'Mobile-First Indexing Compatibility';
	protected static $description = 'Verifies mobile-first index readiness';
	protected static $family      = 'seo';

	public static function check() {
		$cache_key = 'wpshadow_mobile_first';
		$cached    = get_transient( $cache_key );

		if ( false !== $cached ) {
			return $cached;
		}

		$issues = array();

		// Check if responsive.
		if ( ! current_theme_supports( 'responsive-embeds' ) ) {
			$issues[] = 'Theme does not declare responsive support';
		}

		// Check viewport meta tag.
		$homepage = wp_remote_get( home_url(), array( 'timeout' => 10 ) );
		if ( ! is_wp_error( $homepage ) ) {
			$html = wp_remote_retrieve_body( $homepage );
			if ( ! preg_match( '/<meta[^>]+name=["\']viewport["\'][^>]*>/i', $html ) ) {
				$issues[] = 'Missing viewport meta tag';
			}
		}

		if ( ! empty( $issues ) ) {
			$result = array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Site has mobile-first indexing issues. Fix for better rankings.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/seo-mobile-first',
				'data'         => array(
					'issues' => $issues,
				),
			);

			set_transient( $cache_key, $result, 24 * HOUR_IN_SECONDS );
			return $result;
		}

		set_transient( $cache_key, null, 24 * HOUR_IN_SECONDS );
		return null;
	}
}
