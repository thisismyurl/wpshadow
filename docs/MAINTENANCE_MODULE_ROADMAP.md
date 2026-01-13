# Scheduled Maintenance Module - Development Roadmap

## Decision Summary

The Scheduled Performance Maintenance System has been **deferred to a separate module** (`maintenance-support-thisismyurl`) to be developed post-M02.

**Decision Date:** January 13, 2026  
**Decision By:** @thisismyurl  
**Target Milestone:** Post-M02  
**Module Type:** Spoke  
**Repository:** `https://github.com/thisismyurl/maintenance-support-thisismyurl` (planned)

## Rationale

### Why Defer?
1. **Core Focus:** The core plugin should concentrate on foundational capabilities:
   - Vault operations
   - License validation
   - Module discovery and management
   
2. **Scope Management:** The maintenance system is comprehensive and self-contained:
   - WP-Cron integration
   - Database optimization
   - Email reporting
   - Batch processing
   - UI for task scheduling
   
3. **Architecture Benefits:**
   - Better separation of concerns
   - Optional installation (users who don't need scheduled maintenance don't have to install it)
   - Independent development and release cycle
   - Can leverage stable core infrastructure

4. **Ecosystem Alignment:** Fits perfectly into the hub & spoke architecture as an independent spoke module

## Module Overview

### Planned Features

#### 1. Scheduled Task Types

**Daily Tasks (3:00 AM)**
- Clean expired transients
- Remove auto-drafts older than 7 days
- Update performance metrics
- Check for slow queries (if logging enabled)

**Weekly Tasks (Sunday 2:00 AM)**
- Optimize database tables (OPTIMIZE TABLE)
- Clean orphaned metadata (post/comment/user/term meta)
- Remove spam comments (older than threshold)
- Plugin performance audit
- Generate performance report (email summary)

**Monthly Tasks (1st of month, 1:00 AM)**
- Deep database analysis (fragmentation, unused indexes)
- Remove old post revisions (beyond retention limit)
- Clean trashed content (older than 30 days)
- Backup performance history (archive old metrics)
- Security audit (check for bloat, unused plugins)

**On-Demand Tasks**
- Full database optimization (all tables)
- Complete metadata scan (comprehensive cleanup)
- Emergency cleanup (when disk space critical)

#### 2. Task Scheduler UI

The module will provide a dedicated dashboard page under the Support menu:

```
Support → Scheduled Maintenance
```

Features:
- Visual task schedule with next run times
- Enable/disable individual tasks
- Configure task frequency and timing
- Maintenance history log with results
- "Run Now" button for manual execution
- Email report settings

#### 3. Email Reports

**Weekly Summary Example:**
- Completed tasks summary
- Space saved and items cleaned
- Performance metrics comparison
- Recommendations based on analysis
- Link to full dashboard

#### 4. Safety Features

- **Time limits:** Respect `max_execution_time`
- **Batch processing:** Split large operations into chunks
- **Error logging:** Log failures for review
- **Load detection:** Skip during high traffic periods
- **Maintenance mode:** Prevent conflicts with updates
- **Rollback capability:** Ability to undo recent cleanup

#### 5. Dashboard Widget

A "Next Scheduled Maintenance" widget will appear on the main Support dashboard:
- Countdown to next task
- Last run summary
- Quick "Run Now" button
- View history link

## Technical Architecture

### WP-Cron Integration

The module will use WordPress's built-in cron system:

```php
// Custom cron schedules
add_filter('cron_schedules', 'timu_add_maintenance_schedules');

// Register maintenance hooks
add_action('timu_daily_maintenance', 'run_daily_tasks');
add_action('timu_weekly_maintenance', 'run_weekly_tasks');
add_action('timu_monthly_maintenance', 'run_monthly_tasks');
```

### Batch Processing

For large operations, the module will implement batch processing:
- Process 3-5 tables per cron run
- Use transients to track progress
- Resume on next cron cycle if incomplete
- Prevent timeout errors

### Activity Logging

All maintenance operations will be logged via the core activity logger:
- Task name and type
- Items processed
- Space saved
- Duration
- Errors (if any)

### Performance Monitoring

The module will integrate with existing performance features:
- Database Cleanup feature (core)
- Weekly Performance Report feature (core)
- Activity Logger (core)

## Development Plan

### Phase 1: Core Infrastructure (Week 1-2)
- [ ] Create module repository
- [ ] Set up WP-Cron schedules
- [ ] Implement basic task runners (daily/weekly/monthly)
- [ ] Add safety checks and time limits
- [ ] Integrate with core activity logger

