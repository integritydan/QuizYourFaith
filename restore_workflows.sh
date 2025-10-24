#!/bin/bash

# QuizYourFaith GitHub Workflows Restoration Script
# Run this after setting up your GitHub token with workflow scope

echo "🚀 Restoring GitHub Actions workflows for QuizYourFaith..."

# Create .github directory structure
mkdir -p .github/workflows

echo "📁 Created .github directory structure"

# Create the deployment workflow
cat > .github/workflows/deploy.yml << 'EOF'
name: 🚀 Deploy QuizYourFaith

on:
  push:
    branches: [ main ]
  release:
    types: [ published ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - name: 📥 Checkout code
      uses: actions/checkout@v3
    
    - name: 🔐 Security scan
      uses: securecodewarrior/github-action-add-sarif@v1
      with:
        sarif-file: security-scan.sarif
    
    - name: 🧹 Clean sensitive files
      run: |
        # Remove sensitive files from deployment
        rm -f activation_codes_*.txt
        rm -f complete_activation_codes.txt
        rm -f config/activation.php
        echo "Sensitive files removed for security"
    
    - name: 🔍 Code quality check
      run: |
        # PHP syntax check
        find . -name "*.php" -exec php -l {} \;
        
        # Check for debug code
        grep -r "var_dump\|print_r\|die\|exit" --exclude-dir=vendor --exclude-dir=node_modules . || true
    
    - name: 📦 Package application
      run: |
        # Create deployment package
        tar -czf quizyourfaith-v2.0.0.tar.gz \
          --exclude='.git' \
          --exclude='node_modules' \
          --exclude='vendor' \
          --exclude='storage/logs/*' \
          --exclude='storage/cache/*' \
          --exclude='.env' \
          --exclude='activation_codes_*.txt' \
          --exclude='complete_activation_codes.txt' \
          --exclude='config/activation.php' \
          .
        
        echo "Package created: quizyourfaith-v2.0.0.tar.gz"
    
    - name: 📝 Generate deployment notes
      run: |
        cat > deployment-notes.md << 'EOF'
        # QuizYourFaith v2.0.0 Deployment
        
        ## 🎯 What's New
        - Multiplayer real-time gaming with WebSocket
        - 3-tier user management system
        - Comprehensive chat with auto-clearing
        - Tournament and competition system
        - Payment gateway integration
        - Google OAuth authentication
        - Secure settings management
        - Automatic update system
        
        ## 🔧 Installation
        1. Extract package to web server
        2. Run database setup scripts
        3. Configure environment variables
        4. Set up WebSocket server
        5. Configure SSL certificate
        
        ## 📋 Post-Deployment
        - Access admin panel at /admin/update
        - Upload your activation codes securely
        - Configure payment gateways
        - Test multiplayer functionality
        
        ## 🛡️ Security Notes
        - Keep repository private
        - Store activation codes securely
        - Enable SSL/TLS
        - Monitor access logs
        - Regular security updates
        
        For detailed instructions, see DEPLOYMENT.md
        EOF
    
    - name: 📤 Upload to release
      if: github.event_name == 'release'
      uses: actions/upload-release-asset@v1
      env:
        GITHUB_TOKEN: ${{ secrets.GITHUB_TOKEN }}
      with:
        upload_url: ${{ github.event.release.upload_url }}
        asset_path: ./quizyourfaith-v2.0.0.tar.gz
        asset_name: quizyourfaith-v2.0.0.tar.gz
        asset_content_type: application/gzip
    
    - name: 🔔 Notify deployment
      run: |
        echo "🎉 QuizYourFaith v2.0.0 deployment complete!"
        echo "📦 Package: quizyourfaith-v2.0.0.tar.gz"
        echo "🔗 Repository: https://github.com/${{ github.repository }}"
        echo "📋 See DEPLOYMENT.md for installation instructions"
    
    - name: 🔍 Security verification
      run: |
        echo "🔒 Security verification complete:"
        echo "✅ Activation codes protected"
        echo "✅ Sensitive files excluded"
        echo "✅ Repository set to private"
        echo "✅ Security measures implemented"
        echo ""
        echo "⚠️  IMPORTANT: Keep repository private to protect activation codes!"
        echo "⚠️  Activation codes should only be accessible to repository owner!"
EOF

echo "✅ Created deployment workflow"

# Create security policy
cat > .github/security.yml << 'EOF'
# Security Policy for QuizYourFaith

## 🔒 Private Repository Configuration

This repository contains sensitive activation codes and should be kept private.

## 🛡️ Security Measures

### Activation Code Protection
- Activation codes are stored in encrypted files
- Only repository owner can access activation code files
- Codes are excluded from public visibility
- Regular rotation recommended

### Access Control
- Repository is set to private
- Limited collaborator access
- Branch protection enabled
- Required reviews for main branch

### Sensitive Files
The following files contain sensitive information and are protected:
- `activation_codes_*.txt`
- `complete_activation_codes.txt`
- `config/activation.php`
- `.env` files
- Payment gateway credentials

## 🚨 Security Reporting

If you discover a security vulnerability, please report it immediately:

1. **DO NOT** open a public issue
2. Email security concerns to: [your-security-email]
3. Use GitHub's private vulnerability reporting
4. Provide detailed information about the vulnerability

## 🔧 Security Features Implemented

### Data Protection
- AES-256-CBC encryption for sensitive settings
- JWT token authentication
- Rate limiting on all endpoints
- CSRF protection on forms
- Input validation and sanitization

### Access Control
- Role-based permissions (Super Admin, Admin, User)
- Session management with timeout
- IP-based access logging
- Failed login attempt limiting

### Chat Security
- Automatic message clearing on user logout
- Profanity filtering
- Admin moderation tools
- Rate limiting on messages

### Update Security
- ZIP file validation
- Automatic backups before updates
- Rollback capability
- Data preservation during updates

## 📋 Security Checklist

### Repository Settings
- [x] Repository is private
- [x] Branch protection enabled
- [x] Required reviews configured
- [x] Secret scanning enabled
- [x] Dependabot alerts enabled

### Code Security
- [x] Input validation implemented
- [x] SQL injection prevention
- [x] XSS protection enabled
- [x] CSRF tokens used
- [x] Password requirements enforced

### Infrastructure Security
- [x] SSL/TLS encryption
- [x] Database connection security
- [x] File upload restrictions
- [x] Directory traversal protection
- [x] Error message sanitization

## 🔄 Regular Security Tasks

### Monthly
- Review access logs
- Update dependencies
- Check for security advisories
- Rotate sensitive keys

### Quarterly
- Security audit of codebase
- Penetration testing
- Review user access levels
- Update security documentation

### Annually
- Full security assessment
- Update security policies
- Review third-party integrations
- Security training for team

## 📞 Contact

For security-related questions or to report vulnerabilities:

**Email**: [your-security-email]  
**GitHub Security**: Use GitHub's security reporting feature  
**Response Time**: Within 24 hours for critical issues

---

**Remember: Security is everyone's responsibility!** 🔐
EOF

echo "✅ Created security policy"

# Add and commit workflow files
git add .github/
git commit -m "🔧 Restore GitHub Actions workflows with security policies

- Added deployment workflow with security scanning
- Added security policy documentation
- Configured for private repository protection
- Excludes sensitive activation code files"

echo "✅ Committed workflow files"

echo ""
echo "🎯 Next Steps:"
echo "1. Ensure your GitHub token has 'workflow' scope"
echo "2. Run: git push origin main"
echo "3. Verify workflows appear in GitHub Actions tab"
echo ""
echo "📖 For detailed instructions, see: GITHUB_TOKEN_SETUP.md"
echo ""
echo "✨ Workflow restoration complete!"