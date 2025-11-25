<?php
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: log-in.php");
    exit;
}

$userFullName = $_SESSION['username'];
require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;

$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->HaliliDentalClinic;
$usersCollection = $db->users;

// Find user by username (case-insensitive)
$user = $usersCollection->findOne([
    'username' => new MongoDB\BSON\Regex('^' . preg_quote($userFullName) . '$', 'i')
]);

// Safely extract user details
$userEmail = $_SESSION['email'] ?? ($user['email'] ?? '');
$userContact = $user['contactNumber'] ?? '';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <title>Dental Services - Halili Dental</title>
  
  <style>
    /* Theme Variables */
    :root {
      --bg-primary: #f8f9fa;
      --bg-secondary: #ffffff;
      --text-primary: #111827;
      --text-secondary: #6b7280;
      --border-color: #e5e7eb;

      --accent-start: #1d4ed8;
      --accent-end: #1e40af;

      --success-start: #059669;
      --success-end: #047857;

      --muted-bg: #f3f4f6;
      --card-shadow: 0 2px 8px rgba(0,0,0,0.08);
      --card-shadow-hover: 0 8px 24px rgba(0,0,0,0.12);
    }

    [data-theme="dark"] {
      --bg-primary: #0b1220;       /* page background */
      --bg-secondary: #0f1724;     /* card / content background */
      --text-primary: #f9fafb;
      --text-secondary: #cbd5e1;
      --border-color: #22303f;

      --accent-start: #1e40af;
      --accent-end: #5e697aff;
      

      --success-start: #059669;
      --success-end: #064e3b;

      --muted-bg: #111827;
      --card-shadow: 0 2px 8px rgba(0,0,0,0.6);
      --card-shadow-hover: 0 12px 36px rgba(0,0,0,0.6);
    }

    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body { 
      background: var(--bg-primary);
      color: var(--text-primary);
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
      overflow-x: hidden;
      transition: background-color 0.3s ease, color 0.3s ease;
    }

    main {
      margin-left: 0;
      padding: 1rem;
      min-height: 100vh;
      transition: margin-left 0.3s ease;
      background: transparent;
    }

    @media (min-width: 768px) {
      main {
        margin-left: 16rem;
        padding: 2rem;
      }
    }

    @media (max-width: 767px) {
      main {
        padding-top: 5rem;
      }
    }

    /* Header Section */
    .page-header {
      background: linear-gradient(135deg, var(--accent-start) 0%, var(--accent-end) 100%);
      color: white;
      padding: 2.5rem 1.5rem;
      border-radius: 16px;
      margin-bottom: 2rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.12);
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

    /* Services Grid */
    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
      gap: 1.5rem;
      margin-bottom: 2rem;
    }

    @media (max-width: 767px) {
      .services-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
      }
    }

    /* Service Card */
    .service-card {
      background: var(--bg-secondary);
      border-radius: 16px;
      overflow: hidden;
      box-shadow: var(--card-shadow);
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      border: 1px solid var(--border-color);
    }

    .service-card:hover {
      transform: translateY(-4px);
      box-shadow: var(--card-shadow-hover);
    }

    .service-card-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      background: var(--muted-bg);
    }

    .service-card-content {
      padding: 1.5rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .service-card-title {
      color: var(--accent-start);
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 0.75rem;
    }

    .service-card-description {
      color: var(--text-secondary);
      font-size: 0.95rem;
      line-height: 1.6;
      margin-bottom: 1.25rem;
      flex: 1;
    }

    .service-card-footer {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding-top: 1rem;
      border-top: 1px solid var(--muted-bg);
    }

    .service-price {
      font-size: 0.85rem;
      color: var(--text-secondary);
      font-weight: 500;
    }

    .btn-book {
      background: linear-gradient(135deg, var(--accent-start) 0%, var(--accent-end) 100%);
      color: white;
      border: none;
      padding: 0.65rem 1.5rem;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
    }

    .btn-book:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(29, 78, 216, 0.18);
    }

    /* Modal Styles */
    .modal-backdrop {
      display: none;
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: rgba(0, 0, 0, 0.6);
      z-index: 1040;
      backdrop-filter: blur(4px);
    }

    .modal-backdrop.show {
      display: block;
    }

    .booking-modal {
      display: none;
      position: fixed;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      background: var(--bg-secondary);
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      z-index: 1050;
      max-width: 600px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
      border: 1px solid var(--border-color);
    }

    .booking-modal.show {
      display: block;
      animation: modalSlideIn 0.3s ease;
    }

    @keyframes modalSlideIn {
      from {
        opacity: 0;
        transform: translate(-50%, -48%);
      }
      to {
        opacity: 1;
        transform: translate(-50%, -50%);
      }
    }

    .modal-header {
      background: linear-gradient(135deg, var(--accent-start) 0%, var(--accent-end) 100%);
      color: white;
      padding: 1.5rem;
      border-radius: 16px 16px 0 0;
      position: relative;
    }

    .modal-header h2 {
      margin: 0;
      font-size: 1.5rem;
      font-weight: 700;
    }

    .modal-close {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: rgba(255, 255, 255, 0.2);
      border: none;
      color: white;
      width: 36px;
      height: 36px;
      border-radius: 50%;
      cursor: pointer;
      font-size: 1.5rem;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: all 0.3s ease;
    }

    .modal-close:hover {
      background: rgba(255, 255, 255, 0.3);
      transform: rotate(90deg);
    }

    .modal-body {
      padding: 1.5rem;
      color: var(--text-primary);
      background: var(--bg-secondary);
    }

    .form-section {
      margin-bottom: 1.5rem;
      padding-bottom: 1.5rem;
      border-bottom: 2px solid var(--muted-bg);
    }

    .form-section:last-of-type {
      border-bottom: none;
      margin-bottom: 0;
      padding-bottom: 0;
    }

    .form-section-title {
      font-size: 1.1rem;
      font-weight: 700;
      color: var(--accent-start);
      margin-bottom: 1rem;
      display: flex;
      align-items: center;
      gap: 0.5rem;
    }

    .form-section-title i {
      font-size: 1.3rem;
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-label {
      display: block;
      font-weight: 600;
      color: var(--text-primary);
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }

    .form-label i {
      color: var(--accent-start);
      margin-right: 0.5rem;
      width: 20px;
    }

    .form-label .optional {
      color: var(--text-secondary);
      font-weight: 400;
      font-size: 0.85rem;
      font-style: italic;
    }

    .form-control {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 2px solid var(--border-color);
      border-radius: 10px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      background: var(--muted-bg);
      color: var(--text-primary);
    }

    .form-control:focus {
      outline: none;
      border-color: var(--accent-start);
      background: var(--bg-secondary);
      box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.06);
    }

    .form-control:read-only {
      background: var(--muted-bg);
      color: var(--text-secondary);
      cursor: not-allowed;
    }

    .form-control.error {
      border-color: #dc2626;
      background: #fef2f2;
    }

    .error-message {
      color: #dc2626;
      font-size: 0.85rem;
      margin-top: 0.5rem;
      display: none;
    }

    .error-message.show {
      display: block;
    }

    .form-helper-text {
      font-size: 0.85rem;
      color: var(--text-secondary);
      margin-top: 0.5rem;
      display: flex;
      align-items: start;
      gap: 0.5rem;
    }

    .form-helper-text i {
      color: var(--accent-start);
      margin-top: 0.2rem;
    }

    .btn-submit {
      width: 100%;
      background: linear-gradient(135deg, var(--success-start) 0%, var(--success-end) 100%);
      color: white;
      border: none;
      padding: 1rem;
      border-radius: 10px;
      font-weight: 600;
      font-size: 1rem;
      cursor: pointer;
      transition: all 0.3s ease;
      margin-top: 1rem;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 0.5rem;
    }

    .btn-submit:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 16px rgba(5, 150, 105, 0.18);
    }

    .btn-submit:disabled {
      background: #9ca3af;
      cursor: not-allowed;
      transform: none;
    }

    .service-badge {
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      background: rgba(219,234,254,0.14);
      color: var(--accent-start);
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 1rem;
      border: 1px solid var(--border-color);
    }

    /* Info Banner */
    .info-banner {
      background: linear-gradient(90deg, rgba(219,234,254,0.08), rgba(219,234,254,0.02));
      border-left: 4px solid var(--accent-start);
      padding: 1rem 1.25rem;
      border-radius: 8px;
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
      color: var(--text-primary);
    }

    .info-banner i {
      color: var(--accent-start);
      font-size: 1.5rem;
    }

    .info-banner-content p {
      margin: 0;
      color: var(--accent-end);
      font-weight: 500;
    }

    /* Scrollbar */
    .booking-modal::-webkit-scrollbar {
      width: 8px;
    }

    .booking-modal::-webkit-scrollbar-track {
      background: var(--muted-bg);
    }

    .booking-modal::-webkit-scrollbar-thumb {
      background: var(--border-color);
      border-radius: 10px;
    }

    .booking-modal::-webkit-scrollbar-thumb:hover {
      background: var(--text-secondary);
    }

    /* Utility: error highlight for inputs */
    input.error, textarea.error {
      border-color: #dc2626 !important;
      background: #fff5f5;
    }
  </style>
