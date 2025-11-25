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

// Get theme
$theme = isset($_SESSION['theme']) ? $_SESSION['theme'] : 'light';

// SweetAlert flag
$updateSuccess = $_SESSION['update_success'] ?? null;
unset($_SESSION['update_success']);
?>

<!doctype html>
<html lang="en" data-theme="<?= $theme ?>">
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
        /* Theme Variables */
        :root {
            --bg-primary: #f5f7fa;
            --bg-secondary: #ffffff;
            --bg-card: #ffffff;
            --text-primary: #1e293b;
            --text-secondary: #64748b;
            --text-muted: #94a3b8;
            --border-color: #e2e8f0;
            --shadow: 0 4px 12px rgba(0,0,0,0.08);
            --shadow-hover: 0 6px 20px rgba(0,0,0,0.12);
            --gradient-start: #1e3a8a;
            --gradient-end: #3b82f6;
            --info-card-bg: #f8fafc;
            --info-card-border: #3b82f6;
        }

        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-secondary: #1e293b;
            --bg-card: #1e293b;
            --text-primary: #f1f5f9;
            --text-secondary: #cbd5e1;
            --text-muted: #94a3b8;
            --border-color: #334155;
            --shadow: 0 4px 12px rgba(0,0,0,0.4);
            --shadow-hover: 0 6px 20px rgba(0,0,0,0.6);
            --gradient-start: #1e3a8a;
            --gradient-end: #2563eb;
            --info-card-bg: #334155;
            --info-card-border: #3b82f6;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }
        
        body {
            background: var(--bg-primary);
            color: var(--text-primary);
            overflow-x: hidden;
            min-height: 100vh;
            transition: background-color 0.3s ease, color 0.3s ease;
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
            
            .page-header {
                margin-bottom: 1rem;
                padding: 1.25rem;
                flex-shrink: 0;
            }
            
            .profile-card {
                flex: 1;
                overflow: hidden;
                margin-bottom: 0;
                min-height: 0;
                display: flex;
                flex-direction: column;
            }
            
            .profile-banner {
                height: 90px;
                flex-shrink: 0;
            }
            
            .profile-header-content {
                padding: 1rem 2rem;
                flex-shrink: 0;
            }
            
            .profile-image {
                width: 90px;
                height: 90px;
                border: 3px solid var(--bg-card);
            }
            
            .profile-name {
                font-size: 1.5rem;
                margin-bottom: 0.25rem;
            }
            
            .profile-email {
                font-size: 0.875rem;
            }
            
            .info-grid {
                padding: 1rem 2rem;
                gap: 1rem;
                grid-template-columns: repeat(3, 1fr);
                overflow-y: auto;
                flex: 1;
            }
            
            .info-card {
                padding: 1rem;
            }
            
            .info-label {
                font-size: 0.75rem;
                margin-bottom: 0.5rem;
            }
            
            .info-label i {
                font-size: 0.9rem;
            }
            
            .info-value {
                font-size: 1rem;
            }
            
            .action-buttons {
                padding: 1rem 2rem;
                flex-shrink: 0;
            }
            
            .btn-edit-profile {
                padding: 0.75rem 2rem;
                font-size: 0.95rem;
            }
        }
        
        @media (min-width: 1200px) {
            .profile-banner {
                height: 100px;
            }
            
            .profile-image {
                width: 100px;
                height: 100px;
            }
            
            .profile-name {
                font-size: 1.75rem;
            }
            
            .profile-email {
                font-size: 0.95rem;
            }
            
            .info-label {
                font-size: 0.8rem;
            }
            
            .info-value {
                font-size: 1.05rem;
            }
        }
        
        @media (max-width: 767px) {
            .content {
                padding-top: 5rem;
            }
        }

        /* Page Header */
        .page-header {
            background: var(--bg-card);
            border-radius: 16px;
            padding: 1.5rem 2rem;
            margin-bottom: 1.5rem;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
        }
        
        .page-title {
            font-size: 1.75rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .page-title i {
            color: var(--gradient-end);
        }
        
        .page-subtitle {
            color: var(--text-secondary);
            font-size: 0.95rem;
        }

        /* Profile Card */
        .profile-card {
            background: var(--bg-card);
            border-radius: 20px;
            box-shadow: var(--shadow);
            overflow: hidden;
            margin-bottom: 0;
            display: flex;
            flex-direction: column;
            border: 1px solid var(--border-color);
        }

        .profile-banner {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            height: 140px;
            position: relative;
            flex-shrink: 0;
        }

        .profile-header-content {
            display: flex;
            align-items: flex-start;
            gap: 1.5rem;
            padding: 0 2rem;
            margin-top: -50px;
            margin-bottom: 1.5rem;
            flex-shrink: 0;
        }

        .profile-image {
            width: 120px;
            height: 120px;
            border-radius: 20px;
            border: 4px solid var(--bg-card);
            object-fit: cover;
            box-shadow: 0 8px 24px rgba(0,0,0,0.15);
            flex-shrink: 0;
        }

        .profile-info {
            flex: 1;
            padding-top: 60px;
        }

        .profile-name {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: 0.5rem;
        }

        .profile-email {
            color: var(--text-secondary);
            font-size: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .profile-email i {
            color: var(--gradient-end);
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.25rem;
            padding: 0 2rem 1.5rem 2rem;
            flex: 1;
            overflow-y: auto;
        }
        
        @media (min-width: 576px) {
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 992px) {
            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (min-width: 1200px) {
            .info-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        .info-card {
            background: var(--info-card-bg);
            border-radius: 12px;
            padding: 1.25rem;
            border-left: 3px solid var(--info-card-border);
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            border-left: 3px solid var(--info-card-border);
        }
        
        .info-card.full-width {
            grid-column: 1 / -1;
        }

        .info-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-hover);
        }

        .info-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--text-secondary);
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 0.75rem;
        }

        .info-label i {
            color: var(--gradient-end);
            font-size: 1rem;
        }

        .info-value {
            font-size: 1.125rem;
            color: var(--text-primary);
            font-weight: 600;
        }

        .empty-value {
            color: var(--text-muted);
            font-style: italic;
            font-weight: 400;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            padding: 0 2rem 2rem 2rem;
            flex-wrap: wrap;
            flex-shrink: 0;
        }

        .btn-edit-profile {
            background: linear-gradient(135deg, var(--gradient-end) 0%, var(--gradient-start) 100%);
            border: none;
            color: white;
            padding: 1rem 2.5rem;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            display: inline-flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn-edit-profile:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(59, 130, 246, 0.4);
        }

        /* Modal Enhancements */
        .modal-content {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 16px;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            color: white;
            border-bottom: none;
            padding: 1.5rem;
            border-radius: 16px 16px 0 0;
        }

        .modal-title {
            font-size: 1.35rem;
            font-weight: 700;
        }

        .modal-body {
            padding: 2rem;
            background: var(--bg-card);
        }

        .form-label {
            font-size: 0.9rem;
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 1px solid var(--border-color);
            padding: 0.875rem;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: var(--bg-primary);
            color: var(--text-primary);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--gradient-end);
            box-shadow: 0 0 0 0.2rem rgba(59, 130, 246, 0.15);
            background: var(--bg-card);
        }

        .form-control:disabled, .form-control[readonly] {
            background: var(--info-card-bg);
            color: var(--text-muted);
        }

        .modal-footer {
            background: var(--bg-card);
            border-top: 1px solid var(--border-color);
            padding: 1.5rem;
            border-radius: 0 0 16px 16px;
        }

        .btn-save {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            border: none;
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.2s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
        }

        .btn-secondary {
            background: var(--text-muted);
            border: none;
            color: white;
            padding: 0.875rem 2rem;
            border-radius: 10px;
            font-weight: 600;
        }

        .btn-secondary:hover {
            background: var(--text-secondary);
        }

        /* Scrollbar Styling */
        .info-grid::-webkit-scrollbar {
            width: 8px;
        }

        .info-grid::-webkit-scrollbar-track {
            background: var(--bg-primary);
            border-radius: 10px;
        }

        .info-grid::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 10px;
        }

        .info-grid::-webkit-scrollbar-thumb:hover {
            background: var(--text-muted);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .page-header {
                padding: 1.25rem 1.5rem;
            }
            
            .page-title {
                font-size: 1.5rem;
            }
            
            .page-subtitle {
                font-size: 0.875rem;
            }

            .profile-banner {
                height: 120px;
            }

            .profile-header-content {
                flex-direction: column;
                align-items: center;
                text-align: center;
                padding: 0 1.5rem;
                margin-top: -60px;
            }

            .profile-image {
                width: 110px;
                height: 110px;
                border: 4px solid var(--bg-card);
            }

            .profile-info {
                padding-top: 0;
            }

            .profile-name {
                font-size: 1.5rem;
            }
            
            .profile-email {
                font-size: 0.95rem;
                justify-content: center;
            }

            .info-grid {
                grid-template-columns: 1fr;
                padding: 1.5rem;
                gap: 1rem;
            }

            .action-buttons {
                padding: 0 1.5rem 1.5rem 1.5rem;
            }
            
            .btn-edit-profile {
                padding: 0.875rem 2rem;
                font-size: 0.95rem;
                width: 100%;
            }
        }
        
        /* Small Mobile */
        @media (max-width: 480px) {
            .profile-banner {
                height: 100px;
            }
            
            .profile-header-content {
                margin-top: -50px;
            }
            
            .profile-image {
                width: 90px;
                height: 90px;
                border: 3px solid var(--bg-card);
            }
            
            .profile-name {
                font-size: 1.35rem;
            }
            
            .profile-email {
                font-size: 0.85rem;
            }
            
            .info-card {
                padding: 1rem;
            }
            
            .info-label {
                font-size: 0.75rem;
            }
            
            .info-value {
                font-size: 1rem;
            }
        }
        
        /* Section Headers in Modal */
        .section-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 1.25rem;
            padding-bottom: 0.75rem;
            border-bottom: 2px solid var(--border-color);
        }

        .section-header h6 {
            font-weight: 700;
            color: var(--gradient-end);
            margin: 0;
            font-size: 1rem;
        }

        .section-header i {
            color: var(--gradient-end);
            font-size: 1.1rem;
        }

        /* Profile Image Preview in Modal */
        .profile-preview {
            width: 120px;
            height: 120px;
            border-radius: 16px;
            object-fit: cover;
            border: 3px solid var(--gradient-end);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
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
                        <i class="bi bi-person-circle"></i> Patient Profile
                    </h1>
                    <p class="page-subtitle mb-0">
                        View and manage your personal information
                    </p>
                </div>
            </div>
        </div>

        <!-- Profile Card -->
        <div class="profile-card">
            <!-- Banner -->
            <div class="profile-banner"></div>

            <!-- Profile Header -->
            <div class="profile-header-content">
                <img src="<?= !empty($user->profile_image) 
                              ? htmlspecialchars($user->profile_image) 
                              : 'https://cdn-icons-png.flaticon.com/512/847/847969.png' ?>" 
                     class="profile-image" 
                     alt="Profile">
                
                <div class="profile-info">
                    <h2 class="profile-name"><?= htmlspecialchars($user->username ?? 'Patient') ?></h2>
                    <p class="profile-email">
                        <i class="bi bi-envelope-fill"></i>
                        <?= htmlspecialchars($user->email ?? '') ?>
                    </p>
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

                <div class="info-card full-width">
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
                             class="profile-preview mb-3" 
                             id="profilePreview"
                             alt="Current Profile">
                        <div>
                            <label class="form-label">
                                <i class="bi bi-camera-fill me-2"></i>Change Profile Picture
                            </label>
                            <input type="file" class="form-control" name="profile_image" id="profileImageInput" accept="image/*">
                            <small class="text-muted">Supported formats: JPG, PNG, GIF (Max 5MB)</small>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-3">
                        <!-- Personal Information Section -->
                        <div class="col-12">
                            <div class="section-header">
                                <i class="bi bi-person-fill"></i>
                                <h6>Personal Information</h6>
                            </div>
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
                            <div class="section-header">
                                <i class="bi bi-telephone-fill"></i>
                                <h6>Contact Information</h6>
                            </div>
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
                            <div class="section-header">
                                <i class="bi bi-briefcase-fill"></i>
                                <h6>Professional Information</h6>
                            </div>
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
            const profileImageInput = document.getElementById("profileImageInput");
            const profilePreview = document.getElementById("profilePreview");

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

            // Preview image before upload
            profileImageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    // Check file size (5MB max)
                    if (file.size > 5 * 1024 * 1024) {
                        Swal.fire({
                            icon: 'error',
                            title: 'File Too Large',
                            text: 'Please select an image smaller than 5MB',
                            confirmButtonColor: '#ef4444'
                        });
                        profileImageInput.value = '';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(event) {
                        profilePreview.src = event.target.result;
                    }
                    reader.readAsDataURL(file);
                }
            });
        });

        // Success Alert
        <?php if ($updateSuccess): ?>
        Swal.fire({
            icon: 'success',
            title: 'Profile Updated!',
            text: 'Your profile information has been successfully updated.',
            confirmButtonColor: '#3b82f6',
            confirmButtonText: 'Great!',
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#ffffff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b'
        });
        <?php endif; ?>

        // Error Alert
        <?php if (isset($_SESSION['update_error'])): ?>
        Swal.fire({
            icon: 'error',
            title: 'Update Failed',
            text: '<?= htmlspecialchars($_SESSION['update_error']) ?>',
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Try Again',
            background: document.documentElement.getAttribute('data-theme') === 'dark' ? '#1e293b' : '#ffffff',
            color: document.documentElement.getAttribute('data-theme') === 'dark' ? '#f1f5f9' : '#1e293b'
        });
        <?php unset($_SESSION['update_error']); endif; ?>
    </script>
</body>
</html>