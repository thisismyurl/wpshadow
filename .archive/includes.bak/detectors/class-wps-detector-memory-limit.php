<?php

declare(strict_types=1);

namespace WPShadow\Detectors;

use WPShadow\CoreSupport\WPSHADOW_Issue_Detection;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Detector_Memory_Limit extends WPSHADOW_Issue_Detection {

	private const MINIMUM_MEMORY_MB = 64;

	public function __construct() {
		parent::__construct(
			'memory-limit',
			'PHP Memory Limit Too Low',
			'Memory limit is below recommended 64MB',
			self::SEVERITY_MEDIUM,
			false
		);
	}

	public function run(): int {
		if ( $this->has_sufficient_memory() ) {
			return 0;
		}

		$current_memory = $this->get_memory_limit_mb();

		$this->add_issue(
			array(
				'id'            => 'memory-limit-001',
				'detector_id'   => $this->detector_id,
				'severity'      => self::SEVERITY_MEDIUM,
				'title'         => 'PHP Memory Limit Too Low',
				'description'   => sprintf(
					'Your site\'s PHP memory limit is set to %dMB, which is below the recommended minimum of %dMB. This can cause plugin conflicts and performance issues.',
					$current_memory,
					self::MINIMUM_MEMORY_MB
				),
				'resolution'    => sprintf(
					'Contact your hosting provider to increase the PHP memory limit to at least %dMB (recommended: 256MB).',
					self::MINIMUM_MEMORY_MB
				),
				'confidence'    => 0.99,
				'auto_fixable'  => false,
				'data'          => array(
					'current_limit'    => $current_memory . 'MB',
					'wp_memory_limit'  => WP_MEMORY_LIMIT,
					'php_memory_limit' => ini_get( 'memory_limit' ),
				),
			)
		);

		return 1;
	}

	public function get_issue_count(): int {
		return $this->has_sufficient_memory() ? 0 : 1;
	}

	private function has_sufficient_memory(): bool {
		return $this->get_memory_limit_mb() >= self::MINIMUM_MEMORY_MB;
	}

	private function get_memory_limit_mb(): int {
		$limit = WP_MEMORY_LIMIT;

		if ( empty( $limit ) ) {
			$limit = ini_get( 'memory_limit' );
		}

		$limit = (string) $limit;

		if ( strpos( $limit, 'G' ) !== false ) {
			return (int) $limit * 1024;
		}

		if ( strpos( $limit, 'M' ) !== false ) {
			return (int) $limit;
		}

		if ( strpos( $limit, 'K' ) !== false ) {
			return (int) $limit / 1024;
		}

		return (int) $limit / ( 1024 * 1024 );
	}
}
