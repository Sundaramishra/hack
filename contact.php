<?php include 'includes/header.php'; ?>

<!-- Google Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Poppins:wght@400;500&display=swap" rel="stylesheet">

<!-- Contact Hero Section -->
<section class="pt-24 pb-8 bg-white">
  <div class="container mx-auto px-4">
    <div class="text-center">
      <h1 class="text-4xl md:text-5xl font-bold mb-2 text-[#F44B12]" style="font-family: 'Montserrat', sans-serif;">Contact us</h1>
      <p class="text-lg font-medium mb-4 text-[#2B2B2A]" style="font-family: 'Poppins', sans-serif;">Get in touch with our Creative wizards!</p>
    </div>
  </div>
</section>

<!-- Transparent Luxury Contact Cards Section -->
<section class="py-12" style="background: radial-gradient(ellipse at top left, rgba(244,75,18,0.14), transparent 40%), radial-gradient(ellipse at bottom right, rgba(244,75,18,0.14), transparent 40%), linear-gradient(to bottom, #1c1c1b, #2a2a29);">
  <div class="container mx-auto px-4">
    <div class="grid md:grid-cols-2 gap-8">
      <!-- Phone Card -->
      <div class="luxury-card p-8 flex flex-col items-center text-center">
        <div class="luxury-icon mb-5">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M22 16.92V21a1 1 0 0 1-1.09 1A19.72 19.72 0 0 1 3 5.09 1 1 0 0 1 4 4h4.09A1 1 0 0 1 9 5.09c.13 1.05.37 2.06.71 3.03a1 1 0 0 1-.23 1.01l-1.27 1.27a16 16 0 0 0 6.12 6.12l1.27-1.27a1 1 0 0 1 1.01-.23c.97.34 1.98.58 3.03.71A1 1 0 0 1 20 15.91V20a1 1 0 0 1-1.09 1z"></path>
          </svg>
        </div>
        <div class="luxury-title mb-1">Contact Us</div>
        <div class="luxury-value">
          +91 87797 80872<br>
          +91 87790 20018
        </div>
      </div>

      <!-- Email Card -->
      <div class="luxury-card p-8 flex flex-col items-center text-center">
        <div class="luxury-icon mb-5">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="white" stroke="white" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="5" width="18" height="14" rx="2"></rect>
            <polyline points="3 7 12 13 21 7"></polyline>
          </svg>
        </div>
        <div class="luxury-title mb-1">Email Address</div>
        <div class="luxury-value">
          connect.vbind@gmail.com
        </div>
      </div>

      <!-- Social Card -->
      <div class="luxury-card p-8 flex flex-col items-center text-center md:col-span-2">
        <div class="luxury-title mb-2 tracking-wide">FIND US</div>
        <div class="flex justify-center space-x-6">
          <!-- Instagram -->
          <a href="https://www.instagram.com/connect.vbind" target="_blank" class="luxury-social">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="white">
              <rect x="2" y="2" width="20" height="20" rx="5"/>
              <circle cx="12" cy="12" r="4"/>
              <circle cx="18" cy="6" r="1.5" fill="white"/>
            </svg>
          </a>
          <!-- LinkedIn -->
          <a href="https://www.linkedin.com/company/connectvbind/" target="_blank" class="luxury-social">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="white">
              <rect x="2" y="2" width="20" height="20" rx="5"/>
              <line x1="8" y1="11" x2="8" y2="16"/>
              <line x1="8" y1="8" x2="8" y2="8.01"/>
              <line x1="12" y1="11" x2="12" y2="16"/>
              <path d="M12 13c0-1.1 1.79-2 3-2s2.5.9 2.5 2v3"/>
            </svg>
          </a>
          <!-- Facebook -->
          <a href="https://www.facebook.com/connect.vbind" target="_blank" class="luxury-social">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="white">
              <rect x="2" y="2" width="20" height="20" rx="5"/>
              <path d="M16 8h-2a2 2 0 0 0-2 2v2h4"/>
              <line x1="12" y1="16" x2="12" y2="10"/>
            </svg>
          </a>
        </div>
      </div>
    </div>
  </div>
  <style>
    .luxury-card {
      background: rgba(0,0,0,0.55);
      border-radius: 18px;
      border: 1.8px solid rgba(255,255,255,0.08);
      box-shadow: 0 8px 38px 0 rgba(44,44,44,0.15);
      backdrop-filter: blur(8.5px);
      -webkit-backdrop-filter: blur(8.5px);
      transition: border 0.25s, box-shadow 0.28s, transform 0.18s;
      font-family: 'Montserrat', sans-serif;
    }
    .luxury-title {
      color: #fff;
      font-family: 'Montserrat', sans-serif;
      font-size: 1.15rem;
      font-weight: 700;
    }
    .luxury-value {
      color: #fff;
      font-family: 'Poppins', sans-serif;
      font-size: 1rem;
    }
    .luxury-icon {
      width: 56px;
      height: 56px;
      border-radius: 50%;
      background: linear-gradient(135deg, rgba(244,75,18,0.39), rgba(255,110,42,0.17));
      display: flex;
      align-items: center;
      justify-content: center;
    }
    .luxury-social svg { opacity: 0.95; transition: opacity 0.2s; }
    .luxury-social:hover svg { opacity: 1; }
  </style>
</section>

<?php include 'includes/footer.php'; ?>
