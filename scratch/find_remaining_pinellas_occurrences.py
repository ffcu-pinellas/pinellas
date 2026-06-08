import os
import re

workspace = r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu"
exclude_dirs = {".git", "node_modules", "vendor", ".venv", "scratch"}
exclude_extensions = {".png", ".jpg", ".jpeg", ".gif", ".ico", ".apk", ".jar", ".zip", ".tar", ".gz"}

pattern = re.compile(r"pinellas", re.IGNORECASE)

occurrences = []

for root, dirs, files in os.walk(workspace):
    # Modifies dirs in place to skip excluded directories
    dirs[:] = [d for d in dirs if d not in exclude_dirs]
    for f in files:
        ext = os.path.splitext(f)[1].lower()
        if ext in exclude_extensions:
            continue
        file_path = os.path.join(root, f)
        try:
            with open(file_path, "r", encoding="utf-8", errors="ignore") as file:
                for line_num, line in enumerate(file, 1):
                    if pattern.search(line):
                        occurrences.append((file_path, line_num, line.strip()))
        except Exception as e:
            pass

print(f"Total occurrences found: {len(occurrences)}")
for file_path, line_num, line in occurrences[:100]:
    print(f"{file_path}:{line_num}: {line}")
if len(occurrences) > 100:
    print(f"... and {len(occurrences) - 100} more.")
