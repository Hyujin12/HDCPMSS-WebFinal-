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

    main {
      margin-left: 0;
      padding: 1rem;
      min-height: 100vh;
      transition: margin-left 0.3s ease;
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
      background: white;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: 0 2px 8px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      display: flex;
      flex-direction: column;
      border: 1px solid #e5e7eb;
    }

    .service-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .service-card-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      background: #f3f4f6;
    }

    .service-card-content {
      padding: 1.5rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .service-card-title {
      color: #1d4ed8;
      font-size: 1.4rem;
      font-weight: 700;
      margin-bottom: 0.75rem;
    }

    .service-card-description {
      color: #6b7280;
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
      border-top: 1px solid #f3f4f6;
    }

    .service-price {
      font-size: 0.85rem;
      color: #6b7280;
      font-weight: 500;
    }

    .btn-book {
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
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
      box-shadow: 0 4px 12px rgba(29, 78, 216, 0.4);
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
      background: white;
      border-radius: 16px;
      box-shadow: 0 20px 60px rgba(0,0,0,0.3);
      z-index: 1050;
      max-width: 500px;
      width: 90%;
      max-height: 90vh;
      overflow-y: auto;
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
      background: linear-gradient(135deg, #1d4ed8 0%, #1e40af 100%);
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
    }

    .form-group {
      margin-bottom: 1.25rem;
    }

    .form-label {
      display: block;
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.5rem;
      font-size: 0.9rem;
    }

    .form-label i {
      color: #1d4ed8;
      margin-right: 0.5rem;
      width: 20px;
    }

    .form-control {
      width: 100%;
      padding: 0.75rem 1rem;
      border: 2px solid #e5e7eb;
      border-radius: 10px;
      font-size: 0.95rem;
      transition: all 0.3s ease;
      background: #f9fafb;
    }

    .form-control:focus {
      outline: none;
      border-color: #1d4ed8;
      background: white;
      box-shadow: 0 0 0 3px rgba(29, 78, 216, 0.1);
    }

    .form-control:read-only {
      background: #f3f4f6;
      color: #6b7280;
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

    .btn-submit {
      width: 100%;
      background: linear-gradient(135deg, #059669 0%, #047857 100%);
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
      box-shadow: 0 6px 16px rgba(5, 150, 105, 0.4);
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
      background: #dbeafe;
      color: #1e40af;
      padding: 0.5rem 1rem;
      border-radius: 20px;
      font-size: 0.85rem;
      font-weight: 600;
      margin-bottom: 1rem;
    }

    /* Info Banner */
    .info-banner {
      background: #dbeafe;
      border-left: 4px solid #1d4ed8;
      padding: 1rem 1.25rem;
      border-radius: 8px;
      margin-bottom: 2rem;
      display: flex;
      align-items: center;
      gap: 1rem;
    }

    .info-banner i {
      color: #1d4ed8;
      font-size: 1.5rem;
    }

    .info-banner-content p {
      margin: 0;
      color: #1e40af;
      font-weight: 500;
    }

    /* Scrollbar */
    .booking-modal::-webkit-scrollbar {
      width: 8px;
    }

    .booking-modal::-webkit-scrollbar-track {
      background: #f1f1f1;
    }

    .booking-modal::-webkit-scrollbar-thumb {
      background: #cbd5e1;
      border-radius: 10px;
    }

    .booking-modal::-webkit-scrollbar-thumb:hover {
      background: #94a3b8;
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
      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-tooth"></i>Service
        </label>
        <input type="text" id="serviceName" name="serviceName" class="form-control" readonly />
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

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-comment-medical"></i>Description / Reason for Visit
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
    
    // Check if clinic is closed on this day
    if (hours.closed) {
      return false;
    }

    // Check regular hours
    if (time >= hours.open && time <= hours.close) {
      return true;
    }

    // Check evening hours if available
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
        
        // Show operating hours
        Swal.fire({
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
          `,
          confirmButtonColor: '#1d4ed8',
          background: '#f9fafb',
          customClass: {
            popup: 'rounded-xl shadow-lg',
            confirmButton: 'px-4 py-2 font-semibold'
          }
        });
      } else {
        dateInput.classList.remove('error');
        dateError.classList.remove('show');
        validateDateTime();
      }
    });

    // Validate time
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
      
      // Check if selected date and time is in the past
      if (selectedDateTime < now) {
        timeInput.classList.add('error');
        timeError.classList.add('show');
        timeError.textContent = 'Please select a future time';
        submitBtn.disabled = true;
        return;
      }

      // Check if time is within operating hours
      if (!isWithinOperatingHours(selectedDay, selectedTime)) {
        timeInput.classList.add('error');
        timeError.classList.add('show');
        timeError.textContent = `Outside operating hours. ${getOperatingHoursText(selectedDay)}`;
        submitBtn.disabled = true;
        
        Swal.fire({
          icon: 'warning',
          title: 'Outside Operating Hours',
          html: `
            <p>The selected time is outside clinic operating hours.</p>
            <hr style="margin: 1rem 0;">
            <div style="text-align: left; font-size: 0.9rem;">
              <strong>${getOperatingHoursText(selectedDay)}</strong>
            </div>
          `,
          confirmButtonColor: '#1d4ed8',
          background: '#f9fafb',
          customClass: {
            popup: 'rounded-xl shadow-lg',
            confirmButton: 'px-4 py-2 font-semibold'
          }
        });
      } else {
        timeInput.classList.remove('error');
        timeError.classList.remove('show');
        submitBtn.disabled = false;
      }
    }

    // Open modal when clicking book button
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

    // Close modal function
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

    // Close button click
    closeBtn.addEventListener('click', closeModal);

    // Click outside modal
    modalBackdrop.addEventListener('click', closeModal);

    // Form submission
    bookingForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      // Final validation before submission
      const selectedDate = dateInput.value;
      const selectedTime = timeInput.value;
      const selectedDateTime = new Date(`${selectedDate}T${selectedTime}`);
      const selectedDay = new Date(selectedDate + 'T00:00:00').getDay();
      const now = new Date();
      
      // Check if date is in the past
      if (selectedDate < todayString) {
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Date',
          text: 'Cannot book appointment for a past date. Please select today or a future date.',
          confirmButtonColor: '#1d4ed8',
          background: '#f9fafb',
          customClass: {
            popup: 'rounded-xl shadow-lg',
            confirmButton: 'px-4 py-2 font-semibold'
          }
        });
        return;
      }

      // Check if clinic is closed on selected day
      if (clinicHours[selectedDay].closed) {
        Swal.fire({
          icon: 'warning',
          title: 'Clinic Closed',
          text: 'The clinic is closed on the selected day. Please choose another date.',
          confirmButtonColor: '#1d4ed8',
          background: '#f9fafb',
          customClass: {
            popup: 'rounded-xl shadow-lg',
            confirmButton: 'px-4 py-2 font-semibold'
          }
        });
        return;
      }

      // Check if datetime is in the past
      if (selectedDateTime < now) {
        Swal.fire({
          icon: 'warning',
          title: 'Invalid Time',
          text: 'Cannot book appointment in the past. Please select a future date and time.',
          confirmButtonColor: '#1d4ed8',
          background: '#f9fafb',
          customClass: {
            popup: 'rounded-xl shadow-lg',
            confirmButton: 'px-4 py-2 font-semibold'
          }
        });
        return;
      }

      // Check if time is within operating hours
      if (!isWithinOperatingHours(selectedDay, selectedTime)) {
        Swal.fire({
          icon: 'warning',
          title: 'Outside Operating Hours',
          html: `
            <p>The selected time is outside clinic operating hours.</p>
            <hr style="margin: 1rem 0;">
            <div style="text-align: left; font-size: 0.9rem;">
              <strong>${getOperatingHoursText(selectedDay)}</strong>
            </div>
          `,
          confirmButtonColor: '#1d4ed8',
          background: '#f9fafb',
          customClass: {
            popup: 'rounded-xl shadow-lg',
            confirmButton: 'px-4 py-2 font-semibold'
          }
        });
        return;
      }

      const formData = new FormData(bookingForm);
      const data = Object.fromEntries(formData.entries());

      // Show loading state
      Swal.fire({
        title: 'Processing...',
        text: 'Please wait while we confirm your booking.',
        icon: 'info',
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });

      try {
        const response = await fetch('submit-booking.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data),
        });

        const text = await response.text();

        if (response.ok) {
          // Format date and time for display
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

          Swal.fire({
            icon: 'success',
            title: 'Booking Confirmed!',
            html: `
              <div style="text-align: left; padding: 1rem;">
                <p style="margin-bottom: 0.75rem;"><strong>Service:</strong> ${data.serviceName}</p>
                <p style="margin-bottom: 0.75rem;"><strong>Date:</strong> ${formattedDate}</p>
                <p style="margin-bottom: 0.75rem;"><strong>Time:</strong> ${formattedTime}</p>
                <p style="margin-bottom: 0.75rem;"><strong>Patient:</strong> ${data.username}</p>
                <hr style="margin: 1rem 0; border-color: #e5e7eb;">
                <p style="color: #6b7280; font-size: 0.9rem; margin: 0;">We'll send you a confirmation email shortly at <strong>${data.email}</strong></p>
              </div>
            `,
            confirmButtonText: 'Got it!',
            confirmButtonColor: '#059669',
            background: '#f9fafb',
            customClass: {
              popup: 'rounded-xl shadow-lg',
              confirmButton: 'px-6 py-2 font-semibold'
            }
          }).then(() => {
            closeModal();
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Booking Failed',
            text: text || 'There was an error processing your booking. Please try again.',
            confirmButtonColor: '#dc2626',
            background: '#f9fafb',
            customClass: {
              popup: 'rounded-xl shadow-lg',
              confirmButton: 'px-4 py-2 font-semibold'
            }
          });
        }
      } catch (error) {
        Swal.fire({
          icon: 'error',
          title: 'Connection Error',
          text: 'Unable to submit booking. Please check your internet connection and try again.',
          footer: `<small style="color: #6b7280;">Error: ${error.message}</small>`,
          confirmButtonColor: '#dc2626',
          background: '#f9fafb',
          customClass: {
            popup: 'rounded-xl shadow-lg',
            confirmButton: 'px-4 py-2 font-semibold'
          }
        });
      }
    });
  });
</script>
</body>
</html>