</head>
<body>

<?php include 'sidebar.php'; ?>

<main>
  <!-- Page Header -->
  <div class="page-header">
    <h1><i class="fas fa-tooth me-2"></i>Our Dental Services</h1>
    <p>Quality dental care with comprehensive treatment options</p>
  </div>

  <!-- Info Banner -->
  <div class="info-banner">
    <i class="fas fa-info-circle"></i>
    <div class="info-banner-content">
      <p>Book your appointment today! Choose from our wide range of professional dental services.</p>
    </div>
  </div>

  <!-- Services Grid -->
  <div id="servicesContainer" class="services-grid">
    <?php 
      include 'service-grid.php'; 
      foreach ($services as $service): ?>
        <div class="service-card">
          <img src="<?= htmlspecialchars($service['image']) ?>" 
               alt="<?= htmlspecialchars($service['title']) ?>" 
               class="service-card-image">
          <div class="service-card-content">
            <h2 class="service-card-title"><?= htmlspecialchars($service['title']) ?></h2>
            <p class="service-card-description"><?= htmlspecialchars($service['description']) ?></p>
            <div class="service-card-footer">
              <span class="service-price">
                <i class="fas fa-clock me-1"></i>
                Professional Care
              </span>
              <button class="btn-book">
                <?= htmlspecialchars($service['btn']) ?>
                <i class="fas fa-arrow-right"></i>
              </button>
            </div>
          </div>
        </div>
    <?php endforeach; ?>
  </div>
