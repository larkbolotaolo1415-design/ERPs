 <!-- Copy Paste This php block -->
<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../init.php';
$pageTitle = "Employee Profile"; // Change to correct title 
$userName = $_SESSION['user_name'] ?? 'User';
?> 


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Profile</title>

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/dashboard_style.css">

  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f4f6f9;
      margin: 0;
    }



    .profile-card {
      background: #fff;
      border-radius: 15px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
      padding: 20px 30px;
      position: relative;
    }

    .profile-card .icons {
      position: absolute;
      top: 15px;
      right: 20px;
    }

    .profile-card .icons i {
      font-size: 20px;
      margin-left: 15px;
      color: #444;
      cursor: pointer;
    }

    .employee-header {
      display: flex;
      align-items: center;
      margin-bottom: 25px;
      border-bottom: 3px solid #0a2a66;
      padding-bottom: 10px;
    }

    .employee-header img {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      margin-right: 20px;
    }

    .card-section {
      display: flex;
      gap: 20px;
      flex-wrap: wrap;
    }

    .card-box {
      flex: 1;
      min-width: 300px;
      background: #f8f9fc;
      border: 1px solid #ddd;
      border-radius: 10px;
      padding: 20px;
    }

    .card-box h6 {
      text-align: center;
      border-bottom: 2px solid #ccc;
      padding-bottom: 5px;
      font-weight: 600;
      margin-bottom: 15px;
    }

    .modal-content {
      border-radius: 15px;
    }

    .nav-tabs .nav-link {
      color: #0a2a66;
      font-weight: 500;
    }

    .nav-tabs .nav-link.active {
      background-color: #0a2a66;
      color: white;
    }
  </style>
</head>
<body>

    <?php include __DIR__ . '/../includes/layout/sidebar.php'; ?>
    <?php include __DIR__ . '/../includes/layout/topbar.php'; ?>

    <div class="main-content p-4 mt-5">
    <div class="profile-card">
      <div class="icons">
        <i class="bi bi-bell"></i>
        <i class="bi bi-pencil-square" data-bs-toggle="modal" data-bs-target="#editModal"></i>
      </div>

      <div class="employee-header">
        <div>
          <h5>Charl Joven Castro</h5>
          <small>Department | Position</small><br>
          <small>ID: 1001</small>
        </div>
      </div>

      <div class="card-section">
        <div class="card-box">
          <h6>Employee Details</h6>
          <p><strong>Date Hired:</strong> MM-DD-YYYY</p>
          <p><strong>Employment Status:</strong> Regular</p>
          <p><strong>Employee Type:</strong> Full Time</p>
          <p><strong>Basic Salary:</strong> ₱69,696.96</p>
          <p><strong>Work Schedule:</strong> 7:00AM - 5:00PM</p>
        </div>

        <div class="card-box">
          <h6>Bank & Payment Details</h6>
          <p><strong>Bank Name:</strong> BDO</p>
          <p><strong>Account Number:</strong> XXXX-XXXX-XXXX-XXXX</p>
          <p><strong>Payment Method:</strong> GCash, PayMaya</p>
          <p><strong>Contact Number:</strong> +639XXXXXXXXX</p>
          <p><strong>Last Payment Date:</strong> MM-DD-YYYY</p>
        </div>

        <div class="card-box">
          <h6>Contribution Record</h6>
          <p><strong>SSS Posted Amount:</strong> ₱69,696.96</p>
          <p><strong>PhilHealth Posted Amount:</strong> ₱69,696.96</p>
          <p><strong>Pag-IBIG Posted Amount:</strong> ₱69,696.96</p>
          <p><strong>Total Amount from Record:</strong> ₱69,696.96</p>
        </div>
      </div>
    </div>
  </div>

  <!-- Edit Modal -->
  <div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title">Edit Payroll Information</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <div class="modal-body">
          <!-- Nav Tabs -->
          <ul class="nav nav-tabs mb-3" id="editTabs">
            <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#personal">Personal Info</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#bank">Bank Details</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#contribution">Contribution</a></li>
            <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#changelog">Change Log</a></li>
          </ul>

          <!-- Tab Content -->
          <div class="tab-content">
            <div class="tab-pane fade show active" id="personal">
              <div class="row">
                <div class="col-md-6 mb-3">
                  <label>Full Name</label>
                  <input type="text" class="form-control" value="Charl Joven Castro" readonly>
                </div>
                <div class="col-md-6 mb-3">
                  <label>Employee ID</label>
                  <input type="text" class="form-control" value="1001" readonly>
                </div>
                <div class="col-md-6 mb-3">
                  <label>Position</label>
                  <input type="text" class="form-control" value="Nurse" readonly>
                </div>
                <div class="col-md-6 mb-3">
                  <label>Department</label>
                  <input type="text" class="form-control" value="Medical Ward" readonly>
                </div>
              </div>
            </div>

            <div class="tab-pane fade" id="bank">
              <p><strong>Bank Name:</strong> BDO</p>
              <p><strong>Account Number:</strong> XXXX-XXXX-XXXX-XXXX</p>
              <p><strong>Payment Method:</strong> GCash, PayMaya</p>
              <p><strong>Contact Number:</strong> +639XXXXXXXXX</p>
            </div>

            <div class="tab-pane fade" id="contribution">
              <p><strong>SSS Contribution:</strong> ₱69,696.96</p>
              <p><strong>PhilHealth Contribution:</strong> ₱69,696.96</p>
              <p><strong>Pag-IBIG Contribution:</strong> ₱69,696.96</p>
            </div>

            <div class="tab-pane fade" id="changelog">
              <table class="table table-striped">
                <thead>
                  <tr>
                    <th>Date</th>
                    <th>Changed By</th>
                    <th>Change Description</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>2025-10-29</td>
                    <td>Admin</td>
                    <td>Updated bank details.</td>
                  </tr>
                  <tr>
                    <td>2025-10-25</td>
                    <td>HR</td>
                    <td>Added PhilHealth contribution record.</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
          <button class="btn btn-primary">Save Changes</button>
        </div>
      </div>
    </div>
  </div>
</body>
</html>
