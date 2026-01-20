<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

abstract class WPSHADOW_Issue_Detection {

	protected string $detector_id = '';

	protected string $detector_name = '';

	protected string $detector_description = '';

	protected array $detected_issues = array();

	protected array $issue_data_structure = array(
		'id'           => '',
		'detector_id'  => '',
		'severity'     => 'medium',
		'title'        => '',
		'description'  => '',
		'resolution'   => '',
		'confidence'   => 0.5,
		'timestamp'    => 0,
		'data'         => array(),
		'auto_fixable' => false,
		'auto_fix_data' => array(),
	);

	const SEVERITY_CRITICAL = 'critical';
	const SEVERITY_HIGH = 'high';
	const SEVERITY_MEDIUM = 'medium';
	const SEVERITY_LOW = 'low';

	const VALID_SEVERITIES = array(
		self::SEVERITY_CRITICAL,
		self::SEVERITY_HIGH,
		self::SEVERITY_MEDIUM,
		self::SEVERITY_LOW,
	);

	public function __construct( string $detector_id, string $detector_name, string $detector_description = '' ) {
		$this->detector_id          = $detector_id;
		$this->detector_name        = $detector_name;
		$this->detector_description = $detector_description;
	}

	abstract public function run(): bool;

	abstract public function get_issue_count(): int;

	public function get_detector_id(): string {
		return $this->detector_id;
	}

	public function get_detector_name(): string {
		return $this->detector_name;
	}

	public function get_detector_description(): string {
		return $this->detector_description;
	}

	public function get_detected_issues(): array {
		return $this->detected_issues;
	}

	public function add_issue(
		string $issue_id,
		string $title,
		string $description,
		string $severity = self::SEVERITY_MEDIUM,
		float $confidence = 0.5,
		array $data = array(),
		bool $auto_fixable = false,
		array $auto_fix_data = array()
	): bool {
		if ( ! $this->is_valid_severity( $severity ) ) {
			return false;
		}

		if ( $confidence < 0 || $confidence > 1 ) {
			$confidence = 0.5;
		}

		$issue = array(
			'id'             => $issue_id,
			'detector_id'    => $this->detector_id,
			'severity'       => $severity,
			'title'          => $title,
			'description'    => $description,
			'resolution'     => '',
			'confidence'     => $confidence,
			'timestamp'      => time(),
			'data'           => $data,
			'auto_fixable'   => $auto_fixable,
			'auto_fix_data'  => $auto_fix_data,
		);

		$this->detected_issues[ $issue_id ] = $issue;

		return true;
	}

	public function add_issue_with_resolution(
		string $issue_id,
		string $title,
		string $description,
		string $resolution,
		string $severity = self::SEVERITY_MEDIUM,
		float $confidence = 0.5,
		array $data = array(),
		bool $auto_fixable = false,
		array $auto_fix_data = array()
	): bool {
		$result = $this->add_issue(
			$issue_id,
			$title,
			$description,
			$severity,
			$confidence,
			$data,
			$auto_fixable,
			$auto_fix_data
		);

		if ( $result && isset( $this->detected_issues[ $issue_id ] ) ) {
			$this->detected_issues[ $issue_id ]['resolution'] = $resolution;
		}

		return $result;
	}

	public function clear_issues(): void {
		$this->detected_issues = array();
	}

	public function get_critical_issues(): array {
		return $this->filter_issues_by_severity( self::SEVERITY_CRITICAL );
	}

	public function get_high_severity_issues(): array {
		return $this->filter_issues_by_severity( self::SEVERITY_HIGH );
	}

	public function get_medium_severity_issues(): array {
		return $this->filter_issues_by_severity( self::SEVERITY_MEDIUM );
	}

	public function get_low_severity_issues(): array {
		return $this->filter_issues_by_severity( self::SEVERITY_LOW );
	}

	public function get_auto_fixable_issues(): array {
		return array_filter(
			$this->detected_issues,
			static function( $issue ) {
				return $issue['auto_fixable'] === true;
			}
		);
	}

	public function get_auto_not_fixable_issues(): array {
		return array_filter(
			$this->detected_issues,
			static function( $issue ) {
				return $issue['auto_fixable'] === false;
			}
		);
	}

	public function get_high_confidence_issues( float $threshold = 0.75 ): array {
		return array_filter(
			$this->detected_issues,
			static function( $issue ) use ( $threshold ) {
				return $issue['confidence'] >= $threshold;
			}
		);
	}

	public function get_issue_by_id( string $issue_id ): ?array {
		return $this->detected_issues[ $issue_id ] ?? null;
	}

	public function has_critical_issues(): bool {
		return count( $this->get_critical_issues() ) > 0;
	}

	public function has_high_severity_issues(): bool {
		return count( $this->get_high_severity_issues() ) > 0;
	}

	public function get_severity_distribution(): array {
		$distribution = array(
			self::SEVERITY_CRITICAL => 0,
			self::SEVERITY_HIGH     => 0,
			self::SEVERITY_MEDIUM   => 0,
			self::SEVERITY_LOW      => 0,
		);

		foreach ( $this->detected_issues as $issue ) {
			if ( isset( $distribution[ $issue['severity'] ] ) ) {
				$distribution[ $issue['severity'] ]++;
			}
		}

		return $distribution;
	}

	public function get_average_confidence(): float {
		if ( empty( $this->detected_issues ) ) {
			return 0;
		}

		$total_confidence = 0;
		foreach ( $this->detected_issues as $issue ) {
			$total_confidence += $issue['confidence'];
		}

		return $total_confidence / count( $this->detected_issues );
	}

	protected function filter_issues_by_severity( string $severity ): array {
		return array_filter(
			$this->detected_issues,
			static function( $issue ) use ( $severity ) {
				return $issue['severity'] === $severity;
			}
		);
	}

	protected function is_valid_severity( string $severity ): bool {
		return in_array( $severity, self::VALID_SEVERITIES, true );
	}

	protected function validate_issue_structure( array $issue ): bool {
		foreach ( array_keys( $this->issue_data_structure ) as $key ) {
			if ( ! array_key_exists( $key, $issue ) ) {
				return false;
			}
		}

		if ( ! in_array( $issue['severity'], self::VALID_SEVERITIES, true ) ) {
			return false;
		}

		if ( ! is_float( $issue['confidence'] ) && ! is_int( $issue['confidence'] ) ) {
			return false;
		}

		if ( $issue['confidence'] < 0 || $issue['confidence'] > 1 ) {
			return false;
		}

		return true;
	}
}
