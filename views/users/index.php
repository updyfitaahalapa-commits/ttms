<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../helpers/session.php';
require_once '../../config/db.php';
require_once '../../models/User.php';

// Fetch users
$model = new User($pdo);
$data = $model->getAll();
$roles = $model->getroles();
?>

<h5>Users</h5>

<!-- Success/Error Message -->
<?php if (isset($_SESSION['success'])): ?>
  <div class="alert alert-success"><?= $_SESSION['success']; unset($_SESSION['success']); ?></div>
  <script>
    setTimeout(function() {
      window.location.reload();
    }, 2000); 
  </script>
<?php endif; ?>
<?php if (isset($_SESSION['error'])): ?>
  <div class="alert alert-danger"><?= $_SESSION['error']; unset($_SESSION['error']); ?></div>
<?php endif; ?>

<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="fas fa-plus"></i> Add User
  </button>
</div>

<table class="table table-bordered" id="dtable">
  <thead>
    <tr>
      <th>ID</th>
      <th>Full Name</th>
      <th>Username</th>
      <th>Email</th>
      <th>Phone</th>
      <th>Role</th>
      <th>Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($data as $row): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= htmlspecialchars($row['full_name']) ?></td>
        <td><?= htmlspecialchars($row['username']) ?></td>
        <td><?= htmlspecialchars($row['email']) ?></td>
        <td><?= htmlspecialchars($row['phone']) ?></td>
        <td><?= $row['role_name'] ?></td>
        <td><?= $row['status'] ?></td>
        <td>
          <button class='btn btn-sm btn-warning edit-btn'
            data-id='<?= $row['id'] ?>'
            data-full_name='<?= $row['full_name'] ?>'
            data-username='<?= $row['username'] ?>'
            data-email='<?= $row['email'] ?>'
            data-phone='<?= $row['phone'] ?>'
            data-role_id='<?= $row['role_id'] ?>'
            data-status='<?= $row['status'] ?>'>Edit</button>

          <a href='../../controllers/UserController.php?action=delete&id=<?= $row['id'] ?>' 
             class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>

          <a href='../../controllers/UserController.php?action=reset_password&id=<?= $row['id'] ?>' 
             class='btn btn-secondary btn-sm'
             onclick="return confirm('Reset password for <?= htmlspecialchars($row['username']) ?> to default (123)?');">
            <i class='fas fa-key'></i> Reset
          </a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="../../controllers/UserController.php">
      <div class="modal-header"><h5 class="modal-title">Add User</h5></div>
      <div class="modal-body">
        <div class="mb-2"><label>Full Name <span class="text-danger">*</span></label><input type="text" name="full_name" class="form-control" required></div>
        <div class="mb-2"><label>Username <span class="text-danger">*</span></label><input type="text" name="username" class="form-control" required></div>
        <div class="mb-2"><label>Password <span class="text-danger">*</span></label><input type="password" name="password" class="form-control" required></div>
        <div class="mb-2"><label>Email <span class="text-danger">*</span></label><input type="email" name="email" class="form-control" required></div>
        <div class="mb-2"><label>Phone <span class="text-danger">*</span></label><input type="text" name="phone" class="form-control" required></div>
        <div class="mb-2"><label>Role <span class="text-danger">*</span></label>
          <select name="role_id" class="form-control" required>
            <option value="">-- Select Role --</option>
            <?php foreach ($roles as $role): ?>
              <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label>Status <span class="text-danger">*</span></label>
          <select name="status" class="form-control" required>
            <option value="Active">Active</option>
            <option value="InActive">InActive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="create" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="../../controllers/UserController.php">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-header"><h5 class="modal-title">Edit User</h5></div>
      <div class="modal-body">
        <div class="mb-2"><label>Full Name <span class="text-danger">*</span></label><input type="text" name="full_name" id="edit-full_name" class="form-control" required></div>
        <div class="mb-2"><label>Username <span class="text-danger">*</span></label><input type="text" name="username" id="edit-username" class="form-control" required></div>
        <div class="mb-2"><label>Email <span class="text-danger">*</span></label><input type="email" name="email" id="edit-email" class="form-control" required></div>
        <div class="mb-2"><label>Phone <span class="text-danger">*</span></label><input type="text" name="phone" id="edit-phone" class="form-control" required></div>
        <div class="mb-2"><label>Role <span class="text-danger">*</span></label>
          <select name="role_id" id="edit-role_id" class="form-control" required>
            <option value="">-- Select Role --</option>
            <?php foreach ($roles as $role): ?>
              <option value="<?= $role['id'] ?>"><?= htmlspecialchars($role['role_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2"><label>Status <span class="text-danger">*</span></label>
          <select name="status" id="edit-status" class="form-control" required>
            <option value="Active">Active</option>
            <option value="InActive">InActive</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" name="update" class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  new DataTable('#dtable');
  document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function () {
      document.getElementById('edit-id').value = this.dataset.id;
      document.getElementById('edit-full_name').value = this.dataset.full_name;
      document.getElementById('edit-username').value = this.dataset.username;
      document.getElementById('edit-email').value = this.dataset.email;
      document.getElementById('edit-phone').value = this.dataset.phone;
      document.getElementById('edit-role_id').value = this.dataset.role_id;
      document.getElementById('edit-status').value = this.dataset.status;
      new bootstrap.Modal(document.getElementById('editModal')).show();
    });
  });
});
</script>

<?php include '../layout/footer.php'; ?>
