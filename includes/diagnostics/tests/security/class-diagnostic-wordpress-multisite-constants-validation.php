<?php
/**
 * WordPress Multisite Constants Validation Diagnostic
 *
 * For multisite networks, validates network constants are properly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26028.1905
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * WordPress Multisite Constants Validation Class
 *
 * Tests multisite constants.
 *
 * @since 1.26028.1905
 */
class Diagnostic_WordPress_Multisite_Constants_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'wordpress-multisite-constants-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'WordPress Multisite Constants Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'For multisite networks, validates network constants are properly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26028.1905
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Skip if not multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$constants_check = self::check_multisite_constants();
		
		if ( $constants_check['has_mismatches'] ) {
			$issues = array();
			
			if ( $constants_check['domain_mismatch'] ) {
				$issues[] = sprintf(
					/* translators: 1: defined domain, 2: actual domain */
					__( 'DOMAIN_CURRENT_SITE (%1$s) does not match actual domain (%2$s)', 'wpshadow' ),
					$constants_check['defined_domain'],
					$constants_check['actual_domain']
				);
			}

			if ( $constants_check['path_mismatch'] ) {
				$issues[] = sprintf(
					/* translators: 1: defined path, 2: actual path */
					__( 'PATH_CURRENT_SITE (%1$s) does not match actual path (%2$s)', 'wpshadow' ),
					$constants_check['defined_path'],
					$constants_check['actual_path']
				);
			}

			if ( $constants_check['subdomain_setting_mismatch'] ) {
				$issues[] = __( 'SUBDOMAIN_INSTALL setting does not match network configuration', 'wpshadow' );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $issues ),
				'severity'     => 'critical',
				'threat_level' => 80,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/wordpress-multisite-constants-validation',
				'meta'         => array(
					'domain_mismatch'            => $constants_check['domain_mismatch'],
					'path_mismatch'              => $constants_check['path_mismatch'],
					'subdomain_setting_mismatch' => $constants_check['subdomain_setting_mismatch'],
					'defined_domain'             => $constants_check['defined_domain'],
					'actual_domain'              => $constants_check['actual_domain'],
				),
			);
		}

		return null;
	}

	/**
	 * Check multisite constants.
	 *
	 * @since  1.26028.1905
	 * @return array Check results.
	 */
	private static function check_multisite_constants() {
		$check = array(
			'has_mismatches'            => false,
			'domain_mismatch'           => false,
			'path_mismatch'             => false,
			'subdomain_setting_mismatch' => false,
			'defined_domain'            => '',
			'actual_domain'             => '',
			'defined_path'              => '',
			'actual_path'               => '',
		);

		// Get actual domain and path.
		$check['actual_domain'] = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$check['actual_path'] = parse_url( get_site_url(), PHP_URL_PATH );
		if ( empty( $check['actual_path'] ) ) {
			$check['actual_path'] = '/';
		}

		// Get defined constants.
		if ( defined( 'DOMAIN_CURRENT_SITE' ) ) {
			$check['defined_domain'] = DOMAIN_CURRENT_SITE;
		}

		if ( defined( 'PATH_CURRENT_SITE' ) ) {
			$check['defined_path'] = PATH_CURRENT_SITE;
		}

		// Check for domain mismatch.
		if ( ! empty( $check['defined_domain'] ) && 
		     $check['defined_domain'] !== $check['actual_domain'] ) {
			$check['domain_mismatch'] = true;
			$check['has_mismatches'] = true;
		}

		// Check for path mismatch.
		if ( ! empty( $check['defined_path'] ) && 
		     $check['defined_path'] !== $check['actual_path'] ) {
			$check['path_mismatch'] = true;
			$check['has_mismatches'] = true;
		}

		// Check SUBDOMAIN_INSTALL setting.
		if ( defined( 'SUBDOMAIN_INSTALL' ) ) {
			global $wpdb;
			
			// Get network info.
			$network = get_network();
			
			if ( $network ) {
				// Check if network path suggests subdomain vs subdirectory.
				$is_subdomain = ( '/' === $network->path );
				
				if ( SUBDOMAIN_INSTALL !== $is_subdomain ) {
					$check['subdomain_setting_mismatch'] = true;
					$check['has_mismatches'] = true;
				}
			}
		}

		return $check;
	}
}
