<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

class Test_Security_XMLRPC extends Diagnostic_Base {

	public static function check(): ?array {
		$enabled = apply_filters( 'xmlrpc_enabled', true );
		if ( $enabled ) {
			return array(
				'id'           => 'xmlrpc-enabled',
				'title'        => 'XML-RPC is enabled',
				'threat_level' => 40,
			);
		}
		return null;
	}

	public static function test_live_security_xmlrpc(): array {
		$result = self::check();
		return array(
			'passed'  => $result === null,
			'message' => $result === null ? 'XML-RPC disabled' : 'XML-RPC enabled',
		);
	}
}
