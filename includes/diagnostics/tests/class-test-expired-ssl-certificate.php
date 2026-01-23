<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics\Tests;

use WPShadow\Diagnostics\Diagnostic_Base;

/**
 * Diagnostic: Expired SSL Certificate
 *
 * Detects when SSL certificate is expired or about to expire.
 * Expired SSL breaks HTTPS and presents serious security warnings.
 *
 * @since 1.2.0
 */
class Test_Expired_SSL_Certificate extends Diagnostic_Base
{

	private const EXPIRY_WARNING_DAYS = 30;

	/**
	 * Check for expired SSL certificate
	 *
	 * @return array|null Diagnostic array if issues found, null if all good
	 */
	public static function check(): ?array
	{
		$expiry_info = self::check_ssl_expiry();

		if (! $expiry_info || $expiry_info['status'] === 'valid') {
			return null;
		}

		$threat = $expiry_info['status'] === 'expired' ? 95 : 70;

		return [
			'threat_level'    => $threat,
			'threat_color'    => 'red',
			'passed'          => false,
			'issue'           => $expiry_info['message'],
			'metadata'        => [
				'status'           => $expiry_info['status'],
				'expiry_date'      => $expiry_info['expiry_date'] ?? 'Unknown',
				'days_remaining'   => $expiry_info['days_remaining'] ?? 0,
				'certificate_info' => $expiry_info['cert_info'] ?? [],
			],
			'kb_link'         => 'https://wpshadow.com/kb/ssl-certificate-security/',
			'training_link'   => 'https://wpshadow.com/training/wordpress-ssl-https/',
		];
	}

	/**
	 * Guardian Sub-Test: SSL certificate status
	 *
	 * @return array Test result
	 */
	public static function test_ssl_status(): array
	{
		$expiry = self::check_ssl_expiry();
		$is_using_https = is_ssl();

		return [
			'test_name'        => 'SSL Certificate Status',
			'using_https'      => $is_using_https,
			'certificate_status' => $expiry ? $expiry['status'] : 'Unable to verify',
			'passed'           => $is_using_https && $expiry && $expiry['status'] === 'valid',
			'description'      => $expiry ? sprintf('Certificate: %s', $expiry['message']) : 'Unable to check certificate status',
		];
	}

	/**
	 * Guardian Sub-Test: Certificate expiry date
	 *
	 * @return array Test result
	 */
	public static function test_certificate_expiry(): array
	{
		$expiry = self::check_ssl_expiry();

		if (! $expiry) {
			return [
				'test_name'     => 'Certificate Expiry',
				'passed'        => false,
				'description'   => 'Unable to determine certificate expiry',
			];
		}

		return [
			'test_name'       => 'Certificate Expiry',
			'expiry_date'     => $expiry['expiry_date'] ?? 'Unknown',
			'days_remaining'  => $expiry['days_remaining'] ?? 0,
			'status'          => $expiry['status'],
			'passed'          => $expiry['status'] === 'valid',
			'description'     => $expiry['message'],
		];
	}

	/**
	 * Guardian Sub-Test: Certificate issuer details
	 *
	 * @return array Test result
	 */
	public static function test_certificate_details(): array
	{
		$cert_details = self::get_certificate_details();

		return [
			'test_name'          => 'Certificate Details',
			'issued_to'          => $cert_details['issued_to'] ?? 'Unknown',
			'issued_by'          => $cert_details['issued_by'] ?? 'Unknown',
			'valid_from'         => $cert_details['valid_from'] ?? 'Unknown',
			'valid_until'        => $cert_details['valid_until'] ?? 'Unknown',
			'is_self_signed'     => $cert_details['is_self_signed'] ?? false,
			'description'        => sprintf('Issued to: %s', $cert_details['issued_to'] ?? 'Unknown'),
		];
	}

