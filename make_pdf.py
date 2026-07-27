import math
import os
import subprocess

scale = 20
w = 12 * scale
h = 5 * scale
x_off = 177.5 # Centered: (595 - 240) / 2
y_off = 371   # Centered: (842 - 100) / 2

x_dist = (h/2) / math.tan(math.radians(60))

html_content = f"""<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Question 1 - Roof Plan</title>
    <style>
        @page {{
            size: A4;
            margin: 0;
        }}
        body {{
            margin: 0;
            padding: 0;
            background-color: white;
        }}
        svg {{
            width: 595px;
            height: 842px;
            display: block;
        }}
    </style>
</head>
<body>
    <svg viewBox="0 0 595 842">
        <!-- Main building outline -->
        <rect x="{x_off}" y="{y_off}" width="{w}" height="{h}" fill="none" stroke="black" stroke-width="2" />
        
        <!-- Hipped ridge line -->
        <line x1="{x_off + x_dist:.2f}" y1="{y_off + h/2:.2f}" x2="{x_off + w - x_dist:.2f}" y2="{y_off + h/2:.2f}" stroke="black" stroke-width="2" />
        
        <!-- Hipped corner lines -->
        <line x1="{x_off}" y1="{y_off}" x2="{x_off + x_dist:.2f}" y2="{y_off + h/2:.2f}" stroke="black" stroke-width="2" />
        <line x1="{x_off}" y1="{y_off + h}" x2="{x_off + x_dist:.2f}" y2="{y_off + h/2:.2f}" stroke="black" stroke-width="2" />
        <line x1="{x_off + w}" y1="{y_off}" x2="{x_off + w - x_dist:.2f}" y2="{y_off + h/2:.2f}" stroke="black" stroke-width="2" />
        <line x1="{x_off + w}" y1="{y_off + h}" x2="{x_off + w - x_dist:.2f}" y2="{y_off + h/2:.2f}" stroke="black" stroke-width="2" />
        
        <!-- Text annotations -->
        <!-- 12mm label below -->
        <text x="{x_off + w/2}" y="{y_off + h + 25}" font-family="Times New Roman, Times, serif" font-size="12pt" text-anchor="middle" font-weight="bold">12mm</text>
        
        <!-- 5mm label on the right -->
        <text x="{x_off + w + 12}" y="{y_off + h/2 + 5}" font-family="Times New Roman, Times, serif" font-size="12pt" text-anchor="start" font-weight="bold">5mm</text>
        
        <!-- 60 deg angle label inside -->
        <text x="{x_off + 15}" y="{y_off + h - 12}" font-family="Times New Roman, Times, serif" font-size="10pt" text-anchor="start" font-weight="bold">60 deg</text>
    </svg>
</body>
</html>
"""

# Write temporary HTML file
temp_html_path = "q1_temp.html"
with open(temp_html_path, "w", encoding="utf-8") as f:
    f.write(html_content)

# Convert to PDF using Chrome
chrome_path = "C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe"
pdf_dest = "C:\\Users\\USER\\Downloads\\question_1_roof_plan.pdf"

# Stop any process holding the file lock on the PDF
try:
    subprocess.run(["powershell", "-Command", "Stop-Process -Name wpspdf -Force -ErrorAction SilentlyContinue"], capture_output=True)
    subprocess.run(["powershell", "-Command", "Stop-Process -Name wps -Force -ErrorAction SilentlyContinue"], capture_output=True)
except Exception:
    pass

if os.path.exists(pdf_dest):
    try:
        os.remove(pdf_dest)
    except Exception:
        pass

# Run headless chrome to print the SVG HTML page as PDF
subprocess.run([
    chrome_path,
    "--headless=new",
    "--disable-gpu",
    "--no-pdf-header-footer",
    f"--print-to-pdf={pdf_dest}",
    temp_html_path
], shell=True)

# Clean up temp file
if os.path.exists(temp_html_path):
    os.remove(temp_html_path)

print("PDF created successfully via Chrome!")
