# Uptime Monitoring

The Uptime Monitoring feature allows external monitoring services to ping your WordPress site at regular intervals (e.g., every 5 minutes) to verify availability and receive immediate alerts via email or SMS if the site goes down.

## Features

- **Public Health Check Endpoint**: A public URL that external monitoring services can ping
- **Automated Health Checks**: Verifies database connectivity, WordPress core, filesystem access, and memory status
- **Incident Logging**: Tracks all health check attempts with timestamps and status
- **Email Alerts**: Immediate email notifications when the site becomes unavailable
- **SMS Alerts**: Optional SMS notifications via Twilio, Nexmo/Vonage, or AWS SNS
- **Uptime Statistics**: Dashboard widget showing uptime percentage, total checks, and failed checks
- **Security**: Optional access token protection for the health check endpoint
- **Log Retention**: Automatic cleanup of old logs (90 days by default)

## Configuration

1. **Enable the Feature**:
   - Navigate to WPShadow → Settings → Features
   - Enable "Uptime Monitoring"

2. **Configure the Health Check Endpoint**:
   - The public endpoint is automatically available at: `https://yoursite.com/wpshadow-health`
   - Optionally generate an access token for additional security

3. **Configure Email Alerts**:
   - Enable "Email Alerts"
   - Enter comma-separated email addresses to receive alerts

4. **Configure SMS Alerts** (Optional):
   - Enable "SMS Alerts"
   - Select your SMS service provider
   - Enter your phone number with country code

## External Monitoring Services

Works with UptimeRobot, Pingdom, StatusCake, and any HTTP monitoring service.

Configure your service to monitor: `https://yoursite.com/wpshadow-health`

## Requirements

- WordPress 6.4 or higher
- PHP 8.1.29 or higher
- WPShadow Core plugin
- External monitoring service
