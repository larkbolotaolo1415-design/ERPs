# Doctor File Display Fix - Complete Summary

## Problem Identified
Patient-uploaded files were being saved to the database but NOT appearing on the assigned Doctor's dashboard.

## Root Cause
The SQL query in `dashboard_template.php` was trying to SELECT columns (`phone`, `date_of_birth`, `gender`) that don't exist in the `users` table, causing a SQL error that was silently caught by an empty catch block.

## Fixes Applied

### 1. Fixed Doctor Query (`dashboard_template.php`)

**Problem:** Query was selecting non-existent columns
```sql
SELECT DISTINCT u.id, u.name, u.email, u.phone, u.date_of_birth, u.gender, ...
GROUP BY u.id, u.name, u.email, u.phone, u.date_of_birth, u.gender
```

**Solution:** Removed non-existent columns from SELECT and GROUP BY
```sql
SELECT DISTINCT 
    u.id, 
    u.name, 
    u.email,
    MIN(pfa.granted_date) AS assigned_date
FROM users u
INNER JOIN patient_files pf ON pf.patient_id = u.id
INNER JOIN patient_file_access pfa ON pfa.file_id = pf.id
WHERE pfa.doctor_id = ? AND u.role = 'patient'
GROUP BY u.id, u.name, u.email
ORDER BY u.name
```

**Result:** Query now works even if optional columns don't exist in users table.

### 2. Enhanced Error Handling

**Added:**
- Error logging to PHP error log
- Error variable (`$doctorQueryError`) to track failures
- Optional error display in debug mode (add `?debug=1` to URL)
- Better exception handling in upload handler

### 3. Improved Upload Handler (`patient_file_upload.php`)

**Added:**
- Better error handling for doctor assignment
- Logging when files are assigned to doctors
- Count of successfully assigned doctors
- Continues processing even if one doctor assignment fails
- Warning logs if no doctors are assigned

### 4. Display Code Already Handles Missing Columns

The display code in `dashboard_template.php` already uses safe operators:
- `$patient['gender'] ?? 'N/A'` - handles missing gender
- `!empty($patient['phone'])` - only shows phone if it exists
- `!empty($patient['date_of_birth'])` - only calculates age if DOB exists

## Database Structure

The system uses two tables:

### `patient_files`
- Stores the actual file data (BLOB)
- Links to patient via `patient_id`
- Does NOT directly link to doctors

**Columns:**
- `id` (INT, PK, AUTO_INCREMENT)
- `patient_id` (INT)
- `file_name` (VARCHAR(255))
- `original_filename` (VARCHAR(255))
- `file_size` (BIGINT)
- `mime_type` (VARCHAR(100))
- `file_data` (LONGBLOB)
- `upload_date` (TIMESTAMP)
- `description` (TEXT, nullable)

### `patient_file_access`
- Junction table linking files to doctors
- This is the critical table for doctor file visibility

**Columns:**
- `id` (INT, PK, AUTO_INCREMENT)
- `file_id` (INT) - Foreign key to patient_files
- `doctor_id` (INT) - Foreign key to users
- `granted_date` (TIMESTAMP)

**Unique Constraint:** (`file_id`, `doctor_id`) - prevents duplicate assignments

## How It Works Now

### Upload Flow:
1. Patient uploads file → Saved to `patient_files` table
2. Patient selects doctor(s) → Records created in `patient_file_access` table
3. Transaction ensures both succeed or both fail

### Doctor View Flow:
1. Query finds all patients who have files assigned to this doctor
2. For each patient, fetches all their files assigned to this doctor
3. Displays in profile cards with collapsible file lists
4. Shows recently uploaded files in separate section

## Testing & Verification

### Step 1: Run Diagnostic Script
Access `diagnose_doctor_files.php` in browser (while logged in as doctor) or via command line:
```bash
php diagnose_doctor_files.php [doctor_id]
```

This will show:
- Doctor information
- File access records
- Test of dashboard query
- All patient files
- Database structure

### Step 2: Verify Database Structure
Run the SQL in `fix_doctor_file_display.sql` to ensure tables exist with correct structure.

### Step 3: Test Upload Flow
1. Log in as patient
2. Upload a file and select a doctor
3. Check `patient_file_access` table to verify record was created:
   ```sql
   SELECT * FROM patient_file_access WHERE doctor_id = [doctor_id];
   ```
4. Log in as the selected doctor
5. Verify files appear in dashboard

### Step 4: Check Error Logs
If files still don't appear, check PHP error log for:
- Query errors
- Upload assignment failures
- Database connection issues

## SQL Queries for Manual Verification

### Check if files are assigned to a doctor:
```sql
SELECT pf.id, pf.original_filename, pf.patient_id, u1.name AS patient_name,
       pfa.doctor_id, u2.name AS doctor_name, pfa.granted_date
FROM patient_files pf
LEFT JOIN patient_file_access pfa ON pfa.file_id = pf.id
LEFT JOIN users u1 ON u1.id = pf.patient_id
LEFT JOIN users u2 ON u2.id = pfa.doctor_id
WHERE pfa.doctor_id = 2  -- Replace with actual doctor ID
ORDER BY pf.upload_date DESC;
```

### Check all file assignments:
```sql
SELECT COUNT(*) as total_assignments
FROM patient_file_access;
```

### Check files without assignments:
```sql
SELECT pf.id, pf.original_filename, pf.patient_id
FROM patient_files pf
LEFT JOIN patient_file_access pfa ON pfa.file_id = pf.id
WHERE pfa.id IS NULL;
```

## Files Modified

1. **dashboard_template.php**
   - Fixed doctor queries (removed non-existent columns)
   - Added error handling and logging
   - Added debug mode error display

2. **patient_file_upload.php**
   - Enhanced doctor assignment logic
   - Added logging for troubleshooting

3. **document_stream.php**
   - Already correct (uses `patient_file_access` table)

## Files Created

1. **diagnose_doctor_files.php** - Comprehensive diagnostic tool
2. **fix_doctor_file_display.sql** - Database verification SQL
3. **DOCTOR_FILE_FIX_SUMMARY.md** - This document

## Next Steps if Issue Persists

1. Run `diagnose_doctor_files.php` to identify specific issue
2. Check PHP error logs for detailed error messages
3. Verify `patient_file_access` table has records after upload
4. Check that doctor's `user_type_id` is set to 2 (or role is 'doctor')
5. Verify foreign key constraints are working correctly
6. Check if there are any permission issues with the database user

## Debug Mode

To see errors on the dashboard, add `?debug=1` to the URL:
```
dashboard.php?debug=1
```

This will display any query errors that are preventing files from showing.

## Notes

- The display code gracefully handles missing optional columns (phone, date_of_birth, gender)
- Error messages are only shown in debug mode
- All queries use prepared statements to prevent SQL injection
- Transactions ensure data consistency during uploads
- The system works even if optional user columns don't exist


