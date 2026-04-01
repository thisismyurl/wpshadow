<?php
/**
 * Two-Factor Authentication Treatment
 *
 * Issue #4887: No Two-Factor Authentication Option
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if 2FA is available for user accounts.
 * 2FA prevents account compromise even when password is stolen.
 *
 * @package    WPShadow
 * @subpackage Treatments
 * @since 0.6093.1200
 */

declare(strict_types=1);

namespace WPShadow\Treatments;

use WPShadow\Core\Treatment_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Treatment_Two_Factor_Authentication Class
 *
 * @since 0.6093.1200
 */
class Treatment_Two_Factor_Authentication extends Treatment_Base {

	protected static $slug = 'two-factor-authentication';
	protected static $title = 'No Two-Factor Authentication Option';
	protected static $description = 'Checks if 2FA is available to protect accounts';
	protected static $family = 'security';

	public static function check() {
		return self::proxy_diagnostic_check( '\WPShadow\Diagnostics\Diagnostic_Two_Factor_Authentication' );
	}
}
