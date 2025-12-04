<#
  complete_reorganization.ps1
  Usage:
    # Dry run (simulate)
    .\complete_reorganization.ps1 -DryRun

    # Actual run
    .\complete_reorganization.ps1
#>

param(
    [switch]$DryRun
)

# Helper: perform action or simulate
function Do-Action {
    param($ScriptBlock, $Message)
    if ($DryRun) {
        Write-Host "[DRY RUN] $Message"
        # If the command supports -WhatIf, you could call it; we simply print the message here.
    } else {
        Invoke-Command -ScriptBlock $ScriptBlock
        Write-Host "[DONE] $Message"
    }
}

# Logging
$logFile = Join-Path -Path (Get-Location) -ChildPath "reorg_log_$(Get-Date -Format yyyyMMdd_HHmmss).txt"
Write-Host "Log: $logFile"
Add-Content -Path $logFile -Value "Reorganization started at $(Get-Date) (DryRun=$DryRun)"

# Folders to ensure exist
$folders = @(
    'admin', 'doctor', 'patient', 'nurse', 'api', 'includes', 'sql',
    'assets\css', 'uploads\patient_files', 'uploads\doctor_files'
)
foreach ($folder in $folders) {
    $fullPath = Join-Path (Get-Location) $folder
    if (-not (Test-Path $fullPath)) {
        Do-Action -ScriptBlock { New-Item -ItemType Directory -Path $using:fullPath -Force | Out-Null } -Message "Ensure folder exists: $folder"
        Add-Content -Path $logFile -Value "Folder ensured: $folder"
    } else {
        Write-Host "Exists: $folder"
        Add-Content -Path $logFile -Value "Folder exists: $folder"
    }
}

# Function to move sets of files
function Move-Set {
    param($files, $destFolder)
    foreach ($file in $files) {
        if (Test-Path $file) {
            $dest = Join-Path -Path $destFolder -ChildPath $file
            Do-Action -ScriptBlock { Move-Item -Path $using:file -Destination $using:dest -Force } -Message "Move: $file -> $dest"
            Add-Content -Path $logFile -Value "Moved: $file -> $dest"
        } else {
            Write-Host "Not found (skipped): $file"
            Add-Content -Path $logFile -Value "Not found (skipped): $file"
        }
    }
}

# Sets of files (edit these lists if your filenames differ)
$adminFiles = @('admin_home.php','admin_dashboard.php','admin_add_user.php','admin_audit_logs.php','admin_documents.php','admin_file_templates_upload.php','admin_files_revoke.php','admin_permissions.php')
$doctorFiles = @('doctor_home.php','doctor_dashboard.php','doctor_documents.php')
$patientFiles = @('patient_home.php','patient_dashboard.php','patient_file_upload.php','patient_file_download.php','patient_file_view.php','patient_file_delete.php')
$nurseFiles = @('nurse_dashboard.php','staff_dashboard.php')
$apiFiles   = @('files.php','get_file_permissions.php','user_lookup.php','document_download.php','document_stream.php','document_view.php','download_stream.php','file_templates.php')

# Move them
Move-Set -files $adminFiles -destFolder (Join-Path (Get-Location) 'admin')
Move-Set -files $doctorFiles -destFolder (Join-Path (Get-Location) 'doctor')
Move-Set -files $patientFiles -destFolder (Join-Path (Get-Location) 'patient')
Move-Set -files $nurseFiles -destFolder (Join-Path (Get-Location) 'nurse')
Move-Set -files $apiFiles -destFolder (Join-Path (Get-Location) 'api')

# Core files
if (Test-Path 'db_connect.php') {
    $dest = Join-Path (Get-Location) 'includes\db_connect.php'
    Do-Action -ScriptBlock { Move-Item -Path 'db_connect.php' -Destination $using:dest -Force } -Message "Move: db_connect.php -> includes\db_connect.php"
    Add-Content -Path $logFile -Value "Moved: db_connect.php -> includes\db_connect.php"
}
if (Test-Path 'phpmailer_config.php') {
    $dest = Join-Path (Get-Location) 'includes\phpmailer_config.php'
    Do-Action -ScriptBlock { Move-Item -Path 'phpmailer_config.php' -Destination $using:dest -Force } -Message "Move: phpmailer_config.php -> includes\phpmailer_config.php"
    Add-Content -Path $logFile -Value "Moved: phpmailer_config.php -> includes\phpmailer_config.php"
}
if (Test-Path 'document_management_system.sql') {
    $dest = Join-Path (Get-Location) 'sql\document_management_system.sql'
    Do-Action -ScriptBlock { Move-Item -Path 'document_management_system.sql' -Destination $using:dest -Force } -Message "Move: document_management_system.sql -> sql\document_management_system.sql"
    Add-Content -Path $logFile -Value "Moved: document_management_system.sql -> sql\document_management_system.sql"
}
if (Test-Path 'root_colors_fonts.css') {
    $dest = Join-Path (Get-Location) 'assets\css\root_colors_fonts.css'
    Do-Action -ScriptBlock { Move-Item -Path 'root_colors_fonts.css' -Destination $using:dest -Force } -Message "Move: root_colors_fonts.css -> assets\css\root_colors_fonts.css"
    Add-Content -Path $logFile -Value "Moved: root_colors_fonts.css -> assets\css\root_colors_fonts.css"
}

Add-Content -Path $logFile -Value "Reorganization finished at $(Get-Date) (DryRun=$DryRun)"
Write-Host "`nReorganization complete! (DryRun=$DryRun)"
Write-Host "Check log: $logFile"
