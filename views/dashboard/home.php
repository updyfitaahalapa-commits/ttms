<?php
require_once '../../helpers/session.php';
require_once '../layout/header.php';
require_once '../layout/navbar.php';
require_once '../../config/db.php';

// Count summaries
function getCount($pdo, $table, $where = '') {
    $sql = "SELECT COUNT(*) as count FROM {$table} {$where}";
    return $pdo->query($sql)->fetch()['count'] ?? 0;
}

// Get current month and year
$currentMonth = date('Y-m');
$currentYear = date('Y');

// Basic counts
try {
$totalVehicles      = getCount($pdo, 'vehicles', 'WHERE FinishDate IS NULL');
    $totalOwners        = getCount($pdo, 'owners', 'WHERE FinishDate IS NULL');
    $totalDistricts     = getCount($pdo, 'districts', 'WHERE FinishDate IS NULL');
    $totalUsers         = getCount($pdo, 'users', 'WHERE FinishDate IS NULL');
    $totalTaxTypes      = getCount($pdo, 'tax_types', 'WHERE FinishDate IS NULL');
    $totalTaxes         = getCount($pdo, 'taxes', '');
$totalPaidTaxes     = getCount($pdo, 'taxes', "WHERE status = 'paid'");
$totalUnpaidTaxes   = getCount($pdo, 'taxes', "WHERE status = 'unpaid'");
    $totalPayments      = getCount($pdo, 'payments', '');
} catch (Exception $e) {
    $totalVehicles = $totalOwners = $totalDistricts = $totalUsers = $totalTaxTypes = 0;
    $totalTaxes = $totalPaidTaxes = $totalUnpaidTaxes = $totalPayments = 0;
    error_log("Dashboard basic counts error: " . $e->getMessage());
}

// Additional statistics
try {
    $thisMonthPayments = getCount($pdo, 'payments', "WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$currentMonth'");
    $thisYearPayments = getCount($pdo, 'payments', "WHERE YEAR(payment_date) = '$currentYear'");
    $expiredTaxes = getCount($pdo, 'taxes', "WHERE status = 'unpaid' AND end_date < CURDATE()");
    $expiringSoon = getCount($pdo, 'taxes', "WHERE status = 'unpaid' AND end_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)");
} catch (Exception $e) {
    $thisMonthPayments = $thisYearPayments = $expiredTaxes = $expiringSoon = 0;
    error_log("Dashboard additional statistics error: " . $e->getMessage());
}

// Revenue calculations
try {
    $totalRevenue = $pdo->query("SELECT SUM(amount_paid) as total FROM payments")->fetch()['total'] ?? 0;
    $thisMonthRevenue = $pdo->query("SELECT SUM(amount_paid) as total FROM payments WHERE DATE_FORMAT(payment_date, '%Y-%m') = '$currentMonth'")->fetch()['total'] ?? 0;
    $thisYearRevenue = $pdo->query("SELECT SUM(amount_paid) as total FROM payments WHERE YEAR(payment_date) = '$currentYear'")->fetch()['total'] ?? 0;
} catch (Exception $e) {
    $totalRevenue = 0;
    $thisMonthRevenue = 0;
    $thisYearRevenue = 0;
    error_log("Dashboard revenue calculation error: " . $e->getMessage());
}

// Payments per month (last 12 months)
try {
    $paymentMonths = $pdo->query("SELECT DATE_FORMAT(payment_date, '%Y-%m') as month, SUM(amount_paid) as total FROM payments GROUP BY month ORDER BY month DESC LIMIT 12")->fetchAll(PDO::FETCH_ASSOC);
$months = array_reverse(array_column($paymentMonths, 'month'));
$totals = array_reverse(array_map('floatval', array_column($paymentMonths, 'total')));
    
    // If no data, create sample data for demonstration
    if (empty($months)) {
        $months = [];
        $totals = [];
        for ($i = 11; $i >= 0; $i--) {
            $months[] = date('Y-m', strtotime("-$i months"));
            $totals[] = rand(1000, 5000); // Random sample data
        }
    }
} catch (Exception $e) {
    $months = [];
    $totals = [];
    error_log("Dashboard payment months query error: " . $e->getMessage());
    
    // Fallback data
    for ($i = 11; $i >= 0; $i--) {
        $months[] = date('Y-m', strtotime("-$i months"));
        $totals[] = rand(1000, 5000);
    }
}

