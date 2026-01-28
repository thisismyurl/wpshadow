#!/bin/bash
REPO="thisismyurl/wpshadow"

echo "=== Creating 60 User Management & Roles Diagnostics ==="

# CATEGORY 1: User Profile & Account Security (15 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] User Profile Completeness" \
  --body "**Purpose:** Validates user profiles have essential information completed (display name, bio, contact details).

**What to Test:**
- Check for users with default 'admin' username (security risk)
- Verify display names are set (not just login names)
- Test for empty bio fields on author profiles
- Validate email addresses are legitimate domains

**Why It Matters:** Incomplete profiles affect SEO (author rich snippets), credibility, and user trust. Default 'admin' username is a common attack vector.

**Expected Detection:** Users with default/incomplete profiles, especially administrators and content authors.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Weak User Passwords" \
  --body "**Purpose:** Detects users with weak passwords that don't meet security best practices.

**What to Test:**
- Check password strength requirements configuration
- Test for common weak passwords (if detectable via settings)
- Verify password expiration policies
- Check for password reuse prevention

**Why It Matters:** Weak passwords are the #1 entry point for brute force attacks. WordPress default password strength is often ignored by users.

**Expected Detection:** Administrator and editor accounts without strong password policies.

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Unused Administrator Accounts" \
  --body "**Purpose:** Identifies administrator accounts that haven't logged in recently or are potentially abandoned.

**What to Test:**
- Query users with 'administrator' role
- Check last login timestamp (via user meta or plugins)
- Identify accounts created but never activated
- Flag accounts with no content creation history

**Why It Matters:** Unused admin accounts are security liabilities. Compromised old admin accounts can give attackers full site access without raising immediate suspicion.

**Expected Detection:** Admin accounts with no recent activity (90+ days), test accounts, former employee accounts.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Email Verification Status" \
  --body "**Purpose:** Checks if user registration requires email verification and tests for unverified accounts.

**What to Test:**
- Verify email confirmation is enabled for new registrations
- Check for pending/unverified user accounts
- Test email deliverability for verification emails
- Validate email verification timeout settings

**Why It Matters:** Unverified emails allow spam accounts, fake registrations, and reduce data quality for communications.

**Expected Detection:** Sites allowing instant registration without verification, accumulated unverified accounts.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Session Security" \
  --body "**Purpose:** Validates user session management, timeout settings, and concurrent session handling.

**What to Test:**
- Check session timeout configuration
- Test for concurrent session limits
- Verify secure session cookies (httponly, secure flags)
- Check for session fixation vulnerabilities

**Why It Matters:** Insecure session management allows session hijacking, unauthorized access, and credential theft.

**Expected Detection:** Long session timeouts (>24hrs), missing security flags, unlimited concurrent sessions.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Two-Factor Authentication Status" \
  --body "**Purpose:** Checks if 2FA is enabled and enforced for privileged user accounts.

**What to Test:**
- Detect 2FA plugin presence and configuration
- Verify 2FA enforcement for administrator roles
- Check 2FA backup method availability
- Test 2FA recovery process

**Why It Matters:** 2FA reduces account compromise risk by 99.9%. Admin accounts without 2FA are vulnerable to password theft and phishing.

**Expected Detection:** Sites without 2FA for admins, optional (not enforced) 2FA policies.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Enumeration Vulnerability" \
  --body "**Purpose:** Tests if site exposes user information through author archives, REST API, or login error messages.

**What to Test:**
- Check if /?author=1 redirects reveal usernames
- Test REST API /wp-json/wp/v2/users endpoint accessibility
- Verify login error messages don't confirm username existence
- Check author archive accessibility for non-authors

**Why It Matters:** User enumeration helps attackers build targeted brute force lists. Knowing valid usernames reduces attack complexity by 50%.

**Expected Detection:** Default WordPress user enumeration vectors exposed, especially administrator usernames.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Capability Auditing" \
  --body "**Purpose:** Reviews if users have appropriate capabilities for their assigned roles without excessive permissions.

**What to Test:**
- Map each user's capabilities against their role
- Detect custom capabilities added to standard roles
- Flag users with 'delete_users' or 'edit_users' capabilities
- Check for capability escalation via plugins

