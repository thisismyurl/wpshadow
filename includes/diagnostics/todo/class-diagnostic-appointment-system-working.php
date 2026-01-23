<?php

declare(strict_types=1);

namespace WPShadow\Diagnostics;

use WPShadow\Core\Diagnostic_Base;

/**
 * Diagnostic: Booking System Functional?
 *
 * Target Persona: Local Business Owner (Bakery/Plumber/Insurance)
 * Philosophy: Helpful neighbor (#1), show value (#9), educate (#5, #6)
 *
 * NOTE: Moved to TODO because the current check() always returns null. We need a real signal
 * (e.g., form shortcode presence, REST endpoint response, plugin-specific health) to decide pass/fail.
 */
class Diagnostic_Appointment_System_Working extends Diagnostic_Base
{
	protected static $slug        = 'appointment-system-working';
	protected static $title       = 'Booking System Functional?';
	protected static $description = 'Tests appointment/booking form submissions.';

	public static function check(): ?array
	{
		// Current implementation lacks a failure path and always returns null.
		$booking_plugins = array(
			'bookly-responsive-appointment-booking-tool/bookly.php',
			'simply-schedule-appointments/simply-schedule-appointments.php',
			'appointment-booking-calendar/appointment-booking-calendar.php',
			'amelia/amelia-booking.php',
			'booking/wpdev-booking.php',
		);

		foreach ($booking_plugins as $plugin) {
			if (is_plugin_active($plugin)) {
				return null; // Pass - booking system active
			}
		}

		if (class_exists('WC_Bookings')) {
			return null; // Pass - WooCommerce Bookings active
		}

		return null; // TODO: Define failure condition and return finding array when missing.
	}

	/**
	 * Live test placeholder: cannot assert pass/fail without a real detection signal.
	 */
	public static function test_live_appointment_system_working(): array
	{
		return array(
			'passed'  => false,
			'message' => 'TODO: Implement detection for missing/failed booking system; check() currently always returns null.',
		);
	}
}
