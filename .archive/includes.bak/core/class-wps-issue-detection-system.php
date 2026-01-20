<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Issue_Detection_System {

	private static ?self $instance = null;

	private WPSHADOW_Issue_Registry $registry;

	private array $initialized_detectors = array();

	private function __construct() {
		$this->registry = WPSHADOW_Issue_Registry::get_instance();
	}

	public static function get_instance(): self {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	public function initialize(): void {
		add_action( 'wp_loaded', array( $this, 'setup_hooks' ) );
		add_action( 'admin_init', array( $this, 'setup_admin_hooks' ) );
	}

	public function setup_hooks(): void {
		do_action( 'wpshadow_issue_detection_system_loaded', $this );
	}

	public function setup_admin_hooks(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		do_action( 'wpshadow_issue_detection_admin_loaded', $this );
	}

	public function register_detector( WPSHADOW_Issue_Detection $detector ): bool {
		$detector_id = $detector->get_detector_id();

		if ( isset( $this->initialized_detectors[ $detector_id ] ) ) {
			return false;
		}

		$result = $this->registry->register_detector( $detector );

		if ( $result ) {
			$this->initialized_detectors[ $detector_id ] = true;
		}

		return $result;
	}

	public function run_detector( string $detector_id ): bool {
		return $this->registry->run_detector( $detector_id );
	}

	public function run_all_detectors(): array {
		return $this->registry->run_all_detectors();
	}

	public function get_all_issues(): array {
		return $this->registry->get_all_issues();
	}

	public function get_critical_issues(): array {
		return $this->registry->get_critical_issues();
	}

	public function has_critical_issues(): bool {
		return $this->registry->has_critical_issues();
	}

	public function get_statistics(): array {
		return $this->registry->get_statistics();
	}

	public function get_registry(): WPSHADOW_Issue_Registry {
		return $this->registry;
	}

	public static function get_severity_constants(): array {
		return array(
			'critical' => WPSHADOW_Issue_Detection::SEVERITY_CRITICAL,
			'high'     => WPSHADOW_Issue_Detection::SEVERITY_HIGH,
			'medium'   => WPSHADOW_Issue_Detection::SEVERITY_MEDIUM,
			'low'      => WPSHADOW_Issue_Detection::SEVERITY_LOW,
		);
	}

	public static function is_valid_severity( string $severity ): bool {
		return in_array( $severity, WPSHADOW_Issue_Detection::VALID_SEVERITIES, true );
	}

	public function __clone() {
	}

	public function __wakeup() {
	}
}

if ( ! function_exists( 'wpshadow_issue_detection_system' ) ) {
	function wpshadow_issue_detection_system(): WPSHADOW_Issue_Detection_System {
		return WPSHADOW_Issue_Detection_System::get_instance();
	}
}