**Why It Matters:** Excessive capabilities create privilege escalation risks. A compromised editor with admin-level capabilities can destroy a site.

**Expected Detection:** Editors with publish_pages, contributors with upload_files, authors with manage_options.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Metadata Privacy" \
  --body "**Purpose:** Checks for sensitive information stored in user meta that might be exposed via REST API or queries.

**What to Test:**
- Review user meta fields for PII (social security, addresses, phone)
- Check if sensitive meta is public/private
- Test REST API user endpoint for metadata exposure
- Verify GDPR-sensitive fields are protected

**Why It Matters:** User meta often contains sensitive information. Public REST API exposure violates GDPR and exposes personal data.

**Expected Detection:** PII in user meta without privacy controls, custom fields exposed via REST API.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Default User Role Security" \
  --body "**Purpose:** Validates the default role assigned to new users is appropriately restrictive.

**What to Test:**
- Check Settings > General > New User Default Role setting
- Verify it's not set to 'Administrator' or 'Editor'
- Test registration form role assignment
- Check for role escalation during registration

**Why It Matters:** If default role is Administrator, any registration creates an admin account. This is a critical security flaw.

**Expected Detection:** Default role set to Editor, Administrator, or custom roles with dangerous capabilities.

**Threat Level:** 85" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Login Attempt Limiting" \
  --body "**Purpose:** Checks if site has brute force protection via login attempt limiting.

**What to Test:**
- Detect login limiting plugins (Wordfence, Limit Login Attempts, etc.)
- Check lockout thresholds and duration
- Test if IP-based or username-based limiting is active
- Verify CAPTCHA on login form

**Why It Matters:** Unlimited login attempts enable brute force attacks. Without limiting, attackers can try millions of password combinations.

**Expected Detection:** No login limiting plugin, weak thresholds (>10 attempts), short lockouts (<30min).

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Account Deletion Safety" \
  --body "**Purpose:** Validates user deletion process includes content reassignment and doesn't cause data loss.

**What to Test:**
- Check if WordPress prompts for content reassignment
- Test for orphaned content after user deletion
- Verify author attribution is updated correctly
- Check for broken author links post-deletion

**Why It Matters:** Deleting users without content reassignment breaks author archives, attribution, and can cause 404 errors affecting SEO.

**Expected Detection:** Sites that deleted users without reassignment, orphaned content, broken author archives.

**Threat Level:** 40" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Registration Spam Prevention" \
  --body "**Purpose:** Tests effectiveness of spam prevention measures for user registration.

**What to Test:**
- Check for CAPTCHA or reCAPTCHA on registration
- Verify honeypot fields are implemented
- Test for email domain blacklisting
- Check registration rate limiting per IP

**Why It Matters:** Open registration without spam prevention accumulates thousands of bot accounts, affecting database performance and creating security risks.

**Expected Detection:** Registration forms without CAPTCHA, no rate limiting, accumulation of spam user accounts.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Profile Field Sanitization" \
  --body "**Purpose:** Validates user-editable profile fields properly sanitize input to prevent XSS and injection attacks.

**What to Test:**
- Test bio field for script injection
- Check URL fields for javascript: protocol
- Verify custom profile fields strip dangerous HTML
- Test for SQL injection in user meta updates

**Why It Matters:** User profiles are often overlooked for security. XSS in user profiles can compromise admin sessions when viewing user lists.

**Expected Detection:** Profile fields allowing unsanitized HTML, JavaScript, or malicious content.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Login Notification System" \
  --body "**Purpose:** Checks if administrators receive notifications for new logins, failed attempts, or suspicious activity.

**What to Test:**
- Verify admin login notifications are enabled
- Test notifications for new device/location logins
- Check failed login attempt alerts
- Test notification delivery and recipient configuration

**Why It Matters:** Login notifications provide early warning of account compromise. Without them, unauthorized access can go undetected for days.

**Expected Detection:** No login notifications configured, notifications disabled, alerts not reaching admins.

**Threat Level:** 50" && sleep 2

# CATEGORY 2: Role Management & Permissions (15 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] Custom Role Definition Audit" \
  --body "**Purpose:** Reviews all custom roles created beyond WordPress defaults and validates their necessity and security.

