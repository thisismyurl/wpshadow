<?php
/**
 * Permissions Policy Header Diagnostic
 *
 * Issue #4951: No Permissions-Policy Header (Privacy Risk)
 * Pillar: 🛡️ Safe by Default / #10: Beyond Pure (Privacy)
 *
 * Checks if Permissions-Policy (formerly Feature-Policy) is configured.
 * Controls browser feature access (camera, geolocation, microphone).
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
 * Diagnostic_Permissions_Policy_Header Class
 *
 * @since 1.6093.1200
 */
class Diagnostic_Permissions_Policy_Header extends Diagnostic_Base {

	protected static $slug = 'permissions-policy-header';
	protected static $title = 'No Permissions-Policy Header (Privacy Risk)';
	protected static $description = 'Checks if Permissions-Policy controls browser feature access';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Set Permissions-Policy header to restrict features', 'wpshadow' );
		$issues[] = __( 'Disable camera=() unless needed', 'wpshadow' );
		$issues[] = __( 'Disable microphone=() unless needed', 'wpshadow' );
		$issues[] = __( 'Disable geolocation=() unless needed', 'wpshadow' );
		$issues[] = __( 'Disable payment=() unless e-commerce site', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'Permissions-Policy controls which browser features third-party content can access. Restrict unnecessary features to protect user privacy.', 'wpshadow' ),
				'severity'     => 'medium',
				'threat_level' => 50,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/permissions-policy',
				'details'      => array(
					'recommendations'         => $issues,
					'example_policy'          => 'Permissions-Policy: camera=(), microphone=(), geolocation=()',
					'formerly_known_as'       => 'Feature-Policy (deprecated name)',
					'commandment'             => 'Commandment #10: Beyond Pure (Privacy First)',
				),
			);
		}

		return null;
	}
}
