---
title: "Adding a User to WordPress"
description: "Learn how to add new users to your WordPress website and configure their roles and permissions"
category: "Getting Started"
date: 2026-01-27T19:20:00Z
updated: 2026-01-27T19:20:00Z
author: "community"
status: "draft"
issue: 1695
---

# Adding a User to WordPress

## Overview

Learn how to add new users to your WordPress website and configure their roles and permissions. This guide covers adding users through the WordPress admin dashboard, assigning roles, and managing user capabilities.

## Table of Contents

1. [Getting Started](#getting-started)
2. [Adding a New User](#adding-a-new-user)
3. [User Roles and Permissions](#user-roles-and-permissions)
4. [Managing User Settings](#managing-user-settings)
5. [Troubleshooting](#troubleshooting)

## Getting Started

Before adding users, understand WordPress user roles:

- **Administrator** - Full access to all features
- **Editor** - Can publish and manage posts
- **Author** - Can write and manage their own posts
- **Contributor** - Can write posts but cannot publish
- **Subscriber** - Can only manage their profile

## Adding a New User

### Step 1: Navigate to Users
1. Log in to your WordPress dashboard
2. Click on **Users** in the left sidebar
3. Click the **Add New** button

### Step 2: Enter User Information
Fill in the following fields:
- **Username** - Login name (cannot be changed later)
- **Email** - User's email address
- **First Name** - Optional
- **Last Name** - Optional
- **Website** - Optional

### Step 3: Set the Password
- **Password** - Enter a strong password or let WordPress generate one
- **Confirm Password** - Re-enter the password
- Consider sending the new user their password securely

### Step 4: Assign a Role
Select the appropriate role from the dropdown menu based on what access level the user needs.

### Step 5: Save
Click the **Add New User** button to create the account.

## User Roles and Permissions

### Administrator
- Full access to all WordPress features
- Can install/manage plugins and themes
- Can manage all users
- Can access settings
- **Use sparingly** for security

### Editor
- Can publish and manage all posts
- Can manage categories and tags
- Cannot access plugins/themes
- Good for content managers

### Author
- Can write and publish their own posts
- Can upload media
- Cannot edit others' posts
- Good for writers/contributors

### Contributor
- Can write posts but cannot publish
- Posts require editor approval
- Cannot upload media
- Good for content submission

### Subscriber
- Minimal access
- Can manage profile
- Good for community members

## Managing User Settings

### Editing a User
1. Go to **Users** > select the user
2. Update any information
3. Click **Update Profile**

### Changing Password
1. Go to **Users** > select the user
2. Scroll to **Account Management**
3. Click **Generate Password** or enter new password
4. Click **Update Profile**

### Deleting a User
1. Go to **Users** > select the user
2. Click **Delete**
3. Choose what to do with their posts
4. Confirm deletion

## Troubleshooting

### User Cannot Log In
- Verify username is correct (case-sensitive)
- Check email address for password reset email
- Confirm user role has login permissions
- Check if user account is still active

### Cannot Send Password Email
- Verify email is configured correctly
- Check spam folder
- Use password reset link option

### User Sees Limited Options
- Verify user role is set correctly
- Check plugin-specific permissions
- Ensure user capabilities are not restricted

## Best Practices

✅ **DO:**
- Use strong passwords for all accounts
- Regularly review user list for inactive accounts
- Assign minimum necessary permissions
- Keep admin accounts secure
- Monitor user activity

❌ **DON'T:**
- Share admin credentials
- Give all users administrator role
- Create accounts for temporary access
- Use generic usernames
- Ignore user management security

## Related Resources

- [WordPress User Roles Documentation](https://wordpress.org/support/article/roles-and-capabilities/)
- [User Management Guide](https://wpshadow.com/kb/user-management)
- [Security Best Practices](https://wpshadow.com/kb/security)

## Questions?

If you have questions about adding users, please comment on issue #1695.

---

**Status:** This article is in draft. Content team will review and enhance with screenshots before publication.
