import os

seeder_path = r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\database\seeders\PinellasEmailTemplateSeeder.php"
new_seeder_path = r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\database\seeders\FrontFieldEmailTemplateSeeder.php"
db_seeder_path = r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\database\seeders\DatabaseSeeder.php"
readme_path = r"c:\Users\USER\Downloads\frontfield-remodel pinelas fcu\README.md"

# 1. Read and modify the seeder
if os.path.exists(seeder_path):
    with open(seeder_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Replaces
    content = content.replace("PinellasEmailTemplateSeeder", "FrontFieldEmailTemplateSeeder")
    content = content.replace("Pinellas Federal Credit Union", "FrontField Credit Union")
    content = content.replace("Pinellas Credit Union", "FrontField Credit Union")
    content = content.replace("Pinellas FCU", "FrontField FCU")
    content = content.replace("Pinellas", "FrontField")
    content = content.replace("Always Pinellas", "Always FrontField")

    with open(new_seeder_path, 'w', encoding='utf-8') as f:
        f.write(content)

    os.remove(seeder_path)
    print("Rebranded and moved seeder file.")

# 2. Modify DatabaseSeeder.php
if os.path.exists(db_seeder_path):
    with open(db_seeder_path, 'r', encoding='utf-8') as f:
        db_content = f.read()

    db_content = db_content.replace("PinellasEmailTemplateSeeder", "FrontFieldEmailTemplateSeeder")

    with open(db_seeder_path, 'w', encoding='utf-8') as f:
        f.write(db_content)
    print("Updated DatabaseSeeder.php")

# 3. Modify README.md
if os.path.exists(readme_path):
    with open(readme_path, 'r', encoding='utf-8') as f:
        readme_content = f.read()

    readme_content = readme_content.replace("PinellasEmailTemplateSeeder", "FrontFieldEmailTemplateSeeder")

    with open(readme_path, 'w', encoding='utf-8') as f:
        f.write(readme_content)
    print("Updated README.md")
