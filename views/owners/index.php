<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../helpers/session.php';
require_once '../../config/db.php';
require_once '../../models/Owner.php';

$model = new Owner($pdo);
$owners = $model->getAll();
$Districts = $model->getDistrictsAll();
?>
<h5>Owners</h5>
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#addModal">Add Owner</button>
</div>
<table class="table table-bordered"  id="dtable">
  <thead>
    <tr><th>ID</th><th>District</th><th>Name</th><th>ID No</th><th>Phone</th><th>Email</th><th>Address</th><th>Actions</th></tr>
  </thead>
  <tbody>
    <?php foreach ($owners as $row): ?>
    <tr>
      <td><?= $row['owner_id'] ?></td>
      <td><?= $row['district_name'] ?></td>
      <td><?= $row['full_name'] ?></td>
      <td>
        <?php if (!empty($row['ow_id'])): ?>
          <a href="uploads/<?= $row['ow_id'] ?>" target="_blank">View Document</a>
        <?php else: ?>
          No File
        <?php endif; ?>
      </td>
      <td><?= $row['phone'] ?></td>
      <td><?= $row['email'] ?></td>
      <td><?= $row['address'] ?></td>
      <td>
        <button class="btn btn-sm btn-warning edit-btn"
          data-id="<?= $row['owner_id'] ?>"
          data-district_id="<?= $row['district_id'] ?>"
          data-full_name="<?= $row['full_name'] ?>"
          data-ow_id="<?= $row['ow_id'] ?>"
          data-phone="<?= $row['phone'] ?>"
          data-email="<?= $row['email'] ?>"
          data-address="<?= $row['address'] ?>">Edit</button>
        <a href="../../controllers/OwnerController.php?delete=<?= $row['owner_id'] ?>" class="btn btn-danger btn-sm">Delete</a>
      </td>
    </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" enctype="multipart/form-data" action="../../controllers/OwnerController.php">
      <input type="hidden" name="create" value="1">
      <div class="modal-header"><h5 class="modal-title">Add Owner</h5></div>
      <div class="modal-body">
        <div class="mb-2">
          <label>District <span class="text-danger">*</span></label>
          <select name="district_id" class="form-control" required>
            <option value="">Select District</option>
            <?php foreach ($Districts as $d): ?>
              <option value="<?= $d['id'] ?>"><?= $d['name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-2">
          <label>Full Name <span class="text-danger">*</span></label>
          <input name="full_name" class="form-control" placeholder="Full Name" required>
        </div>
        <div class="mb-2">
          <label>ID Document <span class="text-danger">*</span></label>
          <input type="file" name="ow_id" class="form-control" required>
        </div>
        <div class="mb-2">
          <label>Phone <span class="text-danger">*</span></label>
          <input name="phone" class="form-control" placeholder="Phone" required>
        </div>
        <div class="mb-2">
          <label>Email <span class="text-danger">*</span></label>
          <input type="email" name="email" class="form-control" placeholder="Email" required>
        </div>
        <div class="mb-2">
          <label>Address <span class="text-danger">*</span></label>
          <input name="address" class="form-control" placeholder="Address" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" enctype="multipart/form-data" action="../../controllers/OwnerController.php">
      <input type="hidden" name="update" value="1">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-header"><h5 class="modal-title">Edit Owner</h5></div>
      <div class="modal-body">
        <div class="mb-2">
          <label>District <span class="text-danger">*</span></label>
          <select name="district_id" id="edit-district_id" class="form-control" required>
            <option value="">Select District</option>
            <?php foreach ($Districts as $d): ?>
              <option value="<?= $d['id'] ?>"><?= $d['name'] ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-2">
          <label>Full Name <span class="text-danger">*</span></label>
          <input name="full_name" id="edit-full_name" class="form-control" placeholder="Full Name" required>
        </div>

        <div class="mb-2">
          <label>ID Document</label>
          <input type="file" name="ow_id" id="edit-ow_id" class="form-control">
          <!-- Show current uploaded file -->
          <div id="current-file-preview" class="mt-2"></div>
        </div>

        <div class="mb-2">
          <label>Phone <span class="text-danger">*</span></label>
          <input name="phone" id="edit-phone" class="form-control" placeholder="Phone" required>
        </div>
        <div class="mb-2">
          <label>Email <span class="text-danger">*</span></label>
          <input type="email" name="email" id="edit-email" class="form-control" placeholder="Email" required>
        </div>
        <div class="mb-2">
          <label>Address <span class="text-danger">*</span></label>
          <input name="address" id="edit-address" class="form-control" placeholder="Address" required>
        </div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>


<script>
  document.addEventListener('DOMContentLoaded', function () {
    new DataTable('#dtable');
    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.onclick = () => {
        document.getElementById('edit-id').value = btn.dataset.id;
        document.getElementById('edit-district_id').value = btn.dataset.district_id;
        document.getElementById('edit-full_name').value = btn.dataset.full_name;
        document.getElementById('edit-phone').value = btn.dataset.phone;
        document.getElementById('edit-email').value = btn.dataset.email;
        document.getElementById('edit-address').value = btn.dataset.address;

        // File input cannot be pre-filled, but we can show link to current file
        const previewDiv = document.getElementById('current-file-preview');
        if (btn.dataset.ow_id) {
          previewDiv.innerHTML = `<a href="uploads/${btn.dataset.ow_id}" target="_blank" class="btn btn-info btn-sm">Current ID File</a>`;
        } else {
          previewDiv.innerHTML = `<span class="text-muted">No ID file uploaded</span>`;
        }

        new bootstrap.Modal(document.getElementById('editModal')).show();
      };
    });
  });
</script>


<?php include '../layout/footer.php'; ?>
