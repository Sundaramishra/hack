<?php
// team.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include required files
require_once 'includes/db.php';
require_once 'includes/functions.php';
?>
<?php include 'includes/header.php'; ?>

<!-- Fonts -->
<link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@700;800;900&display=swap" rel="stylesheet">

<style>
  :root{
    --orange:#F44B12;
    --orange-deep:#D64209; /* dark orange glow */
  }
  *{box-sizing:border-box}
  body{margin:0;color:#2B2B2A;background:#fff}

  /* ---------- HERO ---------- */
  .team-hero{padding:68px 16px 18px}
  .team-hero h1{margin:0 0 6px;text-align:center;font:900 clamp(28px,4.2vw,52px)/1 'Montserrat',system-ui;color:var(--orange)}
  .team-hero p{margin:0;text-align:center;font:600 clamp(16px,2.2vw,22px)/1.25 'Montserrat',system-ui;color:#2B2B2A}

  /* ---------- SHOWCASE CONTAINER (glow INSIDE) ---------- */
  .team-showcase{
    position:relative; isolation:isolate;
    margin-top:6px; padding:44px 0 70px;
    border-radius:34px 34px 0 0;
    overflow:hidden; /* <<< keeps everything inside rounded box */
    background:
      linear-gradient(180deg, #262626 0%, #1f1f1f 100%);  /* base dark */
    box-shadow:0 28px 70px rgba(0,0,0,.28);
  }
  /* Top-right glow */
  .team-showcase::before{
    content:""; position:absolute; z-index:0; pointer-events:none;
    /* Place INSIDE the box */
    width:60%; height:60%;
    right:-12%; top:-16%;
    background:
      radial-gradient(60% 60% at 70% 30%, rgba(214,66,9,.42) 0%, rgba(214,66,9,.22) 36%, rgba(214,66,9,0) 70%);
    filter: blur(14px);
    /* Clip strictly to rounded container */
    -webkit-mask-image: radial-gradient(75% 75% at 65% 35%, #000 55%, transparent 80%);
            mask-image: radial-gradient(75% 75% at 65% 35%, #000 55%, transparent 80%);
  }
  /* Bottom-left glow */
  .team-showcase::after{
    content:""; position:absolute; z-index:0; pointer-events:none;
    width:68%; height:68%;
    left:-16%; bottom:-18%;
    background:
      radial-gradient(60% 60% at 30% 70%, rgba(178,54,7,.40) 0%, rgba(178,54,7,.20) 36%, rgba(178,54,7,0) 72%);
    filter: blur(16px);
    -webkit-mask-image: radial-gradient(80% 80% at 35% 70%, #000 55%, transparent 82%);
            mask-image: radial-gradient(80% 80% at 35% 70%, #000 55%, transparent 82%);
  }

  .team-wrap{position:relative; z-index:1; max-width:1200px; margin:0 auto; padding:0 18px;}

  /* ---------- ROWS LIKE MOCKUP ---------- */
  .team-row{display:flex; gap:20px; justify-content:center; flex-wrap:nowrap;}
  .team-row.row-1{margin-bottom:40px;}
  .team-row.row-2{gap:18px;}

  /* ---------- CARD ---------- */
  .tcard{width:250px; max-width:92vw; aspect-ratio: 4/5; position:relative;
    transform-origin:center bottom; transition:transform .25s, filter .25s;
    display:flex; align-items:flex-end; justify-content:center; cursor:pointer;
  }
  .tcard:hover{ transform:translateY(-6px) rotate(var(--rot,0deg)); filter:brightness(1.02); }

  .tframe{
    position:relative; width:100%; height:86%;
    border-radius:16px; overflow:hidden;
    box-shadow: 0 10px 28px rgba(0,0,0,.35);
    border: 3px solid rgba(255,255,255,.08);
    background:#2a2a2a;
  }
  .tbg{position:absolute; inset:0;}

  /* colorful backdrops (mock vibes) */
  .tcard.c1 .tbg{ background: linear-gradient(180deg,#35b362 0%, #0f7a46 100%); }
  .tcard.c2 .tbg{ background: linear-gradient(180deg,#4a78b7 0%, #2f5688 100%); }
  .tcard.c3 .tbg{ background: linear-gradient(180deg,#a4cf4c 0%, #6aa020 100%); }
  .tcard.c4 .tbg{ background: linear-gradient(180deg,#4a3fb6 0%, #2a2384 100%); }
  .tcard.c5 .tbg{ background: linear-gradient(180deg,#3db493 0%, #138a6d 100%); }

  .timg{position:absolute; inset:0; width:100%; height:100%; object-fit:cover; display:block}

  /* White plaque (name + subtitle) */
  .tplaque{
    position:absolute; left:50%; transform:translateX(-50%);
    bottom:-22px; min-width:78%; max-width:94%;
    background:#fff; border-radius:14px;
    box-shadow: 0 8px 18px rgba(0,0,0,.22);
    padding:8px 14px 10px; text-align:center;
  }
  .tname{
    color:var(--orange); font-family:'Montserrat',Arial,sans-serif;
    font-weight:900; letter-spacing:.02em; text-transform:uppercase;
    font-size: 0.98rem; line-height:1.05;
  }
  .trole{
    margin-top:4px; color:#2B2B2A; font-weight:600; font-size:0.82rem; line-height:1.05;
  }

  /* individual tilts (match reference) */
  .tcard.n1{ --rot:-8deg;  transform:rotate(-8deg); }
  .tcard.n2{ --rot: 6deg;   transform:rotate(6deg);  }
  .tcard.n3{ --rot:-7deg;   transform:rotate(-7deg); }
  .tcard.n4{ --rot: 5deg;   transform:rotate(5deg);  }
  .tcard.n5{ --rot:-6deg;   transform:rotate(-6deg); }

  /* ---------- Responsive ---------- */
  @media (max-width: 1400px){
    .tcard{ width:260px; }
    .team-row{ gap:26px; }
    .team-row.row-2{ gap:22px; }
  }
  @media (max-width: 1200px){
    .tcard{ width:240px; }
    .team-row{ gap:20px; }
    .team-row.row-2{ gap:16px; }
  }
  @media (max-width: 1024px){
    .team-row{ flex-wrap:wrap; }
    .tcard{ width:220px; }
    .team-row.row-2{ gap:14px; }
  }
  @media (max-width: 768px){
    .team-row{ gap:22px; }
    .tcard{ width:86vw; max-width:430px; }
    .tframe{ height:82%; }
    .tplaque{ bottom:-18px; }
    .tname{ font-size:1rem; }
    .trole{ font-size:0.85rem; }
  }
  @media (max-width: 420px){
    .tcard{ width:92vw; }
    .tframe{ height:80%; }
  }
</style>

<section class="team-hero">
  <h1>Our Team</h1>
  <p>Meet the Creative Wizards</p>
</section>

<section class="team-showcase">
  <div class="team-wrap">
    <?php
      // ---------- FETCH TEAM MEMBERS FROM DATABASE ----------
      $teamMembers = [];
      
      // Get team members using the function from functions.php
      $dbTeamMembers = getTeamMembers();
      
      // Process database results
      if (!empty($dbTeamMembers)) {
        foreach ($dbTeamMembers as $member) {
          // Ensure proper image path
          $imagePath = $member['image'];
          if (!preg_match('~^uploads/|^/|^https?://~i', $imagePath)) {
            $imagePath = 'uploads/team/' . ltrim($imagePath, '/');
          }
          
          $teamMembers[] = [
            'image' => $imagePath,
            'name' => $member['name'],
            'position' => $member['position'],
            'bio' => $member['bio']
          ];
        }
      }
      
      // Fallback demo data if no database records (keep exactly 5)
      $fallback = [
        ["image"=>"uploads/team/team1.jpg","name"=>"PRASHANT KATELIYA","position"=>"CEO","bio"=>"CEO of Making things cool"],
        ["image"=>"uploads/team/team2.jpg","name"=>"PARSHWA PANCHAL","position"=>"COO","bio"=>"CEO of Making things cool"],
        ["image"=>"uploads/team/team3.jpg","name"=>"OM VISHWAKARMA","position"=>"Creative Director","bio"=>"CEO of Making things cool"],
        ["image"=>"uploads/team/team4.jpg","name"=>"RISHI RATHOD","position"=>"Lead Developer","bio"=>"CEO of Making things cool"],
        ["image"=>"uploads/team/team5.jpg","name"=>"RAVI KATELIYA","position"=>"Marketing Head","bio"=>"CEO of Making things cool"],
      ];
      
      // Ensure we have exactly 5 members
      if (count($teamMembers) < 5) {
        $teamMembers = array_slice(array_merge($teamMembers, $fallback), 0, 5);
      } else {
        $teamMembers = array_slice($teamMembers, 0, 5);
      }

      // Helper function to render team card
      function teamCard($m, $idx, $colorClass){
        $img = htmlspecialchars($m['image'] ?? '');
        $name = htmlspecialchars($m['name'] ?? '');
        $pos  = htmlspecialchars($m['position'] ?? '');
        $bio  = htmlspecialchars($m['bio'] ?? '');
        
        echo '<div class="tcard n'.$idx.' '.$colorClass.'">';
          echo '<div class="tframe">';
            echo '<span class="tbg"></span>';
            echo '<img class="timg" src="'.$img.'" alt="'.$name.'" loading="lazy" onerror="this.style.display=\'none\'; this.parentElement.style.background=\'#2a2a2a\';">';
          echo '</div>';
          echo '<div class="tplaque">';
            echo '<div class="tname">'.strtoupper($name).'</div>';
            echo '<div class="trole">'.($bio ?: $pos).'</div>';
          echo '</div>';
        echo '</div>';
      }
    ?>

    <!-- Row 1: 2 cards -->
    <div class="team-row row-1">
      <?php teamCard($teamMembers[0], 1, 'c1'); ?>
      <?php teamCard($teamMembers[1], 2, 'c2'); ?>
    </div>

    <!-- Row 2: 3 cards -->
    <div class="team-row row-2">
      <?php teamCard($teamMembers[2], 3, 'c3'); ?>
      <?php teamCard($teamMembers[3], 4, 'c4'); ?>
      <?php teamCard($teamMembers[4], 5, 'c5'); ?>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>