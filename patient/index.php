<?php
include 'service-grid.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Halili's Dental Clinic - Your Trusted Dental Care Partner</title>
  <script src="https://cdn.tailwindcss.com"></script>
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

    .service-slide {
      min-width: 100%;
      flex-shrink: 0;
    }

    @media (min-width: 768px) {
      .service-slide {
        min-width: calc(50% - 12px);
      }
    }

    @media (min-width: 1024px) {
      .service-slide {
        min-width: calc(33.333% - 16px);
      }
    }

    .slider-container {
      overflow: hidden;
      padding: 0 2px;
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
            <h3 class="text-2xl sm:text-3xl font-bold gradient-text">15+</h3>
            <p class="text-xs sm:text-sm text-gray-600">Years Experience</p>
          </div>
          <div class="text-center">
            <h3 class="text-2xl sm:text-3xl font-bold gradient-text">5K+</h3>
            <p class="text-xs sm:text-sm text-gray-600">Happy Patients</p>
          </div>
          <div class="text-center">
            <h3 class="text-2xl sm:text-3xl font-bold gradient-text">98%</h3>
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

      <div class="relative">
        <div class="slider-container">
          <div id="serviceSlides" class="flex gap-4 sm:gap-6 transition-transform duration-500 ease-in-out">
            <?php
              foreach ($services as $service) {
                  echo '<div class="service-slide">';
                  echo '<div class="card-hover bg-white rounded-2xl shadow-lg overflow-hidden border border-gray-100 h-full">';
                  echo '<div class="relative h-48 sm:h-56 overflow-hidden">';
                  echo '<img src="' . htmlspecialchars($service['image']) . '" alt="' . htmlspecialchars($service['title']) . '" class="w-full h-full object-cover">';
                  echo '</div>';
                  echo '<div class="p-6">';
                  echo '<h3 class="text-xl sm:text-2xl font-bold mb-3">' . htmlspecialchars($service['title']) . '</h3>';
                  echo '<p class="text-gray-600 mb-6 text-sm sm:text-base">' . htmlspecialchars($service['description']) . '</p>';
                  echo '<button class="hero-gradient text-white py-2 sm:py-3 px-6 rounded-full hover:shadow-lg transition duration-300 w-full sm:w-auto font-medium">' . htmlspecialchars($service['btn']) . '</button>';
                  echo '</div>';
                  echo '</div>';
                  echo '</div>';
              }
            ?>
          </div>
        </div>

        <!-- Navigation Buttons -->
        <button id="prevService" class="absolute -left-4 sm:-left-6 top-1/2 -translate-y-1/2 bg-white p-3 rounded-full shadow-lg hover:bg-gray-100 transition z-10 border border-gray-200">
          <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
          </svg>
        </button>
        <button id="nextService" class="absolute -right-4 sm:-right-6 top-1/2 -translate-y-1/2 bg-white p-3 rounded-full shadow-lg hover:bg-gray-100 transition z-10 border border-gray-200">
          <svg class="w-5 h-5 text-gray-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
          </svg>
        </button>
      </div>
    </div>
  </section>

  <!-- Contact Section -->
  <section id="contact" class="bg-gradient-to-br from-purple-600 to-blue-600 py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center mb-12">
        <img src="/images/halili-logo.png" alt="Halili's Dental Clinic" class="w-24 sm:w-32 h-auto mx-auto mb-6 bg-white p-4 rounded-2xl shadow-xl">
        <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Get In Touch With Us</h2>
        <p class="text-purple-100 text-lg max-w-2xl mx-auto">Halili Dental Clinic by Dra Emily E Halili, Rodriguez, Philippines</p>
      </div>

      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <!-- Phone -->
        <div class="contact-card p-6 rounded-2xl text-center">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <img src="/images/telephone.png" alt="Phone" class="w-8 h-8">
          </div>
          <h3 class="font-bold text-gray-900 mb-2">Call Us</h3>
          <p class="text-gray-600">0922 223 3688</p>
        </div>

        <!-- Viber -->
        <div class="contact-card p-6 rounded-2xl text-center">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <img src="/images/viber.png" alt="Viber" class="w-8 h-8">
          </div>
          <h3 class="font-bold text-gray-900 mb-2">Viber</h3>
          <p class="text-gray-600">+63 922 223 3688</p>
        </div>

        <!-- Email -->
        <div class="contact-card p-6 rounded-2xl text-center sm:col-span-2 lg:col-span-1">
          <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-blue-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <img src="../images/email.png" alt="Email" class="w-8 h-8">
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

    // Service Slider
    const slider = document.getElementById('serviceSlides');
    const prevBtn = document.getElementById('prevService');
    const nextBtn = document.getElementById('nextService');
    const slides = slider.children;
    let currentIndex = 0;
    const totalSlides = slides.length;

    function getVisibleSlides() {
      if (window.innerWidth >= 1024) return 3;
      if (window.innerWidth >= 768) return 2;
      return 1;
    }

    function updateSlider() {
      const visibleSlides = getVisibleSlides();
      const slideWidth = slider.offsetWidth / visibleSlides;
      const maxIndex = Math.max(0, totalSlides - visibleSlides);
      currentIndex = Math.min(currentIndex, maxIndex);
      slider.style.transform = `translateX(-${currentIndex * slideWidth}px)`;
    }

    nextBtn.addEventListener('click', () => {
      const visibleSlides = getVisibleSlides();
      const maxIndex = Math.max(0, totalSlides - visibleSlides);
      currentIndex = Math.min(currentIndex + 1, maxIndex);
      updateSlider();
    });

    prevBtn.addEventListener('click', () => {
      currentIndex = Math.max(currentIndex - 1, 0);
      updateSlider();
    });

    // Auto-slide
    let autoSlideInterval = setInterval(() => {
      const visibleSlides = getVisibleSlides();
      const maxIndex = Math.max(0, totalSlides - visibleSlides);
      currentIndex = currentIndex >= maxIndex ? 0 : currentIndex + 1;
      updateSlider();
    }, 4000);

    // Pause auto-slide on hover
    slider.addEventListener('mouseenter', () => clearInterval(autoSlideInterval));
    slider.addEventListener('mouseleave', () => {
      autoSlideInterval = setInterval(() => {
        const visibleSlides = getVisibleSlides();
        const maxIndex = Math.max(0, totalSlides - visibleSlides);
        currentIndex = currentIndex >= maxIndex ? 0 : currentIndex + 1;
        updateSlider();
      }, 4000);
    });

    window.addEventListener('resize', updateSlider);
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