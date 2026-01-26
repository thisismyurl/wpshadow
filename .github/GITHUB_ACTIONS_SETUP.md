# GitHub Actions E2E Testing Setup

## ✅ What's Been Created

Two GitHub Actions workflows for automated E2E testing:

1. **`.github/workflows/e2e-tests.yml`**
   - Runs on every push to `main` or `develop`
   - Runs on every pull request
   - Can be triggered manually

2. **`.github/workflows/e2e-tests-scheduled.yml`**
   - Runs daily at 2 AM UTC
   - Can be triggered manually
   - Creates GitHub issue if tests fail

## 🔐 Required: GitHub Secrets Setup

You need to add these secrets to your GitHub repository:

### 1. Navigate to Repository Settings

```
GitHub Repository → Settings → Secrets and variables → Actions → New repository secret
```

### 2. Add These Secrets

| Secret Name | Example Value | Description |
|-------------|---------------|-------------|
| `WP_BASE_URL` | `https://staging.wpshadow.com` | Your WordPress site URL |
| `WP_ADMIN_USER` | `admin` | WordPress admin username |
| `WP_ADMIN_PASS` | `your-secure-password` | WordPress admin password |

### How to Add Each Secret:

1. Click **"New repository secret"**
2. **Name:** `WP_BASE_URL`
3. **Value:** Your WordPress URL (e.g., `https://staging.wpshadow.com`)
4. Click **"Add secret"**
5. Repeat for `WP_ADMIN_USER` and `WP_ADMIN_PASS`

## 🚀 How It Works

### On Every Push/PR:
```
1. Code pushed to GitHub
2. GitHub Actions starts
3. Installs Node.js and Playwright
4. Creates .env file with your secrets
5. Runs all 31 E2E tests
6. Uploads test reports (success or failure)
7. Comments on PR with results
```

### On Schedule (Daily):
```
1. Runs at 2 AM UTC every day
2. Tests production/staging environment
3. If tests fail → Creates GitHub issue automatically
4. Uploads reports for investigation
```

### Manual Trigger:
```
1. Go to: Actions → E2E Tests → Run workflow
2. Click "Run workflow" button
3. Tests run immediately
```

## 📊 Viewing Test Results

### After Workflow Runs:

1. **Go to Actions tab** in your repository
2. **Click on the workflow run**
3. **Scroll to "Artifacts" section** at bottom
4. **Download "playwright-report"** (contains full HTML report)
5. **Extract and open `index.html`** in browser

### What You'll See:
- ✅ Pass/fail status for all 31 tests
- 📸 Screenshots of failures
- 🎥 Videos of failed test runs
- 🌐 Network logs
- 📝 Console errors
- ⏱️ Test duration

## 🎯 Test Workflow Triggers

### Automatic Triggers:
- ✅ Push to `main` branch
- ✅ Push to `develop` branch
- ✅ Pull request to `main` or `develop`
- ✅ Daily at 2 AM UTC (scheduled)

### Manual Triggers:
- ✅ Actions tab → Select workflow → "Run workflow"

### Customize Triggers:
Edit `.github/workflows/e2e-tests.yml`:

```yaml
on:
  push:
    branches: [ main, develop, staging ]  # Add more branches
  pull_request:
    branches: [ main ]
  schedule:
    - cron: '0 */6 * * *'  # Every 6 hours instead of daily
```

## 🐛 Troubleshooting

### Tests Fail in CI but Pass Locally

**Possible causes:**
1. **Different WordPress versions** - CI might test against staging/production
2. **Timing issues** - CI might be slower, increase timeouts
3. **Environment differences** - Check secrets are correct

**Solution:**
```javascript
// In test files, increase timeouts
test.setTimeout(120000); // 2 minutes instead of 60 seconds
```

### "Cannot reach WordPress site"

**Check:**
1. Is `WP_BASE_URL` secret set correctly?
2. Is the site publicly accessible?
3. Are there IP restrictions blocking GitHub Actions?

**Solution:**
- Make sure staging site is publicly accessible
- Or whitelist GitHub Actions IPs
- Or use VPN in GitHub Actions (advanced)

### "Login failed"

**Check:**
1. Are `WP_ADMIN_USER` and `WP_ADMIN_PASS` correct?
2. Is two-factor auth disabled for test user?
3. Are there CAPTCHA challenges?

**Solution:**
- Create dedicated test admin user
- Disable 2FA for test user
- Disable CAPTCHA for test environment

### Tests timeout

**Solution:**
```yaml
# In workflow file, increase timeout
jobs:
  e2e-tests:
    timeout-minutes: 60  # Increase from 30 to 60
```

## 📈 Best Practices

### 1. Use Staging Environment
Don't test against production! Use staging:
```
WP_BASE_URL=https://staging.wpshadow.com
```

### 2. Create Dedicated Test User
```
Username: e2e-tester
Password: <strong-password>
Role: Administrator
Purpose: Automated testing only
```

### 3. Review Test Reports
After each run:
- Download artifacts
- Review failed tests
- Fix issues before merging

### 4. Don't Commit Secrets
Never commit:
- ❌ `.env` files with real credentials
- ❌ Passwords in code
- ❌ API keys

Always use:
- ✅ GitHub Secrets
- ✅ `.env.example` templates
- ✅ Environment variables

## 🔒 Security Considerations

### Secrets Are:
- ✅ Encrypted at rest
- ✅ Only accessible during workflow runs
- ✅ Never exposed in logs
- ✅ Can be rotated anytime

### Best Practices:
1. Use strong passwords
2. Rotate credentials periodically
3. Use least-privilege accounts
4. Monitor workflow runs
5. Review test user activity

## 📝 Next Steps

### 1. Add Secrets (Required)
```
Repository → Settings → Secrets → Add:
- WP_BASE_URL
- WP_ADMIN_USER  
- WP_ADMIN_PASS
```

### 2. Push to Trigger First Run
```bash
git add .github/
git commit -m "Add E2E testing workflows"
git push origin main
```

### 3. Watch First Run
```
1. Go to Actions tab
2. Click on "E2E Tests" workflow
3. Watch it run live
4. Download report when complete
```

### 4. View Results
```bash
# Download playwright-report.zip from Actions
unzip playwright-report.zip
open index.html
```

## 🎉 You're Ready!

Your E2E tests will now:
- ✅ Run automatically on every commit
- ✅ Test every pull request
- ✅ Run daily health checks
- ✅ Create issues when tests fail
- ✅ Generate detailed reports

**Next:** Add secrets and push to trigger your first automated test run! 🚀
