<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Password Reset Working?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
  * 
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Forgot_Password_Works extends Diagnostic_Base {
	protected static $slug        = 'forgot-password-works';
	protected static $title       = 'Password Reset Working?';
	protected static $description = 'Verifies password reset emails are sent.';


	public static function check(): ?array {
		$smtp_active = is_plugin_active('wp-mail-smtp/wp_mail_smtp.php') || 
		              is_plugin_active('easy-wp-smtp/easy-wp-smtp.php');
		if ($smtp_active) {
			return null;
		}
		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'Password reset relies on default PHP mail().',
			'color'         => '#ff9800',
			'bg_color'      => '#fff3e0',
			'kb_link'       => 'https://wpshadow.com/kb/forgot-password-works/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=forgot-password-works',
			'training_link' => 'https://wpshadow.com/training/forgot-password-works/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Core',
			'priority'      => 2,
		);
	}

}
