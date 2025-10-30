<?php
session_start();

// Redirect to login if no session
if (!isset($_SESSION['email'])) {
    header("Location: log-in.php");
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;
$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->HaliliDentalClinic;

$bookedService = $db->bookedservices;

// Get patient email from session
$userEmail = $_SESSION['email'];

// Fetch booked services for this patient, sorted by latest date & time
$appointmentsCursor = $bookedService->find(
    ['email' => $userEmail],
    [
        'sort' => [
            'date' => -1,
            'time' => -1
        ]
    ]
);

// Convert cursor to array
$appointments = iterator_to_array($appointmentsCursor);

// Count appointments by status
$statusCounts = [
    'Pending' => 0,
    'Accepted' => 0,
    'Rejected' => 0,
    'Cancelled' => 0,
    'Completed' => 0
];

foreach ($appointments as $appt) {
    $status = $appt['status'] ?? 'Pending';
    if (isset($statusCounts[$status])) {
        $statusCounts[$status]++;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Appointments - Halili Dental Clinic</title>
    
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <!-- Tailwind (for sidebar) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        body {
            display: flex;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
        }
        
        .content {
            margin-left: 16rem;
            padding: 2rem;
            flex: 1;
            width: 100%;
        }
        
        /* Stats Cards */
        .stats-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 4px solid;
        }
        
        .stats-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }
        
        .stats-card.pending { border-left-color: #ffc107; }
        .stats-card.accepted { border-left-color: #28a745; }
        .stats-card.rejected { border-left-color: #dc3545; }
        .stats-card.completed { border-left-color: #17a2b8; }
        
        .stats-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }
        
        .stats-card.pending .stats-icon {
            background: #fff3cd;
            color: #856404;
        }
        
        .stats-card.accepted .stats-icon {
            background: #d4edda;
            color: #155724;
        }
        
        .stats-card.rejected .stats-icon {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stats-card.completed .stats-icon {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        /* Page Header */
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .page-title {
            font-size: 2rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.5rem;
        }
        
        .page-subtitle {
            color: #64748b;
            font-size: 1rem;
        }
        
        /* Table Container */
        .table-container {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            overflow-x: auto;
        }
        
        .table {
            margin-bottom: 0;
        }
        
        .table thead th {
            background: #f8f9fa;
            color: #475569;
            font-weight: 600;
            border: none;
            padding: 1rem;
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            color: #334155;
            border-color: #e2e8f0;
        }
        
        .table tbody tr {
            transition: background-color 0.2s;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        /* Status Badges */
        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 500;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .status-badge i {
            font-size: 0.75rem;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-accepted {
            background: #d4edda;
            color: #155724;
        }
        
        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }
        
        .status-cancelled {
            background: #e2e3e5;
            color: #383d41;
        }
        
        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }
        
        /* Action Buttons */
        .btn-cancel {
            background: #ef4444;
            border: none;
            color: white;
            padding: 0.5rem 1.25rem;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.2s;
        }
        
        .btn-cancel:hover:not(:disabled) {
            background: #dc2626;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(239, 68, 68, 0.3);
        }
        
        .btn-cancel:disabled {
            background: #d1d5db;
            color: #9ca3af;
            cursor: not-allowed;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #cbd5e1;
            margin-bottom: 1.5rem;
        }
        
        .empty-state h3 {
            color: #475569;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: #94a3b8;
        }
        
        /* Responsive Design */
        @media (max-width: 1024px) {
            .content {
                margin-left: 0;
                padding: 1rem;
            }
            
            .page-header {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
        }
        
        @media (max-width: 768px) {
            .stats-card {
                margin-bottom: 1rem;
            }
            
            .table-container {
                padding: 1rem;
            }
            
            .table {
                font-size: 0.875rem;
            }
            
            .table thead th,
            .table tbody td {
                padding: 0.75rem 0.5rem;
            }
            
            /* Stack table on mobile */
            .table thead {
                display: none;
            }
            
            .table tbody tr {
                display: block;
                margin-bottom: 1rem;
                border: 1px solid #e2e8f0;
                border-radius: 8px;
                padding: 1rem;
            }
            
            .table tbody td {
                display: flex;
                justify-content: space-between;
                padding: 0.5rem 0;
                border: none;
                text-align: right;
            }
            
            .table tbody td::before {
                content: attr(data-label);
                font-weight: 600;
                color: #64748b;
                text-align: left;
                flex: 1;
            }
            
            .table tbody td:last-child {
                justify-content: flex-end;
            }
        }
        
        @media (max-width: 576px) {
            .page-header {
                padding: 1rem;
            }
            
            .stats-card {
                padding: 1rem;
            }
        }
        
        /* SweetAlert Customization */
        .swal2-popup {
            border-radius: 12px;
            font-family: 'Inter', sans-serif;
        }
    </style>
</head>
<body>

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main content -->
    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="page-title">
                        <i class="bi bi-calendar-check text-primary"></i> My Appointments
                    </h1>
                    <p class="page-subtitle mb-0">
                        View and manage your dental appointments
                    </p>
                </div>
                <div class="mt-3 mt-md-0">
                    <span class="text-muted">
                        <i class="bi bi-person-circle"></i> <?= htmlspecialchars($userEmail) ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="stats-card pending">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon me-3">
                            <i class="bi bi-clock-history"></i>
                        </div>
                        <div>
                            <div class="text-muted small mb-1">Pending</div>
                            <h3 class="mb-0 fw-bold"><?= $statusCounts['Pending'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="stats-card accepted">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon me-3">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        <div>
                            <div class="text-muted small mb-1">Accepted</div>
                            <h3 class="mb-0 fw-bold"><?= $statusCounts['Accepted'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="stats-card completed">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon me-3">
                            <i class="bi bi-check-all"></i>
                        </div>
                        <div>
                            <div class="text-muted small mb-1">Completed</div>
                            <h3 class="mb-0 fw-bold"><?= $statusCounts['Completed'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-sm-6 col-lg-3">
                <div class="stats-card rejected">
                    <div class="d-flex align-items-center">
                        <div class="stats-icon me-3">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <div>
                            <div class="text-muted small mb-1">Cancelled/Rejected</div>
                            <h3 class="mb-0 fw-bold"><?= $statusCounts['Cancelled'] + $statusCounts['Rejected'] ?></h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Appointments Table -->
        <div class="table-container">
            <?php if (empty($appointments)): ?>
                <div class="empty-state">
                    <i class="bi bi-calendar-x"></i>
                    <h3>No Appointments Found</h3>
                    <p>You haven't booked any appointments yet. Start by scheduling your dental visit.</p>
                </div>
            <?php else: ?>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0 fw-semibold">
                        <i class="bi bi-list-ul text-primary"></i> Appointment History
                    </h5>
                    <span class="text-muted small">Total: <?= count($appointments) ?> appointments</span>
                </div>
                
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><i class="bi bi-capsule me-2"></i>Service</th>
                                <th><i class="bi bi-calendar-event me-2"></i>Date</th>
                                <th><i class="bi bi-clock me-2"></i>Time</th>
                                <th><i class="bi bi-card-text me-2"></i>Description</th>
                                <th><i class="bi bi-info-circle me-2"></i>Status</th>
                                <th><i class="bi bi-gear me-2"></i>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appt): ?>
                                <tr>
                                    <td data-label="Service">
                                        <strong><?= htmlspecialchars($appt['serviceName'] ?? 'N/A') ?></strong>
                                    </td>
                                    <td data-label="Date">
                                        <i class="bi bi-calendar3 text-primary me-1"></i>
                                        <?= htmlspecialchars($appt['date'] ?? 'N/A') ?>
                                    </td>
                                    <td data-label="Time">
                                        <i class="bi bi-alarm text-primary me-1"></i>
                                        <?= htmlspecialchars($appt['time'] ?? 'N/A') ?>
                                    </td>
                                    <td data-label="Description">
                                        <?= htmlspecialchars($appt['description'] ?? 'No description provided') ?>
                                    </td>
                                    <td data-label="Status">
                                        <?php
                                        $status = $appt['status'] ?? 'Pending';
                                        $badgeClass = match ($status) {
                                            'Accepted' => 'status-accepted',
                                            'Rejected' => 'status-rejected',
                                            'Cancelled' => 'status-cancelled',
                                            'Completed' => 'status-completed',
                                            'Pending'  => 'status-pending',
                                            default    => 'status-pending',
                                        };
                                        $icon = match ($status) {
                                            'Accepted' => 'check-circle-fill',
                                            'Rejected' => 'x-circle-fill',
                                            'Cancelled' => 'dash-circle-fill',
                                            'Completed' => 'check-all',
                                            'Pending'  => 'clock-fill',
                                            default    => 'clock-fill',
                                        };
                                        ?>
                                        <span class="status-badge <?= $badgeClass ?>">
                                            <i class="bi bi-<?= $icon ?>"></i>
                                            <?= ucfirst($status) ?>
                                        </span>
                                    </td>
                                    <td data-label="Action">
                                        <?php 
                                        $status = $appt['status'] ?? 'Pending';
                                        if (in_array($status, ['Cancelled', 'Completed'])): ?>
                                            <button class="btn btn-cancel" disabled>
                                                <i class="bi bi-x-circle me-1"></i> Cancel
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-cancel"
                                                onclick="cancelAppointment('<?= $appt['_id'] ?>')">
                                                <i class="bi bi-x-circle me-1"></i> Cancel
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function cancelAppointment(id) {
            Swal.fire({
                title: 'Cancel Appointment?',
                text: "Are you sure you want to cancel this appointment? This action cannot be undone.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#6b7280',
                confirmButtonText: '<i class="bi bi-check-circle me-2"></i>Yes, cancel it!',
                cancelButtonText: '<i class="bi bi-x-circle me-2"></i>No, keep it',
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-3',
                    confirmButton: 'fw-semibold',
                    cancelButton: 'fw-semibold'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    // Show loading
                    Swal.fire({
                        title: 'Processing...',
                        text: 'Cancelling your appointment',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    
                    fetch('cancel-appointment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ appointmentId: id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                title: 'Cancelled!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonColor: '#10b981',
                                confirmButtonText: 'OK'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonColor: '#ef4444',
                                confirmButtonText: 'OK'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Something went wrong. Please try again.',
                            icon: 'error',
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'OK'
                        });
                    });
                }
            });
        }
    </script>
</body>
</html>