**What to Test:**
- List all roles beyond default 5 (Admin, Editor, Author, Contributor, Subscriber)
- Review custom role capabilities
- Check for duplicate roles with identical permissions
- Verify custom roles are still in use

**Why It Matters:** Custom roles from old plugins or themes often persist after uninstallation with excessive capabilities, creating security holes.

**Expected Detection:** Abandoned plugin roles, duplicate roles, roles with unclear purpose or excessive permissions.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Administrator Role Assignment" \
  --body "**Purpose:** Validates only appropriate users have Administrator role and reviews total admin account count.

**What to Test:**
- Count total administrator accounts
- Flag sites with >5 administrators
- Check if shop managers/coordinators have admin role
- Verify each admin account has a business justification

**Why It Matters:** Each administrator is a full compromise target. Sites often have 10-20+ admins when 2-3 would suffice, expanding attack surface unnecessarily.

**Expected Detection:** Excessive administrator accounts, service accounts with admin role, test accounts still admin.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Editor Capability Scope" \
  --body "**Purpose:** Validates Editor role hasn't been granted dangerous capabilities beyond content management.

**What to Test:**
- Check if Editors can install/activate plugins
- Verify Editors cannot edit PHP files
- Test if Editors can manage users or delete users
- Check for 'edit_theme_options' capability

**Why It Matters:** Editors should manage content, not site configuration. Escalated Editor capabilities turn content managers into de-facto administrators.

**Expected Detection:** Editors with plugin management, theme editing, user management, or system configuration access.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Author Publishing Restrictions" \
  --body "**Purpose:** Validates Author role has appropriate restrictions on what they can publish and modify.

**What to Test:**
- Check if Authors can publish without review
- Verify Authors cannot edit others' content
- Test if Authors have access to sensitive custom post types
- Check Author media upload restrictions (file types, sizes)

**Why It Matters:** Unrestricted Authors can publish malicious content, upload malware, or damage brand reputation without oversight.

**Expected Detection:** Authors with edit_others_posts, publish without moderation, unrestricted file uploads.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Contributor Workflow Controls" \
  --body "**Purpose:** Ensures Contributor role properly requires editorial approval before content publication.

**What to Test:**
- Verify Contributors cannot publish (only submit for review)
- Check if Contributors can upload media (should be restricted)
- Test if Contributors can edit published content
- Verify contribution workflow requires Editor approval

**Why It Matters:** Contributors submitting malicious or low-quality content without review damages SEO, brand trust, and security.

**Expected Detection:** Contributors with publish capability, media upload access, or bypassing editorial workflow.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Subscriber Role Limitations" \
  --body "**Purpose:** Validates Subscriber role has minimal capabilities and cannot perform administrative actions.

**What to Test:**
- Verify Subscribers only have 'read' capability
- Check if Subscribers can access wp-admin beyond profile
- Test if Subscribers can comment without moderation
- Verify Subscribers cannot upload media or create posts

**Why It Matters:** Subscribers are often compromised accounts. Excessive capabilities allow spam, comment manipulation, or reconnaissance of site structure.

**Expected Detection:** Subscribers with content creation, media upload, or administrative access.

**Threat Level:** 45" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Shop Manager Role Security (WooCommerce)" \
  --body "**Purpose:** For WooCommerce sites, validates Shop Manager role doesn't have excessive capabilities beyond store management.

**What to Test:**
- Check if Shop Managers have plugin/theme management access
- Verify Shop Managers cannot delete users
- Test if Shop Managers can access non-WooCommerce settings
- Review custom capabilities added to Shop Manager role

**Why It Matters:** Shop Managers need store access, not full site access. Compromised shop accounts shouldn't threaten entire site security.

**Expected Detection:** Shop Managers with administrator-level capabilities, user management, or plugin control.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Customer Role Capabilities (WooCommerce)" \
  --body "**Purpose:** Validates WooCommerce Customer role is restricted to order management and profile updates.

**What to Test:**
- Verify Customers cannot access wp-admin
- Check if Customers only see 'My Account' frontend interface
- Test Customer permission to view other customers' data
- Validate Customer download/order history access is properly scoped

**Why It Matters:** Customer accounts are frequently compromised. Excessive permissions enable customer data theft or order manipulation.

