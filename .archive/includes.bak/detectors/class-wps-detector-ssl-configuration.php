<?php

declare(strict_types=1);

namespace WPShadow\Detectors;

use WPShadow\CoreSupport\WPSHADOW_Issue_Detection;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Detector_SSL_Configuration extends WPSHADOW_Issue_Detection {

	public function __construct() {
		parent::__construct(
			'ssl-configuration',
			'SSL/HTTPS Not Configured',
			'HTTPS is not enabled on this site',
			self::SEVERITY_CRITICAL,
			false
		);
	}

	public function run(): int {
		if ( $this->is_ssl_configured() ) {
			return 0;
		}

		$this->add_issue(
			array(
				'id'            => 'ssl-configuration-001',
				'detector_id'   => $this->detector_id,
				'severity'      => self::SEVERITY_CRITICAL,
				'title'         => 'HTTPS Not Configured',
				'description'   => 'Your site is not using SSL/HTTPS encryption. This is a critical security issue that can expose user data.',
				'resolution'    => 'Install an SSL certificate and configure your site to use HTTPS. You can use Let\'s Encrypt for free SSL certificates.',
				'confidence'    => 1.0,
				'auto_fixable'  => false,
				'data'          => array(
					'current_url'        => home_url(),
					'is_ssl'             => is_ssl(),
					'server_https'       => isset( $_SERVER['HTTPS'] ) ? $_SERVER['HTTPS'] : 'off',
					'wordpress_address'  => get_option( 'siteurl' ),
					'home_address'       => get_option( 'home' ),
				),
			)
		);

		return 1;
	}

	public function get_issue_count(): int {
		return $this->is_ssl_configured() ? 0 : 1;
	}

	private function is_ssl_configured(): bool {
		if ( is_ssl() ) {
			return true;
		}

		$siteurl = get_option( 'siteurl' );
		if ( ! empty( $siteurl ) && strpos( $siteurl, 'https://' ) === 0 ) {
			return true;
		}

		return false;
	}
}
