import os
import re

workspace = r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu"
exclude_dirs = {".git", "node_modules", "vendor", ".venv", "scratch", "storage", "build", ".gradle", "app-release"}
exclude_extensions = {".apk", ".jar", ".zip", ".tar", ".gz", ".png", ".jpg", ".jpeg", ".gif", ".ico", ".ttf", ".woff", ".woff2", ".eot"}

# Text replacements
replacements = [
    (re.compile(r"Pinellas Federal Credit Union", re.IGNORECASE), "FrontField Credit Union"),
    (re.compile(r"Pinellas Credit Union", re.IGNORECASE), "FrontField Credit Union"),
    (re.compile(r"Pinellas FCU", re.IGNORECASE), "FrontField FCU"),
    (re.compile(r"Pinellas", re.IGNORECASE), "FrontField"),
    (re.compile(r"PINELLAS", re.IGNORECASE), "FRONTFIELD"),
    (re.compile(r"pinellas", re.IGNORECASE), "frontfield"),
]

def rebrand_file_contents(file_path):
    try:
        with open(file_path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        original_content = content
        for pattern, replacement in replacements:
            content = pattern.sub(replacement, content)
            
        if content != original_content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated contents: {file_path}")
    except Exception as e:
        print(f"Error updating contents of {file_path}: {e}")

# Phase 1: Update contents of all text files
print("Phase 1: Updating file contents...")
for root, dirs, files in os.walk(workspace):
    # Exclude directories in-place to prevent os.walk from entering them
    dirs[:] = [d for d in dirs if d not in exclude_dirs and not d.startswith("build") and not d.startswith(".")]
    for f in files:
        ext = os.path.splitext(f)[1].lower()
        if ext in exclude_extensions:
            continue
        file_path = os.path.join(root, f)
        rebrand_file_contents(file_path)

# Phase 2: Rename files
print("\nPhase 2: Renaming files...")
for root, dirs, files in os.walk(workspace):
    dirs[:] = [d for d in dirs if d not in exclude_dirs and not d.startswith("build") and not d.startswith(".")]
    for f in files:
        if "pinellas" in f.lower():
            old_path = os.path.join(root, f)
            new_filename = f.replace("Pinellas", "FrontField").replace("pinellas", "frontfield").replace("PINELLAS", "FRONTFIELD")
            new_path = os.path.join(root, new_filename)
            try:
                os.rename(old_path, new_path)
                print(f"Renamed file: {old_path} -> {new_path}")
            except Exception as e:
                print(f"Error renaming file {old_path}: {e}")

# Phase 3: Rename directories
print("\nPhase 3: Renaming directories...")
for root, dirs, files in os.walk(workspace, topdown=False):
    dirs[:] = [d for d in dirs if d not in exclude_dirs and not d.startswith("build") and not d.startswith(".")]
    for d in dirs:
        if "pinellas" in d.lower():
            old_path = os.path.join(root, d)
            new_dirname = d.replace("Pinellas", "FrontField").replace("pinellas", "frontfield").replace("PINELLAS", "FRONTFIELD")
            new_path = os.path.join(root, new_dirname)
            try:
                os.rename(old_path, new_path)
                print(f"Renamed directory: {old_path} -> {new_path}")
            except Exception as e:
                print(f"Error renaming directory {old_path}: {e}")

print("\nRebranding run finished!")
