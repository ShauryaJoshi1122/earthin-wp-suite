#!/usr/bin/env python3
"""
Earthin Distribution Packager
Packages each theme and plugin into clean, installable WordPress ZIP packages.
"""

import os
import zipfile
import shutil

BASE_DIR = os.path.dirname(os.path.dirname(os.path.abspath(__file__)))
OUTPUT_BASE = os.path.join(BASE_DIR, 'apps', 'plugin-hub', 'public', 'downloads')

def zip_folder(source_folder, output_zip_path, folder_name_in_zip):
    os.makedirs(os.path.dirname(output_zip_path), exist_ok=True)
    with zipfile.ZipFile(output_zip_path, 'w', zipfile.ZIP_DEFLATED) as zf:
        for root, dirs, files in os.walk(source_folder):
            for file in files:
                if file.startswith('.') or file.endswith('.zip'):
                    continue
                full_path = os.path.join(root, file)
                rel_path = os.path.relpath(full_path, source_folder)
                archive_name = os.path.join(folder_name_in_zip, rel_path)
                zf.write(full_path, archive_name)
    size = os.path.getsize(output_zip_path)
    print(f"Packaged {folder_name_in_zip} -> {output_zip_path} ({size} bytes)")

def package_all():
    themes_dir = os.path.join(BASE_DIR, 'themes')
    plugins_dir = os.path.join(BASE_DIR, 'plugins')

    # 1. Package Themes
    print("--- Packaging Themes ---")
    themes_out = os.path.join(OUTPUT_BASE, 'themes')
    for t_name in sorted(os.listdir(themes_dir)):
        t_path = os.path.join(themes_dir, t_name)
        if os.path.isdir(t_path) and os.path.exists(os.path.join(t_path, 'style.css')):
            zip_dest = os.path.join(themes_out, f"{t_name}.zip")
            zip_folder(t_path, zip_dest, t_name)

    # 2. Package Plugins
    print("\n--- Packaging Plugins ---")
    plugins_out = os.path.join(OUTPUT_BASE, 'plugins')
    for p_name in sorted(os.listdir(plugins_dir)):
        p_path = os.path.join(plugins_dir, p_name)
        if os.path.isdir(p_path):
            zip_dest = os.path.join(plugins_out, f"{p_name}.zip")
            zip_folder(p_path, zip_dest, p_name)

    print("\nAll WordPress theme and plugin ZIP packages created successfully!")

if __name__ == '__main__':
    package_all()
