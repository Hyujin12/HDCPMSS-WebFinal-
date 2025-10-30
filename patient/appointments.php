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
            'date' => -1, // latest date first
            'time' => -1  // for same-day, latest time first
        ]
    ]
);

// Convert cursor to array
$appointments = iterator_to_array($appointmentsCursor);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>My Appointments - Halili Dental</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8f9fa;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            overflow-x: hidden;
        }

        .content {
            margin-left: 0;
            padding: 1rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        @media (min-width: 768px) {
            .content {
                margin-left: 16rem;
                padding: 2rem;
            }
        }

        @media (max-width: 767px) {
            .content {
                padding-top: 5rem;
            }
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            color: white;
            padding: 2.5rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .page-header h1 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .page-header p {
            font-size: 1.1rem;
            opacity: 0.95;
            margin: 0;
        }

        @media (max-width: 767px) {
            .page-header {
                padding: 1.5rem 1rem;
            }
            
            .page-header h1 {
                font-size: 1.5rem;
            }
            
            .page-header p {
                font-size: 0.95rem;
            }
        }

        /* Stats Bar */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.25rem;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            gap: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.04);
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-icon.pending {
            background: #fef3c7;
            color: #92400e;
        }

        .stat-icon.accepted {
            background: #d1fae5;
            color: #065f46;
        }

        .stat-icon.total {
            background: #dbeafe;
            color: #1e40af;
        }

        .stat-content h3 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .stat-content p {
            font-size: 0.875rem;
            color: #6b7280;
            margin: 0;
        }

        /* Appointments Grid */
        .appointments-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 1.5rem;
        }

        @media (max-width: 767px) {
            .appointments-grid {
                grid-template-columns: 1fr;
                gap: 1rem;
            }
        }

        /* Appointment Card */
        .appointment-card {
            background: white;
            border-radius: 16px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            border: 1px solid #e5e7eb;
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .appointment-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0,0,0,0.12);
        }

        .appointment-card-header {
            padding: 1.25rem;
            border-bottom: 1px solid #f3f4f6;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }

        .appointment-service {
            font-size: 1.25rem;
            font-weight: 700;
            color: #1d4ed8;
            margin-bottom: 0.25rem;
        }

        .appointment-id {
            font-size: 0.75rem;
            color: #9ca3af;
            font-family: monospace;
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-badge i {
            font-size: 0.75rem;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-accepted {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .status-cancelled {
            background: #f3f4f6;
            color: #4b5563;
        }

        .status-completed {
            background: #dbeafe;
            color: #1e40af;
        }

        .appointment-card-body {
            padding: 1.25rem;
            flex: 1;
        }

        .appointment-detail {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            margin-bottom: 1rem;
        }

        .appointment-detail:last-child {
            margin-bottom: 0;
        }

        .detail-icon {
            width: 36px;
            height: 36px;
            background: #f3f4f6;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1d4ed8;
            flex-shrink: 0;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 0.75rem;
            color: #6b7280;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-bottom: 0.25rem;
        }

        .detail-value {
            font-size: 0.95rem;
            color: #1f2937;
            font-weight: 500;
        }

        .appointment-card-footer {
            padding: 1rem 1.25rem;
            background: #f9fafb;
            border-top: 1px solid #f3f4f6;
        }

        .btn-cancel {
            width: 100%;
            background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%);
            color: white;
            border: none;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-cancel:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(220, 38, 38, 0.4);
        }

        .btn-cancel:disabled {
            background: #e5e7eb;
            color: #9ca3af;
            cursor: not-allowed;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 16px;
            border: 2px dashed #e5e7eb;
        }

        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 1.5rem;
        }

        .empty-state h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 0.5rem;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 1.5rem;
        }

        .btn-book-now {
            background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
            color: white;
            border: none;
            padding: 0.875rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            text-decoration: none;
        }

        .btn-book-now:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(29, 78, 216, 0.4);
            color: white;
        }

        /* Filter Tabs */
        .filter-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }

        .filter-tab {
            padding: 0.625rem 1.25rem;
            border-radius: 20px;
            border: 2px solid #e5e7eb;
            background: white;
            color: #6b7280;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .filter-tab:hover {
            border-color: #1d4ed8;
            color: #1d4ed8;
        }

        .filter-tab.active {
            background: #1d4ed8;
            color: white;
            border-color: #1d4ed8;
        }
    </style>
