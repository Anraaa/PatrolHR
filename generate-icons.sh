#!/bin/bash

# PWA Icon Generator Script
# This script creates placeholder icons for PWA
# You should replace these with your actual app icons

ICON_DIR="src/public/images"

# Function to create an SVG icon
create_svg_icon() {
    local size=$1
    local filename="icon-${size}x${size}.png"
    
    # Create SVG with proper sizing
    svg_content="<?xml version=\"1.0\" encoding=\"UTF-8\"?>
<svg width=\"${size}\" height=\"${size}\" viewBox=\"0 0 ${size} ${size}\" xmlns=\"http://www.w3.org/2000/svg\">
  <!-- Gradient background -->
  <defs>
    <linearGradient id=\"grad\" x1=\"0%\" y1=\"0%\" x2=\"100%\" y2=\"100%\">
      <stop offset=\"0%\" style=\"stop-color:#3b82f6;stop-opacity:1\" />
      <stop offset=\"100%\" style=\"stop-color:#2563eb;stop-opacity:1\" />
    </linearGradient>
  </defs>
  
  <!-- Background -->
  <rect width=\"${size}\" height=\"${size}\" fill=\"url(#grad)\" rx=\"$(($size / 8))\"/>
  
  <!-- App icon - Checksheet symbol -->
  <circle cx=\"$(($size / 2))\" cy=\"$(($size / 2))\" r=\"$(($size / 3))\" fill=\"white\" opacity=\"0.1\"/>
  <text x=\"$(($size / 2))\" y=\"$(($size / 2 + $size / 8))\" font-size=\"$(($size / 2))\" font-weight=\"bold\" fill=\"white\" text-anchor=\"middle\" font-family=\"Arial\">✓</text>
</svg>"
    
    echo "Creating $filename..."
    echo "$svg_content" > "$ICON_DIR/$filename"
}

# Create icons directory
mkdir -p "$ICON_DIR"

echo "🎨 Generating PWA Icons..."
echo "=========================="
echo ""
echo "Creating placeholder icons. Please replace these with your actual app icons:"
echo ""

# Generate standard sizes
for size in 192 256 384 512; do
    create_svg_icon $size
done

# Create screenshot placeholders
echo "Creating screenshot placeholders..."

# Create 540x720 screenshot (narrow)
convert -size 540x720 xc:'#3b82f6' -draw "text 270,360 'Checksheet Patrol'" \
    "$ICON_DIR/screenshot-540x720.png" 2>/dev/null || {
    # Fallback if ImageMagick not available
    echo "Note: ImageMagick not found. Skipping screenshot generation."
}

# Create 1280x720 screenshot (wide)
convert -size 1280x720 xc:'#3b82f6' -draw "text 640,360 'Checksheet Patrol'" \
    "$ICON_DIR/screenshot-1280x720.png" 2>/dev/null || {
    echo ""
}

echo ""
echo "✅ Icon files created in: $ICON_DIR/"
echo ""
echo "📝 Next Steps:"
echo "1. Review the generated icons in $ICON_DIR/"
echo "2. Replace them with your actual app icons"
echo "3. Icon sizes needed:"
echo "   - 192x192 (Android notification icon)"
echo "   - 256x256 (General purpose)"
echo "   - 384x384 (High DPI)"
echo "   - 512x512 (Splash screen)"
echo ""
echo "💡 Tips:"
echo "- Use PNG format with transparent background"
echo "- Ensure icons are square"
echo "- Use solid colors for better offline visibility"
echo "- Test icons on different devices"
echo ""
