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
      </div>

      <div class="form-group">
        <label class="form-label">
          <i class="fas fa-clock"></i>Appointment Time
        </label>
        <input type="time" id="time" name="time" class="form-control" required />
      </div>

      <button type="submit" class="btn-submit">
        <i class="fas fa-check-circle"></i>
        Confirm Booking
      </button>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const userFullName = <?php echo json_encode($_SESSION['username'] ?? $user['fullname'] ?? ''); ?>;
  const userEmail = <?php echo json_encode($_SESSION['email'] ?? $user['email'] ?? ''); ?>;
  const userContact = <?php echo json_encode($userContact); ?>;

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

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);

    // Pre-fill user data
    fullnameInput.value = userFullName;
    emailInput.value = userEmail;
    contactInput.value = userContact;

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
    };

    // Close button click
    closeBtn.addEventListener('click', closeModal);

    // Click outside modal
    modalBackdrop.addEventListener('click', closeModal);

    // Form submission
    bookingForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(bookingForm);
      const data = Object.fromEntries(formData.entries());

      try {
        const response = await fetch('submit-booking.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data),
        });

        const text = await response.text();

        if (response.ok) {
          alert(`✓ Booking Confirmed!\n\nService: ${data.serviceName}\nDate: ${data.date}\nTime: ${data.time}\n\nThank you, ${data.username}!\n\nWe'll send you a confirmation email shortly.`);
          closeModal();
        } else {
          alert('❌ Booking failed: ' + text);
        }
      } catch (error) {
        alert('❌ Error submitting booking: ' + error.message);
      }
    });
  });
</script>
</body>
</html>