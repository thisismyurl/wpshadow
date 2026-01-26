<?php
/**
 * Diagnostic: Microsoft IIS Server Configuration
 *
 * Detects if site is running on Microsoft IIS and validates WordPress configuration.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class Diagnostic_IIS_Server_Config
 *
 * Identifies Microsoft IIS installations and verifies proper WordPress
 * configuration, including web.config and FastCGI handler.
 *
 * @since 1.2601.2148
 */
class Diagnostic_IIS_Server_Config extends Diagnostic_Base {

	/**
	 * Diagnostic slug.
	 *
	 * @var string
	 */
	protected static $slug = 'iis-server-config';

	/**
	 * Diagnostic title.
	 *
	 * @var string
	 */
	protected static $title = 'Microsoft IIS Server Configuration';

	/**
	 * Diagnostic description.
	 *
	 * @var string
	 */
	protected static $description = 'Detect if site is running on Microsoft IIS and validate WordPress configuration';

	/**
	 * Run the diagnostic check.
	 *
	 * Detects IIS server and checks for proper WordPress configuration.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if configuration issues found, null otherwise.
	 */
	public static function check() {
		// Check if IIS server
		if ( ! isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			return null;
		}

		$server_software = sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) );
		
		if ( false === stripos( $server_software, 'iis' ) && false === stripos( $server_software, 'microsoft' ) ) {
			// Not IIS server
			return null;
		}

		// IIS detected - check configuration
		$issues = array();

		// Check for web.config file
		$web_config = ABSPATH . 'web.config';
		$web_config_exists = file_exists( $web_config );

		if ( ! $web_config_exists ) {
			$issues[] = __( 'web.config file not found. IIS requires web.config for URL rewriting and proper WordPress operation.', 'wpshadow' );
		} else {
			// Check if web.config has WordPress rules
			$config_content = file_get_contents( $web_config );
			if ( false !== $config_content ) {
				if ( false === strpos( $config_content, 'index.php' ) ) {
					$issues[] = __( 'web.config exists but may not contain WordPress rewrite rules.', 'wpshadow' );
				}
			}
		}

		// Check if permalinks are working
		$permalink_structure = get_option( 'permalink_structure' );
		$permalinks_configured = ! empty( $permalink_structure );

		if ( $permalinks_configured && ! $web_config_exists ) {
			$issues[] = __( 'Pretty permalinks are enabled but web.config is missing. Permalinks may not work correctly.', 'wpshadow' );
		}

		// Check if FastCGI is likely configured (indirect check)
		$php_sapi = php_sapi_name();
		if ( false === stripos( $php_sapi, 'cgi' ) && false === stripos( $php_sapi, 'fastcgi' ) ) {
			$issues[] = sprintf(
				/* translators: %s: PHP SAPI name */
				__( 'PHP SAPI is "%s" - IIS typically uses FastCGI. Verify FastCGI handler is properly configured.', 'wpshadow' ),
				esc_html( $php_sapi )
			);
		}

		if ( empty( $issues ) ) {
			// IIS is properly configured
			return null;
		}

		// Build description from issues
		$description = sprintf(
			/* translators: %s: server software version */
			__( 'Microsoft IIS server detected (%s). WordPress requires specific IIS configuration:', 'wpshadow' ),
			esc_html( $server_software )
		) . ' ' . implode( ' ', $issues );

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => $description,
			'severity'    => 'low',
			'threat_level' => 30,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/server-iis-server-config',
			'meta'        => array(
				'server_software' => $server_software,
				'web_config_exists' => $web_config_exists,
				'permalinks_configured' => $permalinks_configured,
				'php_sapi' => $php_sapi,
				'issues' => $issues,
			),
		);
	}
}
