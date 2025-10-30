<?php
// Fetch services from database
require __DIR__ . '/../vendor/autoload.php';
use MongoDB\Client;
use Dotenv\Dotenv;

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

$mongoClient = new Client($_ENV['MONGO_URI']);
$db = $mongoClient->HaliliDentalClinic;
$servicesCollection = $db->services;

// Fetch all services
$servicesCursor = $servicesCollection->find();
$services = iterator_to_array($servicesCursor);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Halili's Dental Clinic - Your Trusted Dental Care Partner</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    body {
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    }

    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .hero-gradient {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .card-hover {
      transition: all 0.3s ease;
    }

    .card-hover:hover {
      transform: translateY(-8px);
      box-shadow: 0 20px 40px rgba(0,0,0,0.15);
    }

    /* Service Slider Styles */
    .slider-container {
      overflow: hidden;
      position: relative;
    }

    #serviceSlides {
      display: flex;
      transition: transform 0.5s ease-in-out;
    }

    .service-slide {
      min-width: 100%;
      flex-shrink: 0;
      padding: 0 0.5rem;
    }

    .service-card {
      background: white;
      border-radius: 1.5rem;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      overflow: hidden;
      border: 1px solid #e5e7eb;
      display: flex;
      flex-direction: column;
      max-width: 600px;
      margin: 0 auto;
      height: 100%;
    }

    .service-image-container {
      width: 100%;
      height: 280px;
      overflow: hidden;
      background: #f3f4f6;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .service-image-container img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.5s ease;
    }

    .service-card:hover .service-image-container img {
      transform: scale(1.05);
    }

    .service-content {
      padding: 2rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .service-title {
      font-size: 1.75rem;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 1rem;
    }

    .service-description {
      color: #6b7280;
      line-height: 1.7;
      margin-bottom: 1.5rem;
      flex: 1;
    }

    .service-button {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      padding: 0.875rem 2rem;
      border-radius: 9999px;
      font-weight: 600;
      text-align: center;
      transition: all 0.3s ease;
      border: none;
      cursor: pointer;
      display: inline-block;
    }

    .service-button:hover {
      box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
      transform: translateY(-2px);
    }

    /* Navigation Buttons */
    .slider-nav-btn {
      position: absolute;
      top: 50%;
      transform: translateY(-50%);
      background: white;
      width: 3rem;
      height: 3rem;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      box-shadow: 0 4px 6px rgba(0,0,0,0.1);
      cursor: pointer;
      transition: all 0.3s ease;
      z-index: 10;
      border: 1px solid #e5e7eb;
    }

    .slider-nav-btn:hover {
      background: #f9fafb;
      box-shadow: 0 6px 12px rgba(0,0,0,0.15);
    }

    .slider-nav-btn.prev {
      left: -1rem;
    }

    .slider-nav-btn.next {
      right: -1rem;
    }

    @media (min-width: 640px) {
      .slider-nav-btn.prev {
        left: -1.5rem;
      }
      .slider-nav-btn.next {
        right: -1.5rem;
      }
    }

    /* Slide Indicators */
    .slide-indicators {
      display: flex;
      justify-content: center;
      gap: 0.5rem;
      margin-top: 2rem;
    }

    .slide-indicator {
      width: 0.5rem;
      height: 0.5rem;
      border-radius: 9999px;
      background: #d1d5db;
      transition: all 0.3s ease;
      cursor: pointer;
    }

    .slide-indicator:hover {
      background: #9ca3af;
    }

    .slide-indicator.active {
      width: 2rem;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }

    .nav-link {
      position: relative;
      padding-bottom: 4px;
    }

    .nav-link::after {
      content: '';
      position: absolute;
      width: 0;
      height: 2px;
      bottom: 0;
      left: 50%;
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      transition: all 0.3s ease;
      transform: translateX(-50%);
    }

    .nav-link:hover::after {
      width: 100%;
    }

    .contact-card {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.9);
      border: 1px solid rgba(102, 126, 234, 0.1);
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">

  <!-- Professional Header -->
  <header class="sticky top-0 z-50 bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <!-- Logo and Branding -->
        <div class="flex items-center space-x-3">
          <img src="/images/logodental.png" alt="Halili's Dental Clinic Logo" class="w-12 h-12 sm:w-14 sm:h-14 object-contain">
          <div>
            <h2 class="text-lg sm:text-xl font-bold gradient-text">Halili's Dental Clinic</h2>
            <p class="text-xs text-gray-500 hidden sm:block">Excellence in Dental Care</p>
          </div>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center space-x-8">
          <a href="#home" class="nav-link text-gray-700 font-medium hover:text-purple-600 transition">Home</a>
          <a href="#about" class="nav-link text-gray-700 font-medium hover:text-purple-600 transition">About</a>
          <a href="#services" class="nav-link text-gray-700 font-medium hover:text-purple-600 transition">Services</a>
          <a href="#contact" class="nav-link text-gray-700 font-medium hover:text-purple-600 transition">Contact</a>
          <a href="log-in.php">
            <button class="hero-gradient text-white px-6 py-2 rounded-full font-medium hover:shadow-lg transition duration-300">Book Now</button>
          </a>
        </nav>

        <!-- Mobile Menu Button -->
        <button id="navToggle" class="md:hidden text-gray-700 focus:outline-none p-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>
    </div>
  </header>

  <!-- Mobile Sidebar -->
  <div id="mobileMenu" class="fixed top-0 right-0 h-full w-72 bg-white shadow-2xl transform translate-x-full transition-transform duration-300 ease-in-out md:hidden z-50">
    <div class="p-6">
      <button id="closeMenu" class="text-gray-600 float-right text-2xl hover:text-purple-600">&times;</button>
      <div class="clear-both mt-8 space-y-6">
        <a href="#home" class="block text-gray-700 text-lg font-medium hover:text-purple-600 transition py-2 border-b border-gray-100">Home</a>
        <a href="#about" class="block text-gray-700 text-lg font-medium hover:text-purple-600 transition py-2 border-b border-gray-100">About</a>
        <a href="#services" class="block text-gray-700 text-lg font-medium hover:text-purple-600 transition py-2 border-b border-gray-100">Services</a>
        <a href="#contact" class="block text-gray-700 text-lg font-medium hover:text-purple-600 transition py-2 border-b border-gray-100">Contact</a>
        <a href="log-in.php">
          <button class="w-full hero-gradient text-white px-6 py-3 rounded-full font-medium hover:shadow-lg transition duration-300 mt-4">Book Appointment</button>
        </a>
      </div>
    </div>
  </div>
  <div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 hidden z-40"></div>

  <!-- Hero Section -->
  <section id="home" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
      <!-- Hero Content -->
      <div class="order-2 lg:order-1 text-center lg:text-left">
        <div class="inline-block px-4 py-2 bg-purple-100 rounded-full mb-6">
          <span class="text-purple-700 font-medium text-sm">🦷 Trusted by 5,000+ Happy Patients</span>
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl xl:text-6xl font-bold mb-6 leading-tight">
          We Are Ready To Help & Take Care of Your 
          <span class="gradient-text">Dental Health</span>
        </h1>
        <p class="text-base sm:text-lg text-gray-600 mb-8 leading-relaxed">
          Creating miles of smiles with gentle care, expert precision, and a commitment to your perfect dental health. Experience comprehensive dental solutions tailored to your unique needs.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start">
          <a href="log-in.php">
            <button class="hero-gradient text-white px-8 py-4 rounded-full font-semibold hover:shadow-xl transition duration-300 transform hover:scale-105">
              Make an Appointment
            </button>
          </a>
          <a href="#services">
            <button class="bg-white border-2 border-purple-600 text-purple-600 px-8 py-4 rounded-full font-semibold hover:bg-purple-50 transition duration-300">
              View Services
            </button>
          </a>
        </div>
        
        <!-- Stats -->
        <div class="grid grid-cols-3 gap-4 mt-12 pt-8 border-t border-gray-200">
          <div class="text-center">
            <h3 class="text-2xl sm:text-3xl font-bold gradient-text">44</h3>
            <p class="text-xs sm:text-sm text-gray-600">Years Experience</p>
          </div>
          <div class="text-center">
            <h3 class="text-2xl sm:text-3xl font-bold gradient-text">500+</h3>
            <p class="text-xs sm:text-sm text-gray-600">Happy Patients</p>
          </div>
          <div class="text-center">
            <h3 class="text-2xl sm:text-3xl font-bold gradient-text">95%</h3>
            <p class="text-xs sm:text-sm text-gray-600">Satisfaction Rate</p>
          </div>
        </div>
      </div>

      <!-- Hero Image -->
      <div class="order-1 lg:order-2 flex justify-center relative">
        <div class="relative w-full max-w-md lg:max-w-lg">
          <div class="absolute inset-0 bg-gradient-to-br from-purple-200 to-blue-200 rounded-full blur-3xl opacity-30"></div>
          <img src="/images/Doctor-image.png" alt="Professional Dentist" class="relative z-10 w-full h-auto object-contain drop-shadow-2xl" />
        </div>
      </div>
    </div>
  </section>

  <!-- About Section -->
  <section id="about" class="bg-white py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">Why Choose <span class="gradient-text">Halili's Dental Clinic?</span></h2>
        <p class="text-gray-600 text-lg">We combine state-of-the-art technology with compassionate care to deliver exceptional dental services.</p>
      </div>
      
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        <div class="card-hover bg-gradient-to-br from-purple-50 to-blue-50 p-6 sm:p-8 rounded-2xl border border-purple-100">
          <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-4">
            <span class="text-2xl">🏥</span>
          </div>
          <h3 class="text-xl font-bold mb-3">Modern Equipment</h3>
          <p class="text-gray-600">Advanced dental technology for accurate diagnosis and comfortable treatment.</p>
        </div>
        
        <div class="card-hover bg-gradient-to-br from-purple-50 to-blue-50 p-6 sm:p-8 rounded-2xl border border-purple-100">
          <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-4">
            <span class="text-2xl">👨‍⚕️</span>
          </div>
          <h3 class="text-xl font-bold mb-3">Expert Team</h3>
          <p class="text-gray-600">Highly qualified dental professionals with years of specialized experience.</p>
        </div>
        
        <div class="card-hover bg-gradient-to-br from-purple-50 to-blue-50 p-6 sm:p-8 rounded-2xl border border-purple-100">
          <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-4">
            <span class="text-2xl">💙</span>
          </div>
          <h3 class="text-xl font-bold mb-3">Patient-Centered Care</h3>
          <p class="text-gray-600">Personalized treatment plans designed around your specific needs and comfort.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Dental Services Section -->
  <section id="services" class="py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">Our <span class="gradient-text">Dental Services</span></h2>
        <p class="text-gray-600 text-lg">
          At Halili's Dental Clinic, we offer a comprehensive range of dental services to meet all your oral health needs. From routine check-ups to advanced cosmetic procedures, our team is dedicated to providing exceptional care tailored to you.
        </p>
      </div>

      <div class="relative max-w-4xl mx-auto">
        <div class="slider-container">
          <div id="serviceSlides">
            <?php if (!empty($services)): ?>
              <?php foreach ($services as $service): ?>
                <div class="service-slide">
                  <div class="service-card">
                    <div class="service-image-container">
                      <img src="<?= htmlspecialchars($service['image'] ?? '/images/default-service.jpg') ?>" 
                           alt="<?= htmlspecialchars($service['title'] ?? 'Dental Service') ?>">
                    </div>
                    <div class="service-content">
                      <h3 class="service-title"><?= htmlspecialchars($service['title'] ?? 'Service') ?></h3>
                      <p class="service-description"><?= htmlspecialchars($service['description'] ?? '') ?></p>
                      <a href="log-in.php">
                        <button class="service-button w-full">
                          <i class="fas fa-calendar-check mr-2"></i>
                          Book Appointment
                        </button>
                      </a>
                    </div>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="service-slide">
                <div class="service-card">
                  <div class="service-content text-center">
                    <p class="text-gray-500">No services available at the moment.</p>
                  </div>
                </div>
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <button id="prevService" class="slider-nav-btn prev">
          <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        <button id="nextService" class="slider-nav-btn next">
          <svg class="w-6 h-6 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>

        <!-- Slide Indicators -->
        <div id="slideIndicators" class="slide-indicators"></div>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="bg-gradient-to-br from-purple-600 to-blue-600 py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <img src="/images/halili-logo.png" alt="Halili's Dental Clinic" class="w-24 sm:w-32 h-auto mx-auto mb-6 bg-white p-4 rounded-2xl shadow-xl">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Get In Touch With Us</h2>
        <p class="text-purple-100 text-lg max-w-2xl mx-auto">Halili Dental Clinic by Doc Kyle Halili DMD, Rodriguez, Philippines</p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <!-- Phone -->
        <div class="contact-card p-6 rounded-2xl text-center">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-phone text-white text-2xl"></i>
          </div>
          <h3 class="font-bold text-gray-900 mb-2">Call Us</h3>
          <p class="text-gray-600">0922 223 3688</p>
        </div>

        <!-- Viber -->
        <div class="contact-card p-6 rounded-2xl text-center">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fab fa-viber text-white text-2xl"></i>
          </div>
          <h3 class="font-bold text-gray-900 mb-2">Viber</h3>
          <p class="text-gray-600">+63 922 223 3688</p>
        </div>

        <!-- Email -->
        <div class="contact-card p-6 rounded-2xl text-center sm:col-span-2 lg:col-span-1">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-envelope text-white text-2xl"></i>
          </div>
          <h3 class="font-bold text-gray-900 mb-2">Email</h3>
          <p class="text-gray-600 break-all">halilidentalcare@gmail.com</p>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer class="bg-gray-900 text-gray-300 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <p>&copy; 2024 Halili's Dental Clinic. All rights reserved.</p>
      <p class="text-sm mt-2">Excellence in Dental Care Since 2009</p>
    </div>
  </footer>

  <!-- Scripts -->
  <script>
    // Mobile Menu Toggle
    const navToggle = document.getElementById("navToggle");
    const mobileMenu = document.getElementById("mobileMenu");
    const closeMenu = document.getElementById("closeMenu");
    const overlay = document.getElementById("overlay");

    function openSidebar() {
      mobileMenu.classList.remove("translate-x-full");
      overlay.classList.remove("hidden");
    }

    function closeSidebar() {
      mobileMenu.classList.add("translate-x-full");
      overlay.classList.add("hidden");
    }

    navToggle.addEventListener("click", openSidebar);
    closeMenu.addEventListener("click", closeSidebar);
    overlay.addEventListener("click", closeSidebar);

    // Close mobile menu when clicking nav links
    document.querySelectorAll('#mobileMenu a').forEach(link => {
      link.addEventListener('click', closeSidebar);
    });

    // Service Slider - ONE SLIDE AT A TIME
    const slider = document.getElementById('serviceSlides');
    const prevBtn = document.getElementById('prevService');
    const nextBtn = document.getElementById('nextService');
    const indicatorsContainer = document.getElementById('slideIndicators');
    const slides = slider.children;
    let currentIndex = 0;
    const totalSlides = slides.length;

    // Create slide indicators
    for (let i = 0; i < totalSlides; i++) {
      const indicator = document.createElement('div');
      indicator.className = 'slide-indicator';
      indicator.onclick = () => goToSlide(i);
      indicatorsContainer.appendChild(indicator);
    }

    function updateIndicators() {
      const indicators = indicatorsContainer.children;
      for (let i = 0; i < indicators.length; i++) {
        if (i === currentIndex) {
          indicators[i].classList.add('active');
        } else {
          indicators[i].classList.remove('active');
        }
      }
    }

    function updateSlider() {
      const slideWidth = slider.offsetWidth;
      slider.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
      updateIndicators();
    }

    function goToSlide(index) {
      currentIndex = index;
      updateSlider();
      resetAutoSlide();
    }

    nextBtn.addEventListener('click', () => {
      currentIndex = (currentIndex + 1) % totalSlides;
      updateSlider();
      resetAutoSlide();
    });

    prevBtn.addEventListener('click', () => {
      currentIndex = (currentIndex - 1 + totalSlides) % totalSlides;
      updateSlider();
      resetAutoSlide();
    });

    // Auto-slide every 4 seconds
    let autoSlideInterval = setInterval(() => {
      currentIndex = (currentIndex + 1) % totalSlides;
      updateSlider();
    }, 4000);

    function resetAutoSlide() {
      clearInterval(autoSlideInterval);
      autoSlideInterval = setInterval(() => {
        currentIndex = (currentIndex + 1) % totalSlides;
        updateSlider();
      }, 4000);
    }

    // Pause auto-slide on hover
    slider.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
    slider.addEventListener('mouseleave', resetAutoSlide);

    // Handle window resize
    window.addEventListener('resize', updateSlider);
    window.addEventListener('load', updateSlider);
    
    // Initialize
    updateSlider();

    // Smooth Scrolling
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
      anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });
  </script>
</body>
</html>