// Tax type distribution
try {
$taxTypeData = $pdo->query("SELECT tt.name, COUNT(t.id) as total FROM taxes t JOIN tax_types tt ON t.tax_type_id = tt.id GROUP BY tt.name")->fetchAll(PDO::FETCH_ASSOC);
$taxTypeLabels = array_column($taxTypeData, 'name');
$taxTypeCounts = array_column($taxTypeData, 'total');
    
    // If no data, create sample data
    if (empty($taxTypeLabels)) {
        $taxTypeLabels = ['Vehicle Tax', 'Registration Fee', 'Road Tax'];
        $taxTypeCounts = [rand(10, 50), rand(5, 30), rand(15, 40)];
    }
} catch (Exception $e) {
    $taxTypeLabels = ['Vehicle Tax', 'Registration Fee', 'Road Tax'];
    $taxTypeCounts = [rand(10, 50), rand(5, 30), rand(15, 40)];
    error_log("Dashboard tax type distribution error: " . $e->getMessage());
}

// Recent activities
try {
    $recentPayments = $pdo->query("SELECT p.*, v.plate_number, o.full_name as owner_name FROM payments p 
                                   JOIN taxes t ON p.tax_id = t.id 
                                   JOIN vehicles v ON t.vehicle_id = v.id 
                                   JOIN owners o ON v.owner_id = o.id 
                                   ORDER BY p.payment_date DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recentPayments = [];
    error_log("Dashboard recent payments query error: " . $e->getMessage());
}

try {
    $recentVehicles = $pdo->query("SELECT v.*, o.full_name as owner_name FROM vehicles v 
                                   JOIN owners o ON v.owner_id = o.id 
                                   WHERE v.FinishDate IS NULL 
                                   ORDER BY v.RegDate DESC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $recentVehicles = [];
    error_log("Dashboard recent vehicles query error: " . $e->getMessage());
}

// Debug: Check if Chart.js is loaded and data is available
?>
<script>
// Debug information
console.log('Chart Data Debug:');
console.log('Months:', <?= json_encode($months) ?>);
console.log('Totals:', <?= json_encode($totals) ?>);
console.log('Tax Type Labels:', <?= json_encode($taxTypeLabels) ?>);
console.log('Tax Type Counts:', <?= json_encode($taxTypeCounts) ?>);
console.log('Total Taxes:', <?= $totalTaxes ?>);
console.log('Paid Taxes:', <?= $totalPaidTaxes ?>);
console.log('Unpaid Taxes:', <?= $totalUnpaidTaxes ?>);
</script>
<?php
?>

<style>
.dashboard-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    padding: 2rem 0;
    margin-bottom: 2rem;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
}

.stat-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
    position: relative;
    overflow: hidden;
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: linear-gradient(90deg, var(--card-color), var(--card-color-light));
}

