<?php
session_start();
if (!isset($_SESSION['user_email'])) {
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
$userEmail = $_SESSION['user_email'] ?? ($user['email'] ?? '');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">

  <title>Dental Services</title>
  <style>
    body { margin: 0; padding: 0; font-family: Arial, sans-serif; }
    main { margin-left: 16rem; padding: 20px; }


    .services-grid {
      display: grid;
      grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
      gap: 24px;
    }
    .service-card {
      background: #e6f0ff;
      border-radius: 12px;
      padding: 20px;
      box-shadow: 0 3px 6px rgba(0,0,0,0.1);
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      transition: box-shadow 0.3s ease;
    }
    .service-card:hover {
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }
    .service-card img {
      max-width: 100%;
      height: 180px;
      object-fit: contain;
      border-radius: 12px;
      margin-bottom: 15px;
    }
    .service-card h2 {
      color: #005cbf;
      margin: 0 0 12px;
      font-size: 1.5rem;
    }
    .service-card p {
      color: #444;
      margin-bottom: 16px;
      min-height: 72px;
    }
    .service-card button {
      background-color: #005cbf;
      border: none;
      color: white;
      padding: 10px 28px;
      border-radius: 25px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .service-card button:hover {
      background-color: #003f7f;
    }

    /* Modal styles */
    .modal {
      display: none;
      position: fixed;
      z-index: 1000;
      left: 0; top: 0;
      width: 100vw; height: 100vh;
      background-color: rgba(0,0,0,0.5);
      justify-content: center;
      align-items: center;
    }
    .modal.active {
      display: flex;
    }
    .modal-content {
      background: white;
      padding: 24px;
      border-radius: 12px;
      max-width: 400px;
      width: 90%;
      box-shadow: 0 8px 24px rgba(0,0,0,0.2);
      position: relative;
    }
    .modal-content h2 {
      margin-top: 0;
      color: #005cbf;
      margin-bottom: 16px;
      font-size: 1.6rem;
    }
    .modal-content label {
      display: block;
      margin-top: 12px;
      font-weight: bold;
      color: #333;
    }
    .modal-content input,
    .modal-content select {
      width: 100%;
      padding: 8px 10px;
      margin-top: 6px;
      border-radius: 6px;
      border: 1px solid #ccc;
      box-sizing: border-box;
      font-size: 1rem;
    }
    .modal-content button.submit-btn {
      margin-top: 20px;
      background-color: #005cbf;
      color: white;
      border: none;
      padding: 10px 25px;
      border-radius: 25px;
      font-weight: bold;
      cursor: pointer;
      transition: background-color 0.3s ease;
    }
    .modal-content button.submit-btn:hover {
      background-color: #003f7f;
    }
    .close-btn {
      position: absolute;
      top: 12px;
      right: 16px;
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: #666;
    }
    .close-btn:hover {
      color: #000;
    }
  </style>
</head>
<body>
<?php include 'sidebar.php'; ?>

<main>
  <h1 class="text-2xl font-bold mb-4">Our Dental Services</h1>
  <div id="servicesContainer" class="services-grid"></div>

  <!-- Modal -->
  <div id="bookingModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-content">
      <button class="close-btn" aria-label="Close modal">&times;</button>
      <h2 id="modalTitle">Book an Appointment</h2>
      <form id="bookingForm">
        <label for="serviceName">Service</label>
        <input type="text" id="serviceName" name="serviceName" readonly />

        <label for="fullname">Full Name</label>
        <input type="text" id="fullname" name="fullname" readonly />

        <label for="email">Email</label>
        <input type="email" id="email" name="email" readonly />

        <label for="phone">Phone Number</label>
        <input type="tel" id="phone" name="phone" placeholder="0912 345 6789" required />

        <label for="description">Description</label>
        <input type="text" id="description" name="description" placeholder="Describe your problem" required />

        <label for="date">Appointment Date</label>
        <input type="date" id="date" name="date" required />

        <label for="time">Appointment Time</label>
        <input type="time" id="time" name="time" required />

        <button type="submit" class="submit-btn">Confirm Booking</button>
      </form>
    </div>
  </div>
</main>

<script type="module">
  import { renderServices, services } from '/patient/services-grid.js';

  const userFullName = <?php echo json_encode($_SESSION['user_name'] ?? $user['fullname'] ?? ''); ?>;
const userEmail = <?php echo json_encode($_SESSION['user_email'] ?? $user['email'] ?? ''); ?>;

  document.addEventListener('DOMContentLoaded', () => {
    renderServices('servicesContainer');

    const modal = document.getElementById('bookingModal');
    const serviceNameInput = document.getElementById('serviceName');
    const fullnameInput = document.getElementById('fullname');
    const emailInput = document.getElementById('email');
    const closeBtn = modal.querySelector('.close-btn');
    const bookingForm = document.getElementById('bookingForm');

    fullnameInput.value = userFullName;
    emailInput.value = userEmail;

    document.getElementById('servicesContainer').addEventListener('click', (e) => {
      if (e.target.tagName === 'BUTTON') {
        const card = e.target.closest('.service-card');
        const serviceTitle = card.querySelector('h2').textContent;
        serviceNameInput.value = serviceTitle;
        fullnameInput.value = userFullName;
        emailInput.value = userEmail;
        modal.classList.add('active');
      }
    });

    closeBtn.addEventListener('click', () => {
      modal.classList.remove('active');
      bookingForm.reset();
      fullnameInput.value = userFullName;
      emailInput.value = userEmail;
    });

    modal.addEventListener('click', (e) => {
      if (e.target === modal) {
        modal.classList.remove('active');
        bookingForm.reset();
        fullnameInput.value = userFullName;
        emailInput.value = userEmail;
      }
    });

    bookingForm.addEventListener('submit', async (e) => {
      e.preventDefault();
      const formData = new FormData(bookingForm);
      const data = Object.fromEntries(formData.entries());

      try {
        const response = await fetch('/patient/submit-booking.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(data),
        });

        const text = await response.text();

        if (response.ok) {
          alert(`Booking confirmed for ${data.serviceName} on ${data.date} at ${data.time}.\nThank you, ${data.fullname}!`);
          modal.classList.remove('active');
          bookingForm.reset();
          fullnameInput.value = userFullName;
          emailInput.value = userEmail;
        } else {
          alert('Booking failed: ' + text);
        }
      } catch (error) {
        alert('Error submitting booking: ' + error.message);
      }
    });
  });
</script>
</body>
</html>
