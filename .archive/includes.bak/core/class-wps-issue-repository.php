<?php

declare(strict_types=1);

namespace WPShadow\CoreSupport;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class WPSHADOW_Issue_Repository {

	private const OPTION_CURRENT_ISSUES = 'wpshadow_detected_issues';

	private const OPTION_SNAPSHOT_PREFIX = 'wpshadow_report_';

	private const SNAPSHOT_RETENTION_DAYS = 90;

	private const SNAPSHOT_ARCHIVE_DAYS = 30;

	private bool $multisite_enabled = false;

	private string $site_context = 'site';

	public function __construct() {
		$this->multisite_enabled = is_multisite();
	}

	public function store_issues( array $issues ): bool {
		if ( empty( $issues ) ) {
			return $this->delete_all_current_issues();
		}

		$clean_issues = $this->normalize_issues_for_storage( $issues );

		if ( ! $this->is_valid_issues_structure( $clean_issues ) ) {
			return false;
		}

		$serialized = $this->serialize_data( $clean_issues );

		if ( $this->multisite_enabled ) {
			update_site_option( self::OPTION_CURRENT_ISSUES, $serialized );
		} else {
			update_option( self::OPTION_CURRENT_ISSUES, $serialized );
		}

		$this->create_daily_snapshot( $issues );

		return true;
	}

	public function store_issue( string $issue_id, array $issue_data ): bool {
		$issues = $this->get_current_issues();

		$issue_data['id'] = $issue_id;
		if ( ! isset( $issue_data['detected_at'] ) ) {
			$issue_data['detected_at'] = time();
		}

		$issues[ $issue_id ] = $issue_data;

		return $this->store_issues( $issues );
	}

	public function get_current_issues(): array {
		if ( $this->multisite_enabled ) {
			$serialized = get_site_option( self::OPTION_CURRENT_ISSUES, '' );
		} else {
			$serialized = get_option( self::OPTION_CURRENT_ISSUES, '' );
		}

		if ( empty( $serialized ) ) {
			return array();
		}

		return $this->unserialize_data( $serialized );
	}

	public function get_issue( string $issue_id ): ?array {
		$issues = $this->get_current_issues();
		return $issues[ $issue_id ] ?? null;
	}

	public function get_issues_by_severity( string $severity ): array {
		$issues = $this->get_current_issues();

		return array_filter(
			$issues,
			static function( $issue ) use ( $severity ) {
				return isset( $issue['severity'] ) && $issue['severity'] === $severity;
			}
		);
	}

	public function delete_issue( string $issue_id ): bool {
		$issues = $this->get_current_issues();

		if ( ! isset( $issues[ $issue_id ] ) ) {
			return false;
		}

		unset( $issues[ $issue_id ] );

		return $this->store_issues( $issues );
	}

	public function delete_all_current_issues(): bool {
		if ( $this->multisite_enabled ) {
			delete_site_option( self::OPTION_CURRENT_ISSUES );
		} else {
			delete_option( self::OPTION_CURRENT_ISSUES );
		}

		return true;
	}

	public function get_issue_count(): int {
		return count( $this->get_current_issues() );
	}

	public function has_issues(): bool {
		return $this->get_issue_count() > 0;
	}

	public function get_severity_breakdown(): array {
		$issues = $this->get_current_issues();

		$breakdown = array(
			WPSHADOW_Issue_Detection::SEVERITY_CRITICAL => 0,
			WPSHADOW_Issue_Detection::SEVERITY_HIGH     => 0,
			WPSHADOW_Issue_Detection::SEVERITY_MEDIUM   => 0,
			WPSHADOW_Issue_Detection::SEVERITY_LOW      => 0,
		);

		foreach ( $issues as $issue ) {
			if ( isset( $issue['severity'] ) && isset( $breakdown[ $issue['severity'] ] ) ) {
				$breakdown[ $issue['severity'] ]++;
			}
		}

		return $breakdown;
	}

	public function create_daily_snapshot( array $issues ): bool {
		$today = gmdate( 'Ymd' );
		$option_key = self::OPTION_SNAPSHOT_PREFIX . $today;

		$snapshot = array(
			'timestamp'             => time(),
			'date'                  => $today,
			'total_issues'          => count( $issues ),
			'severity_breakdown'    => $this->calculate_severity_breakdown( $issues ),
			'issues'                => $this->normalize_issues_for_storage( $issues ),
		);

		$serialized = $this->serialize_data( $snapshot );

		if ( $this->multisite_enabled ) {
			update_site_option( $option_key, $serialized );
		} else {
			update_option( $option_key, $serialized );
		}

		$this->cleanup_old_snapshots();

		return true;
	}

