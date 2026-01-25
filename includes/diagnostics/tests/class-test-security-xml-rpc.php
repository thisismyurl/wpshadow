<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Core\Diagnostic_Base;

class Test_Security_XML_RPC extends Diagnostic_Base {


	protected static $slug        = 'test-security-xml-rpc';
	protected static $title       = 'XML-RPC Security Test';
	protected static $description = 'Tests for exposed XML-RPC endpoint';

	public static function check( ?string $url = null, ?string $html = null ): ?array {
		$xmlrpc_url = home_url( '/xmlrpc.php' );

		$response = wp_remote_post(
			$xmlrpc_url,
			array(
				'timeout'   => 5,
				'sslverify' => false,
				'body'      => '<?xml version="1.0"?><methodCall><methodName>system.listMethods</methodName></methodCall>',
			)
		);

		if ( is_wp_error( $response ) ) {
			return null; // XML-RPC not accessible
		}

		$status_code = wp_remote_retrieve_response_code( $response );

		if ( $status_code === 200 ) {
			$body = wp_remote_retrieve_body( $response );

			// Check if XML-RPC is functioning
			if ( strpos( $body, 'methodResponse' ) !== false || strpos( $body, 'xmlrpc' ) !== false ) {
				return array(
					'id'            => 'security-xmlrpc-enabled',
					'title'         => 'XML-RPC Endpoint Enabled',
					'description'   => 'XML-RPC endpoint (xmlrpc.php) is accessible. XML-RPC is commonly targeted for brute-force attacks and DDoS amplification.'
					'kb_link' => 'https://wpshadow.com/kb/xml-rpc-security/',
					'training_link' => 'https://wpshadow.com/training/wordpress-hardening/',
					'auto_fixable'  => false,
					'threat_level'  => 45,
					'module'        => 'Security',
					'priority'      => 2,
					'meta'          => array(
						'xmlrpc_enabled' => true,
						'xmlrpc_url'     => $xmlrpc_url,
					),
				);
			}
		}

		return null;
	}

	public static function get_name(): string {
		return __( 'XML-RPC Security', 'wpshadow' );
	}

	public static function get_description(): string {
		return __( 'Checks for exposed XML-RPC endpoint.', 'wpshadow' );
	}
}
