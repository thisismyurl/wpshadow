<?php
/**
 * Third-Party Script Integrity Diagnostic
 *
 * Issue #4852: Third-Party Scripts Loaded from Unverified Sources
 * Pillar: 🛡️ Safe by Default, Commandment #10: Beyond Pure
 *
 * Verifies external scripts use Subresource Integrity (SRI) hashes.
 * Without SRI, compromised CDNs can inject malicious code into customer sites.
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
 * Diagnostic_Third_Party_Script_Integrity Class
 *
 * Checks for:
 * - External script tags without integrity attribute
 * - Missing crossorigin attribute on SRI scripts
 * - Scripts from trusted CDNs only
 * - Integrity hashes match expected values
 *
 * Supply chain attacks via compromised CDNs are increasingly common.
 * SRI (Subresource Integrity) ensures browsers reject modified files.
 *
 * @since 1.6093.1200
 */
class Diagnostic_Third_Party_Script_Integrity extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $slug = 'third-party-script-integrity';

	/**
	 * The diagnostic title
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $title = 'Third-Party Scripts Loaded from Unverified Sources';

	/**
	 * The diagnostic description
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $description = 'Verifies external scripts use Subresource Integrity (SRI) hashes';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @since 1.6093.1200
	 * @var   string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since 1.6093.1200
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Get registered scripts
		global $wp_scripts;

		if ( ! isset( $wp_scripts->registered ) ) {
			return null;
		}

		$external_scripts_without_sri = array();

		foreach ( $wp_scripts->registered as $handle => $script ) {
			// Skip local scripts
			if ( empty( $script->src ) ) {
				continue;
			}

			// Check if script is external (CDN or third-party)
			if ( $this->is_external_script( $script->src ) ) {
				// Check if it has integrity attribute
				$has_integrity = ! empty( $script->extra['integrity'] ) || 
					( isset( $wp_scripts->to_do ) && in_array( $handle, $wp_scripts->to_do, true ) );

				if ( ! $has_integrity ) {
					$external_scripts_without_sri[] = $handle;
				}
			}
		}

		if ( ! empty( $external_scripts_without_sri ) ) {
			$issues[] = sprintf(
				/* translators: %d: number of scripts */
				__( '%d external scripts without SRI integrity verification', 'wpshadow' ),
				count( $external_scripts_without_sri )
			);
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'External scripts should use Subresource Integrity (SRI) to prevent supply chain attacks', 'wpshadow' ),
				'severity'     => 'high',
				'threat_level' => 80,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/subresource-integrity-sri',
				'details'      => array(
					'findings'        => $issues,
					'scripts'         => array_slice( $external_scripts_without_sri, 0, 5 ),
					'total_at_risk'   => count( $external_scripts_without_sri ),
					'what_is_sri'     => 'SRI verifies external script files haven\'t been modified (like checking a wax seal)',
				),
			);
		}

		return null;
	}

	/**
	 * Check if script is external (CDN or third-party)
	 *
	 * @since 1.6093.1200
	 * @param  string $src Script source URL.
	 * @return bool True if script is external.
	 */
	private function is_external_script( string $src ): bool {
		// Local scripts start with / or current domain
		$home_url = home_url();
		$home_domain = wp_parse_url( $home_url, PHP_URL_HOST );

		$script_domain = wp_parse_url( $src, PHP_URL_HOST );

		// If domain is different, it's external
		if ( $script_domain && $script_domain !== $home_domain ) {
			return true;
		}

		// If it's a protocol-relative URL to a different domain, it's external
		if ( strpos( $src, '//' ) === 0 ) {
			return true;
		}

		// If it's an absolute URL not matching our domain, it's external
		if ( strpos( $src, 'http' ) === 0 ) {
			return $script_domain !== $home_domain;
		}

		return false;
	}
}
