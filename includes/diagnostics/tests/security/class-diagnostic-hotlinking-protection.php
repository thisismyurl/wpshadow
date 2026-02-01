<?php
/**
 * Hotlinking Protection Diagnostic
 *
 * Checks if hotlinking protection is configured. Tests referrer checks.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.2602.0100
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Hotlinking Protection Diagnostic Class
 *
 * Validates that hotlinking protection is configured to prevent bandwidth theft.
 *
 * @since 1.2602.0100
 */
class Diagnostic_Hotlinking_Protection extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'hotlinking-protection';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Hotlinking Protection';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Checks if hotlinking protection is configured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'security';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2602.0100
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		// Get server software.
		$server_software = isset( $_SERVER['SERVER_SOFTWARE'] ) ? sanitize_text_field( wp_unslash( $_SERVER['SERVER_SOFTWARE'] ) ) : '';
		$is_apache       = stripos( $server_software, 'apache' ) !== false || stripos( $server_software, 'litespeed' ) !== false;

		// If not Apache/LiteSpeed, .htaccess not relevant.
		if ( ! $is_apache ) {
			return null;
		}

		$htaccess_path = ABSPATH . '.htaccess';
		$issues        = array();
		$details       = array(
			'htaccess_path'   => $htaccess_path,
			'htaccess_exists' => file_exists( $htaccess_path ),
			'server_software' => $server_software,
		);

		// Check if .htaccess exists.
		if ( ! file_exists( $htaccess_path ) ) {
			// No .htaccess file, but WordPress might not have created it yet.
			// This is not necessarily an issue.
			return null;
		}

		// Check if .htaccess is readable.
		if ( ! is_readable( $htaccess_path ) ) {
			$issues[]                     = __( '.htaccess file exists but is not readable. Cannot verify hotlinking protection.', 'wpshadow' );
			$details['htaccess_readable'] = false;
		} else {
			$details['htaccess_readable'] = true;

			// Read .htaccess content.
			$htaccess_content = file_get_contents( $htaccess_path ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents

			if ( false === $htaccess_content ) {
				$issues[] = __( 'Failed to read .htaccess file content.', 'wpshadow' );
			} else {
				// Check for hotlinking protection rules.
				$has_referer_check = stripos( $htaccess_content, 'HTTP_REFERER' ) !== false;
				$has_rewrite_rule  = preg_match( '/RewriteRule.*\.(jpg|jpeg|gif|png|webp)/i', $htaccess_content );

				$details['has_referer_check'] = $has_referer_check;
				$details['has_rewrite_rule']  = (bool) $has_rewrite_rule;

				// If no hotlinking protection found.
				if ( ! $has_referer_check && ! $has_rewrite_rule ) {
					$issues[] = __( 'No hotlinking protection rules found in .htaccess. Your images could be embedded on other sites, consuming your bandwidth.', 'wpshadow' );
				}
			}
		}

		if ( ! empty( $issues ) ) {
			return array(
				'id'                 => self::$slug,
				'title'              => self::$title,
				'description'        => __( 'Hotlinking protection is not configured. This allows other sites to use your images directly, consuming your bandwidth and server resources.', 'wpshadow' ),
				'severity'           => 'medium',
				'threat_level'       => 50,
				'site_health_status' => 'recommended',
				'auto_fixable'       => false,
				'kb_link'            => 'https://wpshadow.com/kb/security-hotlinking-protection',
				'family'             => self::$family,
				'details'            => array(
					'issues' => $issues,
					'info'   => $details,
				),
			);
		}

		return null;
	}
}
