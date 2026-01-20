<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Issue_Registry {

	private static ?self $instance = null;

	private array $detectors = array();

	private array $all_issues = array();

	private array $detection_history = array();

	private function __construct() {
	}

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function register_detector( WPSHADOW_Issue_Detection $detector ): bool {
		$detector_id = $detector->get_detector_id();

		if ( isset( $this->detectors[ $detector_id ] ) ) {
			return false;
		}

		$this->detectors[ $detector_id ] = $detector;

		return true;
	}

	public function get_detector( string $detector_id ): ?WPSHADOW_Issue_Detection {
		return $this->detectors[ $detector_id ] ?? null;
	}

	public function get_all_detectors(): array {
		return $this->detectors;
	}

	public function detector_exists( string $detector_id ): bool {
		return isset( $this->detectors[ $detector_id ] );
	}

	public function run_detector( string $detector_id ): bool {
		if ( ! $this->detector_exists( $detector_id ) ) {
			return false;
		}

		$detector = $this->detectors[ $detector_id ];
		$detector->clear_issues();

		$result = $detector->run();

		if ( $result ) {
			$this->store_issues_from_detector( $detector );
			$this->record_detection_history( $detector_id, true, null );
		} else {
			$this->record_detection_history( $detector_id, false, 'Detector returned false' );
		}

		return $result;
	}

	public function run_all_detectors(): array {
		$results = array();

		foreach ( $this->detectors as $detector_id => $detector ) {
			$results[ $detector_id ] = $this->run_detector( $detector_id );
		}

		return $results;
	}

	public function store_issues_from_detector( WPSHADOW_Issue_Detection $detector ): void {
		$detector_id = $detector->get_detector_id();
		$issues      = $detector->get_detected_issues();

		foreach ( $issues as $issue_id => $issue ) {
			$unique_key                      = "{$detector_id}_{$issue_id}";
			$this->all_issues[ $unique_key ] = $issue;
		}
	}

	public function get_all_issues(): array {
		return $this->all_issues;
	}

	public function get_issues_by_detector( string $detector_id ): array {
		return array_filter(
			$this->all_issues,
			static function( $issue ) use ( $detector_id ) {
				return $issue['detector_id'] === $detector_id;
			}
		);
	}

	public function get_issue_by_id( string $detector_id, string $issue_id ): ?array {
		$unique_key = "{$detector_id}_{$issue_id}";
		return $this->all_issues[ $unique_key ] ?? null;
	}

	public function get_issues_by_severity( string $severity ): array {
		return array_filter(
			$this->all_issues,
			static function( $issue ) use ( $severity ) {
				return $issue['severity'] === $severity;
			}
		);
	}

	public function get_critical_issues(): array {
		return $this->get_issues_by_severity( WPSHADOW_Issue_Detection::SEVERITY_CRITICAL );
	}

	public function get_high_severity_issues(): array {
		return $this->get_issues_by_severity( WPSHADOW_Issue_Detection::SEVERITY_HIGH );
	}

	public function get_medium_severity_issues(): array {
		return $this->get_issues_by_severity( WPSHADOW_Issue_Detection::SEVERITY_MEDIUM );
	}

	public function get_low_severity_issues(): array {
		return $this->get_issues_by_severity( WPSHADOW_Issue_Detection::SEVERITY_LOW );
	}

	public function get_auto_fixable_issues(): array {
		return array_filter(
			$this->all_issues,
			static function( $issue ) {
				return $issue['auto_fixable'] === true;
			}
		);
	}

	public function get_high_confidence_issues( float $threshold = 0.75 ): array {
		return array_filter(
			$this->all_issues,
			static function( $issue ) use ( $threshold ) {
				return $issue['confidence'] >= $threshold;
			}
		);
	}

	public function get_issue_count(): int {
		return count( $this->all_issues );
	}

	public function get_issue_count_by_detector( string $detector_id ): int {
		return count( $this->get_issues_by_detector( $detector_id ) );
	}

	public function get_detector_count(): int {
		return count( $this->detectors );
	}

	public function has_critical_issues(): bool {
		return count( $this->get_critical_issues() ) > 0;
	}

	public function has_issues(): bool {
		return count( $this->all_issues ) > 0;
	}

	public function clear_all_issues(): void {
		$this->all_issues = array();
	}

	public function clear_issues_by_detector( string $detector_id ): void {
		$this->all_issues = array_filter(
			$this->all_issues,
			static function( $issue ) use ( $detector_id ) {
				return $issue['detector_id'] !== $detector_id;
			}
		);
	}

	public function record_detection_history( string $detector_id, bool $success, ?string $error = null ): void {
		if ( ! isset( $this->detection_history[ $detector_id ] ) ) {
			$this->detection_history[ $detector_id ] = array();
		}

		$this->detection_history[ $detector_id ][] = array(
			'timestamp' => time(),
			'success'   => $success,
			'error'     => $error,
		);

		$max_history = 100;
		if ( count( $this->detection_history[ $detector_id ] ) > $max_history ) {
			$this->detection_history[ $detector_id ] = array_slice(
				$this->detection_history[ $detector_id ],
				-$max_history
			);
		}
	}

	public function get_detection_history( string $detector_id ): array {
		return $this->detection_history[ $detector_id ] ?? array();
	}

	public function get_severity_distribution(): array {
		$distribution = array(
			WPSHADOW_Issue_Detection::SEVERITY_CRITICAL => 0,
			WPSHADOW_Issue_Detection::SEVERITY_HIGH     => 0,
			WPSHADOW_Issue_Detection::SEVERITY_MEDIUM   => 0,
			WPSHADOW_Issue_Detection::SEVERITY_LOW      => 0,
		);

		foreach ( $this->all_issues as $issue ) {
			if ( isset( $distribution[ $issue['severity'] ] ) ) {
				$distribution[ $issue['severity'] ]++;
			}
		}

		return $distribution;
	}

	public function get_statistics(): array {
		return array(
			'total_issues'              => $this->get_issue_count(),
			'total_detectors'           => $this->get_detector_count(),
			'severity_distribution'     => $this->get_severity_distribution(),
			'auto_fixable_count'        => count( $this->get_auto_fixable_issues() ),
			'high_confidence_count'     => count( $this->get_high_confidence_issues() ),
			'has_critical_issues'       => $this->has_critical_issues(),
			'average_confidence'        => $this->get_average_confidence(),
		);
	}

	public function get_average_confidence(): float {
		if ( empty( $this->all_issues ) ) {
			return 0;
		}

		$total_confidence = 0;
		foreach ( $this->all_issues as $issue ) {
			$total_confidence += $issue['confidence'];
		}

		return $total_confidence / count( $this->all_issues );
	}

	public function get_detector_statistics( string $detector_id ): ?array {
		if ( ! $this->detector_exists( $detector_id ) ) {
			return null;
		}

		$detector = $this->detectors[ $detector_id ];
		$issues   = $this->get_issues_by_detector( $detector_id );

		$distribution = array(
			WPSHADOW_Issue_Detection::SEVERITY_CRITICAL => 0,
			WPSHADOW_Issue_Detection::SEVERITY_HIGH     => 0,
			WPSHADOW_Issue_Detection::SEVERITY_MEDIUM   => 0,
			WPSHADOW_Issue_Detection::SEVERITY_LOW      => 0,
		);

		foreach ( $issues as $issue ) {
			if ( isset( $distribution[ $issue['severity'] ] ) ) {
				$distribution[ $issue['severity'] ]++;
			}
		}

		return array(
			'detector_id'           => $detector_id,
			'detector_name'         => $detector->get_detector_name(),
			'detector_description'  => $detector->get_detector_description(),
			'total_issues'          => count( $issues ),
			'severity_distribution' => $distribution,
			'auto_fixable_count'    => count( array_filter( $issues, static function( $issue ) { return $issue['auto_fixable'] === true; } ) ),
		);
	}

	public function __clone() {
	}

	public function __wakeup() {
	}
}
