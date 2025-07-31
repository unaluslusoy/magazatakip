# Git Configuration Guide

## 🔑 GitHub Authentication Setup

### Personal Access Token Configuration
1. **GitHub Settings**: https://github.com/settings/tokens
2. **Permissions needed**:
   - ✅ `repo` - Full control of private repositories
   - ✅ `workflow` - Update GitHub Action workflows

### Git Remote Setup
```bash
# Template (replace YOUR_TOKEN with actual token):
git remote set-url origin https://YOUR_TOKEN@github.com/unaluslusoy/magazatakip.git
```

## 🚀 Daily Git Operations

### Push Changes
```bash
git add .
git commit -m "Your commit message"
git push origin main
```

### Version Tagging
```bash
git tag -a v1.x.x -m "Version 1.x.x: Description"
git push origin --tags
```

### Check Status
```bash
git status
git log --oneline -5
git remote -v
```

## 📋 Repository Information
- **User**: `unaluslusoy`
- **Repository**: `magazatakip`
- **Main Branch**: `main`
- **Current Version**: `v1.2.0`

## ⚠️ Security Note
- Never commit actual tokens to repository
- Keep tokens in secure environment variables
- Regenerate tokens periodically for security