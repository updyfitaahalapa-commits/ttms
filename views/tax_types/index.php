<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../helpers/session.php';
require_once '../../config/db.php';
require_once '../../models/TaxType.php';

$model = new TaxType($pdo);
$data = $model->getAll();
?>
<h5>Tax Types</h5>
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="fas fa-plus"></i> Add Tax Type
  </button>
</div>



<table class="table table-bordered" id="dtable">
  <thead>
    <tr>
      <th>ID</th>
      <th>Name</th>
      <th>Description</th>
      <th>Amount</th>
      <th>Frequency</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach($data as $row): ?>
      <tr>
        <td><?= $row['id'] ?></td>
        <td><?= $row['name'] ?></td>
        <td><?= $row['description'] ?></td>
        <td>$<?= $row['amount'] ?></td>
        <td><?= $row['frequency'] ?></td>
        <td>
          <button class='btn btn-sm btn-warning edit-btn'
            data-id='<?= $row['id'] ?>'
            data-name='<?= $row['name'] ?>'
            data-description='<?= $row['description'] ?>'
            data-amount='<?= $row['amount'] ?>'
            data-frequency='<?= $row['frequency'] ?>'>Edit</button>
          <a href='../../controllers/TaxTypeController.php?delete=<?= $row['id'] ?>' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="../../controllers/TaxTypeController.php">
      <div class="modal-header"><h5 class="modal-title">Add Tax Type</h5></div>
      <div class="modal-body">
        <div class="mb-2"><label>Name <span class="text-danger">*</span></label><input type="text" name="name" class="form-control" required></div>
        <div class="mb-2"><label>Description <span class="text-danger">*</span></label><input type="text" name="description" class="form-control" required></div>
        <div class="mb-2"><label>Amount <span class="text-danger">*</span></label><input type="number" name="amount" class="form-control" step="0.01" required></div>
        <div class="mb-2"><label>Frequency <span class="text-danger">*</span></label>
          <select name="frequency" class="form-control" required>
            <option value="">Select Frequency</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
          </select>
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Save</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
  <div class="modal-dialog">
    <form class="modal-content" method="POST" action="../../controllers/TaxTypeController.php">
      <input type="hidden" name="id" id="edit-id">
      <div class="modal-header"><h5 class="modal-title">Edit Tax Type</h5></div>
      <div class="modal-body">
        <div class="mb-2"><label>Name <span class="text-danger">*</span></label><input type="text" name="name" id="edit-name" class="form-control" required></div>
        <div class="mb-2"><label>Description <span class="text-danger">*</span></label><input type="text" name="description" id="edit-description" class="form-control" required></div>
        <div class="mb-2"><label>Amount <span class="text-danger">*</span></label><input type="number" name="amount" id="edit-amount" class="form-control" step="0.01" required></div>
        <div class="mb-2"><label>Frequency <span class="text-danger">*</span></label>
          <select name="frequency" id="edit-frequency" class="form-control" required>
            <option value="">Select Frequency</option>
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
            <option value="monthly">Monthly</option>
            <option value="yearly">Yearly</option>
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
      document.getElementById('edit-name').value = this.dataset.name;
      document.getElementById('edit-description').value = this.dataset.description;
      document.getElementById('edit-amount').value = this.dataset.amount;
      document.getElementById('edit-frequency').value = this.dataset.frequency;
      new bootstrap.Modal(document.getElementById('editModal')).show();
    });
  });
  // Round amount before Add form submission
  document.querySelector('#addModal form').addEventListener('submit', function () {
    let amountInput = this.querySelector('input[name="amount"]');
    if (amountInput && amountInput.value) {
      amountInput.value = Math.round(parseFloat(amountInput.value));
    }
  });

  // Round amount before Edit form submission
  document.querySelector('#editModal form').addEventListener('submit', function () {
    let amountInput = this.querySelector('input[name="amount"]');
    if (amountInput && amountInput.value) {
      amountInput.value = Math.round(parseFloat(amountInput.value));
    }
  });

});
</script>
<?php include '../layout/footer.php'; ?>
