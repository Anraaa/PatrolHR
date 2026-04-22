#!/bin/bash

# PWA Quick Start Setup Script
# Helps setup and test PWA locally

set -e

echo "🚀 PWA Quick Start Setup"
echo "======================="
echo ""

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if we're in right directory
if [ ! -f "package.json" ] || [ ! -d "src" ]; then
    echo -e "${RED}❌ Error: Run this script from project root directory${NC}"
    echo "Current directory: $(pwd)"
    exit 1
fi

# Step 1: Generate Icons
echo -e "${YELLOW}Step 1: Generating PWA Icons...${NC}"
echo ""

if [ -f "generate-icons.php" ]; then
    if command -v php &> /dev/null; then
        php generate-icons.php
        echo -e "${GREEN}✅ Icons generated${NC}"
    else
        echo -e "${YELLOW}⚠️  PHP not found, skipping icon generation${NC}"
        echo "   Run manually: php generate-icons.php"
    fi
else
    echo -e "${YELLOW}⚠️  generate-icons.php not found${NC}"
fi

echo ""

# Step 2: Install dependencies if needed
echo -e "${YELLOW}Step 2: Checking dependencies...${NC}"
echo ""

if [ -d "src/node_modules" ]; then
    echo -e "${GREEN}✅ Dependencies already installed${NC}"
else
    echo -e "${YELLOW}📦 Installing dependencies...${NC}"
    cd src
    npm install
    cd ..
    echo -e "${GREEN}✅ Dependencies installed${NC}"
fi

echo ""

# Step 3: Build assets
echo -e "${YELLOW}Step 3: Building assets...${NC}"
echo ""

cd src
npm run build
cd ..
echo -e "${GREEN}✅ Assets built${NC}"

echo ""

# Step 4: Display next steps
echo -e "${GREEN}✅ PWA Setup Complete!${NC}"
echo ""
echo "📋 Next Steps:"
echo ""
echo "1. Start development server:"
echo "   ${YELLOW}cd src && npm run dev${NC}"
echo ""
echo "2. Open browser:"
echo "   ${YELLOW}http://localhost:5173${NC}"
echo ""
echo "3. Test PWA features:"
echo "   - Check DevTools (F12) → Application tab"
echo "   - Manifest should be loaded"
echo "   - Service Worker should be registered"
echo "   - Install prompt should appear (bottom-right)"
echo ""
echo "4. Test Offline:"
echo "   - DevTools → Application → Service Workers"
echo "   - Check 'Offline' checkbox"
echo "   - Reload page"
echo "   - Should see offline.html"
echo ""
echo "5. Install on Android:"
echo "   - Open app in Chrome"
echo "   - Tap 'Install' button"
echo "   - App will be added to home screen"
echo ""
echo "📚 Documentation:"
echo "   - PWA_SETUP_GUIDE.md - Detailed setup guide"
echo "   - README_PWA.md - Complete PWA documentation"
echo ""
echo "🎯 Key Files:"
echo "   - src/public/manifest.json"
echo "   - src/public/service-worker.js"
echo "   - src/resources/js/pwa-install.js"
echo "   - src/resources/css/pwa-ui.css"
echo ""
