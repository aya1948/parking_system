<?php
// driver/add_vehicle.php
require_once __DIR__ . '/../config/session.php';
requireRole('driver');
require_once __DIR__ . '/../classes/Vehicle.php';

$pageTitle = 'Add Vehicle — Rakna';
$user      = currentUser();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $vehObj = new Vehicle();
    $result = $vehObj->addVehicle([
        'user_id'       => $user['user_id'],
        'license_plate' => trim($_POST['license_plate'] ?? ''),
        'make'          => trim($_POST['make'] ?? ''),
        'model'         => trim($_POST['model'] ?? ''),
        'color'         => trim($_POST['color'] ?? ''),
        'vehicle_type'  => $_POST['vehicle_type'] ?? 'sedan',
        'height_cm'     => $_POST['height_cm'] ?: null,
        'width_cm'      => $_POST['width_cm']  ?: null,
        'is_ev'         => isset($_POST['is_ev']) ? 1 : 0,
    ]);
    setFlash($result['success'] ? 'success' : 'error', $result['success'] ? 'Vehicle added!' : 'Plate already registered.');
    header('Location: /parking_system/index.php?action=my_vehicles'); exit;
}

require_once __DIR__ . '/../includes/header.php';
?>
<div class="container-fluid px-0"><div class="row g-0">
<?php require_once __DIR__ . '/../includes/sidebar.php'; ?>
<div class="col-md-10 p-4">
  <div class="row justify-content-center">
    <div class="col-md-6">
      <div class="card">
        <div class="card-header fw-bold"> Add New Vehicle</div>
        <div class="card-body">
          <form method="POST">
            <div class="mb-3">
              <label class="form-label">License Plate *</label>
              <input type="text" name="license_plate" class="form-control text-uppercase" placeholder="ABC 123" required>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Make</label>
                <input type="text" name="make" class="form-control" placeholder="Toyota">
              </div>
              <div class="col-6">
                <label class="form-label">Model</label>
                <input type="text" name="model" class="form-control" placeholder="Corolla">
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Color</label>
                <input type="text" name="color" class="form-control" placeholder="White">
              </div>
              <div class="col-6">
                <label class="form-label">Vehicle Type</label>
                <select name="vehicle_type" class="form-select">
                  <option value="sedan">Sedan</option>
                  <option value="suv">SUV</option>
                  <option value="motorcycle">Motorcycle</option>
                  <option value="truck">Truck</option>
                  <option value="ev">Electric Vehicle</option>
                </select>
              </div>
            </div>
            <div class="row g-3 mb-3">
              <div class="col-6">
                <label class="form-label">Height (cm)</label>
                <input type="number" name="height_cm" class="form-control" placeholder="150" step="0.1">
              </div>
              <div class="col-6">
                <label class="form-label">Width (cm)</label>
                <input type="number" name="width_cm" class="form-control" placeholder="180" step="0.1">
              </div>
            </div>
            <div class="form-check mb-4">
              <input class="form-check-input" type="checkbox" name="is_ev" id="isEv">
              <label class="form-check-label" for="isEv">⚡ This is an Electric Vehicle</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Add Vehicle</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div></div></div>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