### Phase 2: Task Implementation (Week 3-4)
- [ ] Implement transient cleanup
- [ ] Implement auto-draft removal
- [ ] Implement database optimization
- [ ] Implement orphaned metadata cleanup
- [ ] Implement spam comment removal
- [ ] Implement revision cleanup

### Phase 3: UI & UX (Week 5-6)
- [ ] Create task scheduler page
- [ ] Add task enable/disable toggles
- [ ] Build maintenance history view
- [ ] Add "Run Now" functionality
- [ ] Create dashboard widget

### Phase 4: Email Reports (Week 7)
- [ ] Design email templates
- [ ] Implement report generation
- [ ] Add email settings (frequency, recipients)
- [ ] Test email delivery

### Phase 5: Testing & Polish (Week 8)
- [ ] Comprehensive testing on various environments
- [ ] Performance testing with large databases
- [ ] Multisite testing
- [ ] Documentation and user guide
- [ ] Security review

## Integration Points

### Core Plugin Integration
- Activity Logger for task results
- Settings API for configuration storage
- Dashboard Widgets API for summary widget
- Module Registry for installation and updates

### Related Features (Core)
- **Database Cleanup:** Provides manual cleanup tools
- **Weekly Performance Report:** Can be enhanced with maintenance data
- **Activity Logger:** Records all maintenance operations
- **Site Audit:** Can leverage maintenance insights

## Success Criteria

- [ ] All task types implemented (daily/weekly/monthly)
- [ ] WP-Cron scheduling working reliably
- [ ] Task scheduler UI functional and intuitive
- [ ] Maintenance history logged and viewable
- [ ] Email reports sent correctly (optional)
- [ ] Batch processing prevents timeouts
- [ ] Progress tracking for long-running tasks
- [ ] Manual "Run Now" working
- [ ] Configurable schedules and timing
- [ ] Comprehensive error handling and recovery
- [ ] Multisite support
- [ ] Security review passed
- [ ] Documentation complete

## Module Metadata

When ready for development, add to the module catalog:

```json
{
  "slug": "maintenance-support-thisismyurl",
  "type": "spoke",
  "name": "Maintenance Support",
  "description": "Automated scheduled maintenance with WP-Cron for database optimization, cleanup tasks, and performance reports.",
  "version": "1.0.0",
  "suite_id": "thisismyurl-media-suite-2026",
  "requires_core": "1.2601.71818",
  "requires_php": "8.1.29",
  "requires_wp": "6.4.0",
  "download_url": "https://github.com/thisismyurl/maintenance-support-thisismyurl/releases/latest",
  "status": "planned",
  "target_milestone": "post-M02"
}
```

## WP-CLI Integration

The module will also support WP-CLI commands:

```bash
# Run all scheduled tasks immediately
wp timu maintenance run

# Run specific task type
wp timu maintenance run daily
wp timu maintenance run weekly
wp timu maintenance run monthly

# View maintenance history
wp timu maintenance history

# Clear maintenance logs
wp timu maintenance clear-logs --older-than=90d
```

## References

- **Original Issue:** Scheduled Performance Maintenance System
- **Core Plugin:** `plugin-wp-support-thisismyurl`
- **Related Features:** Database Cleanup, Weekly Performance Report, Activity Logger
- **WP-Cron Documentation:** https://developer.wordpress.org/plugins/cron/

## Notes for Future Development

1. **Start Simple:** Begin with daily transient cleanup and gradually add more tasks
2. **Test with Large Sites:** Ensure batch processing works with databases containing millions of rows
3. **Monitor Resource Usage:** Track memory and CPU usage during maintenance runs
4. **Provide Opt-Out:** Allow users to disable specific tasks they don't need
5. **Document Performance Impact:** Clearly communicate what each task does and its impact
6. **Consider Multisite:** Ensure tasks can run per-site or network-wide as appropriate
7. **Email Throttling:** Prevent email spam by grouping multiple tasks into single digest emails
8. **Timezone Awareness:** Allow users to configure maintenance windows based on their timezone
9. **Conflict Detection:** Check for other maintenance plugins and avoid conflicts
10. **Progressive Enhancement:** Start with conservative defaults, allow power users to customize

---

**Status:** Deferred to post-M02  
**Last Updated:** January 13, 2026  
**Maintained By:** @thisismyurl