**Expected Detection:** Customers with wp-admin access, ability to view other customers' orders, or administrative capabilities.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Role Capability Inheritance" \
  --body "**Purpose:** Validates role capability inheritance makes sense and doesn't create unexpected permission gaps or overlaps.

**What to Test:**
- Map capability inheritance hierarchy
- Check for capabilities granted to lower roles but not higher
- Verify plugin-added capabilities follow inheritance pattern
- Test for circular capability dependencies

**Why It Matters:** Broken capability inheritance creates confusion, security gaps, and workflow problems. Contributor shouldn't have capabilities Author lacks.

**Expected Detection:** Illogical capability inheritance, capabilities skipping role levels.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Plugin-Modified Role Capabilities" \
  --body "**Purpose:** Tracks which plugins have modified default WordPress role capabilities and validates changes are appropriate.

**What to Test:**
- Compare current role capabilities to WordPress defaults
- Identify plugins that modified capabilities (via code analysis)
- Check if capability changes persist after plugin deactivation
- Review legitimacy of each capability modification

**Why It Matters:** Plugins often add dangerous capabilities to roles. After plugin removal, these capabilities may persist, creating security holes.

**Expected Detection:** Abandoned plugin capabilities, excessive permissions from deactivated plugins.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Multisite Super Admin Scope" \
  --body "**Purpose:** For multisite, validates Super Admin capabilities are appropriately restricted and audited.

**What to Test:**
- Count total Super Admin accounts
- Check if site-level admins have Super Admin unnecessarily
- Verify Super Admin actions are logged
- Test Super Admin access to individual site data

**Why It Matters:** Super Admins have network-wide control. Excessive Super Admins multiply the risk of network-wide compromise.

**Expected Detection:** Too many Super Admins (>3 on small networks), site admins granted Super Admin role.

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Role Creation Plugin Security" \
  --body "**Purpose:** Audits plugins that create or modify roles (Members, User Role Editor, etc.) for security best practices.

**What to Test:**
- Identify role management plugins installed
- Check if role changes require re-authentication
- Verify role modifications are logged
- Test if plugins validate capability assignments

**Why It Matters:** Role management plugins are powerful tools. Misconfiguration or vulnerabilities can grant unauthorized users full site access.

**Expected Detection:** Role plugins with weak security, no audit logging, or allowing dangerous capability combinations.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Temporary Role Elevation" \
  --body "**Purpose:** Detects if temporary role elevation is used and validates proper de-elevation occurs.

**What to Test:**
- Check for plugins providing temporary admin access
- Verify temporary elevation has expiration timestamps
- Test if elevated users automatically revert to original role
- Review temporary elevation audit trail

**Why It Matters:** Temporary admin access that doesn't expire creates permanent security vulnerabilities. Forgotten elevated accounts are common attack targets.

**Expected Detection:** Temporary elevations without expiration, failed automatic de-elevation, no audit trail.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Role-Based Content Access" \
  --body "**Purpose:** Validates role-based content restrictions are properly enforced across frontend and REST API.

**What to Test:**
- Test if content marked for specific roles is properly restricted
- Check REST API enforces same role restrictions as frontend
- Verify role checks happen on both query and display
- Test for role bypass via direct URL access

**Why It Matters:** Content access controls that only work on the frontend can be bypassed via REST API or direct URL, exposing restricted content.

**Expected Detection:** Role restrictions only enforced on frontend, REST API exposing restricted content, direct URL bypass.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Unassigned User Accounts" \
  --body "**Purpose:** Identifies user accounts without any assigned role (orphaned accounts).

**What to Test:**
- Query for users with no assigned role
- Check if orphaned accounts can log in
- Test what capabilities orphaned accounts have
- Verify orphaned accounts aren't service/API accounts

**Why It Matters:** Users without roles are in an undefined state. They may have no capabilities, or worse, might inherit unexpected permissions.

**Expected Detection:** Accounts from plugin removal, bulk import errors, or database corruption without role assignments.

**Threat Level:** 55" && sleep 2

# CATEGORY 3: Multi-Author & Collaboration (10 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] Post Revision Conflict Detection" \
  --body "**Purpose:** Detects simultaneous editing conflicts when multiple authors work on the same content.

**What to Test:**
- Check for post locking mechanism status
- Test if edit warnings appear for concurrent editing
- Verify revision history shows all author contributions
- Check autosave conflict resolution

