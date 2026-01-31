<?php
/**
 * Diagnostic: Multisite Constants Detection
 *
 * Checks if multisite constants are properly configured when running WordPress Multisite.
 * Detects misconfigurations that can break network functionality.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Configuration
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_Multisite_Constant_Detection
 *
 * Validates multisite constant configuration.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Multisite_Constant_Detection extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-constant-detection';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Constants Detection';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Checks if multisite constants are properly configured';

	/**
	 * Check multisite constants.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue detected, null otherwise.
	 */
	public static function check() {
		// Only check if multisite is enabled.
		if ( ! is_multisite() ) {
			return null; // Not applicable for single site.
		}

		$issues = array();

		// Check WP_ALLOW_MULTISITE constant.
		if ( ! defined( 'WP_ALLOW_MULTISITE' ) || ! WP_ALLOW_MULTISITE ) {
			$issues[] = __( 'WP_ALLOW_MULTISITE is not defined or disabled', 'wpshadow' );
		}

		// Check MULTISITE constant.
		if ( ! defined( 'MULTISITE' ) || ! MULTISITE ) {
			$issues[] = __( 'MULTISITE constant is not defined or disabled', 'wpshadow' );
		}

		// Check SUBDOMAIN_INSTALL constant.
		if ( ! defined( 'SUBDOMAIN_INSTALL' ) ) {
			$issues[] = __( 'SUBDOMAIN_INSTALL constant is not defined', 'wpshadow' );
		}

		// Check DOMAIN_CURRENT_SITE constant.
		if ( ! defined( 'DOMAIN_CURRENT_SITE' ) ) {
			$issues[] = __( 'DOMAIN_CURRENT_SITE constant is not defined', 'wpshadow' );
		}

		// Check PATH_CURRENT_SITE constant.
		if ( ! defined( 'PATH_CURRENT_SITE' ) ) {
			$issues[] = __( 'PATH_CURRENT_SITE constant is not defined', 'wpshadow' );
		}

		// Check SITE_ID_CURRENT_SITE constant.
		if ( ! defined( 'SITE_ID_CURRENT_SITE' ) ) {
			$issues[] = __( 'SITE_ID_CURRENT_SITE constant is not defined', 'wpshadow' );
		}

		// Check BLOG_ID_CURRENT_SITE constant.
		if ( ! defined( 'BLOG_ID_CURRENT_SITE' ) ) {
			$issues[] = __( 'BLOG_ID_CURRENT_SITE constant is not defined', 'wpshadow' );
		}

		// Verify DOMAIN_CURRENT_SITE matches actual domain.
		if ( defined( 'DOMAIN_CURRENT_SITE' ) ) {
			$current_domain = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
			$config_domain  = DOMAIN_CURRENT_SITE;

			if ( $current_domain && $current_domain !== $config_domain ) {
				$issues[] = sprintf(
					/* translators: 1: Configured domain, 2: Current domain */
					__( 'DOMAIN_CURRENT_SITE (%1$s) does not match current domain (%2$s)', 'wpshadow' ),
					$config_domain,
					$current_domain
				);
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => sprintf(
					/* translators: %d: Number of issues */
					_n(
						'%d multisite constant issue detected',
						'%d multisite constant issues detected',
						count( $issues ),
						'wpshadow'
					),
					count( $issues )
				),
				'severity'    => 'low',
				'threat_level' => 25,
				'auto_fixable' => false,
				'kb_link'     => 'https://wpshadow.com/kb/multisite_constant_detection',
				'meta'        => array(
					'issues' => $issues,
				),
			);
		}

		// Multisite constants are properly configured.
		return null;
	}
}
