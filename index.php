<!DOCTYPE html>
<html lang="mr">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<title>विभागीय आयुक्त कार्यालय अमरावती| Divisional Commissioner Office Amravati</title>
<style>
  :root{
    --bar1:#ffcc80; --bar2:#ffb74d; --grey:#f2f2f2; --text:#000;
  }
  body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f8f9fa;}

  /* Header */
  .header{
    display:flex;align-items:center;gap:16px;
    padding:12px 20px;background:#fff;box-shadow:0 2px 5px rgba(0,0,0,.1);
    position:sticky;top:0;z-index:3000;
  }
  .header img{width:80px;height:auto}
  .header h1{margin:0;font-size:20px;line-height:1.4;color:#333}

  /* Navbar (click dropdown, mobile-friendly) */
  .navbar{
    display:flex;align-items:stretch;gap:0;
    background:linear-gradient(to right,var(--bar1),var(--bar2));
    position:relative;z-index:4000;
    padding:0 8px;
  }
  .navbar .spacer{flex:1}
  .dropdown{position:relative}
  .dropbtn{
    appearance:none;border:0;background:transparent;
    color:var(--text);font-size:16px;padding:12px 16px;cursor:pointer;
    -webkit-tap-highlight-color:transparent;touch-action:manipulation;
  }
  .dropbtn::after{content:" ▾";font-size:12px}
  .dropbtn:focus{outline:2px solid rgba(0,0,0,.15);outline-offset:2px}

  .dropdown-content{
    position:absolute;top:100%;left:0;
    display:none;min-width:200px;background:var(--grey);
    box-shadow:0 8px 20px rgba(0,0,0,.15);border-radius:6px;overflow:hidden;
  }
  .dropdown.align-right .dropdown-content{right:0;left:auto}

  .dropdown.open .dropdown-content{display:block}

  .dropdown-content a{
    display:block;padding:10px 14px;text-decoration:none;color:#000;
    background:var(--grey);transition:background .15s ease;
    white-space:nowrap;
  }
  .dropdown-content a:hover{background:#ffb84d;color:#fff}

  /* Slideshow */
  .slideshow-container{max-width:100%;margin:0 auto;overflow:hidden;background:#fff}
  .slides{display:none}
  .slides img{width:100%;height:auto;display:block}

  /* Footer */
  .footer {
    background:linear-gradient(to right,var(--bar1),var(--bar2));
    padding:15px;
    text-align:center;
    color:#000;
    font-size:14px;
    margin-top:20px;
    box-shadow:0 -2px 5px rgba(0,0,0,.1);
  }
  .footer a{
    color:#000;
    text-decoration:none;
    font-weight:bold;
  }
  .footer a:hover{
    text-decoration:underline;
  }
</style>
</head>
<body>

<!-- Header -->
<div class="header">
  <img src="logo.png" alt="Logo" />
  <h1>
    विभागीय आयुक्त कार्यालय अमरावती (मागासवर्गीय कक्ष)<br/>
    Divisional Commissioner Office Amravati (Backward class)
  </h1>
  <div style="margin-left:auto; font-size:25px; font-weight:bold; color:#b34700; white-space:nowrap;">
    बिंदू नामावली नोंदणी
  </div>
</div>

<!-- Navbar -->
<nav class="navbar" aria-label="Main">
  <!-- <div class="dropdown">
    <button class="dropbtn" aria-haspopup="true" aria-expanded="false">मुख्यपृष्ठ</button>
    <div class="dropdown-content" role="menu">
      <a href="#">Sub-item 1</a>
      <a href="#">Sub-item 2</a>
    </div>
  </div>

  <div class="dropdown">
    <button class="dropbtn" aria-haspopup="true" aria-expanded="false">विभागाविषयी</button>
    <div class="dropdown-content" role="menu">
      <a href="#">History</a>
      <a href="#">Details</a>
    </div>
  </div>

  <div class="dropdown">
    <button class="dropbtn" aria-haspopup="true" aria-expanded="false">कार्यालयीन विभाग</button>
    <div class="dropdown-content" role="menu">
      <a href="#">Section 1</a>
      <a href="#">Section 2</a>
    </div>
  </div>

  <div class="dropdown">
    <button class="dropbtn" aria-haspopup="true" aria-expanded="false">शासन निर्णय</button>
    <div class="dropdown-content" role="menu">
      <a href="/ho/hostel/admin/shasan_nirnay_list.php">शासन निर्णय</a>
      
    </div>
  </div> -->

  <div class="spacer"></div>

  <div class="dropdown align-right">
    <button class="dropbtn" aria-haspopup="true" aria-expanded="false">Admin Login</button>
    <div class="dropdown-content" role="menu">
      <a href="/ho/hostel/admin/index.php">Login</a>
      <!-- <a href="admin-dashboard.php">Dashboard</a> -->
    </div>
  </div>

  <div class="dropdown align-right">
    <button class="dropbtn" aria-haspopup="true" aria-expanded="false">User Login</button>
    <div class="dropdown-content" role="menu">
      <a href="/ho/hostel/index.php">Login</a>
      <!-- <a href="user-profile.php">Profile</a> -->
    </div>
  </div>
</nav>

<!-- Slideshow -->
<div class="slideshow-container">
  <div class="slides"><img src="1.jpg" alt="Image 1" /></div>
  <div class="slides"><img src="2.jpg" alt="Image 2" /></div>
</div>

<!-- Footer -->
<div class="footer">
  © 2026 विभागीय आयुक्त कार्यालय अमरावती
</div>

<script>
  /* CLICK-BASED DROPDOWNS */
  const dropdowns = document.querySelectorAll('.dropdown');

  function closeAll() {
    dropdowns.forEach(d => {
      d.classList.remove('open');
      const btn = d.querySelector('.dropbtn');
      if (btn) btn.setAttribute('aria-expanded', 'false');
    });
  }

  dropdowns.forEach(d => {
    const btn = d.querySelector('.dropbtn');
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      const isOpen = d.classList.contains('open');
      closeAll();
      if (!isOpen) {
        d.classList.add('open');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.navbar')) closeAll();
  });

  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeAll();
  });

  /* SLIDESHOW */
  let slideIndex = 0;
  function showSlides() {
    const slides = document.getElementsByClassName('slides');
    for (let i = 0; i < slides.length; i++) slides[i].style.display = 'none';
    slideIndex = (slideIndex + 1) % slides.length;
    slides[slideIndex].style.display = 'block';
    setTimeout(showSlides, 3000);
  }
  (function initSlides(){
    const slides = document.getElementsByClassName('slides');
    if (slides.length) slides[0].style.display = 'block';
    setTimeout(showSlides, 3000);
  })();
</script>

</body>
</html>
