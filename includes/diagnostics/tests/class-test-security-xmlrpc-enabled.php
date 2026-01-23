<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Test: XML-RPC Enabled (Security)
 *
 * Checks if XML-RPC is enabled (increases attack surface)
 * Philosophy: Show value (#9) - reduces attack vectors
 *
 * @package WPShadow
 * @subpackage Diagnostics/Tests
 * @since 1.2601.2112
 */
class Test_Security_XmlRpcEnabled extends Diagnostic_Base
{

	public static function check(): ?array
	{
		// Check if XML-RPC is enabled
		if (apply_filters('xmlrpc_enabled', true)) {
			return [
				'id' => 'xmlrpc-enabled',
				'title' => __('XML-RPC is enabled', 'wpshadow'),
				'description' => __('XML-RPC is rarely needed and increases attack surface. Disable it with wp-config.php or a security plugin.', 'wpshadow'),
				'severity' => 'medium',
				'threat_level' => 40,
			];
		}

		return null;
	}

	public static function test_live_xmlrpc_enabled(): array
	{
		$result = self::check();

		if (null === $result) {
			return [
				'passed' => true,
				'message' => __('XML-RPC is properly disabled', 'wpshadow'),
			];
		}

		return [
			'passed' => false,
			'message' => $result['description'],
		];
	}
}
