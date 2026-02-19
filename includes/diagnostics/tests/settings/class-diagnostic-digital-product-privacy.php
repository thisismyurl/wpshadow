<?php
/**
 * Digital Product Download Privacy Diagnostic
 *
 * Verifies digital product downloads don't expose customer data
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Ecommerce;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
exit;
}

/**
 * Diagnostic_DigitalProductPrivacy Class
 *
 * Checks for secure download URLs, download tracking disclosure, privacy policy
 *
 * @since 1.6031.1445
 */
class Diagnostic_DigitalProductPrivacy extends Diagnostic_Base {

/**
 * The diagnostic slug
 *
 * @var string
 */
protected static $slug = 'digital-product-privacy';

/**
 * The diagnostic title
 *
 * @var string
 */
protected static $title = 'Digital Product Download Privacy';

/**
 * The diagnostic description
 *
 * @var string
 */
protected static $description = 'Verifies digital product downloads don\'t expose customer data';

/**
 * The family this diagnostic belongs to
 *
 * @var string
 */
protected static $family = 'ecommerce';

/**
 * Run the diagnostic check.
 *
 * @since  1.6031.1445
 * @return array|null Finding array if issue found, null otherwise.
 */
public static function check() {
		// Check for ecommerce plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$ecommerce_plugins = array( 'woocommerce', 'easy-digital-downloads', 'edd' );
		$has_ecommerce = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $ecommerce_plugins as $ec_plugin ) {
				if ( stripos( $plugin, $ec_plugin ) !== false ) {
					$has_ecommerce = true;
					break 2;
				}
			}
		}

		if ( ! $has_ecommerce ) {
			return null;
		}

		$issues = array();

		// Check for download file protection.
		if ( class_exists( 'WooCommerce' ) ) {
			$download_method = get_option( 'woocommerce_file_download_method', 'force' );
			if ( 'force' !== $download_method && 'xsendfile' !== $download_method ) {
				$issues[] = __( 'WooCommerce download method not using forced or X-Sendfile protection', 'wpshadow' );
			}
		}

		// Check for license management plugins.
		$license_plugins = array( 'license-manager', 'software-license', 'edd-software-licensing' );
		$has_licensing = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $license_plugins as $lic_plugin ) {
				if ( stripos( $plugin, $lic_plugin ) !== false ) {
					$has_licensing = true;
					break 2;
				}
			}
		}

		if ( ! $has_licensing ) {
			$issues[] = __( 'No license management plugin for digital products', 'wpshadow' );
		}

		// Check for download logs (privacy tracking).
		if ( class_exists( 'WooCommerce' ) ) {
			$enable_logging = get_option( 'woocommerce_downloads_require_login', 'no' );
			if ( 'no' === $enable_logging ) {
				$issues[] = __( 'Download tracking does not require login (potential privacy issue)', 'wpshadow' );
			}
		}

		// Check for hotlink protection.
		$hotlink_plugins = array( 'hotlink', 'prevent-direct-access', 'file-protection' );
		$has_hotlink_protection = false;

		foreach ( $active_plugins as $plugin ) {
			foreach ( $hotlink_plugins as $hot_plugin ) {
				if ( stripos( $plugin, $hot_plugin ) !== false ) {
					$has_hotlink_protection = true;
					break 2;
				}
			}
		}

		if ( ! $has_hotlink_protection ) {
			$issues[] = __( 'No hotlink protection for digital downloads', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Digital product privacy concerns: %s. Protect download files and track access properly.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/digital-product-privacy',
		);
	}
}
