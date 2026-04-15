<?php
/**
 * GATEPILOT - REFACTORED VERSION
 * Modular structure for better maintainability.
 */

// 1. Initialization and Backend Logic
require_once 'includes/init.php';

// 1.5 Handle special landing pages (without header/navbar)
if ($page === 'print-employee-qrs') {
    include 'pages/print_employees_qr.php';
    exit;
}

// 2. Global Headers (HTML Head & CSS)
include 'includes/header.php';

// 3. Global Scripts & Common Modals
include 'includes/scripts.php';

// 4. Navigation Bar
include 'includes/navbar.php';

// 5. Page Routing Controller
if ($page == 'login'):
    include 'pages/login.php';
elseif ($page == 'dashboard'):
    include 'pages/dashboard.php';
elseif (in_array($page, ['guard-patrol', 'tickets', 'edit-register-entry', 'register-entry', 'edit-material-inward'])):
    include 'pages/patrol_and_registers.php';
elseif ($page == 'manage-register-types'):
    include 'manage_register_types.php';
elseif (in_array($page, ['reports', 'inward', 'outward', 'details', 'inward-details', 'outward-details', 'edit-inward', 'edit-outward', 'inside', 'vehicle-history', 'loading', 'unloading', 'loading-details', 'unloading-details', 'unloading-mismatch-report', 'driver-detail', 'vehicle-detail', 'transporter-detail', 'user-detail', 'document-expiry-alerts', 'management', 'qr-scanner'])):
    include 'pages/core_ops.php';
elseif (in_array($page, ['admin', 'user-permissions'])):
    include 'pages/admin.php';
elseif (in_array($page, ['view-registers', 'view-material-inward'])):
    include 'pages/reports.php';
elseif ($page == 'privacy'):
    include 'pages/privacy.php';
elseif ($page == 'terms'):
    include 'pages/terms.php';
elseif ($page == 'app-issues'):
    include 'pages/app_issues.php';
else:
    // Fallback to dashboard
    include 'pages/dashboard.php';
endif;

// 6. Global Footer (Bottom Nav & Closing Tags)
include 'includes/footer.php';
?>