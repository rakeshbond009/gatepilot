# API Documentation: Truck Gate Management System

This document outlines the internal API endpoints and action handlers used for data exchange between the frontend, mobile app, and backend.

## 📡 Base URL
- **Local**: `http://localhost/Truckmovement/`
- **Production**: `https://gatemanagement.codepilotx.com/`

---

## 🛠️ AJAX Endpoints (Public/Internal)

### 1. Fetch Vehicle Details
Used for auto-filling the Inward form based on the vehicle's last trip.
- **Endpoint**: `get_vehicle_details.php`
- **Method**: `GET`
- **Parameters**: 
    - `vehicle_number` (string): Total vehicle number (e.g., MH04GP1234)
- **Response**:
    ```json
    {
        "success": true,
        "found": true,
        "data": {
            "driver_name": "...",
            "driver_mobile": "...",
            "transporter_name": "...",
            "purpose_name": "...",
            "from_location": "...",
            "to_location": "..."
        },
        "message": "Previous details found!"
    }
    ```

### 2. Check Vehicle "Inside" Status
Checks if a vehicle is currently flagged as 'Inside' the premises.
- **Endpoint**: `check_vehicle_inside.php`
- **Method**: `GET`
- **Parameters**: 
    - `vehicle_number` (string)
- **Response**:
    ```json
    {
        "success": true,
        "is_inside": true,
        "inward_id": 45,
        "message": "Vehicle MH04XX is already inside."
    }
    ```

### 3. Fetch Specific Driver Details
- **Endpoint**: `fetch_vehicle_driver.php`
- **Method**: `GET`
- **Parameters**: `driver_name` (string)
- **Response**: JSON object with Driver Master details.

### 4. Fetch Checklist Vehicle Details
Provides comprehensive vehicle and driver data specifically for filling loading/unloading checklists.
- **Endpoint**: `fetch_checklist_vehicle_details.php`
- **Method**: `GET`
- **Parameters**: `vehicle_number` (string)
- **Response**: JSON with deep-linked transporter and driver data.

---

## ⚡ Main Action Handlers (index.php)
The main monolith (`index.php`) handles stateful transitions via `?page=` and `POST` actions.

### 1. Inward Entry Submission
- **Action**: `submit_inward`
- **Required Fields**: `vehicle_number`, `driver_name`, `transporter_name`, `purpose_name`.
- **Optional**: `driver_photo`, `vehicle_photo`, `bill_photo` (Files), `qr_code_data`.

### 2. Outward Entry Submission
- **Action**: `submit_outward`
- **Required Fields**: `inward_id` (selected from inside list), `outward_remarks`.
- **Process**: Calculates duration automatically and updates status to 'OUT'.

### 3. Guard Patrol Log
- **Action**: `patrol_scan`
- **Payload**: `qr_data` (Location ID/QR Hash).
- **Security**: Validates against `patrol_locations` table and prevents duplicate scans within 5 minutes.

---

## 📊 Report Exports
### 1. Excel Export
- **Endpoint**: `export.php`
- **Method**: `GET`
- **Parameters**:
    - `start_date` (YYYY-MM-DD)
    - `end_date` (YYYY-MM-DD)
    - `status` (ALL | INSIDE | OUT)
- **Output**: Binary CSV/Excel stream with headers.

---

## 🛡️ Authentication & Authorization
- **Session-Based**: Secure PHP sessions are used for user state.
- **Permission Mapping**: Every action/page check is filtered through the `hasPermission($key)` function, which validates the user's JSON-stored permission set against the requested scope (e.g., `pages.reports`).

---

## 📷 Media Management
- Photos are uploaded to `uploads/` subdirectories:
    - `/drivers/`: Driver face photos/licenses.
    - `/vehicles/`: Vehicle/Truck plate photos.
    - `/logo/`: System branding.
- **Image Handling**: JavaScript-side previews are generated using `FileReader API` before submission to ensure user confidence.
