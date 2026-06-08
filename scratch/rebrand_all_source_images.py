import shutil
import os

source_logo = r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\new_frontfieldlogos\6RR9UFs6kLq67BrPItMv.png"

destinations = [
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\assets\external\images\logo.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\assets\external\images\pinellas_logo_white_1774915533306 copy.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\assets\external\images\pinellas_logo_white_1774915533306.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\assets\images\bank_logos\pinellas.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\public\assets\external\images\logo.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\public\assets\external\images\pinellas_logo_white_1774915533306.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\public\assets\global\images\8nxo6TLB9BxQEtcNugoF.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\public\assets\images\logo.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\public\assets\images\bank_logos\pinellas.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\android\app\src\main\assets\public\assets\global\images\6RR9UFs6kLq67BrPItMv.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\android\app\src\main\assets\public\assets\global\images\8nxo6TLB9BxQEtcNugoF.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\android\app\src\main\assets\public\assets\external\images\logo.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\android\app\src\main\assets\public\assets\external\images\pinellas_logo_white_1774915533306.png",
    r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\android\app\src\main\assets\public\assets\images\logo.png",
]

for dest in destinations:
    try:
        os.makedirs(os.path.dirname(dest), exist_ok=True)
        shutil.copy2(source_logo, dest)
        print(f"Copied successfully to: {dest}")
    except Exception as e:
        print(f"Failed to copy to {dest}: {e}")