</main>

<!-- Modal Backdrop -->
<div id="modalBackdrop" class="modal-backdrop"></div>

<!-- Booking Modal -->
<div id="bookingModal" class="booking-modal">
  <div class="modal-header">
    <h2><i class="fas fa-calendar-check me-2"></i>Book an Appointment</h2>
    <button class="modal-close" aria-label="Close">&times;</button>
  </div>
  <div class="modal-body">
    <div class="service-badge">
      <i class="fas fa-tooth"></i>
      <span id="selectedService">Selected Service</span>
    </div>
    
    <form id="bookingForm">
      <!-- Service Information Section -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fas fa-clipboard-list"></i>
          Service Information
        </div>
        
        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-tooth"></i>Service
          </label>
          <input type="text" id="serviceName" name="serviceName" class="form-control" readonly />
        </div>
      </div>

      <!-- Personal Information Section -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fas fa-user-circle"></i>
          Personal Information
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-user"></i>Full Name
          </label>
          <input type="text" id="username" name="username" class="form-control" readonly />
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-envelope"></i>Email
          </label>
          <input type="email" id="email" name="email" class="form-control" readonly />
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-phone"></i>Phone Number
          </label>
          <input type="tel" id="contactNumber" name="contactNumber" class="form-control" 
                 placeholder="0912 345 6789" required />
        </div>
      </div>

      <!-- Medical Information Section -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fas fa-notes-medical"></i>
          Medical Information
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-heartbeat"></i>Medical History / Illness
            <span class="optional">(Optional)</span>
          </label>
          <textarea id="medicalHistory" name="medicalHistory" class="form-control" rows="3"
                    placeholder="Please list any medical conditions, illnesses, or health issues (e.g., diabetes, heart disease, hypertension)"></textarea>
          <div class="form-helper-text">
            <i class="fas fa-info-circle"></i>
            <span>Include any ongoing treatments or medications you're currently taking</span>
          </div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-allergies"></i>Allergies
            <span class="optional">(Optional)</span>
          </label>
          <textarea id="allergies" name="allergies" class="form-control" rows="3"
                    placeholder="List any allergies (e.g., medications, anesthesia, latex, foods)"></textarea>
          <div class="form-helper-text">
            <i class="fas fa-info-circle"></i>
            <span>This helps us ensure your safety during treatment</span>
          </div>
        </div>
      </div>

      <!-- Appointment Details Section -->
      <div class="form-section">
        <div class="form-section-title">
          <i class="fas fa-calendar-alt"></i>
          Appointment Details
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-comment-medical"></i>Reason for Visit
          </label>
          <textarea id="description" name="description" class="form-control" rows="3"
                    placeholder="Please describe your dental concern or reason for visit" required></textarea>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-calendar-day"></i>Appointment Date
          </label>
          <input type="date" id="date" name="date" class="form-control" required />
          <div id="dateError" class="error-message">Please select a future date</div>
        </div>

        <div class="form-group">
          <label class="form-label">
            <i class="fas fa-clock"></i>Appointment Time
          </label>
          <input type="time" id="time" name="time" class="form-control" required />
          <div id="timeError" class="error-message">Please select a future time</div>
        </div>
      </div>

      <button type="submit" class="btn-submit" id="submitBtn">
        <i class="fas fa-check-circle"></i>
        Confirm Booking
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
  const userFullName = <?php echo json_encode($_SESSION['username'] ?? $user['fullname'] ?? ''); ?>;
  const userEmail = <?php echo json_encode($_SESSION['email'] ?? $user['email'] ?? ''); ?>;
  const userContact = <?php echo json_encode($userContact); ?>;

  // Helper to read CSS variables (returns trimmed string)
  function cssVar(name) {
    return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
  }

  // Build SweetAlert options that are theme aware
  function swalThemeOptions(extra = {}) {
    const background = cssVar('--bg-secondary') || '#f9fafb';
    const color = cssVar('--text-primary') || '#111827';
    const buttonColor = cssVar('--accent-start') || '#1d4ed8';
    return Object.assign({
      background,
      color,
      confirmButtonColor: buttonColor,
      customClass: {
        popup: 'rounded-xl shadow-lg',
        confirmButton: 'px-4 py-2 font-semibold'
      }
    }, extra);
  }

  // Clinic operating hours
  const clinicHours = {
    0: { open: '08:00', close: '12:00', closed: false }, // Sunday
    1: { open: '08:00', close: '17:00', closed: false }, // Monday
    2: { open: '08:00', close: '17:00', closed: false }, // Tuesday
    3: { open: '08:00', close: '17:00', closed: false }, // Wednesday
    4: { open: '08:00', close: '17:00', closed: false }, // Thursday
    5: { open: '08:00', close: '17:00', closed: false }, // Friday
    6: { open: '08:00', close: '17:00', closed: false }  // Saturday
  };

  // Additional evening hours
  const eveningHours = {
    1: { open: '16:00', close: '19:00' }, // Monday 4-7 PM
    2: { open: '16:00', close: '19:00' }, // Tuesday 4-7 PM
    3: { open: '16:00', close: '19:00' }, // Wednesday 4-7 PM
    4: { open: '14:00', close: '19:00' }, // Thursday 2-7 PM
    6: { open: '14:00', close: '19:00' }  // Saturday 2-7 PM
  };

  function isWithinOperatingHours(dayOfWeek, time) {
    const hours = clinicHours[dayOfWeek];
    
    if (hours.closed) {
      return false;
    }

    if (time >= hours.open && time <= hours.close) {
      return true;
    }

    if (eveningHours[dayOfWeek]) {
      const evening = eveningHours[dayOfWeek];
      if (time >= evening.open && time <= evening.close) {
        return true;
      }
    }

    return false;
  }

  function getOperatingHoursText(dayOfWeek) {
    const hours = clinicHours[dayOfWeek];
    const dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
    
    if (hours.closed) {
      return `${dayNames[dayOfWeek]}: Closed`;
    }

    let hoursText = `${dayNames[dayOfWeek]}: ${formatTime(hours.open)} - ${formatTime(hours.close)}`;
    
    if (eveningHours[dayOfWeek]) {
      const evening = eveningHours[dayOfWeek];
      hoursText += ` and ${formatTime(evening.open)} - ${formatTime(evening.close)}`;
    }

    return hoursText;
  }

  function formatTime(time24) {
    const [hours, minutes] = time24.split(':');
    const hour = parseInt(hours);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    return `${hour12}:${minutes} ${ampm}`;
  }

  document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('bookingModal');
    const modalBackdrop = document.getElementById('modalBackdrop');
    const serviceNameInput = document.getElementById('serviceName');
    const selectedServiceBadge = document.getElementById('selectedService');
    const fullnameInput = document.getElementById('username');
    const emailInput = document.getElementById('email');
    const contactInput = document.getElementById('contactNumber');
    const closeBtn = modal.querySelector('.modal-close');
    const bookingForm = document.getElementById('bookingForm');
    const dateInput = document.getElementById('date');
    const timeInput = document.getElementById('time');
    const dateError = document.getElementById('dateError');
    const timeError = document.getElementById('timeError');
    const submitBtn = document.getElementById('submitBtn');

    // Get today's date in YYYY-MM-DD format
    const today = new Date();
    const todayString = today.toISOString().split('T')[0];
    
    // Set minimum date to today
    dateInput.setAttribute('min', todayString);

    // Pre-fill user data
    fullnameInput.value = userFullName;
    emailInput.value = userEmail;
    contactInput.value = userContact;

    // Validate date - check if clinic is open
    dateInput.addEventListener('change', () => {
      const selectedDate = dateInput.value;
      
      if (selectedDate < todayString) {
        dateInput.classList.add('error');
        dateError.textContent = 'Please select a future date';
        dateError.classList.add('show');
        submitBtn.disabled = true;
        return;
      }

      const selectedDay = new Date(selectedDate + 'T00:00:00').getDay();
      
      if (clinicHours[selectedDay].closed) {
        dateInput.classList.add('error');
        dateError.textContent = 'Clinic is closed on this day. Please select another date.';
        dateError.classList.add('show');
        submitBtn.disabled = true;
        
        Swal.fire(Object.assign({
          icon: 'info',
          title: 'Clinic Closed',
          html: `
            <p>The clinic is closed on the selected day.</p>
            <hr style="margin: 1rem 0;">
            <div style="text-align: left; font-size: 0.9rem;">
              <strong>Operating Hours:</strong><br>
              ${getOperatingHoursText(1)}<br>
              ${getOperatingHoursText(2)}<br>
              ${getOperatingHoursText(3)}<br>
              ${getOperatingHoursText(4)}<br>
              ${getOperatingHoursText(5)}<br>
              ${getOperatingHoursText(6)}<br>
              ${getOperatingHoursText(0)}
            </div>
          `
        }, swalThemeOptions()));
      } else {
        dateInput.classList.remove('error');
        dateError.classList.remove('show');
        validateDateTime();
      }
    });

    timeInput.addEventListener('change', validateDateTime);

    function validateDateTime() {
      const selectedDate = dateInput.value;
      const selectedTime = timeInput.value;
      
      if (!selectedDate || !selectedTime) {
        submitBtn.disabled = false;
        return;
      }

      const now = new Date();
      const selectedDateTime = new Date(`${selectedDate}T${selectedTime}`);
      const selectedDay = new Date(selectedDate + 'T00:00:00').getDay();
      
      if (selectedDateTime < now) {
        timeInput.classList.add('error');
        timeError.classList.add('show');
        timeError.textContent = 'Please select a future time';
        submitBtn.disabled = true;
        return;
      }

      if (!isWithinOperatingHours(selectedDay, selectedTime)) {
        timeInput.classList.add('error');
        timeError.classList.add('show');
        timeError.textContent = `Outside operating hours. ${getOperatingHoursText(selectedDay)}`;
        submitBtn.disabled = true;
        
        Swal.fire(Object.assign({
          icon: 'warning',
          title: 'Outside Operating Hours',
          html: `
            <p>The selected time is outside clinic operating hours.</p>
            <hr style="margin: 1rem 0;">
            <div style="text-align: left; font-size: 0.9rem;">
              <strong>${getOperatingHoursText(selectedDay)}</strong>
            </div>
          `
        }, swalThemeOptions({ confirmButtonColor: cssVar('--accent-start') })));
      } else {
        timeInput.classList.remove('error');
        timeError.classList.remove('show');
        submitBtn.disabled = false;
      }
    }

    document.getElementById('servicesContainer').addEventListener('click', (e) => {
      if (e.target.closest('.btn-book')) {
        const card = e.target.closest('.service-card');
        const serviceTitle = card.querySelector('.service-card-title').textContent;
        serviceNameInput.value = serviceTitle;
        selectedServiceBadge.textContent = serviceTitle;
        modal.classList.add('show');
        modalBackdrop.classList.add('show');
        document.body.style.overflow = 'hidden';
      }
    });

    const closeModal = () => {
      modal.classList.remove('show');
      modalBackdrop.classList.remove('show');
      document.body.style.overflow = 'auto';
      bookingForm.reset();
      fullnameInput.value = userFullName;
      emailInput.value = userEmail;
      contactInput.value = userContact;
      dateInput.classList.remove('error');
      timeInput.classList.remove('error');
      dateError.classList.remove('show');
      timeError.classList.remove('show');
      submitBtn.disabled = false;
    };

    closeBtn.addEventListener('click', closeModal);
    modalBackdrop.addEventListener('click', closeModal);

   bookingForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      const selectedDate = dateInput.value;
      const selectedTime = timeInput.value;
      const selectedDateTime = new Date(`${selectedDate}T${selectedTime}`);
      const selectedDay = new Date(selectedDate + 'T00:00:00').getDay();
      const now = new Date();
      
      if (selectedDate < todayString) {
        Swal.fire(Object.assign({
          icon: 'warning',
          title: 'Invalid Date',
          text: 'Cannot book appointment for a past date. Please select today or a future date.'
        }, swalThemeOptions()));
        return;
      }

      if (clinicHours[selectedDay].closed) {
        Swal.fire(Object.assign({
          icon: 'warning',
          title: 'Clinic Closed',
          text: 'The clinic is closed on the selected day. Please choose another date.'
        }, swalThemeOptions()));
        return;
      }

      if (selectedDateTime < now) {
        Swal.fire(Object.assign({
          icon: 'warning',
          title: 'Invalid Time',
          text: 'Cannot book appointment in the past. Please select a future date and time.'
        }, swalThemeOptions()));
        return;
      }

      if (!isWithinOperatingHours(selectedDay, selectedTime)) {
        Swal.fire(Object.assign({
          icon: 'warning',
          title: 'Outside Operating Hours',
          html: `
            <p>The selected time is outside clinic operating hours.</p>
            <hr style="margin: 1rem 0;">
            <div style="text-align: left; font-size: 0.9rem;">
              <strong>${getOperatingHoursText(selectedDay)}</strong>
            </div>
          `
        }, swalThemeOptions()));
        return;
      }

      // Check for existing booking at this date and time
      Swal.fire(Object.assign({
        title: 'Checking availability...',
        text: 'Please wait while we verify the time slot.',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      }, swalThemeOptions()));

      try {
        const checkResponse = await fetch('check-booking-availability.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            date: selectedDate,
            time: selectedTime
          }),
        });

        const availabilityResult = await checkResponse.json();

        if (!checkResponse.ok || !availabilityResult.available) {
          Swal.fire(Object.assign({
            icon: 'error',
            title: 'Time Slot Unavailable',
            html: `
              <p>This time slot is already booked.</p>
              <hr style="margin: 1rem 0;">
              <p style="color: ${cssVar('--text-secondary')}; font-size: 0.9rem;">
                Please select a different date or time for your appointment.
              </p>
            `,
            confirmButtonColor: '#dc2626'
          }, swalThemeOptions()));
          return;
        }
      } catch (error) {
        Swal.fire(Object.assign({
          icon: 'error',
          title: 'Connection Error',
          text: 'Unable to verify time slot availability. Please try again.',
          footer: `<small style="color: ${cssVar('--text-secondary')}">Error: ${error.message}</small>`,
          confirmButtonColor: '#dc2626'
        }, swalThemeOptions()));
        return;
      }

      const formData = new FormData(bookingForm);
      const data = Object.fromEntries(formData.entries());

      Swal.fire(Object.assign({
        title: 'Processing...',
        text: 'Please wait while we confirm your booking.',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      }, swalThemeOptions()));

      try {
        const response = await fetch('submit-booking.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data),
        });

        const text = await response.text();

        if (response.ok) {
          const formattedDate = new Date(data.date).toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
          });
          
          const formattedTime = new Date(`2000-01-01T${data.time}`).toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
          });

          let medicalInfo = '';
          if (data.medicalHistory || data.allergies) {
            medicalInfo = `<hr style="margin: 1rem 0; border-color: ${cssVar('--border-color') || '#e5e7eb'};">`;
            if (data.medicalHistory) {
              medicalInfo += `<p style="margin-bottom: 0.5rem;"><strong>Medical History:</strong> ${data.medicalHistory}</p>`;
            }
            if (data.allergies) {
              medicalInfo += `<p style="margin-bottom: 0.5rem;"><strong>Allergies:</strong> ${data.allergies}</p>`;
            }
          }

          Swal.fire(Object.assign({
            icon: 'success',
            title: 'Booking Confirmed!',
            html: `
              <div style="text-align: left; padding: 1rem; color: ${cssVar('--text-primary')}">
                <p style="margin-bottom: 0.75rem;"><strong>Service:</strong> ${data.serviceName}</p>
                <p style="margin-bottom: 0.75rem;"><strong>Date:</strong> ${formattedDate}</p>
                <p style="margin-bottom: 0.75rem;"><strong>Time:</strong> ${formattedTime}</p>
                <p style="margin-bottom: 0.75rem;"><strong>Patient:</strong> ${data.username}</p>
                ${medicalInfo}
                <hr style="margin: 1rem 0; border-color: ${cssVar('--border-color')}">
                <p style="color: ${cssVar('--text-secondary')}; font-size: 0.9rem; margin: 0;">We'll send you a confirmation email shortly at <strong>${data.email}</strong></p>
              </div>
            `,
            confirmButtonText: 'Got it!',
            confirmButtonColor: cssVar('--success-start') || '#059669'
          }, swalThemeOptions()));
          
          // close modal after confirmation
          Swal.getPopup && Swal.getPopup(); // ensure swal is mounted
          closeModal();

        } else {
          Swal.fire(Object.assign({
            icon: 'error',
            title: 'Booking Failed',
            text: text || 'There was an error processing your booking. Please try again.',
            confirmButtonColor: '#dc2626'
          }, swalThemeOptions()));
        }
      } catch (error) {
        Swal.fire(Object.assign({
          icon: 'error',
          title: 'Connection Error',
          text: 'Unable to submit booking. Please check your internet connection and try again.',
          footer: `<small style="color: ${cssVar('--text-secondary')}">Error: ${error.message}</small>`,
          confirmButtonColor: '#dc2626'
        }, swalThemeOptions()));
      }
    });
  });
</script>
</body>
</html>