.stat-card.bg-primary::before { --card-color: #0d6efd; --card-color-light: #6ea8fe; }
.stat-card.bg-success::before { --card-color: #198754; --card-color-light: #75b798; }
.stat-card.bg-info::before { --card-color: #0dcaf0; --card-color-light: #6edff6; }
.stat-card.bg-warning::before { --card-color: #ffc107; --card-color-light: #ffda6a; }
.stat-card.bg-danger::before { --card-color: #dc3545; --card-color-light: #ea868f; }
.stat-card.bg-dark::before { --card-color: #212529; --card-color-light: #6c757d; }
.stat-card.bg-secondary::before { --card-color: #6c757d; --card-color-light: #adb5bd; }

.stat-number {
    font-size: 2.5rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.stat-label {
    font-size: 0.9rem;
    opacity: 0.8;
    margin-bottom: 1rem;
}

.stat-icon {
    font-size: 3rem;
    opacity: 0.3;
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
}

.chart-container {
    background: white;
    border-radius: 10px;
    padding: 0.5rem;
    box-shadow: 0 3px 10px rgba(0,0,0,0.08);
    margin-bottom: 0.5rem;
    height: 240px; /* Doubled height for 2x larger charts */
    overflow: hidden;
}

.activity-card {
    background: white;
    border-radius: 15px;
    padding: 1.5rem;
    box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    margin-bottom: 2rem;
}

.activity-item {
    padding: 0.75rem 0;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    align-items: center;
    gap: 1rem;
}

.activity-item:last-child {
    border-bottom: none;
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.2rem;
}

.revenue-highlight {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border-radius: 15px;
    padding: 2rem;
    margin-bottom: 2rem;
    text-align: center;
}

.revenue-number {
    font-size: 3rem;
    font-weight: 700;
    margin-bottom: 0.5rem;
}

.alert-custom {
    border-radius: 15px;
    border: none;
    padding: 1rem 1.5rem;
}

.progress-custom {
    height: 8px;
    border-radius: 10px;
    background-color: #e9ecef;
}

.progress-custom .progress-bar {
    border-radius: 10px;
}

/* Chart container styling */
.chart-container {
    position: relative;
    z-index: 1;
}

/* Prevent chart shaking and movement */
.chart-container canvas {
    display: block !important;
    max-width: 100% !important;
    height: auto !important;
}

.chart-container > div {
    position: relative;
    width: 100%;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .stat-card {
        padding: 1rem;
        margin-bottom: 0.5rem;
    }
    
    .stat-number {
        font-size: 1.8rem;
    }
    
    .stat-label {
        font-size: 0.8rem;
    }
    
    .stat-icon {
        font-size: 2rem;
    }
    
    .revenue-number {
        font-size: 2rem;
    }
    
    .dashboard-header {
        padding: 1rem 0;
    }
    
    .dashboard-header h1 {
        font-size: 1.5rem;
    }
    
    .activity-item {
        padding: 0.5rem 0;
    }
    
    .chart-container {
        padding: 0.25rem;
        margin-bottom: 0.25rem;
    }
}

@media (max-width: 576px) {
    .stat-card {
        padding: 0.75rem;
    }
    
    .stat-number {
        font-size: 1.5rem;
    }
    
    .revenue-highlight {
        padding: 1rem;
    }
    
    .revenue-number {
        font-size: 1.5rem;
    }
}
</style>

<div class="dashboard-header text-center">
    <h1 class="mb-2"><i class="fas fa-tachometer-alt me-3"></i>TTMS Dashboard</h1>
    <p class="mb-0">Welcome back, <?= htmlspecialchars($_SESSION['full_name'] ?? 'User') ?>!</p>
</div>

<!-- Revenue Highlights -->
<div class="revenue-highlight">
    <div class="row">
        <div class="col-md-4">
            <div class="revenue-number">$<?= number_format($totalRevenue, 2) ?></div>
            <div>Total Revenue</div>
        </div>
        <div class="col-md-4">
            <div class="revenue-number">$<?= number_format($thisMonthRevenue, 2) ?></div>
            <div>This Month</div>
        </div>
        <div class="col-md-4">
            <div class="revenue-number">$<?= number_format($thisYearRevenue, 2) ?></div>
            <div>This Year</div>
        </div>
    </div>
</div>

<!-- Alerts Section -->
<?php if ($expiredTaxes > 0 || $expiringSoon > 0): ?>
<div class="row mb-4">
    <?php if ($expiredTaxes > 0): ?>
    <div class="col-md-6">
        <div class="alert alert-danger alert-custom">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong><?= $expiredTaxes ?></strong> taxes have expired and need immediate attention!
        </div>
    </div>
    <?php endif; ?>
    <?php if ($expiringSoon > 0): ?>
    <div class="col-md-6">
        <div class="alert alert-warning alert-custom">
            <i class="fas fa-clock me-2"></i>
            <strong><?= $expiringSoon ?></strong> taxes will expire within 30 days.
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- Statistics Cards -->
<div class="row g-3 mb-4">
    <?php
    $cards = [
        ['Total Vehicles', $totalVehicles, 'bg-primary', 'fa-car', 'Registered vehicles in the system'],
        ['Total Owners', $totalOwners, 'bg-success', 'fa-users', 'Vehicle owners registered'],
        ['Total Districts', $totalDistricts, 'bg-info', 'fa-city', 'Districts covered'],
        ['Total Users', $totalUsers, 'bg-warning', 'fa-user-shield', 'System users'],
        ['Total Tax Types', $totalTaxTypes, 'bg-dark', 'fa-layer-group', 'Different tax categories'],
        ['Total Taxes', $totalTaxes, 'bg-secondary', 'fa-file-invoice-dollar', 'All tax records'],
        ['Paid Taxes', $totalPaidTaxes, 'bg-success', 'fa-check-circle', 'Successfully paid'],
        ['Unpaid Taxes', $totalUnpaidTaxes, 'bg-danger', 'fa-exclamation-triangle', 'Pending payment'],
        ['Total Payments', $totalPayments, 'bg-primary', 'fa-credit-card', 'Payment transactions'],
        ['This Month Payments', $thisMonthPayments, 'bg-info', 'fa-calendar-check', 'Payments this month'],
        ['This Year Payments', $thisYearPayments, 'bg-warning', 'fa-calendar-alt', 'Payments this year'],
        ['Expired Taxes', $expiredTaxes, 'bg-danger', 'fa-calendar-times', 'Overdue taxes']
    ];
    foreach ($cards as [$title, $count, $bg, $icon, $description]): ?>
        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-12">
            <div class="stat-card <?= $bg ?> text-white">
                <div class="stat-number"><?= number_format($count) ?></div>
                <div class="stat-label"><?= $title ?></div>
                <small class="opacity-75 d-none d-md-block"><?= $description ?></small>
                <i class="fa <?= $icon ?> stat-icon"></i>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Charts Row -->
<div class="row">
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="chart-container">
            <h6 class="mb-1"><i class="fas fa-chart-line me-1"></i>Revenue</h6>
            <div style="height: 156px; width: 100%;">
                <canvas id="paymentChart" height="156"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="chart-container">
            <h6 class="mb-1"><i class="fas fa-chart-pie me-1"></i>Tax Types</h6>
            <div style="height: 156px; width: 100%;">
                <canvas id="taxTypeChart" height="156"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="chart-container">
            <h6 class="mb-1"><i class="fas fa-chart-bar me-1"></i>Taxes</h6>
            <div style="height: 156px; width: 100%;">
                <canvas id="taxChart" height="156"></canvas>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-lg-6 col-md-6 col-sm-12">
        <div class="chart-container">
            <h6 class="mb-1"><i class="fas fa-chart-doughnut me-1"></i>Payments</h6>
            <div style="height: 156px; width: 100%;">
                <canvas id="paymentStatusChart" height="156"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Activities -->
<div class="row">
    <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
        <div class="activity-card">
            <h5 class="mb-3"><i class="fas fa-clock me-2"></i>Recent Payments</h5>
            <?php if (!empty($recentPayments)): ?>
                <?php foreach ($recentPayments as $payment): ?>
                    <div class="activity-item">
                        <div class="activity-icon bg-success">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold">$<?= number_format($payment['amount_paid'], 2) ?></div>
                            <small class="text-muted">
                                <?= htmlspecialchars($payment['plate_number']) ?> - 
                                <?= htmlspecialchars($payment['owner_name']) ?>
                            </small>
                        </div>
                        <small class="text-muted d-none d-md-block"><?= date('M d, Y', strtotime($payment['payment_date'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center py-3">No recent payments</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
        <div class="activity-card">
            <h5 class="mb-3"><i class="fas fa-car me-2"></i>Recently Registered Vehicles</h5>
            <?php if (!empty($recentVehicles)): ?>
                <?php foreach ($recentVehicles as $vehicle): ?>
                    <div class="activity-item">
                        <div class="activity-icon bg-primary">
                            <i class="fas fa-car"></i>
                        </div>
                        <div class="flex-grow-1">
                            <div class="fw-bold"><?= htmlspecialchars($vehicle['plate_number']) ?></div>
                            <small class="text-muted">
                                <?= htmlspecialchars($vehicle['model']) ?> - 
                                <?= htmlspecialchars($vehicle['owner_name']) ?>
                            </small>
                        </div>
                        <small class="text-muted d-none d-md-block"><?= date('M d, Y', strtotime($vehicle['RegDate'])) ?></small>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-muted text-center py-3">No recently registered vehicles</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Performance Metrics -->
<div class="row">
    <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
        <div class="chart-container">
            <h5 class="mb-3"><i class="fas fa-percentage me-2"></i>Payment Collection Rate</h5>
            <?php 
            $collectionRate = $totalTaxes > 0 ? ($totalPaidTaxes / $totalTaxes) * 100 : 0;
            ?>
            <div class="text-center mb-3">
                <div class="display-4 fw-bold text-success"><?= number_format($collectionRate, 1) ?>%</div>
                <small class="text-muted">Successfully collected</small>
            </div>
            <div class="progress progress-custom">
                <div class="progress-bar bg-success" style="width: <?= $collectionRate ?>%"></div>
            </div>
            <div class="row text-center mt-3">
                <div class="col-6">
                    <div class="fw-bold"><?= number_format($totalPaidTaxes) ?></div>
                    <small class="text-muted">Paid</small>
                </div>
                <div class="col-6">
                    <div class="fw-bold"><?= number_format($totalUnpaidTaxes) ?></div>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-xl-6 col-lg-12 col-md-12 col-sm-12">
        <div class="chart-container">
            <h5 class="mb-3"><i class="fas fa-trending-up me-2"></i>Monthly Performance</h5>
            <div class="row text-center">
                <div class="col-4">
                    <div class="fw-bold text-primary"><?= number_format($thisMonthPayments) ?></div>
                    <small class="text-muted">Payments</small>
                </div>
                <div class="col-4">
                    <div class="fw-bold text-success">$<?= number_format($thisMonthRevenue, 2) ?></div>
                    <small class="text-muted">Revenue</small>
                </div>
                <div class="col-4">
                    <div class="fw-bold text-info"><?= date('M Y') ?></div>
                    <small class="text-muted">Current Month</small>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Check if Chart.js is loaded
if (typeof Chart === 'undefined') {
    console.error('Chart.js is not loaded!');
    // Load Chart.js dynamically if not available
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
    script.onload = function() {
        initializeCharts();
    };
    document.head.appendChild(script);
} else {
    initializeCharts();
}

function initializeCharts() {
    console.log('Initializing charts...');
    
    // Enhanced Charts with better styling
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            duration: 0 // Disable animations to prevent shaking
        },
        plugins: {
            legend: {
                display: false // Hide legends to save space
            }
        }
    };

    try {
        // Revenue Trend Chart
        const paymentChartCtx = document.getElementById('paymentChart');
        if (paymentChartCtx) {
            new Chart(paymentChartCtx.getContext('2d'), {
                type: 'line',
                data: {
                    labels: <?= json_encode($months) ?>,
                    datasets: [{
                        label: 'Monthly Revenue',
                        data: <?= json_encode($totals) ?>,
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.1)',
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 6
                    }]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        }
                    }
                }
            });
            console.log('Payment chart initialized');
        }

        // Tax Distribution Chart
        const taxTypeChartCtx = document.getElementById('taxTypeChart');
        if (taxTypeChartCtx) {
            new Chart(taxTypeChartCtx.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: <?= json_encode($taxTypeLabels) ?>,
                    datasets: [{
                        data: <?= json_encode($taxTypeCounts) ?>,
                        backgroundColor: [
                            '#667eea', '#764ba2', '#f093fb', '#f5576c', 
                            '#4facfe', '#00f2fe', '#43e97b', '#38f9d7'
                        ],
                        borderWidth: 0
                    }]
                },
                options: chartOptions
            });
            console.log('Tax type chart initialized');
        }

        // Taxes Overview Chart
        const taxChartCtx = document.getElementById('taxChart');
        if (taxChartCtx) {
            new Chart(taxChartCtx.getContext('2d'), {
        type: 'bar',
        data: {
            labels: ['Total', 'Paid', 'Unpaid'],
            datasets: [{
                label: 'Taxes',
                data: [<?= $totalTaxes ?>, <?= $totalPaidTaxes ?>, <?= $totalUnpaidTaxes ?>],
                        backgroundColor: ['#667eea', '#43e97b', '#f5576c'],
                        borderRadius: 8,
                        borderSkipped: false
                    }]
                },
                options: {
                    ...chartOptions,
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });
            console.log('Tax overview chart initialized');
        }

        // Payment Status Chart
        const paymentStatusChartCtx = document.getElementById('paymentStatusChart');
        if (paymentStatusChartCtx) {
            new Chart(paymentStatusChartCtx.getContext('2d'), {
        type: 'pie',
        data: {
                    labels: ['Paid', 'Unpaid'],
            datasets: [{
                        data: [<?= $totalPaidTaxes ?>, <?= $totalUnpaidTaxes ?>],
                        backgroundColor: ['#43e97b', '#f5576c'],
                        borderWidth: 0
                    }]
                },
                options: chartOptions
            });
            console.log('Payment status chart initialized');
        }
    } catch (error) {
        console.error('Error initializing charts:', error);
    }
}
</script>
