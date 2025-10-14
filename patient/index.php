<?php
include 'service-grid.php'; // Make sure this is the PHP file with $services and renderServices()
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Halili's Dental Clinic</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <style>
    .service-card {
      border: 1px solid #ddd;
      padding: 16px;
      border-radius: 8px;
      margin: 12px;
      min-width: 250px;
      flex-shrink: 0;
      background-color: #f9f9f9;
    }
    .service-card img {
      width: 100%;
      height: auto;
      border-radius: 4px;
    }
  </style>
</head>
<body class="bg-white text-gray-900">

  <!-- Responsive Header -->
  <header class="px-12 py-2 mt-4 bg-blue-100 mx-16 rounded-3xl">
    <div class="flex justify-between items-center mx-4 md:-mx-4">
      <div class="flex items-start">
        <img src="/images/logodental.png" alt="Halili's Dental Clinic Logo" class="w-16 h-12 mr-2">
        <h2 class="text-xl font-semibold bg-gradient-to-r from-blue-500 via-purple-500 to-pink-500 text-transparent bg-clip-text">Halili's Dental Clinic</h2>
      </div>
      <button id="navToggle" class="md:hidden text-gray-700 focus:outline-none z-50">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
      </button>
      <nav class="hidden md:flex space-x-16">
        <a href="#" class="text-black font-bold hover:text-blue-700 transition underline">Home</a>
        <a href="#" class="text-black font-bold hover:text-blue-700 transition underline">About</a>
        <a href="#" class="text-black font-bold hover:text-blue-700 transition underline">Services</a>
        <a href="#" class="text-black font-bold hover:text-blue-700 transition underline">Contact</a>
      </nav>
    </div>
  </header>

  <!-- Mobile Sidebar Nav -->
  <div id="mobileMenu" class="fixed top-0 right-0 h-full w-64 bg-white shadow-lg transform translate-x-full transition-transform duration-300 ease-in-out md:hidden z-40">
    <div class="p-6 space-y-4">
      <button id="closeMenu" class="text-gray-600 float-right">✕</button>
      <nav class="flex flex-col mt-8 space-y-4">
        <a href="#" class="text-blue-500 hover:text-blue-700 transition">Home</a>
        <a href="#" class="text-blue-500 hover:text-blue-700 transition">About</a>
        <a href="#" class="text-blue-500 hover:text-blue-700 transition">Services</a>
        <a href="#" class="text-blue-500 hover:text-blue-700 transition">Contact</a>
      </nav>
    </div>
  </div>
  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-30 md:hidden"></div>

  <!-- Hero Section -->
  <div class="flex flex-col-reverse md:flex-row items-start justify-between px-4 md:px-12 gap-8 mt-8">
    <div class="w-full md:w-1/2 text-center md:text-left mt-24">
      <h1 class="text-3xl md:text-5xl font-bold mb-4">We Are Ready To Help & Take Care of Your Dental Health</h1>
      <p class="text-base md:text-lg mb-6">"Creating Miles of Smiles with Gentle Care, Expert Precision, and a Commitment to Your Perfect Dental Health."</p>
      <a href="log-in.php" class="inline-block">
        <button class="bg-gradient-to-r from-yellow-400 via-purple-400 to-pink-500 text-cyan-50 rounded-2xl font-bold py-3 px-6 hover:bg-blue-600 transition duration-300">Make an Appointment</button>
      </a>
    </div>
    <div class="w-full md:w-1/2 flex justify-center relative">
      <div class="absolute w-64 h-64 bg-blue-100 rounded-full z-0 top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 hidden md:block"></div>
      <img src="/images/Doctor-image.png" alt="Doctor" class="w-2/3 max-w-xs md:max-w-md lg:max-w-lg h-auto object-contain z-10 relative" />
    </div>
  </div>

  <!-- Dental Services -->
