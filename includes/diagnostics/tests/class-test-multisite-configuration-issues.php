<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Multisite Configuration Issues
 *
 * Detects common multisite configuration problems (subdomains vs subdirectories, domain mapping, etc).
 * Misconfigured multisite can cause significant functionality issues.
 *
 * @since 1.2.0
 */
class Test_Multisite_Configuration_Issues extends Diagnostic_Base
{

	/**
	 * Check for multisite configuration issues
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		if (! is_multisite()) {
			return null; // Not a multisite installation
		}

		$issues = self::find_configuration_issues();

		if (empty($issues)) {
			return null;
		}

		$threat = count($issues) * 15;
		$threat = min(70, $threat);

		return [
			'threat_level'    => $threat,
			'threat_color'    => 'yellow',
			'passed'          => false,
			'issue'           => sprintf(
				'Found %d multisite configuration issues',
				count($issues)
			),
			'metadata'        => [
				'issues_count' => count($issues),
				'issues'       => $issues,
			],
			'kb_link'         => 'https://wpshadow.com/kb/wordpress-multisite-setup/',
			'training_link'   => 'https://wpshadow.com/training/multisite-configuration/',
		];
	}

	/**
	 * Guardian Sub-Test: Multisite type
	 *
	 * @return array Test result
	 */
	public static function test_multisite_type(): array
	{
		$is_multisite = is_multisite();
		$subdomain_install = defined('SUBDOMAIN_INSTALL') ? SUBDOMAIN_INSTALL : false;

		$type = $is_multisite ? ($subdomain_install ? 'Subdomains' : 'Subdirectories') : 'Single Site';

		return [
			'test_name'         => 'Multisite Type',
			'is_multisite'      => $is_multisite,
			'subdomain_install' => $subdomain_install,
			'type'              => $type,
			'description'       => 'Installation type: ' . $type,
		];
	}

	/**
	 * Guardian Sub-Test: Site count
	 *
	 * @return array Test result
	 */
	public static function test_site_count(): array
	{
		if (! is_multisite()) {
			return [
				'test_name'    => 'Site Count',
				'is_multisite' => false,
				'description'  => 'Not a multisite installation',
			];
		}

		$sites = get_sites();
		$site_count = count($sites);

		return [
			'test_name'  => 'Site Count',
			'site_count' => $site_count,
			'sites'      => array_map(fn($s) => [
				'id'   => $s->blog_id,
				'url'  => $s->home,
				'name' => get_blog_option($s->blog_id, 'blogname'),
			], array_slice($sites, 0, 5)),
			'description' => sprintf('Total sites: %d', $site_count),
		];
	}

	/**
	 * Guardian Sub-Test: Wildcard DNS configuration
	 *
	 * @return array Test result
	 */
	public static function test_wildcard_dns(): array
	{
		$subdomain_install = defined('SUBDOMAIN_INSTALL') ? SUBDOMAIN_INSTALL : false;

		if (! $subdomain_install) {
			return [
				'test_name'    => 'Wildcard DNS',
				'required'     => false,
				'description'  => 'Not required for subdirectory installations',
			];
		}

		$domain = wp_parse_url(network_home_url(), PHP_URL_HOST);

		return [
			'test_name'  => 'Wildcard DNS',
			'domain'     => $domain,
			'required'   => true,
			'description' => 'Wildcard DNS (*.example.com) should be configured',
		];
	}

	/**
	 * Guardian Sub-Test: .htaccess configuration
	 *
	 * @return array Test result
	 */
	public static function test_multisite_htaccess(): array
	{
		if (! is_multisite()) {
			return [
				'test_name'    => 'Multisite .htaccess',
				'is_multisite' => false,
				'description'  => 'Not applicable to single site',
			];
		}

		$htaccess_file = ABSPATH . '.htaccess';
		$htaccess_exists = file_exists($htaccess_file);
		$has_multisite_rules = false;

		if ($htaccess_exists) {
			$content = file_get_contents($htaccess_file);
			$has_multisite_rules = strpos($content, 'RewriteCond %{REQUEST_FILENAME} -f') !== false;
		}

		return [
			'test_name'           => 'Multisite .htaccess',
			'htaccess_exists'     => $htaccess_exists,
			'has_multisite_rules' => $has_multisite_rules,
			'passed'              => $htaccess_exists && $has_multisite_rules,
			'description'         => $has_multisite_rules ? 'Multisite .htaccess configured' : 'Missing or incomplete .htaccess',
		];
	}

	/**
	 * Find multisite configuration issues
	 *
	 * @return array List of issues
	 */
	private static function find_configuration_issues(): array
	{
		$issues = [];

		if (! is_multisite()) {
			return $issues;
		}

		// Check for SUBDOMAIN_INSTALL constant
		if (! defined('SUBDOMAIN_INSTALL')) {
			$issues[] = 'SUBDOMAIN_INSTALL constant not defined';
		}

		// Check .htaccess for multisite subdirectory installations
		$subdomain_install = defined('SUBDOMAIN_INSTALL') ? SUBDOMAIN_INSTALL : false;
		if (! $subdomain_install) {
			$htaccess = ABSPATH . '.htaccess';
			if (! file_exists($htaccess)) {
				$issues[] = 'Missing .htaccess file (required for subdirectory multisite)';
			}
		}

		// Check network home URL vs site URLs
		$network_home = network_home_url();
		$site_home = home_url();
		if (strpos($site_home, $network_home) !== 0) {
			$issues[] = 'Site URL not under network home URL';
		}

		// Check for DOMAIN_CURRENT_SITE if using subdomains
		if ($subdomain_install && ! defined('DOMAIN_CURRENT_SITE')) {
			$issues[] = 'DOMAIN_CURRENT_SITE not defined for subdomain multisite';
		}

		return $issues;
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Multisite Configuration Issues';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Checks for common WordPress multisite configuration problems';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'Configuration';
	}
}