**Why It Matters:** Without proper locking, multiple authors overwrite each other's work, causing content loss and workflow frustration.

**Expected Detection:** Disabled or broken post locking, missing concurrent edit warnings, revision conflicts.

**Threat Level:** 45" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Author Attribution Accuracy" \
  --body "**Purpose:** Validates post author attribution is accurate and hasn't been corrupted or manipulated.

**What to Test:**
- Check for posts with deleted/invalid author IDs
- Verify author archives match actual post authors
- Test co-author plugin functionality if installed
- Check for author ID conflicts after user merges

**Why It Matters:** Incorrect author attribution damages credibility, breaks author archives, and causes SEO issues with author rich snippets.

**Expected Detection:** Orphaned posts (author ID 0 or deleted users), author archive mismatches, co-author plugin errors.

**Threat Level:** 40" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Editorial Workflow Plugin Status" \
  --body "**Purpose:** For sites using editorial workflow plugins, validates the workflow is properly configured and functional.

**What to Test:**
- Detect Edit Flow, PublishPress, or similar plugins
- Check workflow stage definitions and assignments
- Verify notification emails are being sent
- Test if workflow prevents accidental publishing

**Why It Matters:** Broken editorial workflows cause content to bypass review, miss publication deadlines, or get stuck in limbo.

**Expected Detection:** Workflow stages without assigned editors, broken notifications, bypassed approval steps.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Content Collaboration Permissions" \
  --body "**Purpose:** Validates permissions for content collaboration features like comments on drafts, editorial notes, etc.

**What to Test:**
- Check if Authors can comment on others' drafts
- Verify editorial comment permissions
- Test private editorial note visibility
- Check collaboration feature capability requirements

**Why It Matters:** Improper collaboration permissions expose draft content or editorial discussions to unauthorized users.

**Expected Detection:** Contributors seeing editorial notes, Authors accessing Editor-only comments, leaked draft discussions.

**Threat Level:** 50" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Activity Tracking" \
  --body "**Purpose:** Checks if user activity (logins, edits, deletions) is properly logged for accountability.

**What to Test:**
- Detect activity logging plugins (Simple History, WP Activity Log)
- Verify critical actions are logged (post deletion, user creation)
- Check log retention policies
- Test log accessibility and export functionality

**Why It Matters:** Without activity logs, investigating security incidents or content disputes is impossible. Logs provide accountability.

**Expected Detection:** No activity logging enabled, incomplete logging, logs not retained, no access to historical data.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Draft Visibility Controls" \
  --body "**Purpose:** Validates draft and scheduled content isn't accidentally visible to unauthorized users.

**What to Test:**
- Check if drafts appear in search results
- Test REST API draft exposure to non-authors
- Verify scheduled posts don't appear before publish date
- Check preview link security (preview tokens)

**Why It Matters:** Exposed drafts leak embargoed content, competitive information, or incomplete work, damaging credibility and business interests.

**Expected Detection:** Drafts in search, REST API exposing drafts to public, guessable preview links, scheduled posts visible early.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Content Approval Workflow Bypass" \
  --body "**Purpose:** Tests if users can bypass editorial approval workflows through various methods.

**What to Test:**
- Check if status can be changed via URL manipulation
- Test REST API for workflow bypass possibilities
- Verify direct database edits are prevented
- Check if scheduled posts bypass approval

**Why It Matters:** Workflow bypasses allow unapproved content publication, defeating the purpose of editorial review and quality control.

**Expected Detection:** Status manipulation via REST API, scheduled post bypass, direct post_status changes.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Guest Author Management" \
  --body "**Purpose:** For sites using guest author features, validates guest attribution is properly managed.

**What to Test:**
- Detect guest author plugins (Co-Authors Plus, etc.)
- Verify guest authors don't have actual user accounts
- Check if guest author archives work correctly
- Test guest author rich snippet data

**Why It Matters:** Guest authors without proper management create duplicate accounts, broken archives, and SEO issues.

**Expected Detection:** Guest authors with unnecessary user accounts, broken guest archives, missing guest author metadata.

**Threat Level:** 35" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Content Ownership Disputes" \
  --body "**Purpose:** Detects posts with unclear ownership due to author changes, co-authors, or deletion.

