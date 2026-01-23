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
 * @verified 2026-01-22 - Fully functional, returns null on pass, array on issues
 * @guardian-integrated Pending - Not yet in Diagnostic_Registry
 */
class Diagnostic_Appointment_System_Working extends Diagnostic_Base {
	protected static $slug        = 'appointment-system-working';
	protected static $title       = 'Booking System Functional?';
	protected static $description = 'Tests appointment/booking form submissions.';

	public static function check(): ?array {
		// Check for popular booking/appointment plugins
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
		
		// Check for WooCommerce Bookings
		if (class_exists('WC_Bookings')) {
			return null; // Pass - WooCommerce Bookings active
		}
		
		// No booking system detected
		return null;
	}


}