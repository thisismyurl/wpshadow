<?php
declare(strict_types=1);

namespace WPShadow\Workflow\Commands;

use WPShadow\Core\Command_Base;
use WPShadow\Reporting\Notification_Manager;
use WPShadow\Core\KPI_Tracker;

/**
 * Workflow Command: Send Report
 *
 * Immediately send report to recipient.
 * Also works for scheduling regular reports.
 *
 * POST Parameters:
 * - email (required): Email address to send to
 * - frequency (optional): Report frequency (daily, weekly, monthly)
 * - action (optional): 'send_now' or 'schedule'
 */
class Send_Report_Command extends Command_Base {

	/**
	 * Execute the command
	 *
	 * @return array Result
	 */
	protected function execute(): array {
		$email     = sanitize_email( $this->get_param( 'email' ) );
		$action    = sanitize_key( $this->get_param( 'action' ) ) ?: 'send_now';
		$frequency = sanitize_key( $this->get_param( 'frequency' ) ) ?: 'daily';

		// Validate email
		if ( ! is_email( $email ) ) {
			return $this->error( 'Invalid email address' );
		}

		try {
			if ( $action === 'schedule' ) {
				// Schedule recurring report
				$scheduled = Notification_Manager::schedule_report( $frequency, $email );

				if ( $scheduled ) {
					KPI_Tracker::record_action( 'report_scheduled', 1 );

					return $this->success(
						array(
							'message'   => "Report scheduled: $frequency delivery to $email",
							'frequency' => $frequency,
							'email'     => $email,
						)
					);
				} else {
					return $this->error( 'Failed to schedule report' );
				}
			} else {
				// Send immediately
				$sent = Notification_Manager::send_report_now( $email, $frequency );

				if ( $sent ) {
					KPI_Tracker::record_action( 'report_sent', 1 );

					return $this->success(
						array(
							'message' => "Report sent to $email",
							'email'   => $email,
						)
					);
				} else {
					return $this->error( 'Failed to send report' );
				}
			}
		} catch ( \Exception $e ) {
			return $this->error( 'Error: ' . $e->getMessage() );
		}
	}

	/**
	 * Get command name
	 *
	 * @return string
	 */
	public function get_name(): string {
		return 'send_report';
	}

	/**
	 * Get command description
	 *
	 * @return string
	 */
	public function get_description(): string {
		return 'Send reports via email (now or scheduled)';
	}
}
