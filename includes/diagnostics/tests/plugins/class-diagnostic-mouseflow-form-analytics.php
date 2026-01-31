<?php
/**
 * Mouseflow Form Analytics Diagnostic
 *
 * Mouseflow Form Analytics misconfigured.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.1379.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Mouseflow Form Analytics Diagnostic Class
 *
 * @since 1.1379.0000
 */
class Diagnostic_MouseflowFormAnalytics extends Diagnostic_Base {

	protected static $slug = 'mouseflow-form-analytics';
	protected static $title = 'Mouseflow Form Analytics';
	protected static $description = 'Mouseflow Form Analytics misconfigured';
	protected static $family = 'functionality';

	public static function check() {
		$issues = array();

		// Check 1: Form tracking enabled
		$form_tracking = get_option( 'mouseflow_form_tracking_enabled', false );
		if ( ! $form_tracking ) {
			$issues[] = 'Form tracking disabled';
		}

		// Check 2: Form field tracking configured
		$field_tracking = get_option( 'mouseflow_field_tracking', false );
		if ( ! $field_tracking ) {
			$issues[] = 'Field tracking not configured';
		}

		// Check 3: Form abandonment tracking
		$abandonment = get_option( 'mouseflow_abandonment_tracking', false );
		if ( ! $abandonment ) {
			$issues[] = 'Abandonment tracking disabled';
		}

		// Check 4: Form error tracking
		$error_tracking = get_option( 'mouseflow_form_error_tracking', false );
		if ( ! $error_tracking ) {
			$issues[] = 'Form error tracking disabled';
		}

		// Check 5: Form conversion tracking
		$conversion_tracking = get_option( 'mouseflow_form_conversion_tracking', false );
		if ( ! $conversion_tracking ) {
			$issues[] = 'Form conversion tracking disabled';
		}

		// Check 6: Sensitive data masking
		$data_masking = get_option( 'mouseflow_sensitive_data_masking', false );
		if ( ! $data_masking ) {
			$issues[] = 'Sensitive data masking disabled';
		}

		if ( ! empty( $issues ) ) {
			$threat_level = min( 65, 35 + ( count( $issues ) * 5 ) );
			return array(
				'id'          => self::$slug,
				'title'       => self::$title,
				'description' => 'Mouseflow form analytics issues: ' . implode( ', ', $issues ),
				'severity'    => $threat_level,
				'threat_level' => $threat_level,
				'auto_fixable' => true,
				'kb_link'     => 'https://wpshadow.com/kb/mouseflow-form-analytics',
			);
		}

		return null;
	}
		}
		return null;
	}
}
