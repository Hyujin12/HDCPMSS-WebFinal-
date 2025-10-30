<?php
session_start();

// Redirect if not logged in
if (!isset($_SESSION['email'])) {
    header("Location: log-in.php");
    exit;
}

require __DIR__ . '/../vendor/autoload.php';

use MongoDB\BSON\ObjectId;
use MongoDB\Client;
use Dotenv\Dotenv;

// Load .env
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Connect to MongoDB
$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->selectDatabase('HaliliDentalClinic');
$usersCollection = $db->selectCollection('users');

// Get the current logged-in user's email
$userEmail = $_SESSION['email'];

// Find user by email
$user = $usersCollection->findOne(['email' => $userEmail]);

if (!$user) {
    die("User not found.");
}

// SweetAlert flag
$updateSuccess = $_SESSION['update_success'] ?? null;
unset($_SESSION['update_success']);
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Patient Profile - Halili Dental Clinic</title>
    
    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e8ecf1 100%);
            overflow-x: hidden;
            min-height: 100vh;
        }
        
        .content {
            min-height: 100vh;
            padding: 1rem;
            margin-left: 0;
            transition: margin-left 0.3s ease;
        }
        
        @media (min-width: 768px) {
            .content {
                margin-left: 16rem;
                padding: 2rem;
            }
        }
        
        /* Desktop - Single Page, No Scroll */
        @media (min-width: 992px) {
            body {
                overflow: hidden;
            }
            
            .content {
                height: 100vh;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                padding: 1.5rem;
            }
            
            .content > * {
                flex-shrink: 0;
            }
            
            .page-header {
                margin-bottom: 1rem;
                padding: 1.25rem;
            }
            
            .stats-container {
                margin-bottom: 1rem;
            }
            
            .profile-card {
                flex: 1;
                overflow-y: auto;
                margin-bottom: 0;
                display: flex;
                flex-direction: column;
            }
            
            .profile-banner {
                height: 100px;
            }
            
            .profile-image-container {
                margin-top: -50px;
                margin-bottom: 0.5rem;
            }
            
            .profile-image {
                width: 100px;
                height: 100px;
            }
            
            .profile-name {
                font-size: 1.5rem;
                margin-bottom: 0.25rem;
            }
            
            .profile-email {
                font-size: 0.9rem;
                margin-bottom: 1rem;
            }
            
            .profile-progress {
                margin: 0 1.5rem 1rem 1.5rem;
                padding: 0.75rem;
            }
            
            .info-grid {
                padding: 1rem 1.5rem;
                gap: 1rem;
                grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            }
            
            .info-card {
                padding: 1rem;
            }
            
            .action-buttons {
                padding: 0 1.5rem 1.25rem 1.5rem;
            }
            
            .stat-card {
                padding: 1rem;
            }
            
            .stat-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }
            
            .stat-info h4 {
                font-size: 1.25rem;
            }
        }
        
        @media (max-width: 767px) {
            .content {
                padding-top: 5rem;
            }
        }

        /* Page Header */
        .page-header {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        
        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }
        
        .page-subtitle {
            color: #64748b;
            font-size: 0.875rem;
        }

        /* Profile Card */
        .profile-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
        }

        .profile-banner {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            height: 140px;
            position: relative;
        }

        .profile-image-container {
            position: relative;
            margin-top: -70px;
            text-align: center;
            margin-bottom: 1rem;
        }

        .profile-image {
            width: 140px;
            height: 140px;
            border-radius: 50%;
            border: 5px solid white;
            object-fit: cover;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .profile-name {
            font-size: 1.75rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
            text-align: center;
        }

        .profile-email {
            color: #64748b;
            font-size: 1rem;
            text-align: center;
            margin-bottom: 1.5rem;
        }

        .verified-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #d1fae5;
            color: #065f46;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
            margin: 0 auto;
            justify-content: center;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
            padding: 2rem;
        }

        .info-card {
            background: #f8fafc;
            border-radius: 12px;
            padding: 1.5rem;
            border-left: 4px solid #3b82f6;
            transition: all 0.3s;
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .info-label i {
            color: #3b82f6;
            font-size: 1rem;
        }

        .info-value {
            font-size: 1.125rem;
            color: #1e293b;
            font-weight: 600;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            padding: 0 2rem 2rem 2rem;
            flex-wrap: wrap;
        }

        .btn-edit-profile {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: all 0.3s;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-edit-profile:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
        }

        /* Stats Cards */
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 16px rgba(0,0,0,0.12);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .stat-card.profile-complete .stat-icon {
            background: #dbeafe;
            color: #1e40af;
        }

        .stat-card.account-age .stat-icon {
            background: #fef3c7;
            color: #92400e;
        }

        .stat-card.last-update .stat-icon {
            background: #d1fae5;
            color: #065f46;
        }

        .stat-info h4 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 0.25rem;
        }

        .stat-info p {
            font-size: 0.875rem;
            color: #64748b;
            margin: 0;
        }

        /* Modal Enhancements */
        .modal-header {
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
        }

        .modal-title {
            font-size: 1.25rem;
            font-weight: 700;
        }

        .modal-body {
            padding: 2rem;
        }

        .form-label {
            font-size: 0.875rem;
            font-weight: 600;
            color: #475569;
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #cbd5e1;
            padding: 0.75rem;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
        }

        .btn-save {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        /* Scrollbar Styling for Profile Card */
        .profile-card::-webkit-scrollbar {
            width: 8px;
        }

        .profile-card::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 10px;
        }

        .profile-card::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }

        .profile-card::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                padding: 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }

            .profile-banner {
                height: 100px;
            }

            .profile-image-container {
                margin-top: -50px;
            }

            .profile-image {
                width: 100px;
                height: 100px;
            }

            .profile-name {
                font-size: 1.5rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
                padding: 1.5rem;
                gap: 1rem;
            }

            .action-buttons {
                padding: 0 1.5rem 1.5rem 1.5rem;
            }

            .stats-container {
                grid-template-columns: 1fr;
            }
        }

        /* Empty State */
        .empty-value {
            color: #94a3b8;
            font-style: italic;
        }

        /* Progress Bar */
        .profile-progress {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 1rem;
            margin: 0 2rem 1.5rem 2rem;
        }

        .progress-label {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            font-size: 0.875rem;
            font-weight: 600;
            color: #475569;
        }

        .progress {
            height: 8px;
            border-radius: 8px;
            background: #e2e8f0;
        }

        .progress-bar {
            background: linear-gradient(90deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 8px;
        }
    </style>
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <div class="content">
        <!-- Page Header -->
        <div class="page-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h1 class="page-title">
                        <i class="bi bi-person-circle text-primary"></i> Patient Profile
                    </h1>
                    <p class="page-subtitle mb-0">
                        Manage your personal information and account settings
                    </p>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-container">
            <div class="stat-card profile-complete">
                <div class="stat-icon">
                    <i class="bi bi-clipboard-check-fill"></i>
                </div>
                <div class="stat-info">
                    <h4>95%</h4>
                    <p>Profile Complete</p>
                </div>
            </div>
            <div class="stat-card account-age">
                <div class="stat-icon">
                    <i class="bi bi-calendar-event"></i>
                </div>
                <div class="stat-info">
                    <h4>Member</h4>
                    <p>Active Patient</p>
                </div>
            </div>
            <div class="stat-card last-update">
                <div class="stat-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-info">
                    <h4>Updated</h4>
                    <p>Recently Modified</p>
                </div>
            </div>
        </div>

        <!-- Profile Card -->
        <div class="profile-card">
            <!-- Banner -->
            <div class="profile-banner"></div>

            <!-- Profile Image -->
            <div class="profile-image-container">
                <img src="<?= !empty($user->profile_image) 
                              ? htmlspecialchars($user->profile_image) 
                              : 'https://cdn-icons-png.flaticon.com/512/847/847969.png' ?>" 
                     class="profile-image" 
                     alt="Profile">
            </div>

            <!-- Profile Info -->
            <div class="text-center px-4">
                <h2 class="profile-name"><?= htmlspecialchars($user->username ?? 'Patient') ?></h2>
                <p class="profile-email">
                    <i class="bi bi-envelope me-2"></i><?= htmlspecialchars($user->email ?? '') ?>
                </p>
                <div class="verified-badge">
                    <i class="bi bi-patch-check-fill"></i>
                    Verified Patient
                </div>
            </div>

            <!-- Profile Progress -->
            <div class="profile-progress">
                <div class="progress-label">
                    <span>Profile Completion</span>
                    <span>95%</span>
                </div>
                <div class="progress">
                    <div class="progress-bar" role="progressbar" style="width: 95%" aria-valuenow="95" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>

            <!-- Information Grid -->
            <div class="info-grid">
                <!-- Personal Information -->
                <div class="info-card">
                    <div class="info-label">
                        <i class="bi bi-calendar3"></i>
                        Date of Birth
                    </div>
                    <div class="info-value">
                        <?= !empty($user->birthday) ? htmlspecialchars($user->birthday) : '<span class="empty-value">Not provided</span>' ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-label">
                        <i class="bi bi-hourglass-split"></i>
                        Age
                    </div>
                    <div class="info-value">
                        <?= !empty($user->age) ? htmlspecialchars($user->age) . ' years' : '<span class="empty-value">Not provided</span>' ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-label">
                        <i class="bi bi-gender-ambiguous"></i>
                        Gender
                    </div>
                    <div class="info-value">
                        <?= !empty($user->gender) ? htmlspecialchars($user->gender) : '<span class="empty-value">Not provided</span>' ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-label">
                        <i class="bi bi-heart"></i>
                        Civil Status
                    </div>
                    <div class="info-value">
                        <?= !empty($user->status) ? htmlspecialchars($user->status) : '<span class="empty-value">Not provided</span>' ?>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="info-card">
                    <div class="info-label">
                        <i class="bi bi-telephone"></i>
                        Phone Number
                    </div>
                    <div class="info-value">
                        <?= !empty($user->contactNumber) ? htmlspecialchars($user->contactNumber) : '<span class="empty-value">Not provided</span>' ?>
                    </div>
                </div>

                <div class="info-card" style="grid-column: span 2;">
                    <div class="info-label">
                        <i class="bi bi-geo-alt"></i>
                        Address
                    </div>
                    <div class="info-value">
                        <?= !empty($user->address) ? htmlspecialchars($user->address) : '<span class="empty-value">Not provided</span>' ?>
                    </div>
                </div>

                <!-- Professional Information -->
                <div class="info-card">
                    <div class="info-label">
                        <i class="bi bi-briefcase"></i>
                        Occupation
                    </div>
                    <div class="info-value">
                        <?= !empty($user->occupation) ? htmlspecialchars($user->occupation) : '<span class="empty-value">Not provided</span>' ?>
                    </div>
                </div>

                <div class="info-card">
                    <div class="info-label">
                        <i class="bi bi-flag"></i>
                        Nationality
                    </div>
                    <div class="info-value">
                        <?= !empty($user->nationality) ? htmlspecialchars($user->nationality) : '<span class="empty-value">Not provided</span>' ?>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <button class="btn-edit-profile" data-bs-toggle="modal" data-bs-target="#editProfileModal">
                    <i class="bi bi-pencil-square"></i>
                    Edit Profile
                </button>
            </div>
        </div>
    </div>

    <!-- Edit Profile Modal -->
    <div class="modal fade" id="editProfileModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <form method="POST" action="update_profile.php" enctype="multipart/form-data" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil-square me-2"></i>Edit Personal Information
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?= (string)$user->_id ?>">

                    <!-- Profile Image Upload -->
                    <div class="text-center mb-4">
                        <img src="<?= !empty($user->profile_image) 
                                      ? htmlspecialchars($user->profile_image) 
                                      : 'https://cdn-icons-png.flaticon.com/512/847/847969.png' ?>" 
                             class="rounded-circle mb-3" 
                             width="100" height="100"
                             style="object-fit: cover; border: 3px solid #3b82f6;"
                             alt="Current Profile">
                        <div>
                            <label class="form-label">
                                <i class="bi bi-camera-fill me-2"></i>Change Profile Picture
                            </label>
                            <input type="file" class="form-control" name="profile_image" accept="image/*">
                            <small class="text-muted">Supported formats: JPG, PNG, GIF (Max 5MB)</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <!-- Personal Information Section -->
                        <div class="col-12">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-person-fill me-2"></i>Personal Information
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-person me-2"></i>Full Name
                            </label>
                            <input type="text" class="form-control" name="username" 
                                   value="<?= htmlspecialchars($user->username ?? '') ?>" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-calendar3 me-2"></i>Date of Birth
                            </label>
                            <input type="date" class="form-control" id="dobInput" name="birthday" 
                                   value="<?= htmlspecialchars($user->birthday ?? '') ?>">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-hourglass-split me-2"></i>Age
                            </label>
                            <input type="number" class="form-control" id="ageInput" name="age" 
                                   value="<?= htmlspecialchars($user->age ?? '') ?>" readonly>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-gender-ambiguous me-2"></i>Gender
                            </label>
                            <select class="form-select" name="gender" required>
                                <option value="">Select Gender</option>
                                <option value="Male" <?= ($user->gender ?? '') == "Male" ? "selected" : "" ?>>Male</option>
                                <option value="Female" <?= ($user->gender ?? '') == "Female" ? "selected" : "" ?>>Female</option>
                                <option value="Other" <?= ($user->gender ?? '') == "Other" ? "selected" : "" ?>>Other</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">
                                <i class="bi bi-heart me-2"></i>Civil Status
                            </label>
                            <select name="status" class="form-select" required>
                                <option value="">Select Status</option>
                                <option value="Single" <?= ($user['status'] ?? '') === 'Single' ? 'selected' : '' ?>>Single</option>
                                <option value="Married" <?= ($user['status'] ?? '') === 'Married' ? 'selected' : '' ?>>Married</option>
                                <option value="Separated" <?= ($user['status'] ?? '') === 'Separated' ? 'selected' : '' ?>>Separated</option>
                                <option value="Widowed" <?= ($user['status'] ?? '') === 'Widowed' ? 'selected' : '' ?>>Widowed</option>
                                <option value="Divorced" <?= ($user['status'] ?? '') === 'Divorced' ? 'selected' : '' ?>>Divorced</option>
                                <option value="Complicated" <?= ($user['status'] ?? '') === 'Complicated' ? 'selected' : '' ?>>Complicated</option>
                            </select>
                        </div>

                        <!-- Contact Information Section -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-telephone-fill me-2"></i>Contact Information
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-envelope me-2"></i>Email Address
                            </label>
                            <input type="email" class="form-control" name="email" 
                                   value="<?= htmlspecialchars($user->email ?? '') ?>" required readonly>
                            <small class="text-muted">Email cannot be changed</small>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-telephone me-2"></i>Phone Number
                            </label>
                            <input type="text" class="form-control" name="contactNumber" 
                                   value="<?= htmlspecialchars($user->contactNumber ?? '') ?>" 
                                   placeholder="+63 XXX XXX XXXX">
                        </div>

                        <div class="col-12">
                            <label class="form-label">
                                <i class="bi bi-geo-alt me-2"></i>Complete Address
                            </label>
                            <input type="text" class="form-control" name="address" 
                                   value="<?= htmlspecialchars($user->address ?? '') ?>"
                                   placeholder="Street, Barangay, City, Province">
                        </div>

                        <!-- Professional Information Section -->
                        <div class="col-12 mt-4">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="bi bi-briefcase-fill me-2"></i>Professional Information
                            </h6>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-briefcase me-2"></i>Occupation
                            </label>
                            <input type="text" class="form-control" name="occupation" 
                                   value="<?= htmlspecialchars($user->occupation ?? '') ?>"
                                   placeholder="e.g., Software Engineer">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">
                                <i class="bi bi-flag me-2"></i>Nationality
                            </label>
                            <select name="nationality" class="form-select" required>
                                <option value="">Select Nationality</option>
                                <option value="Filipino" <?= (($user['nationality'] ?? '') === 'Filipino') ? 'selected' : '' ?>>Filipino</option>
                                <option value="Foreign National" <?= (($user['nationality'] ?? '') === 'Foreign National') ? 'selected' : '' ?>>Foreign National</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-save">
                        <i class="bi bi-check-circle me-2"></i>Save Changes
                    </button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle me-2"></i>Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Calculate Age from Date of Birth
        document.addEventListener("DOMContentLoaded", function() {
            const dobInput = document.getElementById("dobInput");
            const ageInput = document.getElementById("ageInput");

            function calculateAge(dob) {
                if (!dob) return "";
                const birthDate = new Date(dob);
                const today = new Date();
                let age = today.getFullYear() - birthDate.getFullYear();
                const monthDiff = today.getMonth() - birthDate.getMonth();
                if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
                    age--;
                }
                return age;
            }

            // Update Age when DOB changes
            dobInput.addEventListener("input", function() {
                ageInput.value = calculateAge(dobInput.value);
            });

            // Auto-fill if DOB already has value
            if (dobInput.value) {
                ageInput.value = calculateAge(dobInput.value);
            }
        });

        // Success Alert
        <?php if ($updateSuccess): ?>
        Swal.fire({
            icon: 'success',
            title: 'Profile Updated!',
            text: 'Your profile information has been successfully updated.',
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Great!'
        });
        <?php endif; ?>

        // Error Alert
        <?php if (isset($_SESSION['update_error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            text: '<?= htmlspecialchars($_SESSION['update_error']) ?>',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Try Again'
        });
        <?php unset($_SESSION['update_error']); endif; ?>

        // Preview image before upload
        document.querySelector('input[name="profile_image"]').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(event) {
                    document.querySelector('.modal-body img').src = event.target.result;
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>