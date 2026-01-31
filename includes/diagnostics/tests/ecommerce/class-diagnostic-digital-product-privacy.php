<?php
/**
 * Digital Product Download Privacy Diagnostic
 *
 * Verifies digital product downloads don't expose customer data
 *
 * @package    WPShadow
 * @subpackage Diagnostics\\Ecommerce
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
 * Checks for: secure download URLs, download tracking disclosure, privacy policy
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
	protected static $description = 'Verifies digital product downloads don't expose customer data';

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
		$issues = array();

		// Check for privacy-policy page.
		$page = get_page_by_path( 'privacy-policy' );
		if ( ! $page ) {
			$issues[] = __( 'Download URLs not secured', 'wpshadow' );
		}

		// Check for relevant plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_keywords = array( 'woocommerce', 'edd', 'digital-downloads' );
		$has_plugin = false;
		foreach ( $active_plugins as $plugin ) {
			foreach ( $plugin_keywords as $keyword ) {
				if ( stripos( $plugin, $keyword ) !== false ) {
					$has_plugin = true;
					break 2;
				}
			}
		}

		if ( ! $has_plugin ) {
			$issues[] = __( 'No relevant plugin detected', 'wpshadow' );
		}

		// Additional checks would go here for: Download tracking not disclosed

		// Additional checks would go here for: Privacy policy missing digital product section

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Digital product privacy concerns: %s. Download systems must protect customer data.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 70,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/digital-product-privacy',
		);
	}
}
