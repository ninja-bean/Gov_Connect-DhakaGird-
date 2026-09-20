
<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>GovtConnect | Voice for the People</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f8f9fa;
      overflow-x: hidden;
    }
    .hero-section {
      background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)),
                  url('https://media.istockphoto.com/id/543568310/vector/help-signs-in-hands.jpg?s=612x612&w=0&k=20&c=YIq1G12MyEweNClNErZVt5738NHc0sKRYd7stdOY_U8=') center/cover no-repeat;
      height: 100vh;
      color: white;
      display: flex;
      align-items: center;
      justify-content: center;
      text-align: center;
    }
    .hero-content h1 {
      font-weight: 700;
      font-size: 3rem;
    }
    .hero-content p {
      font-size: 1.2rem;
      margin: 1rem 0 2rem;
    }
    .feature-icon {
      font-size: 2.5rem;
      color: #0d6efd;
    }
    footer {
      background-color: #0d6efd;
      color: white;
      padding: 20px 0;
      text-align: center;
    }
    .team-card img {
      border-radius: 50%;
      width: 120px;
      height: 120px;
      object-fit: cover;
      margin-bottom: 15px;
    }
  </style>
</head>
<body>

  <!-- Hero Section -->
  <section class="hero-section">
    <div class="container hero-content">
      <h1>Welcome to GovtConnect</h1>
      <p>Empowering citizens to connect with their government, report issues, and request help — all in one place.</p>
      <a href="login.php" class="btn btn-primary btn-lg me-2">Login</a>
      <a href="register.php" class="btn btn-outline-light btn-lg">Register</a>
    </div>
  </section>

  <!-- About Section -->
  <section class="py-5 bg-light text-center">
    <div class="container">
      <h2 class="fw-bold mb-4">About the Project</h2>
      <p class="lead mb-5">
        <strong>GovtConnect</strong> is a citizen-centric platform that bridges the gap between people and the government.
        Citizens can raise complaints, request help, and share feedback directly through this system, while authorities can respond and track community needs efficiently.
      </p>

      <div class="row g-4">
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="feature-icon mb-3">📢</div>
              <h5 class="card-title fw-semibold">Report Complaints</h5>
              <p class="card-text">Easily file complaints about local issues like road damage, sanitation, or services.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="feature-icon mb-3">🤝</div>
              <h5 class="card-title fw-semibold">Ask for Help</h5>
              <p class="card-text">Request emergency assistance or help from local authorities in just a few clicks.</p>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card shadow-sm border-0">
            <div class="card-body">
              <div class="feature-icon mb-3">📊</div>
              <h5 class="card-title fw-semibold">Track Responses</h5>
              <p class="card-text">Stay informed on how your issues are being addressed, and get real-time updates.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Team Section -->
  <section class="py-5 text-center">
    <div class="container">
      <h2 class="fw-bold mb-4">Meet Our Team</h2>
      <p class="lead mb-5">The passionate developers and designers behind <strong>GovtConnect</strong>.</p>

      <div class="row g-4 justify-content-center">
        <!-- Member 1 -->
        <div class="col-md-3">
          <div class="card shadow-sm border-0 team-card p-4">
           <img src="images/injabin.jpeg" alt="Md Injabin Alam">
            <h5 class="fw-semibold">Md Injabin Alam</h5>
            <p class="text-muted mb-2">Full Stack developers</p>
            <p>Crafted the user interface and designed an efficient database architecture for the platform.</p>
          </div>
        </div>

        <!-- Member 2 -->
        <div class="col-md-3">
          <div class="card shadow-sm border-0 team-card p-4">
            <img src="images/s2.jpeg" alt="Md. Al Shahariyar">

            <h5 class="fw-semibold">Md. Al Shahariyar</h5>
            <p class="text-muted mb-2">Planner & Backend Expert</p>
            <p>Planned the project architecture and developed core backend functionality to make everything work seamlessly.</p>
          </div>
        </div>

        <!-- Member 3 -->
        <div class="col-md-3">
          <div class="card shadow-sm border-0 team-card p-4">
            <img src="images/manisha.jpeg" alt="Manisha Choudhury">
            <h5 class="fw-semibold">Manisha Choudhury</h5>
            <p class="text-muted mb-2">Presenter & Data Analytics</p>
            <p>Specialized in presentation, data analysis, and visualization of government and citizen engagement data.</p>
          </div>
        </div>

        <!-- Member 4 -->
        <div class="col-md-3">
          <div class="card shadow-sm border-0 team-card p-4">
            <img src="images/binita.jpeg" alt="Binita Gope">
            <h5 class="fw-semibold">Binita Gope</h5>
            <p class="text-muted mb-2">PHP Expert</p>
            <p>Focused on PHP development and integration to ensure smooth and efficient system operation.</p>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- Footer -->
  <footer>
    <p class="mb-0">&copy; <?php echo date('Y'); ?> GovtConnect — Built with ❤️ by the GovtConnect Team.</p>
  </footer>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
```
