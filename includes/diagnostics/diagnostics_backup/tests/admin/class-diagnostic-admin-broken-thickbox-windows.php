<?php
/**
 * Admin Broken Thickbox Windows Diagnostic
 *
 * Detects missing ThickBox stylesheet when the script is enqueued. Without the
 * stylesheet, ThickBox windows render incorrectly or invisibly.
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
 * Admin Broken Thickbox Windows Diagnostic Class
 *
 * Ensures thickbox script and style are both present to avoid broken modal
 * rendering in admin pages.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Broken_Thickbox_Windows extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-broken-thickbox-windows';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Broken Thickbox Windows';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects thickbox script enqueued without its stylesheet';

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

		$script_enqueued = $wp_scripts && $wp_scripts->is_enqueued( 'thickbox' );
		$style_enqueued  = $wp_styles && $wp_styles->is_enqueued( 'thickbox' );
		$style_reg       = $wp_styles && $wp_styles->is_registered( 'thickbox' );

		if ( $script_enqueued && ( ! $style_enqueued || ! $style_reg ) ) {
			$issues = array();

			if ( ! $style_reg ) {
				$issues[] = __( 'ThickBox stylesheet is not registered.', 'wpshadow' );
			}

			if ( ! $style_enqueued ) {
				$issues[] = __( 'ThickBox script is enqueued but stylesheet is not enqueued.', 'wpshadow' );
			}

			$items_list = '';
			foreach ( $issues as $issue ) {
				$items_list .= "\n- " . esc_html( $issue );
			}

			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => sprintf(
					/* translators: 1: number of issues, 2: list */
					__( 'Detected %1$d issue(s) with ThickBox assets. Missing styles cause broken modal windows.%2$s', 'wpshadow' ),
					count( $issues ),
					$items_list
				),
				'severity'     => 'medium',
				'threat_level' => 35,
				'auto_fixable' => false,
				'kb_link'      => 'https://wpshadow.com/kb/admin-broken-thickbox-windows',
				'meta'         => array(
					'issues' => $issues,
				),
			);
		}

		return null;
	}
}
