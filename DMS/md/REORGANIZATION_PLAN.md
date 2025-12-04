# Project Reorganization Plan

## Folder Structure

```
SIA_DMS/
├── admin/              # All admin-related pages
├── doctor/             # All doctor-related pages  
├── patient/            # All patient-related pages
├── nurse/              # All nurse/staff-related pages
├── api/                # Shared API endpoints and utilities
├── includes/           # Core includes (db_connect, phpmailer_config)
├── sql/                # Database SQL files
├── assets/
│   └── css/            # Stylesheets
├── uploads/            # Upload directories
├── PHPMailer/          # PHPMailer library (unchanged)
└── [root auth files]   # login.php, logout.php, etc.
```

## File Mapping

### Root Level (Auth & Core)
- `login.php` → stays in root (updated paths)
- `logout.php` → stays in root (updated paths)
- `forgot_password.php` → stays in root (updated paths)
- `reset_password.php` → stays in root (updated paths)
- `change_password.php` → stays in root (updated paths)
- `google_callback.php` → stays in root (updated paths)
- `dashboard.php` → stays in root (updated paths)
- `dashboard_template.php` → stays in root (updated paths)
- `setup_documents.php` → stays in root (updated paths)
- `setup_demo_accounts.php` → stays in root (updated paths)
- `diagnose_doctor_files.php` → stays in root (updated paths)
- `generate_hashes.php` → stays in root

### Admin Files → admin/
- `admin_home.php` → `admin/admin_home.php`
- `admin_dashboard.php` → `admin/admin_dashboard.php`
- `admin_add_user.php` → `admin/admin_add_user.php`
- `admin_audit_logs.php` → `admin/admin_audit_logs.php`
- `admin_documents.php` → `admin/admin_documents.php`
- `admin_file_templates_upload.php` → `admin/admin_file_templates_upload.php`
- `admin_files_revoke.php` → `admin/admin_files_revoke.php`
- `admin_permissions.php` → `admin/admin_permissions.php`

### Doctor Files → doctor/
- `doctor_home.php` → `doctor/doctor_home.php`
- `doctor_dashboard.php` → `doctor/doctor_dashboard.php`
- `doctor_documents.php` → `doctor/doctor_documents.php`

### Patient Files → patient/
- `patient_home.php` → `patient/patient_home.php`
- `patient_dashboard.php` → `patient/patient_dashboard.php`
- `patient_file_upload.php` → `patient/patient_file_upload.php`
- `patient_file_download.php` → `patient/patient_file_download.php`
- `patient_file_view.php` → `patient/patient_file_view.php`
- `patient_file_delete.php` → `patient/patient_file_delete.php`

### Nurse/Staff Files → nurse/
- `nurse_dashboard.php` → `nurse/nurse_dashboard.php`
- `staff_dashboard.php` → `nurse/staff_dashboard.php`

### API Files → api/
- `files.php` → `api/files.php`
- `get_file_permissions.php` → `api/get_file_permissions.php`
- `user_lookup.php` → `api/user_lookup.php`
- `document_download.php` → `api/document_download.php`
- `document_stream.php` → `api/document_stream.php`
- `document_view.php` → `api/document_view.php`
- `download_stream.php` → `api/download_stream.php`
- `file_templates.php` → `api/file_templates.php`

### Includes → includes/
- `db_connect.php` → `includes/db_connect.php` ✓
- `phpmailer_config.php` → `includes/phpmailer_config.php` ✓

### SQL → sql/
- `document_management_system.sql` → `sql/document_management_system.sql` ✓

### Assets → assets/css/
- `root_colors_fonts.css` → `assets/css/root_colors_fonts.css` ✓

## Path Update Rules

### For Root Level Files:
- `require_once 'db_connect.php'` → `require_once __DIR__ . '/includes/db_connect.php'`
- `require_once 'phpmailer_config.php'` → `require_once __DIR__ . '/includes/phpmailer_config.php'`
- `href="root_colors_fonts.css"` → `href="assets/css/root_colors_fonts.css"`
- Admin redirects: `admin_home.php` → `admin/admin_home.php`
- Doctor redirects: `doctor_home.php` → `doctor/doctor_home.php`
- Patient redirects: `patient_home.php` → `patient/patient_home.php`

### For Admin Files (in admin/):
- `require_once 'db_connect.php'` → `require_once __DIR__ . '/../includes/db_connect.php'`
- `href="root_colors_fonts.css"` → `href="../assets/css/root_colors_fonts.css"`
- `header('Location: login.php')` → `header('Location: ../login.php')`
- `header('Location: admin_home.php')` → `header('Location: admin_home.php')` (same folder)
- `action="admin_file_templates_upload.php"` → `action="admin_file_templates_upload.php"` (same folder)
- `href="file_templates.php"` → `href="../api/file_templates.php"`
- `href="logout.php"` → `href="../logout.php"`

### For Doctor Files (in doctor/):
- `require_once 'db_connect.php'` → `require_once __DIR__ . '/../includes/db_connect.php'`
- `href="root_colors_fonts.css"` → `href="../assets/css/root_colors_fonts.css"`
- `header('Location: login.php')` → `header('Location: ../login.php')`
- `header('Location: dashboard.php')` → `header('Location: ../dashboard.php')`

### For Patient Files (in patient/):
- `require_once 'db_connect.php'` → `require_once __DIR__ . '/../includes/db_connect.php'`
- `href="root_colors_fonts.css"` → `href="../assets/css/root_colors_fonts.css"`
- `header('Location: login.php')` → `header('Location: ../login.php')`
- `header('Location: dashboard.php')` → `header('Location: ../dashboard.php')`
- `action="patient_file_upload.php"` → `action="patient_file_upload.php"` (same folder)

### For API Files (in api/):
- `require_once 'db_connect.php'` → `require_once __DIR__ . '/../includes/db_connect.php'`
- `href="root_colors_fonts.css"` → `href="../assets/css/root_colors_fonts.css"`
- `header('Location: login.php')` → `header('Location: ../login.php')`
- `header('Location: file_templates.php')` → `header('Location: file_templates.php')` (same folder)

## Status

- [x] Core includes moved and paths updated
- [x] SQL file moved
- [x] CSS file moved
- [x] Root auth files paths updated
- [ ] Admin files moved and paths updated
- [ ] Doctor files moved and paths updated
- [ ] Patient files moved and paths updated
- [ ] API files moved and paths updated
- [ ] All redirects and form actions updated
- [ ] All CSS/JS references updated

