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

## 🛠 Phase 2: Hostinger Website Initialization (One-Time)

Because Hostinger won't let you "Create Repository" in a folder with files (`public_html`), follow this:

1.  **Login**: Use File Manager or FTP on Hostinger.
2.  **Move**: Select all files in `public_html` and move them into a temporary folder named `backup`.
3.  **Git Link**: Go to Hostinger **Advanced -> Git**.
    *   **Repository**: `https://github.com/rakeshbond009/Truckmovement.git`
    *   **Branch**: `main`
    *   **Click "Create"**. **(Hostinger will now pull the code from GitHub)**
4.  **Restore Data**: Move your `uploads/` folder and `config.php` back from `backup` into the root `public_html`.
5.  **Clear**: Delete the `backup` folder.

---

## 🚀 Phase 3: Regular Updates (Your Daily Workflow)

Whenever you finish a piece of work on your laptop, follow these **THREE** commands to update everything:

1.  **Add**: `git add .`
2.  **Save**: `git commit -m "Briefly describe what you changed"`
3.  **Push**: `git push`
4.  **Deploy**: In Hostinger, click the **"Deploy"** button.

---

## 🔒 Safety & Data Persistence
*   **Photos/Images**: Never deleted or overwritten because of the `.gitignore` mapping.
*   **Passwords**: The `config.php` file on your Hostinger server is protected and will not be changed by your local settings.
*   **Crash Prevention**: All large `.hprof` and `.mp4` files are excluded, keeping your pushes fast and clean.

Guide by: Rakesh Verma
Email: rakeshbond009@gmail.com
Status: Production Configuration Complete
