<?php
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../helpers/session.php';
require_once '../../config/db.php';
require_once '../../models/Vehicle.php';
require_once '../../models/Owner.php';
require_once '../../models/TaxType.php';

$vehicleModel = new Vehicle($pdo);
$ownerModel = new Owner($pdo);
$TaxTypeModel = new TaxType($pdo);
$vehicles = $vehicleModel->getAll();
$owners = $ownerModel->getAll();
$TaxType = $TaxTypeModel->getAll();

?>

<h5>Vehicles</h5>
<div class="d-flex justify-content-end mb-3">
  <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
    <i class="fas fa-plus"></i> Add Vehicle
  </button>
</div>

<!-- DataTables CSS/JS -->
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap5.min.css">
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap5.min.js"></script>

<table class="table table-bordered" id="vehicleTable">
  <thead>
    <tr>
      <th>ID</th>
      <th>Owner</th>
      <th>Plate Number</th>
      <th>Type</th>
      <th>Model</th>
      <th>Registration Date</th>
      <th>Vehicle Status</th>
      <th>Payment Status</th>
      <th>Actions</th>
    </tr>
  </thead>
  <tbody>
    <?php 
      $i=0;
      foreach($vehicles as $row): 
        $i++;
        $PaymentStatus=$row['Tstatus'];
        $vehiclesStatus=$row['status'];
        // unpaid', 'paid', 'overdue', 'verified'
        $TheStatus='';
        $The_Status='';
        if ($PaymentStatus=='unpaid') 
        {
          $TheStatus='<span style="color: blue; font-size: 14pt; font-weight: bold;">'.$PaymentStatus.'</span>';
        }

        if ($PaymentStatus=='paid') 
        {
          $TheStatus='<span style="color: green; font-size: 14pt; font-weight: bold;">'.$PaymentStatus.'</span>';
        }

        if ($PaymentStatus=='overdue') 
        {
          $TheStatus='<span style="color: red; font-size: 14pt; font-weight: bold;">'.$PaymentStatus.'</span>';
        }

        if ($vehiclesStatus=="active") 
        {
          $The_Status='<span style="color: green; font-size: 14pt; font-weight: bold;">'.$vehiclesStatus.'</span>';
        }

        if ($vehiclesStatus=="Inactive") 
        {
          $The_Status='<span style="color: red; font-size: 14pt; font-weight: bold;">'.$vehiclesStatus.'</span>';
        }
      ?>
      <tr>
        <td><?= $i ?></td>
        <td><?= $row['owner_name'] ?></td>
        <td><?= $row['plate_number'] ?></td>
        <td><?= $row['vehicle_type'] ?></td>
        <td><?= $row['model'] ?></td>
        <td><?= $row['RegDate'] ?></td>
        <td><?= $The_Status ?></td>
        <td><?= $TheStatus ?></td>

        <td>
          <button class="btn btn-sm btn-info view-btn"
            data-vehicle_id="<?= $row['id'] ?>"
            data-bs-toggle="modal"
            data-bs-target="#viewModal">
            View
          </button>

          <button class="btn btn-sm btn-primary edit-btn"
            data-id="<?= $row['id'] ?>"
            data-tax_id="<?= $row['tax_id'] ?>"
            data-owner_id="<?= $row['owner_id'] ?>"
            data-tax_type_id="<?= $row['tax_type_id'] ?>"
            data-start_date="<?= date('Y-m-d', strtotime($row['start_date'])) ?>"
            data-end_date="<?= date('Y-m-d', strtotime($row['end_date'])) ?>"
            data-plate_number="<?= htmlspecialchars($row['plate_number']) ?>"
            data-vehicle_type="<?= htmlspecialchars($row['vehicle_type']) ?>"
            data-model="<?= htmlspecialchars($row['model']) ?>"
            data-status="<?= htmlspecialchars($row['status']) ?>">
            Edit
          </button>

          <button class="btn btn-sm btn-primary transfer-btn"
            data-id="<?= $row['id'] ?>"
            data-owner_id="<?= $row['owner_id'] ?>"
            data-model="<?= htmlspecialchars($row['model']) ?>">
            Transfer
          </button>


          <a href='../../controllers/VehicleController.php?delete=<?= $row['id'] ?>' class='btn btn-danger btn-sm'><i class='fas fa-trash'></i></a>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
  <div class="modal-dialog modal-lg"> <!-- Use modal-lg for wider modal -->
    <form class="modal-content" method="POST" action="../../controllers/VehicleController.php">
      <input type="hidden" name="create" value="1">
      
      <div class="modal-header">
        <h5 class="modal-title">Add Vehicle</h5>
      </div>

      <div class="modal-body">
        <div class="container-fluid">
          <div class="row g-3">

            <!-- Owner -->
            <div class="col-md-6">
              <label>Owner <span class="text-danger">*</span></label>
              <select name="owner_id" class="form-control" required>
                <option value="">Select Owner</option>
                <?php foreach ($owners as $o): ?>
                  <option value="<?= $o['owner_id'] ?>"><?= $o['full_name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Tax Type -->
            <div class="col-md-6">
              <label>Tax Type <span class="text-danger">*</span></label>
              <select name="tax_type_id" class="form-control" required>
                <option value="">Select Tax Type</option>
                <?php foreach ($TaxType as $t): ?>
                  <option value="<?= $t['id'] ?>"><?= $t['name'] ?></option>
                <?php endforeach; ?>
              </select>
            </div>

            <!-- Start Date -->
            <div class="col-md-6">
              <label>Start Date <span class="text-danger">*</span></label>
              <input type="date" name="start_date" class="form-control" required>
            </div>

            <!-- End Date -->
            <div class="col-md-6">
              <label>End Date <span class="text-danger">*</span></label>
              <input type="date" name="end_date" class="form-control" required>
            </div>

            <!-- Plate Number -->
            <div class="col-md-6">
              <label>Plate Number <span class="text-danger">*</span></label>
              <input name="plate_number" class="form-control" placeholder="Plate Number" required>
            </div>

            <!-- Vehicle Type -->
            <div class="col-md-6">
              <label>Vehicle Type <span class="text-danger">*</span></label>
                          <select name="vehicle_type" class="form-control" required>
              <option value="">Select Vehicle Type</option>
              <option value="Bajaaj A">Bajaaj A</option>
              <option value="Bajaaj B">Bajaaj B</option>
            </select>
            </div>

            <!-- Model -->
            <div class="col-md-6">
              <label>Model <span class="text-danger">*</span></label>
              <input name="model" class="form-control" placeholder="Model" required>
            </div>

            <!-- Status -->
            <div class="col-md-6">
              <label>Status <span class="text-danger">*</span></label>
              <select name="status" class="form-control" required>
                <option value="">Select Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>

          </div>
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
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" action="../../controllers/VehicleController.php">
      <input type="hidden" name="update" value="1">
      <input type="hidden" name="id" id="edit-id">
      <input type="hidden" name="Tstatus" id="edit-Tstatus">
      <input type="hidden" name="tax_id" id="edit-tax_id">

      <div class="modal-header"><h5 class="modal-title">Edit Vehicle</h5></div>
      <div class="modal-body">
        <div class="row g-3">

          <div class="col-md-6">
            <label>Owner <span class="text-danger">*</span></label>
            <select name="owner_id" id="edit-owner_id" class="form-control" required>
              <option value="">Select Owner</option>
              <?php foreach ($owners as $o): ?>
                <option value="<?= $o['owner_id'] ?>"><?= $o['full_name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label>Tax Type <span class="text-danger">*</span></label>
            <select name="tax_type_id" id="edit-tax_type_id" class="form-control" required>
              <option value="">Select Tax Type</option>
              <?php foreach ($TaxType as $t): ?>
                <option value="<?= $t['id'] ?>"><?= $t['name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-6">
            <label>Start Date <span class="text-danger">*</span></label>
            <input type="date" name="start_date" id="edit-start_date" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label>End Date <span class="text-danger">*</span></label>
            <input type="date" name="end_date" id="edit-end_date" class="form-control" required>
          </div>

          <div class="col-md-6">
            <label>Plate Number <span class="text-danger">*</span></label>
            <input name="plate_number" id="edit-plate_number" class="form-control" placeholder="Plate Number" required>
          </div>

          <div class="col-md-6">
            <label>Vehicle Type <span class="text-danger">*</span></label>
            <select name="vehicle_type" id="edit-vehicle_type" class="form-control" required>
              <option value="">Select Vehicle Type</option>
              <option value="Bajaaj A">Bajaaj A</option>
              <option value="Bajaaj B">Bajaaj B</option>
            </select>
          </div>

          <div class="col-md-6">
            <label>Model <span class="text-danger">*</span></label>
            <input name="model" id="edit-model" class="form-control" placeholder="Model" required>
          </div>

          <div class="col-md-6">
            <label>Status <span class="text-danger">*</span></label>
            <select name="status" id="edit-status" class="form-control" required>
              <option value="">Select Status</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Transfer Modal -->
<div class="modal fade" id="TransferModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" method="POST" action="../../controllers/VehicleController.php">
      <input type="hidden" name="transfer" value="1">
      <input type="hidden" name="id" id="vehicle-id">
      <!-- <input type="hidden" name="owner_id" id="transfer-owner_id"> -->

      <div class="modal-header"><h5 class="modal-title">Transfer Vehicle</h5></div>
      <div class="modal-body">
        <div class="row g-3">

          <div class="col-md-6">
            <label>Current Owner</label>
            <!-- Display full name of current owner (read-only) -->
            <input type="text" id="transfer-owner-name" class="form-control" readonly>
            <!-- Hidden input for sending owner_id to backend -->
            <input type="hidden" name="owner_id" id="transfer-owner_id">
          </div>

          <div class="col-md-6">
            <label>New Owner <span class="text-danger">*</span></label>
            <select name="new_owner_id" id="new_owner_id" class="form-control" required>
              <option value="">Select Owner</option>
              <?php foreach ($owners as $o): ?>
                <option value="<?= $o['owner_id'] ?>"><?= $o['full_name'] ?></option>
              <?php endforeach; ?>
            </select>
          </div>

        </div>
      </div>

      <div class="modal-footer">
        <button class="btn btn-primary">Update</button>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- View Modal -->
<div class="modal fade" id="viewModal" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <h5 class="modal-title">Vehicle Tax Details</h5>
        <div id="taxesContent">Loading...</div>

        <br><br>

        <h5 class="modal-title">Owners Detail</h5>
        <div id="ownerContent">Loading...</div>
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>
    </div>
  </div>
</div>


<script>
  document.addEventListener('DOMContentLoaded', function () {
  new DataTable('#vehicleTable');
  // Transfer Modal Setup
  document.querySelectorAll('.transfer-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const ownerId = this.dataset.owner_id;
      const owner = <?= json_encode($owners) ?>.find(o => o.owner_id == ownerId);

      document.getElementById('vehicle-id').value = this.dataset.id;
      document.getElementById('transfer-owner_id').value = ownerId;
      document.getElementById('transfer-owner-name').value = owner ? owner.full_name : 'Unknown';

      new bootstrap.Modal(document.getElementById('TransferModal')).show();
    });
  });

  // Prevent submission if owner_id == new_owner_id
  const transferForm = document.querySelector('#TransferModal form');
  transferForm.addEventListener('submit', function (e) {
    const currentOwnerId = document.getElementById('transfer-owner_id').value;
    const newOwnerId = document.getElementById('new_owner_id').value;

    if (currentOwnerId === newOwnerId) {
      e.preventDefault();
      alert("Current and new owners are the same. Please select another.");
    }
  });


  document.querySelectorAll('.edit-btn').forEach(function(btn) {
    btn.addEventListener('click', function () {
      document.getElementById('edit-id').value = this.dataset.id;
      document.getElementById('edit-tax_id').value = this.dataset.tax_id;
      document.getElementById('edit-owner_id').value = this.dataset.owner_id;
      document.getElementById('edit-tax_type_id').value = this.dataset.tax_type_id;
      document.getElementById('edit-start_date').value = this.dataset.start_date;
      document.getElementById('edit-end_date').value = this.dataset.end_date;
      document.getElementById('edit-plate_number').value = this.dataset.plate_number;
      
      // Debug vehicle type value
      console.log('Vehicle Type Value:', this.dataset.vehicle_type);
      console.log('Vehicle Type Value Length:', this.dataset.vehicle_type ? this.dataset.vehicle_type.length : 0);
      
      // Handle vehicle type - add dynamic option if not exists
      const vehicleTypeSelect = document.getElementById('edit-vehicle_type');
      const vehicleTypeValue = this.dataset.vehicle_type;
      
      if (vehicleTypeValue && vehicleTypeValue.trim() !== '') {
        // Check if option exists
        let optionExists = false;
        for (let i = 0; i < vehicleTypeSelect.options.length; i++) {
          if (vehicleTypeSelect.options[i].value === vehicleTypeValue) {
            optionExists = true;
            break;
          }
        }
        
        // If option doesn't exist, add it
        if (!optionExists) {
          const newOption = document.createElement('option');
          newOption.value = vehicleTypeValue;
          newOption.textContent = vehicleTypeValue;
          vehicleTypeSelect.appendChild(newOption);
        }
      }
      
      vehicleTypeSelect.value = vehicleTypeValue;
      document.getElementById('edit-model').value = this.dataset.model;
      document.getElementById('edit-status').value = this.dataset.status;
      new bootstrap.Modal(document.getElementById('editModal')).show();
    });
  });

  // View tax records by vehicle_id
  document.querySelectorAll('.view-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      const vehicleId = this.dataset.vehicle_id;

      // Vehicle Tax Records
      const taxesContent = document.getElementById('taxesContent');
      taxesContent.innerHTML = "Loading...";

      fetch(`../../controllers/get_taxes.php?vehicle_id=${vehicleId}`)
        .then(res => res.text())
        .then(html => {
          taxesContent.innerHTML = html;
        })
        .catch(err => {
          taxesContent.innerHTML = "<div class='text-danger'>Failed to load tax data.</div>";
        });

      // Vehicle Owner History
      const ownerContent = document.getElementById('ownerContent');
      ownerContent.innerHTML = "Loading...";

      fetch(`../../controllers/get_vehicle_owner.php?vehicle_id=${vehicleId}`)
        .then(res => res.text())
        .then(html => {
          ownerContent.innerHTML = html;
        })
        .catch(err => {
          ownerContent.innerHTML = "<div class='text-danger'>Failed to load owner data.</div>";
        });
    });
  });
});

</script>

<?php include '../layout/footer.php'; ?>
