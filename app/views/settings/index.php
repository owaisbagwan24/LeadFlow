<?php
$user = $user ?? [
    'name'  => $_SESSION['user_name']  ?? 'User',
    'email' => $_SESSION['user_email'] ?? '',
    'role'  => $_SESSION['user_role']  ?? 'Employee'
];
$uInitials = '';
foreach (explode(' ', trim($user['name'])) as $p) {
    if (!empty($p)) $uInitials .= strtoupper($p[0]);
}
$uInitials    = substr($uInitials, 0, 2) ?: 'U';
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError   ?? null;
?>
<!-- Main Settings Page Body -->
<main class="page-content">

  <?php if ($flashSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show mb-3">
      <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flashSuccess) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if ($flashError): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-3">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($flashError) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="page-header">
    <div>
      <h1 class="page-title">Account & System Preferences</h1>
      <p class="text-secondary fs-7 mb-0">Configure your profile details, password security, and workspace options.</p>
    </div>
  </div>

  <div class="row g-4">
    <!-- Left Column: Navigation Tabs -->
    <div class="col-lg-3">
      <div class="saas-card p-2">
        <div class="nav flex-column nav-pills nav-pills-custom" id="settingsTab" role="tablist">
          <button class="nav-link active text-start py-2 px-3 mb-1" id="tab-profile"
            data-bs-toggle="pill" data-bs-target="#content-profile" type="button" role="tab">
            <i class="bi bi-person-circle me-2 text-cyan"></i>Personal Profile
          </button>
          <button class="nav-link text-start py-2 px-3 mb-1" id="tab-security"
            data-bs-toggle="pill" data-bs-target="#content-security" type="button" role="tab">
            <i class="bi bi-shield-lock me-2 text-purple"></i>Password & Security
          </button>
          <button class="nav-link text-start py-2 px-3 mb-1" id="tab-display"
            data-bs-toggle="pill" data-bs-target="#content-display" type="button" role="tab">
            <i class="bi bi-palette me-2 text-info"></i>Display & System
          </button>
          <hr class="border-white border-opacity-10 my-2">
          <a href="/leadflow/logout" class="nav-link text-start py-2 px-3 text-danger">
            <i class="bi bi-box-arrow-right me-2"></i>Logout
          </a>
        </div>
      </div>
    </div>

    <!-- Right Column: Tab Panes -->
    <div class="col-lg-9">
      <div class="tab-content" id="settingsTabContent">

        <!-- 1. Profile Tab -->
        <div class="tab-pane fade show active" id="content-profile" role="tabpanel">
          <div class="saas-card p-3 p-md-4">
            <h3 class="h6 fw-bold text-main mb-1">Personal Information</h3>
            <p class="text-secondary fs-8 mb-4">Your current workspace identity and contact information.</p>

            <form id="profileForm" method="POST" action="/leadflow/settings/profile">
              <!-- Avatar Row -->
              <div class="d-flex align-items-center gap-4 mb-4 pb-4 border-bottom border-white border-opacity-10">
                <div class="avatar avatar-lg" id="profileAvatarBig" style="font-size: 1.5rem;"><?= htmlspecialchars($uInitials) ?></div>
                <div>
                  <div class="fw-semibold text-main fs-7 mb-1"><?= htmlspecialchars($user['name']) ?></div>
                  <div class="text-muted fs-8"><?= htmlspecialchars($user['role']) ?> Account</div>
                </div>
              </div>

              <div class="row g-3">
                <div class="col-md-6">
                  <label for="profileFullName" class="form-label">Full Name <span class="text-danger">*</span></label>
                  <input type="text" class="form-control" id="profileFullName" name="name"
                    value="<?= htmlspecialchars($user['name']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="profileEmail" class="form-label">Work Email <span class="text-danger">*</span></label>
                  <input type="email" class="form-control" id="profileEmail" name="email"
                    value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>
                <div class="col-md-6">
                  <label for="profileRole" class="form-label">Assigned Role</label>
                  <input type="text" class="form-control text-muted" id="profileRole"
                    value="<?= htmlspecialchars($user['role']) ?>" disabled>
                </div>
              </div>

              <div class="mt-4 pt-3 border-top border-white border-opacity-10 d-flex justify-content-end">
                <button type="submit" class="btn btn-primary-glow">
                  <i class="bi bi-check-lg"></i> Save Profile Changes
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- 2. Password & Security Tab -->
        <div class="tab-pane fade" id="content-security" role="tabpanel">
          <div class="saas-card p-3 p-md-4">
            <h3 class="h6 fw-bold text-main mb-1">Password & Authentication</h3>
            <p class="text-secondary fs-8 mb-4">Use a strong, unique password. After changing, you will need to log in again.</p>

            <form id="passwordForm" method="POST" action="/leadflow/settings/password">
              <div class="row g-3 mb-4">
                <div class="col-12">
                  <label for="currentPassword" class="form-label">Current Password</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-lock"></i></span>
                    <input type="password" class="form-control" id="currentPassword"
                      name="current_password" placeholder="••••••••" required>
                    <button class="btn btn-glass toggle-password-btn" type="button" data-target="currentPassword">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>
                <div class="col-md-6">
                  <label for="newPassword" class="form-label">New Password</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-key"></i></span>
                    <input type="password" class="form-control" id="newPassword"
                      name="new_password" placeholder="Minimum 8 characters" required>
                    <button class="btn btn-glass toggle-password-btn" type="button" data-target="newPassword">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>
                <div class="col-md-6">
                  <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                  <div class="input-group">
                    <span class="input-group-text"><i class="bi bi-shield-check"></i></span>
                    <input type="password" class="form-control" id="confirmNewPassword"
                      name="new_password_confirmation" placeholder="Re-type new password" required>
                    <button class="btn btn-glass toggle-password-btn" type="button" data-target="confirmNewPassword">
                      <i class="bi bi-eye"></i>
                    </button>
                  </div>
                </div>
              </div>
              <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary-glow">
                  <i class="bi bi-shield-check"></i> Update Password
                </button>
              </div>
            </form>
          </div>
        </div>

        <!-- 3. Display & System Tab -->
        <div class="tab-pane fade" id="content-display" role="tabpanel">
          <div class="saas-card p-3 p-md-4">
            <h3 class="h6 fw-bold text-main mb-1">System & Interface Options</h3>
            <p class="text-secondary fs-8 mb-4">Customize regional formatting and data table defaults.</p>

            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="currencySetting" class="form-label">Currency Symbol</label>
                <select class="form-select" id="currencySetting">
                  <option value="USD" selected>USD ($) — United States Dollar</option>
                  <option value="EUR">EUR (€) — Euro</option>
                  <option value="GBP">GBP (£) — British Pound</option>
                  <option value="INR">INR (₹) — Indian Rupee</option>
                </select>
              </div>
              <div class="col-md-6">
                <label for="dateFormatSetting" class="form-label">Date Format</label>
                <select class="form-select" id="dateFormatSetting">
                  <option value="YYYY-MM-DD" selected>YYYY-MM-DD (ISO)</option>
                  <option value="MM/DD/YYYY">MM/DD/YYYY (US)</option>
                  <option value="DD/MM/YYYY">DD/MM/YYYY (UK/EU)</option>
                </select>
              </div>
            </div>

            <div class="alert alert-dark border border-secondary border-opacity-25 py-2 px-3 fs-8 text-secondary">
              <i class="bi bi-info-circle text-cyan me-1"></i>
              LeadFlow uses a fixed SaaS Deep Dark theme. Interface theme switching is not available in this version.
            </div>
          </div>
        </div>

      </div><!-- /.tab-content -->
    </div><!-- /.col-lg-9 -->
  </div><!-- /.row -->
</main>
