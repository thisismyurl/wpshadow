<?php
/**
 * Theme Support Status Diagnostic
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

class Diagnostic_Theme_Support_Status extends Diagnostic_Base {
	protected static $slug = 'theme-support-status';
	protected static $title = 'Theme Support Status';
	protected static $description = 'Checks if theme is actively maintained and supported';
	protected static $family = 'functionality';

	public static function check() {
		$theme       = wp_get_theme();
		$theme_slug  = $theme->get_stylesheet();
		$last_update = $theme->get( 'Version' );

		// Check if theme from wordpress.org.
		$response = wp_remote_get(
			"https://api.wordpress.org/themes/info/1.1/?action=theme_information&request[slug]={$theme_slug}",
			array( 'timeout' => 5 )
		);

		if ( ! is_wp_error( $response ) ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			if ( isset( $data['last_updated'] ) ) {
				$last_updated = strtotime( $data['last_updated'] );
				$months_old   = ( time() - $last_updated ) / ( 30 * 24 * 60 * 60 );

				if ( $months_old > 24 ) {
					return array(
						'id'           => self::$slug,
						'title'        => self::$title,
						'description'  => sprintf(
							__( 'Theme not updated in %.0f months - may be abandoned or unsupported', 'wpshadow' ),
							$months_old
						),
						'severity'     => 'medium',
						'threat_level' => 45,
						'auto_fixable' => false,
						'kb_link'      => 'https://wpshadow.com/kb/theme-support-status',
					);
				}
			}
		}

		return null;
	}
}
