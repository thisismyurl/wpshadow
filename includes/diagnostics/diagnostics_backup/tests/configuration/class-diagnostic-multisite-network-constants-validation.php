<?php
/**
 * Multisite Network Constants Validation Diagnostic
 *
 * For multisite installs, validates network constants are correctly configured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.26029.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Multisite Network Constants Validation Class
 *
 * Tests multisite network constants.
 *
 * @since 1.26029.0000
 */
class Diagnostic_Multisite_Network_Constants_Validation extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'multisite-network-constants-validation';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Multisite Network Constants Validation';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'For multisite installs, validates network constants are correctly configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'configuration';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.26029.0000
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Only run on multisite.
		if ( ! is_multisite() ) {
			return null;
		}

		$multisite_check = self::check_multisite_constants();
		
		if ( $multisite_check['has_errors'] ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => implode( ' ', $multisite_check['errors'] ),
				'severity'     => 'critical',
				'threat_level' => 85,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/multisite-network-constants-validation',
				'meta'         => array(
					'subdomain_install' => $multisite_check['subdomain_install'],
					'domain_current'    => $multisite_check['domain_current'],
				),
			);
		}

		return null;
	}

	/**
	 * Check multisite constants configuration.
	 *
	 * @since  1.26029.0000
	 * @return array Check results.
	 */
	private static function check_multisite_constants() {
		$check = array(
			'has_errors'        => false,
			'errors'            => array(),
			'subdomain_install' => defined( 'SUBDOMAIN_INSTALL' ) ? SUBDOMAIN_INSTALL : null,
			'domain_current'    => defined( 'DOMAIN_CURRENT_SITE' ) ? DOMAIN_CURRENT_SITE : null,
		);

		// Check SUBDOMAIN_INSTALL.
		if ( ! defined( 'SUBDOMAIN_INSTALL' ) ) {
			$check['has_errors'] = true;
			$check['errors'][] = __( 'SUBDOMAIN_INSTALL not defined (multisite network misconfigured)', 'wpshadow' );
		}

		// Check DOMAIN_CURRENT_SITE.
		if ( ! defined( 'DOMAIN_CURRENT_SITE' ) ) {
			$check['has_errors'] = true;
			$check['errors'][] = __( 'DOMAIN_CURRENT_SITE not defined (network domain not set)', 'wpshadow' );
		} else {
			// Verify domain matches current site.
			$current_domain = wp_parse_url( home_url(), PHP_URL_HOST );
			
			if ( DOMAIN_CURRENT_SITE !== $current_domain ) {
				$check['has_errors'] = true;
				$check['errors'][] = sprintf(
					/* translators: 1: configured domain, 2: actual domain */
					__( 'DOMAIN_CURRENT_SITE (%1$s) does not match actual domain (%2$s)', 'wpshadow' ),
					DOMAIN_CURRENT_SITE,
					$current_domain
				);
			}
		}

		// Check PATH_CURRENT_SITE.
		if ( ! defined( 'PATH_CURRENT_SITE' ) ) {
			$check['has_errors'] = true;
			$check['errors'][] = __( 'PATH_CURRENT_SITE not defined (network path not set)', 'wpshadow' );
		}

		// Check SITE_ID_CURRENT_SITE.
		if ( ! defined( 'SITE_ID_CURRENT_SITE' ) ) {
			$check['has_errors'] = true;
			$check['errors'][] = __( 'SITE_ID_CURRENT_SITE not defined (network site ID missing)', 'wpshadow' );
		}

		// Check BLOG_ID_CURRENT_SITE.
		if ( ! defined( 'BLOG_ID_CURRENT_SITE' ) ) {
			$check['has_errors'] = true;
			$check['errors'][] = __( 'BLOG_ID_CURRENT_SITE not defined (main site ID missing)', 'wpshadow' );
		}

		return $check;
	}
}
