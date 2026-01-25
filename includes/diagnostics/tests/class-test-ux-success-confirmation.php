<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_UX_Success_Confirmation extends Diagnostic_Base
{

protected static $slug        = 'test-ux-success-confirmation';
protected static $title       = 'Success Confirmation Test';
protected static $description = 'Tests for success messages after form submissions';

public static function check( ?string $url = null, ?string $html = null ): ?array {
	if ( $html !== null ) {
		return self::analyze_html( $html, $url ?? 'provided-html' );
	}

	$html = self::fetch_html( $url ?? home_url( '/' ) );
	if ( $html === false ) {
		return null;
	}

	return self::analyze_html( $html, $url ?? home_url( '/' ) );
}

protected static function analyze_html( string $html, string $checked_url ): ?array {
	// Check for forms
	$has_forms = preg_match( '/<form[^>]*>/i', $html );
	if ( ! $has_forms ) {
		return null;
	}

	// Check for success message containers
	$has_success_container = preg_match( '/class=["\'][^"\']*success|role=["\']status|aria-live=["\']polite/i', $html );

	// Check for hidden success message (good pattern)
	$has_hidden_success = preg_match( '/class=["\'][^"\']*success[^"\']*hidden|display:\s*none[^}]*success/i', $html );

	// If form exists but no success feedback mechanism
	if ( ! $has_success_container && ! $has_hidden_success ) {
		return array(
			'id'            => 'ux-success-confirmation-missing',
			'title'         => 'No Success Confirmation Pattern',
			'description'   => 'Forms detected but no success confirmation containers (role="status" or success message divs). Users need clear feedback when actions succeed.'
			'kb_link' => 'https://wpshadow.com/kb/success-messages/',
			'training_link' => 'https://wpshadow.com/training/form-ux/',
			'auto_fixable'  => false,
			'threat_level'  => 40,
			'module'        => 'UX',
			'priority'      => 2,
			'meta'          => array(
				'has_forms'             => $has_forms,
				'has_success_container' => $has_success_container,
				'has_hidden_success'    => $has_hidden_success,
				'checked_url'           => $checked_url,
			),
		);
	}

	return null;
}

protected static function fetch_html( string $url ) {
	$response = wp_remote_get(
		$url,
		array(
			'timeout'   => 10,
			'sslverify' => false,
		)
	);
	return is_wp_error( $response ) ? false : wp_remote_retrieve_body( $response );
}

public static function get_name(): string {
	return __( 'Success Confirmation', 'wpshadow' );
}

public static function get_description(): string {
	return __( 'Checks for success messages after form submissions.', 'wpshadow' );
}
