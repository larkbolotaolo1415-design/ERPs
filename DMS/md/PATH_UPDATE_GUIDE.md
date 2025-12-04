# Path Update Guide - Remaining Work

## Files Already Updated ✓

### Root Level Files:
- ✓ login.php - db_connect, CSS, redirects updated
- ✓ dashboard.php - db_connect updated
- ✓ forgot_password.php - db_connect, phpmailer_config updated
- ✓ logout.php - db_connect updated
- ✓ change_password.php - db_connect updated
- ✓ reset_password.php - db_connect updated
- ✓ google_callback.php - db_connect updated
- ✓ setup_documents.php - db_connect updated
- ✓ setup_demo_accounts.php - db_connect updated
- ✓ diagnose_doctor_files.php - db_connect updated

### Admin Files (Partially Updated):
- ✓ admin_home.php - db_connect, CSS, login redirect updated
- ✓ admin_add_user.php - db_connect, CSS updated
- ✓ admin_audit_logs.php - db_connect, CSS updated
- ⚠ admin_documents.php - needs update
- ⚠ admin_file_templates_upload.php - needs update
- ⚠ admin_files_revoke.php - needs update
- ⚠ admin_permissions.php - needs update
- ⚠ admin_dashboard.php - needs update

## Remaining Updates Needed

### Pattern for Admin Files (in admin/ folder):
```php
// OLD:
require_once 'db_connect.php';
href="root_colors_fonts.css"
header('Location: login.php');
href="admin_home.php"
href="file_templates.php"
href="logout.php"

// NEW:
require_once __DIR__ . '/../includes/db_connect.php';
href="../assets/css/root_colors_fonts.css"
header('Location: ../login.php');
href="admin_home.php"  // same folder, no change
href="../api/file_templates.php"
href="../logout.php"
```

### Pattern for Doctor Files (in doctor/ folder):
```php
// OLD:
require_once 'db_connect.php';
href="root_colors_fonts.css"
header('Location: login.php');
header('Location: dashboard.php');

// NEW:
require_once __DIR__ . '/../includes/db_connect.php';
href="../assets/css/root_colors_fonts.css"
header('Location: ../login.php');
header('Location: ../dashboard.php');
```

### Pattern for Patient Files (in patient/ folder):
```php
// OLD:
require_once 'db_connect.php';
header('Location: dashboard.php');
action="patient_file_upload.php"

// NEW:
require_once __DIR__ . '/../includes/db_connect.php';
header('Location: ../dashboard.php');
action="patient_file_upload.php"  // same folder
```

### Pattern for API Files (in api/ folder):
```php
// OLD:
require_once 'db_connect.php';
href="root_colors_fonts.css"
header('Location: login.php');
header('Location: file_templates.php');

// NEW:
require_once __DIR__ . '/../includes/db_connect.php';
href="../assets/css/root_colors_fonts.css"
header('Location: ../login.php');
header('Location: file_templates.php');  // same folder
```

## Batch Update Commands

After updating paths, move files:

```bash
# Admin files
move admin_*.php admin\
move admin_dashboard.php admin\
move admin_home.php admin\

# Doctor files  
move doctor_*.php doctor\

# Patient files
move patient_*.php patient\

# API files
move files.php api\
move get_file_permissions.php api\
move user_lookup.php api\
move document_*.php api\
move download_stream.php api\
move file_templates.php api\

# Nurse files
move nurse_dashboard.php nurse\
move staff_dashboard.php nurse\
```

## Critical Redirects to Update

### In admin files:
- `admin_home.php` → `admin_home.php` (same folder)
- `login.php` → `../login.php`
- `logout.php` → `../logout.php`
- `file_templates.php` → `../api/file_templates.php`
- `admin_documents.php` → `admin_documents.php` (same folder)
- `admin_add_user.php` → `admin_add_user.php` (same folder)

### In patient files:
- `dashboard.php` → `../dashboard.php`
- `patient_file_upload.php` → `patient_file_upload.php` (same folder)

### In doctor files:
- `dashboard.php` → `../dashboard.php`
- `doctor_home.php` → `doctor_home.php` (same folder)

## Form Actions to Update

### In dashboard_template.php (root):
- `action="patient_file_upload.php"` → `action="patient/patient_file_upload.php"`

### In admin files:
- `action="admin_file_templates_upload.php"` → `action="admin_file_templates_upload.php"` (same folder)
- `action="admin_permissions.php"` → `action="admin_permissions.php"` (same folder)
- `action="admin_files_revoke.php"` → `action="admin_files_revoke.php"` (same folder)

## Next Steps

1. Update remaining admin files using the pattern above
2. Update all doctor files
3. Update all patient files  
4. Update all API files
5. Move files to their folders
6. Update dashboard_template.php form actions
7. Test all redirects and form submissions
8. Verify file uploads/downloads work
9. Test login/logout flows

