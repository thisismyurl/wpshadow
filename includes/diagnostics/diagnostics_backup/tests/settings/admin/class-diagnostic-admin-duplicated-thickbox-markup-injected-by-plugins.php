<?php
/**
 * Admin Duplicated Thickbox Markup Injected By Plugins Diagnostic
 *
 * Detects multiple custom ThickBox-like scripts registered in admin, which can
 * lead to duplicated modal markup and conflicting behaviors.
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
 * Admin Duplicated Thickbox Markup Injected By Plugins Diagnostic Class
 *
 * Flags when multiple scripts with thickbox/lightbox naming are registered
 * alongside the core thickbox script, suggesting plugins may inject duplicate
 * markup or conflicting modal implementations.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Duplicated_Thickbox_Markup_Injected_By_Plugins extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-duplicated-thickbox-markup-injected-by-plugins';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Duplicated Thickbox Markup Injected By Plugins';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects multiple thickbox-like scripts that can inject duplicate modal markup';

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

		global $wp_scripts;

		if ( ! $wp_scripts || ! isset( $wp_scripts->registered ) ) {
			return null;
		}

		$thickbox_like = array();

		foreach ( $wp_scripts->registered as $handle => $script ) {
			if ( self::is_thickbox_like_handle( $handle ) && 'thickbox' !== $handle ) {
				$thickbox_like[] = array(
					'handle' => $handle,
					'src'    => isset( $script->src ) ? $script->src : '',
				);
			}
		}

		if ( count( $thickbox_like ) <= 0 ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;
		foreach ( array_slice( $thickbox_like, 0, $max_items ) as $item ) {
			$items_list .= sprintf(
				"\n- %s (%s)",
				esc_html( $item['handle'] ),
				esc_html( ! empty( $item['src'] ) ? $item['src'] : __( 'local script', 'wpshadow' ) )
			);
		}

		if ( count( $thickbox_like ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: number of additional items */
				__( "\n...and %d more scripts", 'wpshadow' ),
				count( $thickbox_like ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: count, 2: list */
					__( 'Found %1$d thickbox/lightbox-like admin scripts besides core thickbox. Multiple modal frameworks can inject duplicate markup and break keyboard focus handling.%2$s', 'wpshadow' ),
					count( $thickbox_like ),
					$items_list
				),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-duplicated-thickbox-markup-injected-by-plugins',
			'meta'         => array(
				'scripts' => $thickbox_like,
			),
		);
	}

	/**
	 * Determine if a handle suggests thickbox/lightbox behavior.
	 *
	 * @since  1.2601.2148
	 * @param  string $handle Script handle.
	 * @return bool Whether it is thickbox-like.
	 */
	private static function is_thickbox_like_handle( string $handle ): bool {
		$keywords = array( 'thickbox', 'lightbox', 'fancybox', 'magnific' );

		foreach ( $keywords as $keyword ) {
			if ( false !== strpos( $handle, $keyword ) ) {
				return true;
			}
		}

		return false;
	}
}
