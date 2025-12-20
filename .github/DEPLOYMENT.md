# GitHub Actions Setup Guide

## 📋 What's Included

### 1. **CI Pipeline** (`.github/workflows/ci.yml`)

Runs on every push and PR:

-   ✅ Runs PHPUnit tests
-   ✅ Code style check with Laravel Pint
-   ✅ Sets up PostgreSQL for testing
-   ✅ Runs migrations

### 2. **Deployment Pipeline** (`.github/workflows/deploy.yml`)

Deploys to EC2 on push to `master`/`main`:

-   🚀 SSH into EC2
-   📦 Pulls latest code
-   📦 Installs dependencies
-   🔄 Runs migrations
-   ⚡ Clears/caches config
-   🔁 Restarts services

---

## 🔧 Setup Instructions

### Step 1: Add GitHub Secrets

Go to your repo: **Settings → Secrets and variables → Actions → New repository secret**

Add these secrets:

| Secret Name    | Value                | Example                         |
| -------------- | -------------------- | ------------------------------- |
| `EC2_HOST`     | Your EC2 IP address  | `3.142.199.233`                 |
| `EC2_USERNAME` | SSH username         | `ubuntu` or `ec2-user`          |
| `EC2_SSH_KEY`  | Your private SSH key | Copy from `~/.ssh/your-key.pem` |

**To get your SSH key:**

```bash
cat ~/.ssh/your-ec2-key.pem
```

Copy the entire output (including `-----BEGIN RSA PRIVATE KEY-----`)

---

### Step 2: Update Deployment Script (if needed)

Edit `.github/workflows/deploy.yml` if your paths differ:

```yaml
script: |
    cd /var/www/api-inventory-tracking  # ← Change this to your actual path
```

---

### Step 3: Commit and Push

```bash
git add .github/
git commit -m "Add CI/CD pipeline"
git push
```

---

## 🎯 How It Works

### On Every Push/PR:

```
GitHub Push → Run Tests → Check Code Style → ✅ or ❌
```

### On Push to Master:

```
Push to Master → Run Tests → Deploy to EC2 → Restart Services → ✅ Live!
```

---

## 🐛 Troubleshooting

### Deployment fails with "Permission denied"

-   Make sure your SSH key is correct
-   Check EC2 security group allows SSH (port 22)
-   Verify the deployment path exists on EC2

### Tests fail

-   Check database connection in `phpunit.xml`
-   Ensure all migrations run successfully
-   Run `php artisan test` locally first

### SSH Key Issues

```bash
# Generate new key if needed
ssh-keygen -t rsa -b 4096 -C "github-actions"

# Add public key to EC2
cat ~/.ssh/id_rsa.pub  # Copy this to EC2 ~/.ssh/authorized_keys
```

---

## 📝 Notes

-   Deployment only runs on `master`/`main` branch
-   Tests run on every branch
-   Make sure your EC2 has git, composer, and PHP installed
-   Update PHP version in CI if you're not using 8.2

---

## 🔒 Security Tips

-   Never commit `.env` files
-   Keep SSH keys in GitHub Secrets only
-   Use deployment keys instead of personal SSH keys
-   Consider adding approval step for production deploys
