<?php
/**
 * Admin Outdated Button Secondary Class Usage Diagnostic
 *
 * Detects use of outdated "button-secondary" class in admin buttons.
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
 * Admin Outdated Button Secondary Class Usage Diagnostic Class
 *
 * Identifies buttons using the outdated "button-secondary" class.
 * Modern WordPress uses "button" class with no secondary modifier.
 *
 * @since 1.2601.2148
 */
class Diagnostic_Admin_Outdated_Button_Secondary_Class_Usage extends Diagnostic_Base {

	/**
	 * The diagnostic slug
	 *
	 * @var string
	 */
	protected static $slug = 'admin-outdated-button-secondary-class-usage';

	/**
	 * The diagnostic title
	 *
	 * @var string
	 */
	protected static $title = 'Outdated "button-secondary" Class Usage';

	/**
	 * The diagnostic description
	 *
	 * @var string
	 */
	protected static $description = 'Detects use of deprecated button-secondary class in admin UI';

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

		$outdated_buttons = array();

		// Check for scripts that might inject or reference button-secondary.
		global $wp_scripts;

		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( empty( $script_obj->src ) ) {
					continue;
				}

				// Check if script source mentions button-secondary patterns.
				$src = (string) $script_obj->src;

				if ( strpos( $src, 'button-secondary' ) !== false ) {
					$outdated_buttons[] = array(
						'handle' => $handle,
						'type'   => 'script',
						'source' => $src,
					);
				}
			}
		}

		// Check styles.
		global $wp_styles;

		if ( ! empty( $wp_styles ) && isset( $wp_styles->registered ) ) {
			foreach ( $wp_styles->registered as $handle => $style_obj ) {
				if ( empty( $style_obj->src ) ) {
					continue;
				}

				$src = (string) $style_obj->src;

				if ( strpos( $src, 'button-secondary' ) !== false ) {
					$outdated_buttons[] = array(
						'handle' => $handle,
						'type'   => 'style',
						'source' => $src,
					);
				}
			}
		}

		// Look for common patterns in localized data.
		if ( ! empty( $wp_scripts ) && isset( $wp_scripts->registered ) ) {
			foreach ( $wp_scripts->registered as $handle => $script_obj ) {
				if ( isset( $script_obj->extra['data'] ) ) {
					if ( strpos( (string) $script_obj->extra['data'], 'button-secondary' ) !== false ) {
						$outdated_buttons[] = array(
							'handle' => $handle,
							'type'   => 'localized_data',
							'reason' => __( 'Localized data contains "button-secondary"', 'wpshadow' ),
						);
					}
				}
			}
		}

		if ( empty( $outdated_buttons ) ) {
			return null;
		}

		$items_list = '';
		$max_items  = 5;

		foreach ( array_slice( $outdated_buttons, 0, $max_items ) as $button ) {
			$items_list .= sprintf(
				"\n- %s (%s)",
				esc_html( $button['handle'] ),
				esc_html( $button['type'] )
			);
		}

		if ( count( $outdated_buttons ) > $max_items ) {
			$items_list .= sprintf(
				/* translators: %d: count */
				__( "\n...and %d more outdated button references", 'wpshadow' ),
				count( $outdated_buttons ) - $max_items
			);
		}

		return array(
			'id'           => self::$slug,
			'title'        => self::$title,
			'description'  => sprintf(
				/* translators: 1: count, 2: list */
				__( 'Found %1$d reference(s) to deprecated "button-secondary" class. Use "button" class for secondary buttons instead. The "button-secondary" class is no longer supported in modern WordPress.%2$s', 'wpshadow' ),
				count( $outdated_buttons ),
				$items_list
			),
			'severity'     => 'low',
			'threat_level' => 25,
			'auto_fixable' => false,
			'kb_link'      => 'https://wpshadow.com/kb/admin-outdated-button-secondary-class-usage',
			'meta'         => array(
				'outdated_buttons' => $outdated_buttons,
			),
		);
	}
}
