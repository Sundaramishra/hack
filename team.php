<?php include 'includes/header.php'; ?>

<!-- Team Hero Section -->
<section class="pt-20 pb-10 bg-white">
  <div class="container mx-auto px-4">
    <div class="text-center mb-3">
      <h1 class="text-4xl md:text-5xl font-bold mb-2" style="color:#F44B12;">Our Team</h1>
      <p class="text-lg font-medium" style="color:#2B2B2A;">Meet the Creative Wizards</p>
    </div>
  </div>
</section>

<!-- Team Card Section -->
<section style="background: radial-gradient(ellipse at 65% 90%, rgba(244,75,18,0.14) 0%, rgba(44,44,44,0.13) 100%); border-radius: 32px 32px 0 0; margin-top:-24px;">
  <div class="container mx-auto px-4 py-14">
    <?php
    // Fetch team members from database
    $teamMembers = [];
    $result = mysqli_query($conn, "SELECT name, position, bio, image FROM team ORDER BY id ASC LIMIT 5");
    if ($result) {
      while ($row = mysqli_fetch_assoc($result)) {
        $teamMembers[] = $row;
      }
    }
    // fallback demo data if less than 5
    if (count($teamMembers) < 5) {
      $teamMembers = array_merge($teamMembers, [
        ["image" => "team1.jpg", "name" => "PRASHANT KATELIYA", "position" => "CEO", "bio" => "CEO of making things cool"],
        ["image" => "team2.jpg", "name" => "PARSHWA PANCHAL", "position" => "COO", "bio" => "Operations Head"],
        ["image" => "team3.jpg", "name" => "OM VISHWAKARMA", "position" => "Creative Director", "bio" => "Creative wizard"],
        ["image" => "team4.jpg", "name" => "RISHI RATHOD", "position" => "Lead Developer", "bio" => "Tech lead"],
        ["image" => "team5.jpg", "name" => "RAVI KATELIYA", "position" => "Marketing Head", "bio" => "Marketing expert"],
      ]);
      $teamMembers = array_slice($teamMembers, 0, 5);
    }
    ?>
    <!-- First Row - 2 Cards -->
    <div class="team-row-1 grid grid-cols-1 md:grid-cols-2 gap-8 lg:gap-16 justify-items-center mb-12">
      <?php for ($i = 0; $i < 2 && $i < count($teamMembers); $i++): $m = $teamMembers[$i]; ?>
      <div class="luxury-team-card group relative my-4 mx-auto" style="background:#fff;">
        <div class="card-img-wrap relative overflow-hidden rounded-lg" style="height:200px; width:200px;">
          <img src="<?= htmlspecialchars($m["image"]) ?>" alt="<?= htmlspecialchars($m["name"]) ?>" class="team-photo w-full h-full object-cover rounded-lg shadow-md" />
        </div>
        <!-- Hover overlay - name and position -->
        <div class="team-hover-name absolute left-0 right-0 bottom-14 z-10 flex flex-col items-center opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-200">
          <div class="team-hover-name-label bg-white text-[#F44B12] font-bold px-6 py-2 rounded-t-xl text-base shadow-md uppercase mb-0"><?= strtoupper($m["name"]) ?></div>
          <div class="team-hover-name-desc bg-white text-[#2B2B2A] px-4 py-2 rounded-b-xl text-sm shadow-md font-semibold"><?= htmlspecialchars($m["position"]) ?></div>
        </div>
        <!-- Name and position below card (appear on hover only) -->
        <div class="team-name-below absolute w-full left-0 bottom-0 z-10 text-center bg-white text-[#F44B12] font-bold text-base rounded-b-xl shadow-md py-2 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-200">
          <?= strtoupper($m["name"]) ?><br>
          <span class="block text-[#2B2B2A] font-semibold text-sm"><?= htmlspecialchars($m["position"]) ?></span>
        </div>
      </div>
      <?php endfor; ?>
    </div>
    
    <!-- Second Row - 3 Cards -->
    <div class="team-row-2 grid grid-cols-1 md:grid-cols-3 gap-8 lg:gap-12 justify-items-center">
      <?php for ($i = 2; $i < 5 && $i < count($teamMembers); $i++): $m = $teamMembers[$i]; ?>
      <div class="luxury-team-card group relative my-4 mx-auto" style="background:#fff;">
        <div class="card-img-wrap relative overflow-hidden rounded-lg" style="height:200px; width:200px;">
          <img src="<?= htmlspecialchars($m["image"]) ?>" alt="<?= htmlspecialchars($m["name"]) ?>" class="team-photo w-full h-full object-cover rounded-lg shadow-md" />
        </div>
        <!-- Hover overlay - name and position -->
        <div class="team-hover-name absolute left-0 right-0 bottom-14 z-10 flex flex-col items-center opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-200">
          <div class="team-hover-name-label bg-white text-[#F44B12] font-bold px-6 py-2 rounded-t-xl text-base shadow-md uppercase mb-0"><?= strtoupper($m["name"]) ?></div>
          <div class="team-hover-name-desc bg-white text-[#2B2B2A] px-4 py-2 rounded-b-xl text-sm shadow-md font-semibold"><?= htmlspecialchars($m["position"]) ?></div>
        </div>
        <!-- Name and position below card (appear on hover only) -->
        <div class="team-name-below absolute w-full left-0 bottom-0 z-10 text-center bg-white text-[#F44B12] font-bold text-base rounded-b-xl shadow-md py-2 opacity-0 pointer-events-none group-hover:opacity-100 group-hover:pointer-events-auto transition-opacity duration-200">
          <?= strtoupper($m["name"]) ?><br>
          <span class="block text-[#2B2B2A] font-semibold text-sm"><?= htmlspecialchars($m["position"]) ?></span>
        </div>
      </div>
             <?php endfor; ?>
     </div>
  </div>
  <style>
    .team-grid {
      display: grid;
      grid-template-columns: 1fr;
      gap: 28px;
    }
    @media (min-width: 768px) {
      .team-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 40px 32px;
      }
    }
    @media (min-width: 1024px) {
      .team-grid {
        grid-template-columns: repeat(5, 1fr);
        gap: 36px 24px;
      }
    }
    .luxury-team-card {
      width: 100%;
      max-width: 270px;
      height: 270px;
      border-radius: 18px;
      box-shadow: 0 8px 38px 0 rgba(44,44,44,0.17), 0 0 0 0 #F44B12;
      overflow: visible;
      display: flex;
      align-items: flex-end;
      justify-content: center;
      position: relative;
      background: #fff;
      margin-bottom: 0;
      flex-direction: column;
      transition: box-shadow 0.25s, transform 0.18s;
      cursor: pointer;
    }
    .luxury-team-card:hover,
    .luxury-team-card:focus-within {
      box-shadow: 0 12px 38px 0 #F44B1270;
      transform: scale(1.03) translateY(-3px);
      z-index: 2;
    }
    .card-img-wrap {
      width: 100%;
      height: 180px;
      border-radius: 16px 16px 0 0;
      background: #f3f3f3;
      box-shadow: 0 2px 12px 0 rgba(44,44,44,0.08);
    }
    .team-photo {
      transition: filter 0.25s;
    }
    .luxury-team-card:hover .team-photo,
    .luxury-team-card:focus-within .team-photo {
      filter: brightness(0.75) blur(0.5px);
    }
    .team-hover-name-label {
      background: #fff;
      color: #F44B12;
      font-weight: 700;
      padding: 8px 18px 2px 18px;
      border-radius: 16px 16px 10px 10px;
      font-size: 1rem;
      letter-spacing: 0.02em;
      box-shadow: 0 2px 8px 0 #F44B1270;
      text-align: center;
      text-transform: uppercase;
      line-height: 1.1;
    }
    .team-hover-name-desc {
      background: #fff;
      color: #2B2B2A;
      padding: 2px 12px 6px 12px;
      border-radius: 0 0 10px 10px;
      font-size: 0.93rem;
      letter-spacing: 0.01em;
      box-shadow: 0 2px 8px 0 #F44B1270;
      text-align: center;
      font-weight: 600;
      line-height: 1.1;
    }
    .team-name-below {
      background: #fff;
      color: #F44B12;
      text-align: center;
      font-weight: 700;
      font-size: 1.08rem;
      border-radius: 0 0 18px 18px;
      box-shadow: 0 2px 8px 0 #F44B1270;
      letter-spacing: 0.02em;
      padding: 6px 0 0 0;
      margin-bottom: 0;
      line-height: 1.15;
      transition: opacity 0.22s;
    }
    .team-name-below span {
      display: block;
      color: #2B2B2A;
      font-weight: 600;
      font-size: 0.93rem;
      margin-top: 2px;
    }
    @media (max-width:1100px) {
      .luxury-team-card { max-width: 200px; height: 210px; }
      .card-img-wrap { height: 140px; }
    }
    @media (max-width:900px) {
      .team-grid { grid-template-columns: repeat(2, 1fr); }
      .luxury-team-card { max-width: 180px; height: 170px; }
      .card-img-wrap { height: 110px; }
    }
    @media (max-width:700px) {
      .team-grid { grid-template-columns: 1fr; }
      .luxury-team-card { max-width: 98vw; height: 210px; margin: 0 auto; }
      .card-img-wrap { height: 160px; }
    }
    @media (max-width:500px) {
      .luxury-team-card { max-width: 99vw; height: 160px; }
      .card-img-wrap { height: 95px; }
      .team-hover-name-label { font-size: 0.97rem; }
      .team-hover-name-desc { font-size: 0.83rem; }
      .team-name-below { font-size: 0.93rem; }
      .team-name-below span { font-size: 0.78rem; }
    }
  </style>
</section>

<?php include 'includes/footer.php'; ?>