**What to Test:**
- Check for posts with modified author history
- Verify audit trail of author changes exists
- Test for posts claimed by multiple co-author accounts
- Check for posts without clear primary author

**Why It Matters:** Ownership disputes create legal issues, content management confusion, and attribution problems affecting credibility.

**Expected Detection:** Posts with suspicious author changes, missing change audit trail, ambiguous co-author relationships.

**Threat Level:** 40" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Team Member Onboarding Process" \
  --body "**Purpose:** Validates new team member onboarding includes proper role assignment, training, and capability setup.

**What to Test:**
- Check if new users start with appropriate restrictive roles
- Verify welcome emails are sent to new users
- Test if onboarding documentation is provided
- Check for default content/training posts for new authors

**Why It Matters:** Poor onboarding leads to security mistakes, workflow errors, and frustrated team members making costly content errors.

**Expected Detection:** New users immediately granted high-level roles, no welcome emails, missing documentation.

**Threat Level:** 40" && sleep 2

# CATEGORY 4: User Data & Privacy (10 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] GDPR User Data Export Functionality" \
  --body "**Purpose:** Validates WordPress GDPR data export functionality works correctly for user data requests.

**What to Test:**
- Test Tools > Export Personal Data functionality
- Verify all user data types are included in export
- Check export includes custom user meta and plugin data
- Test export file generation and download

**Why It Matters:** GDPR requires functional data export within 30 days. Broken export functionality creates legal compliance issues and fines.

**Expected Detection:** Broken export functionality, incomplete data in exports, missing custom user meta, export failures.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] GDPR User Data Erasure Functionality" \
  --body "**Purpose:** Validates WordPress GDPR data erasure (right to be forgotten) works correctly.

**What to Test:**
- Test Tools > Erase Personal Data functionality
- Verify user data is actually erased (not just anonymized incorrectly)
- Check if erasure includes plugin-stored user data
- Test for orphaned data after erasure

**Why It Matters:** GDPR right to be forgotten is legally required. Incomplete erasure creates privacy violations and potential fines up to €20M.

**Expected Detection:** Broken erasure functionality, incomplete data removal, plugin data not erased, orphaned personal data.

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Consent Tracking" \
  --body "**Purpose:** Checks if user consents (cookies, privacy policy, terms) are properly tracked and stored.

**What to Test:**
- Detect cookie consent plugins and configuration
- Check if consent timestamps are stored per user
- Verify consent can be withdrawn
- Test consent audit trail for compliance

**Why It Matters:** GDPR requires documented proof of consent. Without tracking, sites cannot prove compliance and face regulatory penalties.

**Expected Detection:** No consent tracking, missing consent timestamps, inability to prove consent for specific actions.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Data Retention Policies" \
  --body "**Purpose:** Validates user data is not retained longer than necessary per privacy policies.

**What to Test:**
- Check if inactive user data is automatically deleted
- Verify data retention policy is documented
- Test for accounts inactive >2 years still in database
- Check if transactional data has retention limits

**Why It Matters:** Indefinite data retention violates GDPR principles and increases breach exposure. Every retained user account is a liability.

**Expected Detection:** No automatic data deletion, indefinite retention, thousands of inactive user accounts, missing retention policy.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User IP Address Logging" \
  --body "**Purpose:** Checks if user IP addresses are being logged and if logging complies with privacy regulations.

**What to Test:**
- Identify plugins logging IP addresses (comments, forms, security)
- Verify IP logging has legitimate purpose documented
- Check if IPs are anonymized for non-security purposes
- Test IP data retention periods

**Why It Matters:** IP addresses are personal data under GDPR. Unnecessary logging creates privacy violations and increases breach risk.

**Expected Detection:** Excessive IP logging, long-term storage without justification, non-anonymized IPs, missing disclosure in privacy policy.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Third-Party Data Sharing Disclosure" \
  --body "**Purpose:** Validates privacy policy accurately discloses all third-party services receiving user data.

**What to Test:**
- Identify all external APIs called during user sessions
- Check for tracking scripts (Google Analytics, Facebook Pixel, etc.)
- Verify privacy policy lists all third-party services
- Test for undisclosed data sharing via plugins

**Why It Matters:** Undisclosed data sharing violates privacy laws. Users must be informed about all third parties receiving their data.

