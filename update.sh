#!/bin/bash
# Pull the latest changes from GitHub
git pull origin main

# Set correct permissions
chmod -R 755 storage
chmod -R 755 public/assets
