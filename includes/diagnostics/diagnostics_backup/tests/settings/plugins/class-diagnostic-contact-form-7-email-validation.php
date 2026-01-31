<?php
declare(strict_types=1);
namespace WPShadow\Diagnostics;
use WPShadow\Core\Diagnostic_Base;
if ( ! defined( 'ABSPATH' ) ) { exit; }

class Diagnostic_ContactForm7EmailValidation extends Diagnostic_Base {
	protected static $slug = 'contact-form-7-email-validation';
	protected static $title = 'Contact Form 7 Email Validation';
	protected static $description = 'Validates plugin configuration';
	protected static $family = 'plugins';
	
	public static function check() {
		if ( ! defined( 'WPCF7_VERSION' ) ) { if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/contact-form-7-email-validation',
		);
	}
	return null; }
		$mail = get_option( 'wpcf7_mail' );
		if ( empty( $mail['recipient'] ) || false === strpos( $mail['recipient'], '@' ) ) {
			return array(
				'id' => self::$slug,
				'title' => self::$title,
				'description' => __( 'Invalid email recipient configured', 'wpshadow' ),
				'severity' => 'high',
				'threat_level' => 65,
				'auto_fixable' => false,
				'kb_link' => 'https://wpshadow.com/kb/cf7-email',
			);
		}
		
	if ( ! (get_option( "akismet_api_key" ) !== "") ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'SPAM protection enabled', 'wpshadow' );
	}

	if ( ! (! empty( get_option( "default_email" ) )) ) {
		if ( ! isset( $issues ) ) {
			$issues = array();
		}
		$issues[] = __( 'Email notifications', 'wpshadow' );
	}
	if ( isset( $issues ) && ! empty( $issues ) ) {
		return array(
			'id' => self::$slug,
			'title' => self::$title,
			'description' => sprintf(
				__( 'Found %d issues', 'wpshadow' ),
				count( $issues )
			),
			'severity' => 'medium',
			'threat_level' => 45,
			'auto_fixable' => false,
			'kb_link' => 'https://wpshadow.com/kb/contact-form-7-email-validation',
		);
	}
	return null;
	}
}
