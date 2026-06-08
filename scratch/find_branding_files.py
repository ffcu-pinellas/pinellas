import os

workspace = r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu"
targets = ["logo.png", "pinellas.png", "pinellas_logo_white_1774915533306.png", "pinellas_logo_white_1774915533306 copy.png", "8nxo6TLB9BxQEtcNugoF.png", "6RR9UFs6kLq67BrPItMv.png"]

found_files = []
for root, dirs, files in os.walk(workspace):
    for f in files:
        if f.lower() in [t.lower() for t in targets] or "pinellas" in f.lower() or "frontfield" in f.lower():
            if ".git" not in root and "node_modules" not in root and "vendor" not in root and ".venv" not in root:
                found_files.append(os.path.join(root, f))

print("Found files:")
for f in found_files:
    print(f)
