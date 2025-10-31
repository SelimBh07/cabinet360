#!/usr/bin/env python3
"""
Cabinet360 PWA Icon Generator
Creates 192x192 and 512x512 PNG icons for the PWA
"""

try:
    from PIL import Image, ImageDraw, ImageFont
    import os
    
    def create_icon(size, filename):
        # Create image with dark background
        img = Image.new('RGB', (size, size), color='#1a1a1a')
        draw = ImageDraw.Draw(img)
        
        # Draw blue circle background
        circle_radius = int(size * 0.44)
        draw.ellipse(
            [(size//2 - circle_radius, size//2 - circle_radius),
             (size//2 + circle_radius, size//2 + circle_radius)],
            fill=(0, 123, 255, 25)
        )
        
        # Scale factor
        scale = size / 192
        centerX = size // 2
        centerY = int(size / 2.4)
        
        # Gold color
        gold = '#D4AF37'
        
        # Draw scales of justice
        # Stand
        draw.rectangle(
            [(centerX - 2*scale, centerY - 20*scale),
             (centerX + 2*scale, centerY + 30*scale)],
            fill=gold
        )
        
        # Base
        draw.rectangle(
            [(centerX - 30*scale, centerY + 30*scale),
             (centerX + 30*scale, centerY + 38*scale)],
            fill=gold
        )
        
        # Horizontal beam
        draw.rectangle(
            [(centerX - 40*scale, centerY - 20*scale),
             (centerX + 40*scale, centerY - 16*scale)],
            fill=gold
        )
        
        # Left scale chain and pan
        draw.line(
            [(centerX - 35*scale, centerY - 18*scale),
             (centerX - 35*scale, centerY - 10*scale)],
            fill=gold, width=int(2*scale)
        )
        draw.polygon(
            [(centerX - 35*scale, centerY - 5*scale),
             (centerX - 25*scale, centerY - 5*scale),
             (centerX - 30*scale, centerY)],
            fill=gold
        )
        
        # Right scale chain and pan
        draw.line(
            [(centerX + 35*scale, centerY - 18*scale),
             (centerX + 35*scale, centerY - 10*scale)],
            fill=gold, width=int(2*scale)
        )
        draw.polygon(
            [(centerX + 35*scale, centerY - 5*scale),
             (centerX + 45*scale, centerY - 5*scale),
             (centerX + 40*scale, centerY)],
            fill=gold
        )
        
        # Add text
        try:
            if size == 192:
                font = ImageFont.truetype("arial.ttf", 18)
            else:
                font = ImageFont.truetype("arial.ttf", 42)
                small_font = ImageFont.truetype("arial.ttf", 18)
        except:
            font = ImageFont.load_default()
            small_font = ImageFont.load_default()
        
        # Main text
        text = "CABINET360"
        bbox = draw.textbbox((0, 0), text, font=font)
        text_width = bbox[2] - bbox[0]
        text_x = (size - text_width) // 2
        text_y = int(size * 0.7)
        draw.text((text_x, text_y), text, fill=gold, font=font)
        
        # Subtitle for 512x512
        if size == 512:
            subtitle = "Gestion d'Avocat"
            bbox2 = draw.textbbox((0, 0), subtitle, font=small_font)
            subtitle_width = bbox2[2] - bbox2[0]
            subtitle_x = (size - subtitle_width) // 2
            subtitle_y = int(size * 0.82)
            draw.text((subtitle_x, subtitle_y), subtitle, fill='#999999', font=small_font)
        
        # Save
        output_path = os.path.join('assets', 'icons', filename)
        img.save(output_path, 'PNG')
        print(f"✓ Created {filename}")
    
    # Create both icons
    print("Generating Cabinet360 PWA icons...")
    create_icon(192, 'icon-192x192.png')
    create_icon(512, 'icon-512x512.png')
    print("\n✓ Icons generated successfully!")
    print("Icons are saved in: assets/icons/")
    
except ImportError:
    print("PIL/Pillow not found. Using HTML generator instead.")
    print("Open create_icons.html in a browser to generate icons manually.")
except Exception as e:
    print(f"Error: {e}")
    print("Open create_icons.html in a browser to generate icons manually.")