	public function get_snapshot( string $date ): ?array {
		$option_key = self::OPTION_SNAPSHOT_PREFIX . $date;

		if ( $this->multisite_enabled ) {
			$serialized = get_site_option( $option_key, '' );
		} else {
			$serialized = get_option( $option_key, '' );
		}

		if ( empty( $serialized ) ) {
			return null;
		}

		return $this->unserialize_data( $serialized );
	}

	public function get_snapshots_between( string $start_date, string $end_date ): array {
		$snapshots = array();

		$start_ts = strtotime( $start_date );
		$end_ts   = strtotime( $end_date ) + 86400;

		if ( ! $start_ts || ! $end_ts ) {
			return array();
		}

		for ( $ts = $start_ts; $ts < $end_ts; $ts += 86400 ) {
			$date = gmdate( 'Ymd', $ts );
			$snapshot = $this->get_snapshot( $date );

			if ( $snapshot ) {
				$snapshots[ $date ] = $snapshot;
			}
		}

		return $snapshots;
	}

	public function get_history( int $days = 30 ): array {
		$history = array();

		for ( $i = 0; $i < $days; $i++ ) {
			$ts   = time() - ( $i * 86400 );
			$date = gmdate( 'Ymd', $ts );

			$snapshot = $this->get_snapshot( $date );

			if ( $snapshot ) {
				$history[ $date ] = $snapshot;
			}
		}

		krsort( $history );

		return $history;
	}

	public function get_latest_snapshot(): ?array {
		$history = $this->get_history( 1 );

		if ( empty( $history ) ) {
			return null;
		}

		return array_shift( $history );
	}

	public function cleanup_old_snapshots(): int {
		$deleted_count = 0;
		$cutoff_ts     = time() - ( self::SNAPSHOT_RETENTION_DAYS * 86400 );
		$cutoff_date   = gmdate( 'Ymd', $cutoff_ts );

		for ( $days_ago = self::SNAPSHOT_RETENTION_DAYS + 1; $days_ago < self::SNAPSHOT_RETENTION_DAYS + 365; $days_ago++ ) {
			$ts   = time() - ( $days_ago * 86400 );
			$date = gmdate( 'Ymd', $ts );

			if ( $date > $cutoff_date ) {
				break;
			}

			$option_key = self::OPTION_SNAPSHOT_PREFIX . $date;

			if ( $this->multisite_enabled ) {
				if ( delete_site_option( $option_key ) ) {
					$deleted_count++;
				}
			} else {
				if ( delete_option( $option_key ) ) {
					$deleted_count++;
				}
			}
		}

		return $deleted_count;
	}

	public function get_snapshot_statistics(): array {
		$history = $this->get_history( 90 );

		if ( empty( $history ) ) {
			return array(
				'total_snapshots'    => 0,
				'date_range'         => null,
				'average_issues'     => 0,
				'peak_issues'        => 0,
				'lowest_issues'      => 0,
				'trend'              => 'stable',
			);
		}

		$issue_counts = array();
		$dates        = array_keys( $history );

		foreach ( $history as $snapshot ) {
			$issue_counts[] = $snapshot['total_issues'] ?? 0;
		}

		$average = array_sum( $issue_counts ) / count( $issue_counts );
		$peak    = max( $issue_counts );
		$lowest  = min( $issue_counts );

		$first_count = end( $issue_counts );
		$last_count  = reset( $issue_counts );
		$trend       = 'stable';

		if ( $last_count > $first_count * 1.1 ) {
			$trend = 'increasing';
		} elseif ( $last_count < $first_count * 0.9 ) {
			$trend = 'decreasing';
		}

		return array(
			'total_snapshots'    => count( $history ),
			'date_range'         => array(
				'start' => end( $dates ),
				'end'   => reset( $dates ),
			),
			'average_issues'     => round( $average, 2 ),
			'peak_issues'        => $peak,
			'lowest_issues'      => $lowest,
			'trend'              => $trend,
		);
	}

	public function get_multisite_issues( int $site_id = 0 ): array {
		if ( ! $this->multisite_enabled ) {
			return $this->get_current_issues();
		}

		if ( $site_id <= 0 ) {
			$site_id = get_current_blog_id();
		}

		switch_to_blog( $site_id );
		$issues = $this->get_current_issues();
		restore_current_blog();

		return $issues;
	}