	/**
	 * Guardian Sub-Test: HTTPS enforcement
	 *
	 * @return array Test result
	 */
	public static function test_https_enforcement(): array
	{
		$site_url = home_url();
		$is_using_https = is_ssl();
		$force_ssl_admin = defined('FORCE_SSL_ADMIN') && FORCE_SSL_ADMIN;
		$force_ssl_login = defined('FORCE_SSL_LOGIN') && FORCE_SSL_LOGIN;

		return [
			'test_name'          => 'HTTPS Enforcement',
			'site_url'           => $site_url,
			'using_https'        => $is_using_https,
			'force_ssl_admin'    => $force_ssl_admin,
			'force_ssl_login'    => $force_ssl_login,
			'passed'             => $is_using_https,
			'description'        => $is_using_https ? 'HTTPS enforced' : 'Site not using HTTPS (security risk)',
		];
	}

	/**
	 * Check SSL certificate expiry
	 *
	 * @return array|null Certificate expiry info or null
	 */
	private static function check_ssl_expiry(): ?array
	{
		$host = wp_parse_url(home_url(), PHP_URL_HOST);

		if (! $host) {
			return null;
		}

		try {
			$cert_details = self::get_certificate_details();

			if (! $cert_details || ! isset($cert_details['valid_until'])) {
				return null;
			}

			$expiry_time = strtotime($cert_details['valid_until']);
			$now = time();
			$days_remaining = (int) (($expiry_time - $now) / 86400);

			if ($days_remaining < 0) {
				$status = 'expired';
				$message = sprintf('SSL certificate expired %d days ago', abs($days_remaining));
			} elseif ($days_remaining < self::EXPIRY_WARNING_DAYS) {
				$status = 'expiring_soon';
				$message = sprintf('SSL certificate expires in %d days', $days_remaining);
			} else {
				$status = 'valid';
				$message = sprintf('SSL certificate valid for %d days', $days_remaining);
			}

			return [
				'status'          => $status,
				'message'         => $message,
				'expiry_date'     => $cert_details['valid_until'],
				'days_remaining'  => $days_remaining,
				'cert_info'       => $cert_details,
			];
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * Get SSL certificate details
	 *
	 * @return array|null Certificate details or null
	 */
	private static function get_certificate_details(): ?array
	{
		$host = wp_parse_url(home_url(), PHP_URL_HOST);

		if (! $host) {
			return null;
		}

		$stream_context = stream_context_create([
			'ssl' => [
				'capture_peer_cert' => true,
			],
		]);

		try {
			$resource = @stream_socket_client(
				'ssl://' . $host . ':443',
				$errno,
				$errstr,
				5,
				STREAM_CLIENT_CONNECT,
				$stream_context
			);

			if (! $resource) {
				return null;
			}

			$params = stream_context_get_params($stream_context);
			$cert = $params['options']['ssl']['peer_certificate'];

			if (! $cert) {
				return null;
			}

			$cert_info = openssl_x509_parse($cert);

			if (! $cert_info) {
				return null;
			}

			fclose($resource);

			return [
				'issued_to'     => $cert_info['subject']['CN'] ?? 'Unknown',
				'issued_by'     => $cert_info['issuer']['CN'] ?? 'Unknown',
				'valid_from'    => isset($cert_info['validFrom_time_t']) ? date('Y-m-d', $cert_info['validFrom_time_t']) : 'Unknown',
				'valid_until'   => isset($cert_info['validTo_time_t']) ? date('Y-m-d H:i:s', $cert_info['validTo_time_t']) : 'Unknown',
				'is_self_signed' => $cert_info['subject']['CN'] === $cert_info['issuer']['CN'],
			];
		} catch (\Exception $e) {
			return null;
		}
	}

	/**
	 * Get diagnostic name
	 *
	 * @return string
	 */
	public static function get_name(): string
	{
		return 'Expired SSL Certificate';
	}

	/**
	 * Get diagnostic description
	 *
	 * @return string
	 */
	public static function get_description(): string
	{
		return 'Checks if SSL certificate is valid and not expired';
	}

	/**
	 * Get diagnostic category
	 *
	 * @return string
	 */
	public static function get_category(): string
	{
		return 'Security';
	}
}
