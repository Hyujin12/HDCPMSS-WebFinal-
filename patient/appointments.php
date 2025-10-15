<?php
session_start();

// Redirect to login if no session
if (!isset($_SESSION['user_email'])) {
    header("Location: log-in.php");
    exit;
}

require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

// Connect to MongoDB
$mongo = new Client("mongodb://localhost:27017");
$db = $mongo->HaliliDentalClinic; 
$bookedService = $db->booked_service;

// Get patient email from session
$userEmail = $_SESSION['user_email'];

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
    <title>My Appointments</title>
    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <!-- Tailwind (for sidebar) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- SweetAlert -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <style>
        body {
            display: flex;
            min-height: 100vh;
        }
        .content {
            margin-left: 16rem; /* same width as sidebar (w-64 = 16rem) */
            padding: 2rem;
            flex: 1;
        }
    </style>
</head>
<body class="bg-light">

    <!-- Sidebar -->
    <?php include 'sidebar.php'; ?>

    <!-- Main content -->
    <div class="content">
        <h2 class="mb-4">My Appointments</h2>

        <?php if (empty($appointments)): ?>
            <div class="alert alert-info">No appointments found.</div>
        <?php else: ?>
            <table class="table table-bordered table-striped">
                <thead class="table-dark">
                    <tr>
                        <th>Service</th>
                        <th>Date</th>
                        <th>Time</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($appointments as $appt): ?>
                        <tr>
                            <td><?= htmlspecialchars($appt['serviceName'] ?? '') ?></td>
                            <td><?= htmlspecialchars($appt['date'] ?? '') ?></td>
                            <td><?= htmlspecialchars($appt['time'] ?? '') ?></td>
                            <td><?= htmlspecialchars($appt['description'] ?? '') ?></td>
                            <td>
                                <?php
                                $status = $appt['status'] ?? 'pending';
                                $badgeClass = match ($status) {
                                    'accepted' => 'bg-success',
                                    'rejected' => 'bg-danger',
                                    'cancelled' => 'bg-secondary',
                                    'pending'  => 'bg-warning text-dark',
                                    default    => 'bg-secondary',
                                };
                                ?>
                                <span class="badge <?= $badgeClass ?>">
                                    <?= ucfirst($status) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (($appt['status'] ?? '') !== 'cancelled'): ?>
                                    <button class="btn btn-sm btn-danger"
                                        onclick="cancelAppointment('<?= $appt['_id'] ?>')">
                                        Cancel
                                    </button>
                                <?php else: ?>
                                    <em>—</em>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        function cancelAppointment(id) {
            Swal.fire({
                title: 'Cancel Appointment?',
                text: "Are you sure you want to cancel this appointment?",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, cancel it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('cancel-appointment.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ id })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire('Cancelled!', data.message, 'success')
                                .then(() => location.reload());
                        } else {
                            Swal.fire('Error!', data.message, 'error');
                        }
                    })
                    .catch(() => Swal.fire('Error!', 'Something went wrong.', 'error'));
                }
            });
        }
    </script>
</body>
</html>
