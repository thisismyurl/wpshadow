<?php
/**
 * Admin Broken WordPress Media Modal Markup Diagnostic
 *
 * Detects when core WordPress media modal assets are missing or deregistered,
 * which typically results in broken media modals in the admin.
 *
 * @package    WPShadow
 * @subpackage Diagnostics\Admin
 * @since      1.2601.2148
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin Broken WordPress Media Modal Markup Diagnostic Class
 *
 * Checks that the core media modal scripts/styles are registered. If plugins
 * deregister or fail to enqueue them, the media modal markup can break.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Broken_WordPress_Media_Modal_Markup extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-broken-wordpress-media-modal-markup';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken WordPress Media Modal Markup';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects when core media modal assets are missing, leading to broken media UI';

	/**
	 * The family this diagnostic belongs to
	 *
	 * @var string
	 */
	protected static $family = 'admin';

	/**
	 * Run the diagnostic check.
	 *
	 * @since  1.2601.2148
	 * @return array|null Finding array if issue found, null otherwise.
	 */
	public static function check() {
		if ( ! is_admin() ) {
			return null;
		}

		global $wp_scripts, $wp_styles;

		$missing = array();

		$required_scripts = array( 'media-views', 'wp-mediaelement', 'media-models' );
		$required_styles  = array( 'media-views', 'wp-mediaelement' );

		foreach ( $required_scripts as $handle ) {
			if ( ! self::is_registered( $wp_scripts, $handle ) ) {
				$missing[] = array( 'type' => 'script', 'handle' => $handle );
			}
		}

		foreach ( $required_styles as $handle ) {
			if ( ! self::is_registered( $wp_styles, $handle ) ) {
				$missing[] = array( 'type' => 'style', 'handle' => $handle );
			}
		}

		if ( empty( $missing ) ) {
			return null;
		}

		$items_list = '';
		foreach ( $missing as $item ) {
			$items_list .= sprintf(
				"\n- %s: %s",
				ucfirst( $item['type'] ),
				esc_html( $item['handle'] )
			);
		}

		return array(
			'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: count, 2: list */
					__( 'Core media modal assets are missing or deregistered: %1$d item(s). This often leads to broken media modals or JavaScript errors. Ensure media-views and wp-mediaelement assets remain registered.%2$s', 'wpshadow' ),
					count( $missing ),
					$items_list
				),
			'severity'     => 'medium',
			'threat_level' => 35,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-broken-wordpress-media-modal-markup',
			'meta'         => array(
				'missing' => $missing,
			),
		);
	}

	/**
	 * Check if a handle is registered in scripts/styles.
	 *
	 * @since  1.2601.2148
	 * @param  \WP_Scripts|\WP_Styles|null $registry Scripts or styles registry.
	 * @param  string                       $handle   Handle name.
	 * @return bool Whether registered.
	 */
	private static function is_registered( $registry, string $handle ): bool {
		if ( ! $registry || ! isset( $registry->registered ) ) {
			return false;
		}

		return $registry->is_registered( $handle );
	}
}