**Expected Detection:** Third-party scripts not disclosed in privacy policy, plugin data sharing not documented, analytics tools not mentioned.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Profile Data Encryption" \
  --body "**Purpose:** Checks if sensitive user data in database is encrypted at rest.

**What to Test:**
- Review database for plaintext sensitive data (SSN, DOB, etc.)
- Check if custom user meta uses encryption
- Verify payment/financial data is not stored or is encrypted
- Test encryption key management

**Why It Matters:** Unencrypted sensitive data is immediately exposed in database breaches. Encryption is required for PCI-DSS and recommended for GDPR.

**Expected Detection:** Plaintext sensitive data in user meta, unencrypted custom fields, poor key management.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Privacy Policy Page Configuration" \
  --body "**Purpose:** Validates privacy policy page is properly configured, accessible, and current.

**What to Test:**
- Check Settings > Privacy > Privacy Policy page is set
- Verify privacy policy page is published and accessible
- Test if privacy policy date is recent (<1 year)
- Check if privacy policy is linked in footer/registration

**Why It Matters:** Legal requirement in most jurisdictions. Missing or outdated privacy policy creates compliance issues and erodes user trust.

**Expected Detection:** No privacy policy page set, page unpublished, policy not updated for >1 year, missing footer link.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] User Data Breach Response Plan" \
  --body "**Purpose:** Checks if site has documented breach response procedures for user data incidents.

**What to Test:**
- Look for breach response documentation
- Check if security plugin has breach detection
- Verify notification procedures are defined
- Test if breach timeline requirements are documented (72hr EU)

**Why It Matters:** GDPR requires breach notification within 72 hours. Without a plan, sites miss legal deadlines and increase penalties.

**Expected Detection:** No documented breach response plan, no breach detection, undefined notification procedures.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Anonymous User Data Collection" \
  --body "**Purpose:** Validates anonymous data collection (analytics, usage stats) is properly anonymized.

**What to Test:**
- Check Google Analytics anonymization settings
- Verify comment IP addresses can be anonymized
- Test if analytics cookies comply with consent requirements
- Check for user tracking without consent

**Why It Matters:** Non-anonymized analytics violate GDPR without consent. Tracking without consent creates privacy violations.

**Expected Detection:** Google Analytics without anonymization, tracking without consent, identifiable user tracking.

**Threat Level:** 60" && sleep 2

# CATEGORY 5: Access Control & Authentication (10 diagnostics)
gh issue create --repo "$REPO" --title "[Diagnostic] Login Page Customization Security" \
  --body "**Purpose:** Validates custom login pages don't introduce security vulnerabilities.

**What to Test:**
- Check if login page is moved from /wp-admin
- Test custom login forms for CSRF protection
- Verify custom login implements nonce checking
- Check if custom login bypasses core security

**Why It Matters:** Custom login pages often lack WordPress core security features, creating authentication bypass vulnerabilities.

**Expected Detection:** Custom login without CSRF protection, missing nonces, bypassed core authentication checks.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Password Reset Process Security" \
  --body "**Purpose:** Validates password reset process is secure and cannot be exploited.

**What to Test:**
- Check password reset token strength and expiration
- Test for user enumeration via reset form
- Verify reset emails contain secure tokens
- Check if old password is invalidated after reset

**Why It Matters:** Weak password reset allows account takeover. Guessable tokens or no expiration enables mass account compromise.

**Expected Detection:** Weak reset tokens, no expiration, user enumeration, old passwords still valid.

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Remember Me Cookie Security" \
  --body "**Purpose:** Validates 'Remember Me' functionality uses secure cookies with appropriate expiration.

**What to Test:**
- Check remember me cookie expiration duration (default 14 days)
- Verify cookies use Secure and HttpOnly flags
- Test if remember me tokens are rotated
- Check for remember me token invalidation on logout

**Why It Matters:** Long-lived remember me cookies are theft targets. Stolen cookies grant persistent access without requiring passwords.

**Expected Detection:** Remember me cookies >30 days, missing security flags, no token rotation, logout doesn't invalidate tokens.

**Threat Level:** 60" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Authentication Cookie Hijacking Prevention" \
  --body "**Purpose:** Tests for session fixation and cookie hijacking vulnerabilities.

