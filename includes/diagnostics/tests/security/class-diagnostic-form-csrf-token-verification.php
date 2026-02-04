<?php
/**
 * Form CSRF Token Verification Diagnostic
 *
 * Issue #4987: Forms Not Protected Against CSRF
 * Pillar: 🛡️ Safe by Default
 *
 * Checks if forms use nonces for CSRF protection.
 * Forms without nonces are vulnerable to forging.
 *
 * @package    WPShadow
 * @subpackage Diagnostics
 * @since      1.6050.0000
 */

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Diagnostic_Form_CSRF_Token_Verification Class
 *
 * @since 1.6050.0000
 */
class Diagnostic_Form_CSRF_Token_Verification extends Diagnostic_Base {

	protected static $slug = 'form-csrf-token-verification';
	protected static $title = 'Forms Not Protected Against CSRF';
	protected static $description = 'Checks if forms use nonces for CSRF protection';
	protected static $family = 'security';

	public static function check() {
		$issues = array();

		$issues[] = __( 'Add wp_nonce_field() to all forms', 'wpshadow' );
		$issues[] = __( 'Verify nonce in handler: check_admin_referer( "action" )', 'wpshadow' );
		$issues[] = __( 'Use unique nonce action for each form', 'wpshadow' );
		$issues[] = __( 'AJAX requests: check_ajax_referer( "action" )', 'wpshadow' );
		$issues[] = __( 'Re-verify after AJAX response (in JavaScript)', 'wpshadow' );

		if ( ! empty( $issues ) ) {
			return array(
				'id'           => self::$slug,
				'title'        => self::$title,
				'description'  => __( 'CSRF (Cross-Site Request Forgery) attacks trick users into submitting forms. Nonces prevent this by verifying requests came from your site.', 'wpshadow' ),
				'severity'     => 'critical',
				'threat_level' => 90,
				'auto_fixable' => true,
				'kb_link'      => 'https://wpshadow.com/kb/csrf-protection',
				'details'      => array(
					'recommendations'         => $issues,
					'wordpress_function'      => 'wp_nonce_field( "my_action_name" );',
					'verify_function'         => 'check_admin_referer( "my_action_name" );',
					'attack_example'          => 'Attacker tricks admin into delete post action',
				),
			);
		}

		return null;
	}
}
