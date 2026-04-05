<?php
/**
 * Treatment: Form Rate Limiting Active
 *
 * Enables WPShadow's native comment/form rate-limiting feature by toggling the
 * `wpshadow_form_rate_limiting_enabled` option.
 *
 * The actual enforcement logic runs inside Treatment_Hooks::init() via a
 * `preprocess_comment` filter. Anonymous visitors are limited to 3 comment
 * submissions per 5-minute sliding window per IP. Logged-in users are excluded
 * from the limit. Both the limit and the window are filterable.
 *
 * Risk level: safe — option toggle only.
 *
 * @package WPShadow
 * @subpackage Treatments
 * @since 0.6093.1300
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Enables native comment/form submission rate limiting via an option toggle.
 */
class Treatment_Form_Rate_Limiting_Active extends Treatment_Base {

	/** @var string */
	protected static $slug = 'form-rate-limiting-active';

	const OPTION_KEY = 'wpshadow_form_rate_limiting_enabled';

	// =========================================================================
	// Treatment_Base contract
	// =========================================================================

	public static function get_finding_id(): string {
		return self::$slug;
	}

	public static function get_risk_level(): string {
		return 'safe';
	}

	/**
	 * Enable native comment rate limiting.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function apply(): array {
		update_option( self::OPTION_KEY, true, false );

		return [
			'success' => true,
			'message' => __(
				'Comment/form rate limiting enabled. Anonymous visitors are limited to 3 comment submissions per 5 minutes per IP. '
				. 'Logged-in users are not rate-limited. '
				. 'Thresholds are filterable via wpshadow_comment_rate_limit and wpshadow_comment_rate_window. '
				. 'This protection works alongside existing anti-spam plugins.',
				'wpshadow'
			),
		];
	}

	/**
	 * Disable native form rate limiting.
	 *
	 * @return array{success:bool, message:string}
	 */
	public static function undo(): array {
		delete_option( self::OPTION_KEY );

		return [
			'success' => true,
			'message' => __( 'Form rate limiting disabled. WPShadow will no longer throttle comment submissions. Consider installing Akismet or a CAPTCHA plugin for continued protection.', 'wpshadow' ),
		];
	}
}
