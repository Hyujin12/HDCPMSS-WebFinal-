<?php
include 'service-grid.php';
include 'review-grid.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Halili Dental Clinic - Your Trusted Dental Care Partner</title>
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

    /* Responsive Service Card */
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
      transition: transform 0.5s ease;
    }

    .service-card:hover .service-card-image {
      transform: scale(1.05);
    }

    .service-card-content {
      padding: 1.25rem;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .btn-book-landing {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      color: white;
      border: none;
      padding: 0.75rem 1.75rem;
      border-radius: 25px;
      font-weight: 600;
      cursor: pointer;
      transition: all 0.3s ease;
      font-size: 0.9rem;
      display: inline-flex;
      align-items: center;
      gap: 0.5rem;
      text-decoration: none;
    }

    .btn-book-landing:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }

    /* Testimonial Card Styles */
    .testimonial-card {
      background: white;
      border-radius: 16px;
      padding: 1.5rem;
      box-shadow: 0 2px 12px rgba(0,0,0,0.08);
      transition: all 0.3s ease;
      border: 1px solid #e5e7eb;
      display: flex;
      flex-direction: column;
      height: 100%;
    }

    .testimonial-card:hover {
      transform: translateY(-4px);
      box-shadow: 0 8px 24px rgba(102, 126, 234, 0.15);
    }

    .patient-image {
      width: 80px;
      height: 80px;
      border-radius: 50%;
      object-fit: cover;
      border: 3px solid #f3f4f6;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }

    .star-rating {
      color: #fbbf24;
      font-size: 1.1rem;
    }

    .quote-icon {
      font-size: 3rem;
      color: #e9d5ff;
      line-height: 1;
      opacity: 0.5;
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">

 <!-- HEADER -->
  <header class="sticky top-0 z-40 bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <div class="flex items-center space-x-3">
          <img src="/images/newlogohalili.png" alt="Halili Dental Clinic Logo" class="w-12 h-12 sm:w-14 sm:h-14 object-contain">
          <div>
            <h2 class="text-lg sm:text-xl font-bold gradient-text">Halili Dental Clinic</h2>
            <p class="text-xs text-gray-500 hidden sm:block">Excellence in Dental Care</p>
          </div>
        </div>

        <nav class="hidden md:flex items-center space-x-8">
          <a href="#home" class="nav-link text-gray-700 font-medium hover:text-purple-600 transition">Home</a>
          <a href="#about" class="nav-link text-gray-700 font-medium hover:text-purple-600 transition">About</a>
          <a href="#services" class="nav-link text-gray-700 font-medium hover:text-purple-600 transition">Services</a>
          <a href="#contact" class="nav-link text-gray-700 font-medium hover:text-purple-600 transition">Contact</a>
          <a href="log-in.php">
            <button class="hero-gradient text-white px-6 py-2 rounded-full font-medium hover:shadow-lg transition duration-300">Book Now</button>
          </a>
        </nav>

        <button id="navToggle" class="md:hidden text-gray-700 focus:outline-none p-2">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"></path>
          </svg>
        </button>
      </div>
    </div>
  </header>

  <!-- HERO -->
  <section id="home" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20 scroll-mt-20">
    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
      <div class="order-2 lg:order-1 text-center lg:text-left">
        <div class="inline-block px-4 py-2 bg-purple-100 rounded-full mb-6">
          <span class="text-purple-700 font-medium text-sm">🦷 Trusted by 1000+ Happy Patients</span>
        </div>
        <h1 class="text-3xl sm:text-4xl lg:text-5xl font-bold mb-6 leading-tight">
          We Are Ready To Help & Take Care of Your 
          <span class="gradient-text">Dental Health</span>
        </h1>
        <p class="text-base sm:text-lg text-gray-600 mb-8 leading-relaxed">
          Creating miles of smiles with gentle care and advanced precision. Experience personalized dental solutions for your needs.
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
      </div>

      <div class="order-1 lg:order-2 flex justify-center relative">
        <div class="relative w-full max-w-md lg:max-w-lg">
          <div class="absolute inset-0 bg-gradient-to-br from-purple-200 to-blue-200 rounded-full blur-3xl opacity-30"></div>
          <img src="/images/Doctor-image.png" alt="Professional Dentist" class="relative z-10 w-full h-auto object-contain drop-shadow-2xl" />
        </div>
      </div>
    </div>
  </section>

  <!-- ABOUT -->
  <section id="about" class="bg-white py-16 sm:py-20 scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">Why Choose <span class="gradient-text">Halili Dental Clinic?</span></h2>
        <p class="text-gray-600 text-lg">We combine state-of-the-art technology with compassionate care.</p>
      </div>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
        <div class="card-hover bg-gradient-to-br from-purple-50 to-blue-50 p-6 sm:p-8 rounded-2xl border border-purple-100">
          <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-4">
            <span class="text-2xl">🏥</span>
          </div>
          <h3 class="text-xl font-bold mb-3">Modern Equipment</h3>
          <p class="text-gray-600">Advanced technology for accurate diagnosis and painless treatment.</p>
        </div>
        <div class="card-hover bg-gradient-to-br from-purple-50 to-blue-50 p-6 sm:p-8 rounded-2xl border border-purple-100">
          <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-4">
            <span class="text-2xl">👨‍⚕️</span>
          </div>
          <h3 class="text-xl font-bold mb-3">Expert Team</h3>
          <p class="text-gray-600">Experienced and qualified dental professionals.</p>
        </div>
        <div class="card-hover bg-gradient-to-br from-purple-50 to-blue-50 p-6 sm:p-8 rounded-2xl border border-purple-100">
          <div class="w-14 h-14 bg-gradient-to-br from-purple-500 to-blue-500 rounded-xl flex items-center justify-center mb-4">
            <span class="text-2xl">💙</span>
          </div>
          <h3 class="text-xl font-bold mb-3">Patient-Centered Care</h3>
          <p class="text-gray-600">Personalized treatment focused on your comfort and needs.</p>
        </div>
      </div>
    </div>
  </section>

  <!-- TESTIMONIALS -->
  <section class="py-16 sm:py-20 bg-gradient-to-br from-purple-50 via-blue-50 to-purple-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">What Our <span class="gradient-text">Patients Say</span></h2>
        <p class="text-gray-600 text-lg">Real experiences from our valued patients who trust us with their smiles.</p>
      </div>

      <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($testimonials as $testimonial): ?>
          <div class="testimonial-card">
            <div class="quote-icon mb-3">"</div>
            
            <div class="flex items-center gap-4 mb-4">
              <img src="<?= htmlspecialchars($testimonial['image']) ?>" 
                   alt="<?= htmlspecialchars($testimonial['name']) ?>" 
                   class="patient-image">
              <div>
                <h3 class="font-bold text-gray-900 text-lg"><?= htmlspecialchars($testimonial['name']) ?></h3>
                <p class="text-sm text-gray-500"><?= htmlspecialchars($testimonial['location']) ?></p>
                <div class="star-rating mt-1">
                  <?php for ($i = 0; $i < $testimonial['rating']; $i++): ?>
                    ★
                  <?php endfor; ?>
                </div>
              </div>
            </div>

            <p class="text-gray-600 leading-relaxed mb-4 flex-1">
              "<?= htmlspecialchars($testimonial['review']) ?>"
            </p>

            <div class="pt-3 border-t border-gray-100">
              <span class="inline-block px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-xs font-medium">
                <?= htmlspecialchars($testimonial['treatment']) ?>
              </span>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <div class="text-center mt-12">
        <p class="text-gray-600 mb-6">Join thousands of satisfied patients who trust Halili Dental Clinic</p>
        <a href="log-in.php">
          <button class="hero-gradient text-white px-8 py-4 rounded-full font-semibold hover:shadow-xl transition duration-300 transform hover:scale-105">
            Book Your Appointment Today
          </button>
        </a>
      </div>
    </div>
  </section>

  <!-- SERVICES -->
  <section id="services" class="py-16 sm:py-20 bg-gray-50 scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">Our <span class="gradient-text">Dental Services</span></h2>
        <p class="text-gray-600 text-lg">Comprehensive care for your oral health.</p>
      </div>

      <!-- Responsive Grid Instead of Slider -->
      <div class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($services as $service): ?>
          <div class="service-card">
            <img src="<?= htmlspecialchars($service['image']) ?>" alt="<?= htmlspecialchars($service['title']) ?>" class="service-card-image">
            <div class="service-card-content">
              <h2 class="text-xl font-bold gradient-text mb-2"><?= htmlspecialchars($service['title']) ?></h2>
              <p class="text-gray-600 text-sm mb-4 flex-1"><?= htmlspecialchars($service['description']) ?></p>
              <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                <span class="text-sm text-gray-500">Professional Care</span>
                <a href="log-in.php" class="btn-book-landing">
                  <?= htmlspecialchars($service['btn']) ?>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </a>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- CONTACT -->
  <section id="contact" class="bg-gradient-to-br from-purple-600 to-blue-600 py-16 sm:py-20 scroll-mt-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <img src="/images/newlogohalili.png" alt="Halili Dental Clinic" class="w-24 sm:w-32 mx-auto mb-6 bg-white p-4 rounded-2xl shadow-xl">
      <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Get In Touch With Us</h2>
      <p class="text-purple-100 text-lg mb-10">Halili Dental Clinic by Doc Kyle Halili DMD, Rodriguez, Philippines</p>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <div class="contact-card p-6 rounded-2xl text-center">
          <h3 class="font-bold text-gray-900 mb-2">📞 Call Us</h3>
          <p class="text-gray-600">0920 669 3290</p>
        </div>
        <div class="contact-card p-6 rounded-2xl text-center">
          <h3 class="font-bold text-gray-900 mb-2">📱 Viber</h3>
          <p class="text-gray-600">0920 669 3290</p>
        </div>
        <div class="contact-card p-6 rounded-2xl text-center sm:col-span-2 lg:col-span-1">
          <h3 class="font-bold text-gray-900 mb-2">✉️ Email</h3>
          <p class="text-gray-600 break-all">DrKyleHalili@gmail.com</p>
        </div>
        <div class="contact-card p-6 rounded-2xl text-center sm:col-span-2 lg:col-span-1">
          <h3 class="font-bold text-gray-900 mb-2">📘 Facebook</h3>
          <p class="text-gray-600 break-all">facebook.com/DrKyle</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-gray-900 text-gray-300 py-8 text-center">
    <p>&copy; 2025 Halili Dental Clinic. All rights reserved.</p>
    <p class="text-sm mt-2">Excellence in Dental Care Since 1981</p>
  </footer>


  <!-- Mobile Menu -->
  <div id="mobileMenu" class="hidden fixed inset-0 z-50 bg-black bg-opacity-50 md:hidden">
    <div class="fixed right-0 top-0 bottom-0 w-64 bg-white shadow-xl transform transition-transform duration-300">
      <div class="p-6">
        <button id="closeMenu" class="absolute top-4 right-4 text-gray-700 hover:text-purple-600">
          <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
        <nav class="flex flex-col space-y-4 mt-8">
          <a href="#home" class="mobile-nav-link text-gray-700 font-medium hover:text-purple-600 transition py-2">Home</a>
          <a href="#about" class="mobile-nav-link text-gray-700 font-medium hover:text-purple-600 transition py-2">About</a>
          <a href="#services" class="mobile-nav-link text-gray-700 font-medium hover:text-purple-600 transition py-2">Services</a>
          <a href="#contact" class="mobile-nav-link text-gray-700 font-medium hover:text-purple-600 transition py-2">Contact</a>
          <a href="log-in.php">
            <button class="hero-gradient text-white px-6 py-3 rounded-full font-medium hover:shadow-lg transition duration-300 w-full">Book Now</button>
          </a>
        </nav>
      </div>
    </div>
  </div>

  <script>
    // Mobile menu toggle
    const navToggle = document.getElementById('navToggle');
    const mobileMenu = document.getElementById('mobileMenu');
    const closeMenu = document.getElementById('closeMenu');
    const mobileNavLinks = document.querySelectorAll('.mobile-nav-link');

    navToggle.addEventListener('click', () => {
      mobileMenu.classList.remove('hidden');
    });

    closeMenu.addEventListener('click', () => {
      mobileMenu.classList.add('hidden');
    });

    // Close menu when clicking outside
    mobileMenu.addEventListener('click', (e) => {
      if (e.target === mobileMenu) {
        mobileMenu.classList.add('hidden');
      }
    });

    // Close menu when clicking on nav links
    mobileNavLinks.forEach(link => {
      link.addEventListener('click', () => {
        mobileMenu.classList.add('hidden');
      });
    });
  </script>

</body>
</html>