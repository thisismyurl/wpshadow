<?php
declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Are Emails Being Delivered?
 *
 * Target Persona: Non-technical Site Owner (Mom/Dad)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 */
class Diagnostic_Email_Delivery_Working extends Diagnostic_Base {
	protected static $slug        = 'email-delivery-working';
	protected static $title       = 'Are Emails Being Delivered?';
	protected static $description = 'Tests if WordPress can send emails successfully.';


	public static function check(): ?array {
		$smtp_plugins = array(
			'wp-mail-smtp/wp_mail_smtp.php',
			'easy-wp-smtp/easy-wp-smtp.php',
			'post-smtp/postman-smtp.php',
		);
		foreach ($smtp_plugins as $plugin) {
			if (is_plugin_active($plugin)) {
				return null;
			}
		}
		return array(
			'id'            => static::$slug,
			'title'         => static::$title,
			'description'   => 'Using default PHP mail() - consider SMTP for better deliverability.',
			'color'         => '#ff9800',
			'bg_color'      => '#fff3e0',
			'kb_link'       => 'https://wpshadow.com/kb/email-delivery-working/?utm_source=wpshadow&utm_medium=dashboard&utm_campaign=email-delivery-working',
			'training_link' => 'https://wpshadow.com/training/email-delivery-working/',
			'auto_fixable'  => false,
			'threat_level'  => 60,
			'module'        => 'Core',
			'priority'      => 1,
		);
	}

}
