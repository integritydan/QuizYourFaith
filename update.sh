#!/usr/bin/env bash
# QuizYourFaith – one-line updater
# Run:  bash update.sh   (inside project root)
echo "⬇️  Pulling latest commit..."
git pull origin main
echo "🔧 Setting permissions..."
chmod -R 755 storage
chmod -R 755 public/assets
echo "✅ Up-to-date!"
