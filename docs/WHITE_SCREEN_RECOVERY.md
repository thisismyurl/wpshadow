# White Screen Auto-Recovery

## Overview

The White Screen Auto-Recovery feature automatically detects and recovers from fatal PHP errors that cause "White Screen of Death" (WSoD) situations. This emergency toolkit functionality helps users regain control of their WordPress site when plugin conflicts or fatal errors occur.

## How It Works

### 1. Fatal Error Detection
The system registers a shutdown function that monitors for fatal PHP errors:
- `E_ERROR` - Fatal runtime errors
- `E_PARSE` - Compile-time parse errors
- `E_COMPILE_ERROR` - Fatal compile-time errors
- `E_CORE_ERROR` - Fatal errors from PHP core
- `E_USER_ERROR` - User-generated fatal errors

### 2. Plugin Conflict Identification
When a fatal error occurs, the recovery system:
1. Analyzes the error file path
2. Identifies if the error originated from a plugin
3. Extracts the plugin basename for targeted recovery

### 3. Automatic Recovery Attempts
The system attempts up to **3 automatic recovery attempts**:

**Attempt 1-3**: If a problematic plugin is identified, the system:
- Automatically deactivates the problematic plugin
- Logs the recovery attempt
- Displays an admin notice explaining what happened
- Marks the plugin as "problematic" for tracking

**After 3 attempts**: If recovery continues to fail:
- Automatic recovery stops to prevent recovery loops
- The system flags the issue for manual intervention
- Admins are directed to the Emergency Support dashboard

### 4. Recovery Mode
If the problematic plugin cannot be identified, the system activates **Recovery Mode**:
- All plugins except WP Support are temporarily disabled
- Admins can safely access the dashboard to diagnose issues
- Recovery mode can be manually exited once issues are resolved

## User Interface

### Admin Notices

**Auto-Recovery Success**:
```
⚠️ WP Support Auto-Recovery:
A fatal error was detected and plugin "example-plugin/example.php" was 
automatically deactivated (attempt #1). Please review the error details 
and contact the plugin author.

[View Error Details]
```

**Recovery Mode Active**:
```
🚨 Recovery Mode Active

All plugins except WP Support have been temporarily disabled due to a 
critical error. Please review the error details and fix the issue before 
exiting recovery mode.

[Emergency Dashboard] [Exit Recovery Mode]
```

**Manual Recovery Required**:
```
🚨 Critical Error - Manual Recovery Required

Multiple recovery attempts have been made. Manual intervention is 
required to fix this issue.

[View Details & Get Help]
```

### Emergency Dashboard Integration

The Auto-Recovery Status metabox displays:

- **Recovery Mode**: Active/Inactive status
- **Recovery Attempts**: Current count (0-3) with maximum shown
- **Problematic Plugins**: List of plugins automatically deactivated
- **Actions**: 
  - Exit Recovery Mode
  - Clear Problematic Plugins List
  - Manually Activate Recovery Mode
  - Reset Recovery Counter

## Recovery Endpoint

A special recovery endpoint is available for emergency access:

```
https://example.com/wp-admin/?wps-recovery&_wpnonce=NONCE
```

This immediately activates recovery mode and redirects to the Emergency Support dashboard.

## Technical Details

### Key Options
- `WPS_recovery_attempts`: Tracks number of automatic recovery attempts
- `WPS_recovery_mode_active`: Boolean flag for recovery mode status
- `WPS_problematic_plugins`: Array of plugins that have been deactivated

### Integration Points
1. **Early Loading**: Recovery mode handler runs on `muplugins_loaded` hook (priority 1)
2. **Shutdown Function**: Fatal error handler registered with priority 0
3. **Admin Integration**: Connected to existing Emergency Support dashboard
4. **Activity Logging**: All recovery attempts logged via WPS_Activity_Logger

### Safety Mechanisms
- Maximum 3 automatic recovery attempts prevents infinite loops
- WP Support plugin itself cannot be deactivated by recovery system
- Recovery mode preserves WP Support functionality for troubleshooting
- All actions are logged for audit trail

## Usage Scenarios

### Scenario 1: Plugin Conflict After Update
1. User updates a plugin
2. Plugin has fatal PHP error due to PHP version incompatibility
3. Auto-recovery detects error, identifies plugin
4. Plugin is automatically deactivated
5. Admin sees notice explaining what happened
6. Admin contacts plugin author or updates PHP version
7. Admin can safely reactivate plugin after fix

### Scenario 2: Unknown Fatal Error
1. Fatal error occurs from unknown source
2. Auto-recovery cannot identify problematic plugin
3. Recovery mode automatically activated
4. All plugins disabled except WP Support
5. Admin accesses dashboard safely
6. Admin reviews error details in Emergency Support page
7. Admin manually fixes issue and exits recovery mode

### Scenario 3: Recurring Issues
1. Fatal errors continue after first recovery
2. System attempts recovery 2 more times
3. After 3rd attempt, automatic recovery stops
4. Manual recovery notice displayed
5. Admin directed to Emergency Support for professional help
6. Recovery counter can be reset once issue is resolved

## Best Practices

### For Site Administrators
1. Monitor the Emergency Support dashboard regularly
2. Review recovery notices promptly when they appear
3. Contact plugin authors when issues are identified
4. Keep recovery counter reset after resolving issues
5. Use recovery mode as last resort, not regular maintenance

### For Developers
1. Test plugins thoroughly before deployment
2. Handle errors gracefully with try-catch blocks
3. Follow WordPress coding standards to prevent fatal errors
4. Provide clear error messages for debugging
5. Test compatibility with common plugins and PHP versions

## Limitations

1. **Frontend Errors**: Recovery only helps with admin-accessible errors
2. **Server-Level Errors**: Cannot recover from server configuration issues
3. **Database Errors**: Does not handle database connection failures
4. **Theme Errors**: Currently focused on plugin conflicts (theme recovery planned)
5. **Network Issues**: Cannot fix networking or DNS problems

## Future Enhancements

Planned improvements for future versions:
- Theme conflict detection and recovery
- Automatic PHP memory limit adjustment
- Integration with Site Health checks
- Recovery mode email notifications
- Automatic snapshot creation before recovery attempts
- Machine learning for better error pattern recognition

## Related Features

- **Emergency Support**: Critical error logging and professional support integration
- **Auto Rollback**: Automatic rollback for failed updates
- **Snapshot Manager**: Site state snapshots for recovery
- **Activity Logger**: Comprehensive audit trail of all plugin actions

## Support

For issues or questions about White Screen Auto-Recovery:
1. Visit the Emergency Support dashboard
2. Export error reports for forums or support tickets
3. Contact professional support at https://thisismyurl.com/emergency-support
4. Use Emergency SOS for 24/7 critical support
