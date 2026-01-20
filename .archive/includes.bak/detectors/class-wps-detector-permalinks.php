<?php

declare(strict_types=1);

namespace WPShadow\Detectors;

use WPShadow\CoreSupport\WPSHADOW_Issue_Detection;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Detector_Permalinks extends WPSHADOW_Issue_Detection {

	public function __construct() {
		parent::__construct(
			'permalinks-configuration',
			'Permalinks Not Configured',
			'Permalink structure is set to plain URLs',
			self::SEVERITY_MEDIUM,
			true
		);
	}

	public function run(): int {
		if ( $this->has_permalink_structure() ) {
			return 0;
		}

		$this->add_issue(
			array(
				'id'            => 'permalinks-001',
				'detector_id'   => $this->detector_id,
				'severity'      => self::SEVERITY_MEDIUM,
				'title'         => 'Permalinks Not Configured',
				'description'   => 'Your site is using plain URL structure (domain.com/?p=123) instead of user-friendly URLs. This impacts SEO and user experience.',
				'resolution'    => 'Go to Settings → Permalinks and select a custom structure such as "/%postname%/" for better SEO.',
				'confidence'    => 0.98,
				'auto_fixable'  => true,
				'data'          => array(
					'current_structure' => get_option( 'permalink_structure' ),
				),
			)
		);

		return 1;
	}

	public function get_issue_count(): int {
		return $this->has_permalink_structure() ? 0 : 1;
	}

	private function has_permalink_structure(): bool {
		$structure = get_option( 'permalink_structure' );

		if ( empty( $structure ) ) {
			return false;
		}

		return true;
	}
}
