<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Sign In — LeadFlow SaaS</title>
  <meta name="description" content="Sign in to LeadFlow - The modern SaaS Lead Management System to accelerate your sales pipeline.">
  
  <!-- Favicon & Fonts -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  
  <!-- Bootstrap 5 CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <!-- Bootstrap Icons -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <!-- Custom Design System Stylesheet -->
  <link rel="stylesheet" href="/leadflow/assets/css/style.css">
</head>
<body>

  <div class="auth-wrapper">
    <!-- Floating Background Orbs -->
    <div class="auth-bg-orb auth-bg-orb-1"></div>
    <div class="auth-bg-orb auth-bg-orb-2"></div>
    <div class="auth-bg-orb auth-bg-orb-3"></div>

    <!-- Left Section: Hero & Value Proposition -->
    <section class="auth-hero-section">
      <div class="auth-hero-top">
        <a href="/leadflow/login" class="brand-logo mb-4">
          <div class="brand-icon">
            <i class="bi bi-intersect"></i>
          </div>
          <span class="brand-name">Lead<span class="text-cyan">Flow</span></span>
        </a>
      </div>

      <div class="auth-hero-middle my-auto py-4">
        <div class="badge bg-purple-subtle text-purple border border-purple border-opacity-25 px-3 py-2 rounded-pill mb-3 fs-7 fw-semibold">
          <i class="bi bi-lightning-charge-fill text-cyan me-1"></i> Next-Gen Lead Intelligence
        </div>
        
        <h1 class="display-5 fw-bold text-main lh-tight mb-3">
          Turn Every Lead <br>
          <span style="background: linear-gradient(135deg, #7C3AED 0%, #22D3EE 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">
            Into An Opportunity
          </span>
        </h1>
        
        <p class="text-secondary fs-6 mb-4 col-lg-10">
          Manage your leads, build stronger relationships, and accelerate your growth with automated pipeline tracking and real-time conversion insights.
        </p>

        <!-- Abstract Interactive Stats Graphic -->
        <div class="auth-graphic-box">
          <div class="auth-graphic-glow"></div>
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="d-flex align-items-center gap-2">
              <span class="badge-dot" style="background:#22C55E; box-shadow: 0 0 8px #22C55E;"></span>
              <span class="text-main fw-semibold fs-7">Live Pipeline Velocity</span>
            </div>
            <span class="badge bg-success-subtle text-success fs-8 fw-semibold">+24.8% This Month</span>
          </div>

          <div class="row g-3">
            <div class="col-6">
              <div class="p-3 rounded-3" style="background: rgba(11, 16, 32, 0.6); border: 1px solid rgba(255, 255, 255, 0.05);">
                <div class="text-muted fs-8 mb-1">Active Pipeline</div>
                <div class="text-main fw-bold fs-5">$184,500</div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 rounded-3" style="background: rgba(11, 16, 32, 0.6); border: 1px solid rgba(255, 255, 255, 0.05);">
                <div class="text-muted fs-8 mb-1">Conversion Rate</div>
                <div class="text-cyan fw-bold fs-5">28.4%</div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="auth-hero-bottom text-muted fs-8">
        &copy; 2026 LeadFlow Technologies Inc. Designed for high-velocity sales teams.
      </div>
    </section>

    <!-- Right Section: Login Glass Card Form -->
    <section class="auth-form-section">
      <div class="auth-card">
        <!-- Mobile Logo fallback -->
        <div class="d-lg-none mb-4 text-center">
          <a href="/leadflow/login" class="brand-logo justify-content-center">
            <div class="brand-icon"><i class="bi bi-intersect"></i></div>
            <span class="brand-name">Lead<span class="text-cyan">Flow</span></span>
          </a>
        </div>

        <div class="mb-4">
          <h2 class="h4 fw-bold text-main mb-1">Welcome Back</h2>
          <p class="text-secondary fs-7 mb-0">Enter your credentials to access your sales workspace.</p>
        </div>

        <!-- Feedback Alerts -->
        <?php if (!empty($error)): ?>
          <div class="alert alert-danger border-0 bg-danger-subtle text-danger py-2 px-3 mb-3 fs-8 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0"></i>
            <div><?= htmlspecialchars($error) ?></div>
          </div>
        <?php endif; ?>

        <?php if (!empty($success)): ?>
          <div class="alert alert-success border-0 bg-success-subtle text-success py-2 px-3 mb-3 fs-8 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-6 flex-shrink-0"></i>
            <div><?= htmlspecialchars($success) ?></div>
          </div>
        <?php endif; ?>

        <!-- Quick Demo Credential Helper Chip -->
        <div id="demoCredChip" class="p-2 mb-3 rounded-3 d-flex align-items-center justify-content-between" 
             style="background: rgba(124, 58, 237, 0.12); border: 1px dashed rgba(124, 58, 237, 0.35); cursor: pointer;"
             title="Click to fill sample credentials">
          <div class="d-flex align-items-center gap-2">
            <i class="bi bi-magic text-cyan fs-6"></i>
            <span class="fs-8 text-secondary">Demo Mode: <strong>admin@leadflow.io</strong></span>
          </div>
          <span class="badge bg-purple-subtle text-purple fs-8">Auto-fill</span>
        </div>

        <!-- Login Form -->
        <form id="loginForm" method="POST" action="/leadflow/login">
          <!-- Email Input -->
          <div class="mb-3">
            <label for="loginEmail" class="form-label">Email Address</label>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-envelope"></i></span>
              <input type="email" id="loginEmail" name="email" class="form-control" placeholder="name@company.com" required autocomplete="email" value="<?= htmlspecialchars($old['email'] ?? '') ?>">
            </div>
            <div class="invalid-feedback">Please provide a valid work email.</div>
          </div>

          <!-- Password Input with Show/Hide -->
          <div class="mb-3">
            <div class="d-flex align-items-center justify-content-between mb-1">
              <label for="loginPassword" class="form-label mb-0">Password</label>
              <a href="#" class="fs-8 text-cyan" onclick="if(typeof showToast === 'function') showToast('Password Reset', 'Please contact your administrator to reset your password.', 'info'); return false;">
                Forgot password?
              </a>
            </div>
            <div class="input-group">
              <span class="input-group-text"><i class="bi bi-lock"></i></span>
              <input type="password" id="loginPassword" name="password" class="form-control" placeholder="••••••••" required autocomplete="current-password">
              <button class="btn btn-glass toggle-password-btn" type="button" data-target="loginPassword" aria-label="Toggle password visibility">
                <i class="bi bi-eye"></i>
              </button>
            </div>
            <div class="invalid-feedback">Please enter your password.</div>
          </div>

          <!-- Remember Me -->
          <div class="mb-4 d-flex align-items-center justify-content-between">
            <div class="form-check">
              <input class="form-check-input" type="checkbox" id="rememberMe" name="remember_me">
              <label class="form-check-label fs-8" for="rememberMe">Remember me on this device</label>
            </div>
          </div>

          <!-- Submit Button -->
          <button type="submit" id="loginSubmitBtn" class="btn btn-primary-glow w-100 py-2 mb-3">
            <span>Sign In to LeadFlow</span>
            <i class="bi bi-arrow-right"></i>
          </button>

          <!-- Register Link -->
          <div class="text-center text-secondary fs-8">
            Don't have an account yet? 
            <a href="/leadflow/register" class="text-cyan fw-semibold ms-1">Create an account</a>
          </div>
        </form>

        <!-- System Architecture Notice -->
        <div class="mt-4 pt-3 border-top border-white border-opacity-10 text-center">
          <span class="text-muted fs-8">
            <i class="bi bi-shield-check text-purple me-1"></i> Powered by LeadFlow Core PHP MVC &amp; MySQL
          </span>
        </div>
      </div>
    </section>
  </div>

  <!-- Toast Notifications Container -->
  <div id="toastContainer" class="toast-container-custom"></div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="/leadflow/assets/js/app.js"></script>
  <script src="/leadflow/assets/js/auth.js"></script>
</body>
</html>