</head>
<body>

    <?php include 'sidebar.php'; ?>

    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-calendar-check me-2"></i>My Appointments</h1>
            <p>View and manage all your dental appointments</p>
        </div>

        <!-- Stats Bar -->
        <?php
        $totalCount = count($appointments);
        $pendingCount = count(array_filter($appointments, fn($a) => ($a['status'] ?? 'Pending') === 'Pending'));
        $acceptedCount = count(array_filter($appointments, fn($a) => ($a['status'] ?? '') === 'Accepted'));
        ?>
        <div class="stats-bar">
            <div class="stat-card">
                <div class="stat-icon total">
                    <i class="fas fa-calendar-alt"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $totalCount ?></h3>
                    <p>Total Appointments</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $pendingCount ?></h3>
                    <p>Pending Approval</p>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon accepted">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <h3><?= $acceptedCount ?></h3>
                    <p>Accepted</p>
                </div>
            </div>
        </div>

        <!-- Appointments List -->
        <?php if (empty($appointments)): ?>
            <div class="empty-state">
                <i class="fas fa-calendar-times"></i>
                <h3>No Appointments Yet</h3>
                <p>You haven't booked any appointments. Start by booking your first dental service!</p>
                <a href="book-appointment.php" class="btn-book-now">
                    <i class="fas fa-plus-circle"></i>
                    Book Appointment
                </a>
            </div>
        <?php else: ?>
            <div class="appointments-grid">
                <?php foreach ($appointments as $appt): 
                    $status = $appt['status'] ?? 'Pending';
                    $statusClass = match ($status) {
                        'Accepted' => 'accepted',
                        'Rejected' => 'rejected',
                        'Cancelled' => 'cancelled',
                        'Completed' => 'completed',
                        'Pending'  => 'pending',
                        default    => 'pending',
                    };
                    $statusIcon = match ($status) {
                        'Accepted' => 'fa-check-circle',
                        'Rejected' => 'fa-times-circle',
                        'Cancelled' => 'fa-ban',
                        'Completed' => 'fa-flag-checkered',
                        'Pending'  => 'fa-clock',
                        default    => 'fa-clock',
                    };
                ?>
                    <div class="appointment-card">
                        <div class="appointment-card-header">
                            <div>
                                <div class="appointment-service">
                                    <?= htmlspecialchars($appt['serviceName'] ?? 'N/A') ?>
                                </div>
                                <div class="appointment-id">
                                    ID: <?= htmlspecialchars(substr((string)$appt['_id'], -8)) ?>
                                </div>
                            </div>
                            <span class="status-badge status-<?= $statusClass ?>">
                                <i class="fas <?= $statusIcon ?>"></i>
                                <?= ucfirst($status) ?>
                            </span>
                        </div>

                        <div class="appointment-card-body">
                            <div class="appointment-detail">
                                <div class="detail-icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Appointment Date</div>
                                    <div class="detail-value">
                                        <?= date('F d, Y', strtotime($appt['date'] ?? 'now')) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="appointment-detail">
                                <div class="detail-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Time</div>
                                    <div class="detail-value">
                                        <?= date('h:i A', strtotime($appt['time'] ?? 'now')) ?>
                                    </div>
                                </div>
                            </div>

                            <div class="appointment-detail">
                                <div class="detail-icon">
                                    <i class="fas fa-comment-medical"></i>
                                </div>
                                <div class="detail-content">
                                    <div class="detail-label">Description</div>
                                    <div class="detail-value">
                                        <?= htmlspecialchars($appt['description'] ?? 'No description provided') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="appointment-card-footer">
                            <?php if (in_array($status, ['Cancelled', 'Completed', 'Rejected'])): ?>
                                <button class="btn-cancel" disabled>
                                    <i class="fas fa-ban"></i>
                                    Cannot Cancel
                                </button>
                            <?php else: ?>
                                <button class="btn-cancel" onclick="cancelAppointment('<?= $appt['_id'] ?>')">
                                    <i class="fas fa-times-circle"></i>
                                    Cancel Appointment
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script>
        function cancelAppointment(id) {
            Swal.fire({
                title: 'Cancel Appointment?',
                text: "Are you sure you want to cancel this appointment?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc2626',
                cancelButtonColor: '#6b7280',
                confirmButtonText: 'Yes, cancel it!',
                cancelButtonText: 'Keep it'
            }).then((result) => {
                if (result.isConfirmed) {
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
                                confirmButtonColor: '#1d4ed8'
                            }).then(() => location.reload());
                        } else {
                            Swal.fire({
                                title: 'Error!',
                                text: data.message,
                                icon: 'error',
                                confirmButtonColor: '#dc2626'
                            });
                        }
                    })
                    .catch(() => {
                        Swal.fire({
                            title: 'Error!',
                            text: 'Something went wrong.',
                            icon: 'error',
                            confirmButtonColor: '#dc2626'
                        });
                    });
                }
            });
        }
    </script>
</body>
</html>