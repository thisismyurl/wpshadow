<?php

/**
 * WPShadow Admin Diagnostic Test: Unnecessary Head Bloat
 *
 * Tests if wp-admin <head> contains unnecessary meta tags and links, which causes:
 * - Larger HTML payload (wasted bandwidth)
 * - Slower HTML parsing
 * - Security concerns (version disclosure)
 * - Privacy concerns (third-party discovery links)
 *
 * Pattern: Buffers admin_head output and analyzes meta/link tags
 * Context: Requires admin context
 *
 * @package     WPShadow\Diagnostics\Tests
 * @since       1.2601.2312
 * @category    Admin Performance & Security
 * @philosophy  #10 Beyond Pure - Privacy-first, minimal external connections
 */

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

if (! defined('ABSPATH')) {
	exit;
}

/**
 * Test: Admin Head Bloat
 *
 * Detects unnecessary tags in <head> section
 *
 * @verified Not yet tested
 */
class Test_Admin_Head_Bloat extends Diagnostic_Base
{

	/**
	 * Run the diagnostic test
	 *
	 * @return array|null Diagnostic result array, or null if no issue found
	 */
	public function check(): ?array
	{
		// Only run in admin context
		if (! is_admin()) {
			return null;
		}

		$issues = array();
		$total_size = 0;

		// Check for WordPress generator meta tag
		if (has_action('admin_head', 'wp_generator')) {
			$issues[] = array(
				'type'        => 'generator',
				'description' => 'WordPress version meta tag (security risk)',
				'impact'      => 'Version disclosure helps attackers',
				'size'        => 50,
			);
			$total_size += 50;
		}

		// Check for REST API discovery links
		if (has_action('admin_head', 'rest_output_link_wp_head')) {
			$issues[] = array(
				'type'        => 'rest-api',
				'description' => 'REST API discovery link',
				'impact'      => 'Not needed in admin context',
				'size'        => 120,
			);
			$total_size += 120;
		}

		// Check for oEmbed discovery links
		if (has_action('admin_head', 'wp_oembed_add_discovery_links')) {
			$issues[] = array(
				'type'        => 'oembed',
				'description' => 'oEmbed discovery links',
				'impact'      => 'Not needed in admin context',
				'size'        => 200,
			);
			$total_size += 200;
		}

		// Check for RSD link (Really Simple Discovery for XML-RPC)
		if (has_action('admin_head', 'rsd_link')) {
			$issues[] = array(
				'type'        => 'rsd',
				'description' => 'RSD/XML-RPC discovery link',
				'impact'      => 'Legacy feature, rarely needed',
				'size'        => 80,
			);
			$total_size += 80;
		}

		// Check for Windows Live Writer manifest
		if (has_action('admin_head', 'wlwmanifest_link')) {
			$issues[] = array(
				'type'        => 'wlw',
				'description' => 'Windows Live Writer manifest',
				'impact'      => 'Obsolete blogging tool from 2012',
				'size'        => 90,
			);
			$total_size += 90;
		}

		// Check for shortlink
		if (has_action('admin_head', 'wp_shortlink_wp_head')) {
			$issues[] = array(
				'type'        => 'shortlink',
				'description' => 'Shortlink meta tag',
				'impact'      => 'Not needed in admin',
				'size'        => 60,
			);
			$total_size += 60;
		}

		// Check for emoji detection script
		if (has_action('admin_head', 'print_emoji_detection_script')) {
			$issues[] = array(
				'type'        => 'emoji',
				'description' => 'Emoji detection JavaScript',
				'impact'      => 'Modern browsers support emoji natively',
				'size'        => 7000, // ~7KB script
			);
			$total_size += 7000;
		}

		$issue_count = count($issues);

		// Threshold: More than 3 unnecessary items is bloat
		$threshold = 3;

		if ($issue_count <= $threshold) {
			return null; // Pass
		}

		return array(
			'id'           => 'admin-head-bloat',
			'title'        => 'Unnecessary Tags in Admin <head>',
			'description'  => sprintf(
				'WordPress admin <head> contains %d unnecessary meta tags and links totaling ~%s. These add bloat without benefit and may expose security information. Common culprits: generator meta, REST API discovery, oEmbed links, emoji script.',
				$issue_count,
				$this->format_bytes($total_size)
			)
			'kb_link'      => 'https://wpshadow.com/kb/clean-wordpress-head',
			'training_link' => 'https://wpshadow.com/training/optimize-html-output',
			'auto_fixable' => true, // Can remove via remove_action
			'threat_level' => 35,
			'module'       => 'admin-performance',
			'priority'     => 19,
			'meta'         => array(
				'issue_count' => $issue_count,
				'total_size'  => $total_size,
				'issues'      => $issues,
			),
		);
	}

	/**
	 * Format bytes to human readable
	 *
	 * @param int $bytes Byte count
	 * @return string Formatted size
	 */
	private function format_bytes(int $bytes): string
	{
		if ($bytes >= 1024) {
			return round($bytes / 1024, 1) . 'KB';
		}
		return $bytes . ' bytes';
	}

	/**
	 * Get diagnostic metadata
	 *
	 * @return array Diagnostic information
	 */
	public static function get_info(): array
	{
		return array(
			'name'        => 'Admin Head Bloat',
			'category'    => 'admin-performance',
			'severity'    => 'medium',
			'description' => 'Detects unnecessary meta tags and links in <head>',
		);
	}
}
