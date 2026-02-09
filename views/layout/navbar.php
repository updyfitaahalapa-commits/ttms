<?php
  require_once '../../helpers/session.php';
  $role_id = $_SESSION['role_id'] ?? null;
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><i class="fas fa-cogs"></i>TTMS</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav mx-auto">
        <?php if ($role_id == 1 || $role_id == 2): // Admin or Manager ?>
          <li class="nav-item"><a class="nav-link" href="../../views/dashboard/home.php"><span style="color: white; font-size: 12pt;">Dashboard</span></a></li>
        <?php endif; ?>

        <?php if ($role_id == 1): // Admin only ?>
          <li class="nav-item"><a class="nav-link" href="../../views/tax_types/index.php"><span style="color: white; font-size: 12pt;">Tax Types</span></a></li>
        <?php endif; ?>

        <?php if ($role_id == 1 || $role_id == 2): // Admin only ?>
          <li class="nav-item"><a class="nav-link" href="../../views/owners/index.php"><span style="color: white; font-size: 12pt;">Owner</span></a></li>
        <?php endif; ?>

        <?php if ($role_id == 1 || $role_id == 2): // Admin only ?>
          <li class="nav-item"><a class="nav-link" href="../../views/vehicles/index.php"><span style="color: white; font-size: 12pt;">Vehicles</span></a></li>
        <?php endif; ?>

        <?php if ($role_id == 1): // Admin only ?>
          <li class="nav-item"><a class="nav-link" href="../../views/users/index.php"><span style="color: white; font-size: 12pt;">Users</span></a></li>
        <?php endif; ?>

        <?php if ($role_id == 1 || $role_id == 2): // Admin only ?>
          <li class="nav-item"><a class="nav-link" href="../../views/reports/taxes_report.php"><span style="color: white; font-size: 12pt;">Report</span></a></li>
        <?php endif; ?>
        <?php if ($role_id == 1 || $role_id == 2 || $role_id == 3): // Admin only ?>
          <li class="nav-item"><a class="nav-link" href="../../views/payments/Verifypayment.php"><span style="color: white; font-size: 12pt;">Verifier</span></a></li>
        <?php endif; ?>
      </ul>

      <ul class="navbar-nav ms-auto">
        <li class="nav-item">
          <a class="nav-link" href="../../views/change_password/index.php">
           <i class="fas fa-key"></i> Change Password
          </a>
        </li>

        <li class="nav-item">
          <a class="nav-link" href="../../controllers/AuthController.php?logout=true">
            <i class="fas fa-sign-out-alt"></i> Logout
          </a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>
<div class="container mt-4">
