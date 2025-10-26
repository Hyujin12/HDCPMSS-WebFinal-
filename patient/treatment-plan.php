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

$user = $usersCollection->findOne([
    'fullname' => new MongoDB\BSON\Regex('^' . preg_quote($userFullName) . '$', 'i')
]);

// Fallback for email if not set in session
$userEmail = $_SESSION['email'] ?? ($user['email'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <title>Dental Services - Halili Dental</title>
  <style>
    body { 
      margin: 0; 
      padding: 0; 
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: #f3f4f6;
    }

    .main-content {
      padding: 1.5rem;
      min-height: 100vh;
      margin-top: 70px;
    }

    @media (min-width: 640px) {
      .main-content {
        padding: 2rem;
      }
    }

    .page-header {
      background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
      color: white;
      padding: 2rem;
      border-radius: 1rem;
      margin-bottom: 2rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }

    .page-header h1 {
      margin: 0;
      font-size: 2rem;
      font-weight: 700;
    }

    .page-header p {
      margin: 0.5rem 0 0 0;
      opacity: 0.9;
      font-size: 1rem;
    }

    @media (max-width: 575px) {
      .page-header h1 {
        font-size: 1.5rem;
      }
      .page-header p {
        font-size: 0.9rem;
      }
    }

    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
      gap: 1.5rem;
      max-width: 1400px;
      margin: 0 auto;
    }

    @media (max-width: 640px) {
      .services-grid {
        grid-template-columns: 1fr;
      }
    }

    .service-card {
      background: white;
      border-radius: 1rem;
      overflow: hidden;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      transition: all 0.3s ease;
      height: 100%;
    }

    .service-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 16px rgba(0,0,0,0.15);
    }

    .service-card-image {
      width: 100%;
      height: 200px;
      object-fit: cover;
      background: linear-gradient(135deg, #e0f2fe 0%, #bae6fd 100%);
    }

    .service-card-content {
      padding: 1.5rem;
      flex-grow: 1;
      display: flex;
      flex-direction: column;
    }

    .service-card h2 {
      color: #1e40af;
      margin: 0 0 0.75rem 0;
      font-size: 1.375rem;
      font-weight: 700;
    }

    .service-card p {
      color: #4b5563;
      margin-bottom: 1.25rem;
      line-height: 1.6;
      flex-grow: 1;
    }

    .service-card button {
      background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
      border: none;
      color: white;
      padding: 0.75rem 1.75rem;
      border-radius: 0.5rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      font-size: 1rem;
    }

    .service-card button:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 8px rgba(30, 64, 175, 0.3);
    }

    .service-card button:active {
      transform: translateY(0);
    }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1100;
      left: 0; 
      top: 0;
      width: 100vw; 
      height: 100vh;
      background-color: rgba(0,0,0,0.6);
      justify-content: center;
      align-items: center;
      padding: 1rem;
      overflow-y: auto;
    }

    .modal.active {
      display: flex;
    }

    .modal-content {
      background: white;
      padding: 2rem;
      border-radius: 1rem;
      max-width: 500px;
      width: 100%;
      box-shadow: 0 20px 25px -5px rgba(0,0,0,0.2);
      position: relative;
      margin: auto;
      max-height: 90vh;
      overflow-y: auto;
    }

    @media (max-width: 575px) {
      .modal-content {
        padding: 1.5rem;
        max-width: 95%;
      }
    }

    .modal-content h2 {
      margin-top: 0;
      color: #1e40af;
      margin-bottom: 1.5rem;
      font-size: 1.75rem;
      font-weight: 700;
      padding-right: 2rem;
    }

    .modal-content label {
      display: block;
      margin-top: 1rem;
      font-weight: 600;
      color: #374151;
      margin-bottom: 0.5rem;
      font-size: 0.95rem;
    }

    .modal-content input,
    .modal-content select,
    .modal-content textarea {
      width: 100%;
      padding: 0.75rem;
      margin-top: 0.25rem;
      border-radius: 0.5rem;
      border: 1.5px solid #d1d5db;
      box-sizing: border-box;
      font-size: 1rem;
      transition: all 0.2s ease;
      font-family: inherit;
    }

    .modal-content input:focus,
    .modal-content select:focus,
    .modal-content textarea:focus {
      outline: none;
      border-color: #1e40af;
      box-shadow: 0 0 0 3px rgba(30, 64, 175, 0.1);
    }

    .modal-content input[readonly] {
      background-color: #f3f4f6;
      color: #6b7280;
    }

    .modal-content textarea {
      resize: vertical;
      min-height: 100px;
    }

    .modal-content button.submit-btn {
      margin-top: 1.5rem;
      background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 100%);
      color: white;
      border: none;
      padding: 0.875rem 2rem;
      border-radius: 0.5rem;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      width: 100%;
      font-size: 1.05rem;
    }

    .modal-content button.submit-btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 6px 12px rgba(30, 64, 175, 0.3);
    }

    .modal-content button.submit-btn:active {
      transform: translateY(0);
    }

    .close-btn {
      position: absolute;
      top: 1rem;
      right: 1rem;
      background: #f3f4f6;
      border: none;
      width: 2.5rem;
      height: 2.5rem;
      border-radius: 0.5rem;
      font-size: 1.5rem;
      cursor: pointer;
      color: #6b7280;
      transition: all 0.2s ease;
      display: flex;
      align-items: center;
      justify-content: center;
      line-height: 1;
    }

    .close-btn:hover {
      background: #e5e7eb;
      color: #1f2937;
    }

    .form-group {
      margin-bottom: 0.25rem;
    }

    .required-note {
      font-size: 0.875rem;
      color: #6b7280;
      margin-top: 1rem;
      font-style: italic;
    }

    /* Loading state */
    .loading {
      opacity: 0.6;
      pointer-events: none;
    }

    .spinner {
      display: inline-block;
      width: 1rem;
      height: 1rem;
      border: 2px solid #ffffff;
      border-radius: 50%;
      border-top-color: transparent;
      animation: spin 0.6s linear infinite;
      margin-left: 0.5rem;
    }

    @keyframes spin {
      to { transform: rotate(360deg); }
    }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<main class="main-content">
  <div class="page-header">
    <h1>🦷 Our Dental Services</h1>
    <p>Choose from our comprehensive range of dental care services</p>
  </div>
  
  <div id="servicesContainer" class="services-grid">
    <?php 
      include 'service-grid.php'; 
      foreach ($services as $service): ?>
        <div class="service-card">
          <img 
            src="<?= htmlspecialchars($service['image']) ?>" 
            alt="<?= htmlspecialchars($service['title']) ?>"
            class="service-card-image"
          >
          <div class="service-card-content">
            <h2><?= htmlspecialchars($service['title']) ?></h2>
            <p><?= htmlspecialchars($service['description']) ?></p>
            <button data-service="<?= htmlspecialchars($service['title']) ?>">
              <?= htmlspecialchars($service['btn']) ?>
            </button>
          </div>
        </div>
    <?php endforeach; ?>
  </div>

  <!-- Booking Modal -->
  <div id="bookingModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
      <button class="close-btn" aria-label="Close modal">&times;</button>
      <h2 id="modalTitle">📅 Book an Appointment</h2>
      <form id="bookingForm">
        <div class="form-group">
          <label for="serviceName">Service *</label>
          <input type="text" id="serviceName" name="serviceName" readonly />
        </div>

        <div class="form-group">
          <label for="fullname">Full Name *</label>
          <input type="text" id="fullname" name="fullname" readonly />
        </div>

        <div class="form-group">
          <label for="email">Email Address *</label>
          <input type="email" id="email" name="email" readonly />
        </div>

        <div class="form-group">
          <label for="phone">Phone Number *</label>
          <input 
            type="tel" 
            id="phone" 
            name="phone" 
            placeholder="09XX XXX XXXX" 
            required 
            pattern="[0-9]{10,11}"
          />
        </div>

        <div class="form-group">
          <label for="description">Description / Concerns *</label>
          <textarea 
            id="description" 
            name="description" 
            placeholder="Please describe your dental concern or request..."
            required
          ></textarea>
        </div>

        <div class="form-group">
          <label for="date">Appointment Date *</label>
          <input type="date" id="date" name="date" required />
        </div>

        <div class="form-group">
          <label for="time">Appointment Time *</label>
          <input type="time" id="time" name="time" required />
        </div>

        <p class="required-note">* Required fields</p>

        <button type="submit" class="submit-btn" id="submitBtn">
          Confirm Booking
        </button>
      </form>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const userFullName = <?php echo json_encode($_SESSION['username'] ?? $user['fullname'] ?? ''); ?>;
  const userEmail = <?php echo json_encode($_SESSION['email'] ?? $user['email'] ?? ''); ?>;

  document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('bookingModal');
    const serviceNameInput = document.getElementById('serviceName');
    const fullnameInput = document.getElementById('fullname');
    const emailInput = document.getElementById('email');
    const dateInput = document.getElementById('date');
    const closeBtn = modal.querySelector('.close-btn');
    const bookingForm = document.getElementById('bookingForm');
    const submitBtn = document.getElementById('submitBtn');

    // Pre-fill user data
    fullnameInput.value = userFullName;
    emailInput.value = userEmail;

    // Set minimum date to today
    const today = new Date().toISOString().split('T')[0];
    dateInput.setAttribute('min', today);

    // Open modal when clicking book button
    document.getElementById('servicesContainer').addEventListener('click', (e) => {
      if (e.target.tagName === 'BUTTON') {
        const serviceTitle = e.target.getAttribute('data-service') || 
                           e.target.closest('.service-card').querySelector('h2').textContent;
        serviceNameInput.value = serviceTitle;
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
      }
    });

    // Close modal function
    function closeModal() {
      modal.classList.remove('active');
      document.body.style.overflow = '';
      bookingForm.reset();
      fullnameInput.value = userFullName;
      emailInput.value = userEmail;
      dateInput.setAttribute('min', today);
      submitBtn.disabled = false;
      submitBtn.innerHTML = 'Confirm Booking';
    }

    // Close button click
    closeBtn.addEventListener('click', closeModal);

    // Click outside modal
    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        closeModal();
      }
    });

    // ESC key to close
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        closeModal();
      }
    });

    // Form submission
    bookingForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      
      // Disable button and show loading
      submitBtn.disabled = true;
      submitBtn.innerHTML = 'Booking... <span class="spinner"></span>';
      
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
          // Success message
          alert(`✅ Booking Confirmed!\n\nService: ${data.serviceName}\nDate: ${data.date}\nTime: ${data.time}\n\nThank you, ${data.fullname}! We'll contact you soon.`);
          closeModal();
        } else {
          alert('❌ Booking failed: ' + text);
          submitBtn.disabled = false;
          submitBtn.innerHTML = 'Confirm Booking';
        }
      } catch (error) {
        alert('❌ Error submitting booking: ' + error.message);
        submitBtn.disabled = false;
        submitBtn.innerHTML = 'Confirm Booking';
      }
    });

    // Phone number validation
    const phoneInput = document.getElementById('phone');
    phoneInput.addEventListener('input', (e) => {
      e.target.value = e.target.value.replace(/[^0-9]/g, '');
    });
  });
</script>
</body>
</html>