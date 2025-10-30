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
    }

    .btn-book-landing:hover {
      transform: translateY(-2px);
      box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
    }
  </style>
</head>
<body class="bg-gray-50 text-gray-900">

  <!-- HEADER -->
  <header class="sticky top-0 z-50 bg-white shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center py-4">
        <div class="flex items-center space-x-3">
          <img src="/images/logodental.png" alt="Halili's Dental Clinic Logo" class="w-12 h-12 sm:w-14 sm:h-14 object-contain">
          <div>
            <h2 class="text-lg sm:text-xl font-bold gradient-text">Halili's Dental Clinic</h2>
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
  <section id="home" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-20">
    <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center">
      <div class="order-2 lg:order-1 text-center lg:text-left">
        <div class="inline-block px-4 py-2 bg-purple-100 rounded-full mb-6">
          <span class="text-purple-700 font-medium text-sm">🦷 Trusted by 5,000+ Happy Patients</span>
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
  <section id="about" class="bg-white py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
      <div class="text-center max-w-3xl mx-auto mb-12">
        <h2 class="text-3xl sm:text-4xl font-bold mb-4">Why Choose <span class="gradient-text">Halili's Dental Clinic?</span></h2>
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

  <!-- SERVICES -->
  <section id="services" class="py-16 sm:py-20 bg-gray-50">
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
                <button class="btn-book-landing">
                  <?= htmlspecialchars($service['btn']) ?>
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                  </svg>
                </button>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
    </div>
  </section>

  <!-- CONTACT -->
  <section id="contact" class="bg-gradient-to-br from-purple-600 to-blue-600 py-16 sm:py-20">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
      <img src="/images/halili-logo.png" alt="Halili's Dental Clinic" class="w-24 sm:w-32 mx-auto mb-6 bg-white p-4 rounded-2xl shadow-xl">
      <h2 class="text-3xl sm:text-4xl font-bold text-white mb-4">Get In Touch With Us</h2>
      <p class="text-purple-100 text-lg mb-10">Halili Dental Clinic by Doc Kyle Halili DMD, Rodriguez, Philippines</p>
      <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 max-w-5xl mx-auto">
        <div class="contact-card p-6 rounded-2xl text-center">
          <h3 class="font-bold text-gray-900 mb-2">📞 Call Us</h3>
          <p class="text-gray-600">0922 223 3688</p>
        </div>
        <div class="contact-card p-6 rounded-2xl text-center">
          <h3 class="font-bold text-gray-900 mb-2">💬 Viber</h3>
          <p class="text-gray-600">+63 922 223 3688</p>
        </div>
        <div class="contact-card p-6 rounded-2xl text-center sm:col-span-2 lg:col-span-1">
          <h3 class="font-bold text-gray-900 mb-2">📧 Email</h3>
          <p class="text-gray-600 break-all">halilidentalcare@gmail.com</p>
        </div>
      </div>
    </div>
  </section>

  <!-- FOOTER -->
  <footer class="bg-gray-900 text-gray-300 py-8 text-center">
    <p>&copy; 2025 Halili's Dental Clinic. All rights reserved.</p>
    <p class="text-sm mt-2">Excellence in Dental Care Since 1981</p>
  </footer>

</body>
</html>