**What to Test:**
- Verify auth cookies regenerate after login
- Check if cookies are IP-bound
- Test for user agent validation
- Verify cookies are invalidated on logout

**Why It Matters:** Session fixation and cookie hijacking enable attackers to impersonate users without knowing passwords.

**Expected Detection:** Auth cookies not regenerated, no IP binding, missing user agent checks, persistent cookies after logout.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Login Page Rate Limiting" \
  --body "**Purpose:** Validates login page has rate limiting to prevent enumeration and brute force attacks.

**What to Test:**
- Check for progressive rate limiting on login attempts
- Test if rate limits are per-IP or per-username
- Verify rate limit effectiveness (response timing)
- Check for rate limit bypass methods

**Why It Matters:** Without rate limiting, attackers can test millions of credentials rapidly. Rate limiting reduces brute force effectiveness by 99%.

**Expected Detection:** No rate limiting, insufficient rate limits (>10/min), easy bypass methods, timing attacks possible.

**Threat Level:** 75" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] API Authentication Strength" \
  --body "**Purpose:** Validates REST API and application passwords use strong authentication methods.

**What to Test:**
- Check if application passwords are enabled and managed
- Verify API endpoints require authentication
- Test for weak API keys in public code
- Check API rate limiting per authentication

**Why It Matters:** Weak API authentication enables automated attacks, data scraping, and unauthorized API usage affecting site performance.

**Expected Detection:** API endpoints without authentication, weak application passwords, exposed API keys, no API rate limiting.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] OAuth/SSO Integration Security" \
  --body "**Purpose:** For sites using OAuth or SSO, validates integration is secure and properly configured.

**What to Test:**
- Check OAuth client secret storage (not in code)
- Verify OAuth redirect URI whitelist
- Test for OAuth state parameter implementation (CSRF)
- Check SSO fallback authentication method

**Why It Matters:** OAuth misconfigurations allow account takeover via redirect manipulation and CSRF attacks.

**Expected Detection:** Client secrets in code, missing redirect URI whitelist, no state parameter, insecure fallback.

**Threat Level:** 70" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Magic Link Authentication Security" \
  --body "**Purpose:** For sites using passwordless magic link authentication, validates security best practices.

**What to Test:**
- Check magic link token strength and randomness
- Verify link expiration time (<15 minutes recommended)
- Test for single-use token enforcement
- Check email deliverability for magic links

**Why It Matters:** Weak magic links or long expiration allows link interception and account takeover. Reusable tokens enable persistent access.

**Expected Detection:** Weak tokens, long expiration (>30min), reusable tokens, no single-use enforcement.

**Threat Level:** 65" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Inactive User Account Locking" \
  --body "**Purpose:** Checks if inactive user accounts are automatically locked or flagged for review.

**What to Test:**
- Test for automatic account locking after inactivity period
- Check if locked accounts require admin reactivation
- Verify locking notifications are sent
- Test for inactive account reporting

**Why It Matters:** Inactive accounts are prime targets for compromise. Automated locking reduces attack surface from abandoned accounts.

**Expected Detection:** No automatic locking, accounts inactive >1 year still active, no inactivity monitoring.

**Threat Level:** 55" && sleep 2

gh issue create --repo "$REPO" --title "[Diagnostic] Login Page Accessibility" \
  --body "**Purpose:** Validates login page is accessible to users with disabilities (WCAG compliance).

**What to Test:**
- Check for proper form labels and ARIA attributes
- Verify keyboard navigation works completely
- Test screen reader compatibility
- Check color contrast on login form

**Why It Matters:** Inaccessible login pages violate ADA/accessibility laws and prevent users from accessing accounts, creating legal liability.

**Expected Detection:** Missing form labels, keyboard navigation broken, poor color contrast, screen reader issues.

**Threat Level:** 50" && sleep 2

echo ""
echo "=== User Management & Roles Diagnostics Complete ==="
echo "Total Created: 60 diagnostics"
echo ""
echo "Categories:"
echo "  • User Profile & Account Security: 15"
echo "  • Role Management & Permissions: 15"
echo "  • Multi-Author & Collaboration: 10"
echo "  • User Data & Privacy: 10"
echo "  • Access Control & Authentication: 10"
