<?php
session_start();

$to = "youremail@example.com"; // <-- CHANGE TO YOUR EMAIL

// CSRF protection: generate and check token
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (!isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
        $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        echo "<div class='contact-error'>Invalid or expired session. Please reload the page and try again.</div>";
        exit;
    }

    // Sanitize and validate
    $name    = htmlspecialchars(trim($_POST["name"] ?? ""), ENT_QUOTES, 'UTF-8');
    $email   = htmlspecialchars(trim($_POST["email"] ?? ""), ENT_QUOTES, 'UTF-8');
    $message = htmlspecialchars(trim($_POST["message"] ?? ""), ENT_QUOTES, 'UTF-8');

    if (empty($name) || empty($email) || empty($message)) {
        echo "<div class='contact-error'>Please fill in all fields.</div>";
        exit;
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo "<div class='contact-error'>Invalid email address.</div>";
        exit;
    }

    // Prevent header injection
    if (preg_match("/[\r\n]/", $name) || preg_match("/[\r\n]/", $email)) {
        echo "<div class='contact-error'>Invalid input detected.</div>";
        exit;
    }

    $subject = "New Contact Form Submission";
    $body    = "Name: $name\nEmail: $email\n\nMessage:\n$message";
    $headers = "From: $name <$email>\r\nReply-To: $email\r\n";

    if (mail($to, $subject, $body, $headers)) {
        echo "<div class='contact-success'>Thank you for contacting us! We will respond soon.</div>";
    } else {
        echo "<div class='contact-error'>There was a problem sending your message. Please try again later.</div>";
    }
} else {
    // Show contact form (with CSRF token)
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Contact Us | GADPATI</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- Tailwind CDN for theme consistency -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Montserrat:700,400&display=swap">
  <style>
    body { font-family: 'Montserrat', Arial, sans-serif;}
    .contact-bg {
      background: linear-gradient(120deg, #232323 60%, #101010 100%);
      min-height: 100vh;
    }
    .contact-form-glow {
      box-shadow: 0 0 70px 8px #F44B1255, 0 2px 32px #fff2;
      border-radius: 32px;
      background: rgba(255,255,255,0.03);
    }
    .contact-success {
      background: #1cc96b;
      color: #fff;
      padding: 1.1em 1.5em;
      border-radius: 0.75em;
      margin-bottom: 1.5em;
      font-weight: 600;
      box-shadow: 0 4px 16px #12e27444;
      text-align: center;
    }
    .contact-error {
      background: #F44B12;
      color: #fff;
      padding: 1.1em 1.5em;
      border-radius: 0.75em;
      margin-bottom: 1.5em;
      font-weight: 600;
      box-shadow: 0 4px 16px #F44B1244;
      text-align: center;
    }
    /* Animation for call-to-action */
    .cta-btn-animate {
      animation: pulse 1.5s infinite;
    }
    @keyframes pulse {
      0% { box-shadow: 0 0 0 0 #F44B1260;}
      70% { box-shadow: 0 0 0 12px #F44B1200;}
      100% { box-shadow: 0 0 0 0 #F44B1200;}
    }
    /* Responsive for side CTA */
    @media (min-width:900px) {
      .contact-main-grid {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 2.5rem;
        align-items: start;
      }
    }
  </style>
</head>
<body class="contact-bg flex items-center justify-center min-h-screen">
  <main class="w-full max-w-5xl px-2 py-10 md:px-10">
    <h1 class="text-3xl md:text-4xl font-bold text-white mb-10 text-center tracking-tight">Contact Us</h1>
    <div class="contact-main-grid">
      <!-- Contact Form -->
      <form method="POST" action="contact_process.php" autocomplete="off"
        class="contact-form-glow p-8 md:p-12 mb-10 md:mb-0 flex flex-col gap-6 max-w-xl mx-auto">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <label class="text-gray-200 font-semibold text-left" for="name">Full Name</label>
        <input class="rounded-lg px-4 py-3 bg-white/10 text-white placeholder-gray-300 outline-none border-2 border-transparent focus:border-orange-500 transition"
          type="text" id="name" name="name" maxlength="100" required autocomplete="off" placeholder="Enter your name">
        <label class="text-gray-200 font-semibold text-left" for="email">Email</label>
        <input class="rounded-lg px-4 py-3 bg-white/10 text-white placeholder-gray-300 outline-none border-2 border-transparent focus:border-orange-500 transition"
          type="email" id="email" name="email" maxlength="120" required autocomplete="off" placeholder="you@email.com">
        <label class="text-gray-200 font-semibold text-left" for="message">Message</label>
        <textarea class="rounded-lg px-4 py-3 bg-white/10 text-white placeholder-gray-300 outline-none border-2 border-transparent focus:border-orange-500 transition min-h-[120px] resize-vertical"
          id="message" name="message" maxlength="1500" required autocomplete="off" placeholder="How can we help you?"></textarea>
        <button type="submit"
          class="mt-4 bg-gradient-to-r from-orange-500 to-orange-600 text-white font-bold py-3 rounded-xl shadow-lg hover:from-orange-600 hover:to-orange-700 transition cta-btn-animate">
          Send Message
        </button>
      </form>
      <!-- Side Call-to-Action -->
      <aside class="flex flex-col items-center justify-center pt-6 md:pt-0 md:pl-8">
        <div class="bg-white/10 rounded-2xl px-6 py-8 md:px-10 md:py-14 shadow-xl flex flex-col items-center">
          <svg width="48" height="48" fill="none" viewBox="0 0 48 48" class="mb-4 text-orange-500">
            <circle cx="24" cy="24" r="24" fill="#F44B12" fill-opacity="0.18"/>
            <path d="M36 16.14c0-1.04-.85-1.89-1.89-1.89H13.89A1.89 1.89 0 0 0 12 16.14V32.1c0 1.04.85 1.89 1.89 1.89h20.22A1.89 1.89 0 0 0 36 32.1V16.14zm-1.89-4.11A6.11 6.11 0 0 0 28 7.89h-8a6.11 6.11 0 0 0-6.11 6.14v.89h20.22v-.89z" fill="#F44B12"/>
          </svg>
          <h2 class="text-2xl font-bold text-white mb-3">Let's Connect!</h2>
          <p class="text-gray-200 mb-6 max-w-xs">Have a project in mind, want to collaborate, or just say hello? We're eager to hear from you. Let’s build something <span class="text-orange-500 font-bold">out of the box</span> together!</p>
          <a href="portfolio.php" class="px-7 py-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-full font-semibold shadow-lg transition cta-btn-animate">
            View Our Work
          </a>
        </div>
      </aside>
    </div>
  </main>
</body>
</html>
<?php } ?>