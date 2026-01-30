<?php
/**
 * Gravity Forms Conditional Logic Diagnostic
 *
 * Gravity Forms conditional logic too complex.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.257.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Gravity Forms Conditional Logic Diagnostic Class
 *
 * @since 1.257.0000
 */
class Diagnostic_GravityFormsConditionalLogic extends Diagnostic_Base {

	protected static $slug = 'gravity-forms-conditional-logic';
	protected static $title = 'Gravity Forms Conditional Logic';
	protected static $description = 'Gravity Forms conditional logic too complex';
	protected static $family = 'performance';

	public static function check() {
		if ( ! class_exists( 'GFForms' ) ) {
			return null;
		}

		$issues = array();
		$forms = \GFAPI::get_forms();

		// Check 1: Complex conditional logic
		foreach ( $forms as $form ) {
			if ( ! empty( $form['conditionalLogic'] ) ) {
				$rules = count( $form['conditionalLogic']['rules'] );
				if ( $rules > 10 ) {
					$issues[] = sprintf( __( 'Form %d: %d conditional rules (slow rendering)', 'wpshadow' ), $form['id'], $rules );
					break;
				}
			}
		}

		// Check 2: Nested conditions
		foreach ( $forms as $form ) {
			if ( isset( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {
					if ( ! empty( $field['conditionalLogic'] ) && isset( $field['conditionalLogic']['rules'] ) ) {
						foreach ( $field['conditionalLogic']['rules'] as $rule ) {
							if ( isset( $rule['operator'] ) && 'is' === $rule['operator'] ) {
								$issues[] = sprintf( __( 'Form %d has nested conditionals (performance)', 'wpshadow' ), $form['id'] );
								break 3;
							}
						}
					}
				}
			}
		}

		// Check 3: Conditional notification count
		$conditional_notifications = 0;
		foreach ( $forms as $form ) {
			if ( ! empty( $form['notifications'] ) ) {
				foreach ( $form['notifications'] as $notification ) {
					if ( ! empty( $notification['conditionalLogic'] ) ) {
						$conditional_notifications++;
					}
				}
			}
		}
		if ( $conditional_notifications > 20 ) {
			$issues[] = sprintf( __( '%d conditional notifications (processing delay)', 'wpshadow' ), $conditional_notifications );
		}

		// Check 4: AJAX-enabled forms with conditionals
		$ajax_conditionals = 0;
		foreach ( $forms as $form ) {
			if ( ! empty( $form['enableAnimation'] ) && ! empty( $form['conditionalLogic'] ) ) {
				$ajax_conditionals++;
			}
		}
		if ( $ajax_conditionals > 5 ) {
			$issues[] = sprintf( __( '%d AJAX forms with conditionals (load time)', 'wpshadow' ), $ajax_conditionals );
		}

		// Check 5: JavaScript dependencies
		$js_dependencies = get_option( 'gform_enable_jquery', 'yes' );
		if ( 'yes' === $js_dependencies && count( $forms ) > 10 ) {
			$issues[] = __( 'jQuery dependency with multiple forms (bloat)', 'wpshadow' );
		}

		// Check 6: Conditional logic caching
		$caching = get_option( 'gform_conditional_logic_cache', 'no' );
		if ( 'no' === $caching ) {
			$issues[] = __( 'No conditional logic caching (repeated processing)', 'wpshadow' );
		}

		if ( empty( $issues ) ) {
			return null;
		}

		$threat_level = 35;
		if ( count( $issues ) >= 4 ) {
			$threat_level = 47;
		} elseif ( count( $issues ) >= 3 ) {
			$threat_level = 41;
		}

		return array(
			'id'          => self::$slug,
			'title'       => self::$title,
			'description' => sprintf(
				__( 'Gravity Forms has %d conditional logic issues: %s', 'wpshadow' ),
				count( $issues ),
				implode( ', ', $issues )
			),
			'severity'    => self::calculate_severity( $threat_level ),
			'threat_level' => $threat_level,
			'auto_fixable' => false,
			'kb_link'     => 'https://wpshadow.com/kb/gravity-forms-conditional-logic',
		);
	}
}
