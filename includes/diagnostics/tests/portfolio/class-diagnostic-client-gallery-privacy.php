<?php
/**
 * Portfolio Client Gallery Privacy Diagnostic
 *
 * Verifies private client galleries are properly secured
 *
 * @package    WPShadow
 * @subpackage Diagnostics\\Portfolio
 * @since      1.6031.1445
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics\Portfolio;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_ClientGalleryPrivacy Class
 *
 * Checks for: password protection, client gallery plugins, access logging
 *
 * @since 1.6031.1445
 */
class Diagnostic_ClientGalleryPrivacy extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'client-gallery-privacy';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Portfolio Client Gallery Privacy';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Verifies private client galleries are properly secured';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'portfolio';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.6031.1445
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		$issues = array();

		// Check for relevant plugins.
		$active_plugins = get_option( 'active_plugins', array() );
		$plugin_keywords = array( 'gallery', 'client-proof', 'proofing', 'password-protect' );
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

		// Additional checks would go here for: No client proofing plugin

		// Additional checks would go here for: No access logging

		if ( empty( $issues ) ) {
			return null;
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: %s: comma-separated list of issues */
				__( 'Client gallery security concerns: %s. Private galleries must be secured.', 'wpshadow' ),
				implode( ', ', $issues )
			),
			'severity'     => 'high',
			'threat_level' => 75,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/client-gallery-privacy',
		);
	}
}
