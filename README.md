# Truck Movement - Full Setup & Version Update Guide

This project includes a professional Version Control system using Git and GitHub, designed to work seamlessly with Hostinger's standard deployment.

---

## 🏗 Phase 1: Initial Local Git Setup (First Time Only)

These are the commands used to initialize this repository on your laptop and link it to GitHub.

1.  **Start Git**: Open your terminal in the `Truckmovement` folder and run:
    ```bash
    git init
    ```
2.  **Identify Yourself**: Tell Git who you are!
    ```bash
    git config --local user.name "Rakesh Verma"
    git config --local user.email "rakeshbond009@gmail.com"
    ```
3.  **Prepare Files**: Add all files (ignoring photos and crash logs due to `.gitignore`):
    ```bash
    git add .
    ```
4.  **Save Your Version**: Save the first "Snapshot" of your code:
    ```bash
    git commit -m "Initial stabilization and audit log update"
    ```
5.  **Rename Branch**: Ensure your main code is on the "main" branch:
    ```bash
    git branch -M main
    ```
6.  **Connect to GitHub**: Link your local laptop to your GitHub repository:
    ```bash
    git remote add origin https://github.com/rakeshbond009/Truckmovement.git
    ```
7.  **Push to Cloud**: Send your code to the online GitHub repository:
    ```bash
    git push -u origin main
    ```

---

## 🛠 Phase 2: Hostinger One-Time Setup (Bypassing "Non-Empty Directory" Error)

Hostinger won't let you link a GitHub repository if your `public_html` folder already has files. To solve this **safely without losing your data**, follow these steps:

1.  **Backup Existing Files**:
    *   Log in to Hostinger **File Manager**.
    *   Create a new folder called `OLD_BACKUP` outside `public_html` (or inside it).
    *   **Move** all files and folders from `public_html` into `OLD_BACKUP`.
    *   *Result*: Your `public_html` should now be completely **EMPTY**.

2.  **Link GitHub Repository**:
    *   Go to Hostinger Dashboard -> **Advanced -> Git**.
    *   **Repository URL**: `https://github.com/rakeshbond009/Truckmovement.git`
    *   **Branch**: `main`
    *   **Install Directory**: Leave it as `public_html` (don't change).
    *   Click **"Create"**.
    *   *Result*: Hostinger will now download your code from GitHub into `public_html`.

3.  **Restore Your Critical Data**:
    *   Go back to the `OLD_BACKUP` folder.
    *   **Copy** your `uploads/` folder and `config.php` back into `public_html`.
    *   (This ensures your live photos and live database connection are restored).
    *   You can now delete the `OLD_BACKUP` folder.

---

## 🚀 Phase 3: Regular Updates (One-Click Sync)

You no longer need to type manual commands on your laptop! 

1.  **Sync from Laptop**:
    *   Open your **Admin Panel** on your laptop (localhost).
    *   Go to **Settings** (Gear Icon ⚙️).
    *   Scroll to the bottom and click **"☁️ Push to Cloud (Sync GitHub)"**.
    *   A console will appear; wait for it to finish.

2.  **Deploy on Hostinger**:
    *   Login to Hostinger -> **Advanced -> Git**.
    *   Scroll down to **"Manage Repositories"**.
    *   Click the **"Deploy"** button.
    *   *Result*: Your live website is now updated with your latest changes!

---

## 🔒 Safety & Data Persistence
*   **Photos/Images**: Never deleted or overwritten because of the `.gitignore` mapping.
*   **Passwords**: The `config.php` file on your Hostinger server is protected and will not be changed by your local settings.
*   **Crash Prevention**: All large `.hprof` and `.mp4` files are excluded, keeping your pushes fast and clean.

Guide by: Rakesh Verma
Email: rakeshbond009@gmail.com
Status: Production Configuration Complete
