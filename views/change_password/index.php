<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../helpers/session.php';
require_once '../../config/db.php';
?>

<div class="container mt-4">
  <h5 class="mb-3">Change Password</h5>

  <form method="POST" action="../../controllers/ChangePassword.php" class="w-50">
    <div class="mb-3">
      <label for="current_password" class="form-label">Current Password</label>
      <input type="password" name="current_password" id="current_password" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="new_password" class="form-label">New Password</label>
      <input type="password" name="new_password" id="new_password" class="form-control" required>
    </div>

    <div class="mb-3">
      <label for="confirm_password" class="form-label">Confirm Password</label>
      <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
    </div>

    <div class="d-flex justify-content-start gap-2">
      <button type="submit" name="change_password" class="btn btn-primary">
        <i class="fas fa-save"></i> Save
      </button>
      <a href="../dashboard/home.php" class="btn btn-secondary">Cancel</a>
    </div>
  </form>
</div>

<?php include '../layout/footer.php'; ?>