<div class="px-4 md:px-12 mt-16">
  <div class="text-center md:text-left mb-8">
    <h2 class="text-2xl md:text-4xl font-bold mb-4">Our Dental Services</h2>
    <p class="text-base md:text-lg mb-6">
      "At Halili's Dental Clinic, we offer a comprehensive range of dental services to meet all your oral health needs. From routine check-ups to advanced cosmetic procedures, our team is dedicated to providing exceptional care tailored to you."
    </p>
  </div>

  <div class="relative max-w-6xl mx-auto">
    

  <div class="relative max-w-md mx-auto"> <!-- Container width for 1 card -->
    <!-- Slider -->
    <div id="serviceSlides" class="flex transition-transform duration-500">
      <?php
        foreach ($services as $service) {
            echo '<div class="flex-shrink-0 w-full p-6 bg-gray-100 rounded-lg shadow hover:scale-105 transition-transform duration-300">';
            echo '<img src="' . htmlspecialchars($service['image']) . '" alt="' . htmlspecialchars($service['title']) . '" class="w-full h-64 object-cover rounded mb-4">';
            echo '<h2 class="text-2xl font-semibold mb-2">' . htmlspecialchars($service['title']) . '</h2>';
            echo '<p class="text-base mb-4">' . htmlspecialchars($service['description']) . '</p>';
            echo '<button class="bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700 transition">' . htmlspecialchars($service['btn']) . '</button>';
            echo '</div>';
        }
      ?>
    </div>

    <!-- Slider Buttons -->
    <button id="prevService" class="absolute -left-6 top-1/2 -translate-y-1/2 bg-white p-2 rounded-full shadow hover:bg-gray-200">&#10094;</button>
    <button id="nextService" class="absolute -right-6 top-1/2 -translate-y-1/2 bg-white p-2 rounded-full shadow hover:bg-gray-200">&#10095;</button>
  </div>
</div>
  <!-- Halili's Info -->
  <div class="text-center mt-8">
    <img src="/images/halili-logo.png" alt="" class="w-1/5 h-2/6 mx-auto mt-8 mb-4">
    <h1 class="text-1xl font-bold text-charcoal-500">Halili Dental Clinic by Dra Emily E Halili, Rodriguez, Philippines</h1>
  </div>

  <!-- Contact Section -->
  <div class="mb-20">
    <div class="flex flex-row items-center justify-around relative">
      <div class="text-center mt-8 flex flex-row items-center justify-center relative gap-4 w-34">
        <img src="/images/telephone.png" alt="" class="w-8 mx-auto">
        <h1>Call Us: 0922 223 3688</h1>
      </div>
      <div class="text-center mt-8 flex flex-row items-center justify-center relative gap-4 w-34">
        <img src="/images/viber.png" alt="" class="w-8 mx-auto">
        <h1>Viber: +63 922 223 3688</h1>
      </div>
    </div>
    <div class="flex items-center justify-around relative">
      <div class="text-center mt-8 flex flex-row items-center justify-center relative gap-4 w-34">
        <img src="../images/email.png" alt="" class="w-8 mx-auto">
        <h1>Email: halilidentalcare@gmail.com</h1>
      </div>
    </div>
  </div>

  <!-- Scripts -->
  <script>
    const navToggle = document.getElementById("navToggle");
    const mobileMenu = document.getElementById("mobileMenu");
    const closeMenu = document.getElementById("closeMenu");
    const overlay = document.getElementById("overlay");

    navToggle.addEventListener("click", () => {
      mobileMenu.classList.remove("translate-x-full");
      overlay.classList.remove("hidden");
      navToggle.classList.add("hidden");
    });

    function closeSidebar() {
      mobileMenu.classList.add("translate-x-full");
      overlay.classList.add("hidden");
      navToggle.classList.remove("hidden");
    }

    closeMenu.addEventListener("click", closeSidebar);
    overlay.addEventListener("click", closeSidebar);

const slider = document.getElementById('serviceSlides');
const prevBtn = document.getElementById('prevService');
const nextBtn = document.getElementById('nextService');
const slides = slider.children;
let currentIndex = 0; // start from the first slide
const totalSlides = slides.length;

function updateSlider() {
    const slideWidth = slides[0].offsetWidth;
    slider.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
}

// Next / Previous buttons
nextBtn.addEventListener('click', () => {
    currentIndex = (currentIndex + 1) % totalSlides;
    updateSlider();
});

prevBtn.addEventListener('click', () => {
    currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
    updateSlider();
});

// Auto-slide every 5 seconds
setInterval(() => {
    currentIndex = (currentIndex + 1) % totalSlides;
    updateSlider();
}, 2000);

// Adjust on window resize
window.addEventListener('resize', updateSlider);
  </script>
</body>
</html>