	public function export_snapshot( string $date, string $format = 'json' ): string {
		$snapshot = $this->get_snapshot( $date );

		if ( ! $snapshot ) {
			return '';
		}

		if ( 'json' === $format ) {
			return json_encode( $snapshot, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES );
		}

		if ( 'csv' === $format ) {
			return $this->convert_snapshot_to_csv( $snapshot );
		}

		return '';
	}

	private function serialize_data( array $data ): string {
		$json = json_encode( $data, JSON_UNESCAPED_SLASHES );

		if ( false === $json ) {
			return '';
		}

		if ( strlen( $json ) > 10000 ) {
			$compressed = gzcompress( $json, 9 );
			if ( false !== $compressed ) {
				return 'gzipped:' . base64_encode( $compressed );
			}
		}

		return $json;
	}

	private function unserialize_data( string $data ): array {
		if ( strpos( $data, 'gzipped:' ) === 0 ) {
			$compressed = base64_decode( substr( $data, 8 ), true );
			if ( false !== $compressed ) {
				$json = gzuncompress( $compressed );
				if ( false !== $json ) {
					$decoded = json_decode( $json, true );
					return is_array( $decoded ) ? $decoded : array();
				}
			}
		}

		$decoded = json_decode( $data, true );
		return is_array( $decoded ) ? $decoded : array();
	}

	private function normalize_issues_for_storage( array $issues ): array {
		$normalized = array();

		foreach ( $issues as $key => $issue ) {
			if ( is_array( $issue ) ) {
				$normalized[ $key ] = $this->normalize_issue_data( $issue );
			}
		}

		return $normalized;
	}

	private function normalize_issue_data( array $issue ): array {
		$required_fields = array(
			'id'          => '',
			'severity'    => WPSHADOW_Issue_Detection::SEVERITY_MEDIUM,
			'title'       => '',
			'detected_at' => time(),
		);

		$normalized = array_intersect_key( $issue, $required_fields );

		foreach ( $required_fields as $field => $default ) {
			if ( ! isset( $normalized[ $field ] ) ) {
				$normalized[ $field ] = $default;
			}
		}

		return $normalized;
	}

	private function is_valid_issues_structure( array $issues ): bool {
		foreach ( $issues as $issue ) {
			if ( ! is_array( $issue ) ) {
				return false;
			}

			if ( ! isset( $issue['id'], $issue['severity'] ) ) {
				return false;
			}

			if ( ! in_array( $issue['severity'], WPSHADOW_Issue_Detection::VALID_SEVERITIES, true ) ) {
				return false;
			}
		}

		return true;
	}

	private function calculate_severity_breakdown( array $issues ): array {
		$breakdown = array(
			WPSHADOW_Issue_Detection::SEVERITY_CRITICAL => 0,
			WPSHADOW_Issue_Detection::SEVERITY_HIGH     => 0,
			WPSHADOW_Issue_Detection::SEVERITY_MEDIUM   => 0,
			WPSHADOW_Issue_Detection::SEVERITY_LOW      => 0,
		);

		foreach ( $issues as $issue ) {
			if ( isset( $issue['severity'], $breakdown[ $issue['severity'] ] ) ) {
				$breakdown[ $issue['severity'] ]++;
			}
		}

		return $breakdown;
	}

	private function convert_snapshot_to_csv( array $snapshot ): string {
		$csv = "Date,Total Issues,Critical,High,Medium,Low\n";

		$date                = $snapshot['date'] ?? gmdate( 'Ymd' );
		$total               = $snapshot['total_issues'] ?? 0;
		$breakdown           = $snapshot['severity_breakdown'] ?? array();
		$critical            = $breakdown[ WPSHADOW_Issue_Detection::SEVERITY_CRITICAL ] ?? 0;
		$high                = $breakdown[ WPSHADOW_Issue_Detection::SEVERITY_HIGH ] ?? 0;
		$medium              = $breakdown[ WPSHADOW_Issue_Detection::SEVERITY_MEDIUM ] ?? 0;
		$low                 = $breakdown[ WPSHADOW_Issue_Detection::SEVERITY_LOW ] ?? 0;

		$csv .= "$date,$total,$critical,$high,$medium,$low\n";

		return $csv;
	}
}
