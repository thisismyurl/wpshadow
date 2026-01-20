<?php

declare(strict_types=1);

namespace WPShadow\Detectors;

use WPShadow\CoreSupport\WPSHADOW_Issue_Detection;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Detector_Site_Description extends WPSHADOW_Issue_Detection {

	public function __construct() {
		parent::__construct(
			'site-description',
			'Site Description Empty',
			'Site description is empty or not configured',
			self::SEVERITY_LOW,
			true
		);
	}

	public function run(): int {
		if ( $this->has_site_description() ) {
			return 0;
		}

		$this->add_issue(
			array(
				'id'            => 'site-description-001',
				'detector_id'   => $this->detector_id,
				'severity'      => self::SEVERITY_LOW,
				'title'         => 'Site Description Is Empty',
				'description'   => 'Your site does not have a tagline/description configured. This is used by search engines and helps with SEO.',
				'resolution'    => 'Go to Settings → General and add a descriptive tagline for your site (recommended: 50-160 characters).',
				'confidence'    => 0.95,
				'auto_fixable'  => true,
				'data'          => array(
					'current_description' => get_option( 'blogdescription' ),
				),
			)
		);

		return 1;
	}

	public function get_issue_count(): int {
		return $this->has_site_description() ? 0 : 1;
	}

	private function has_site_description(): bool {
		$description = get_option( 'blogdescription' );

		if ( empty( $description ) ) {
			return false;
		}

		if ( strlen( trim( $description ) ) === 0 ) {
			return false;
		}

		return true;
	}
}
