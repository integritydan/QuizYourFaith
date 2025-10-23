#!/bin/bash
cd ~/Desktop/QuizYourFaith
git add -A
git commit -m "release: desktop → GitHub – payment keys, HTTPS, CSRF, logo, cards, docs" --no-edit
git push origin main --force-with-lease
echo "✅  Brute push complete – online repo updated"
