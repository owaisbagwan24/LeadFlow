<?php
$user = $user ?? [
    'name' => $_SESSION['user_name'] ?? 'User',
    'role' => $_SESSION['user_role'] ?? 'Employee',
    'email' => $_SESSION['user_email'] ?? ''
];
$userName = htmlspecialchars($user['name'] ?? 'User');
$userRole = htmlspecialchars($user['role'] ?? 'Employee');
$userEmail = htmlspecialchars($user['email'] ?? '');
$initials = '';
foreach (explode(' ', trim($user['name'] ?? 'User')) as $p) {
    if (!empty($p)) $initials .= strtoupper($p[0]);
}
$initials = substr($initials, 0, 2) ?: 'U';

$breadcrumbCategory = $breadcrumbCategory ?? 'Dashboard';
$breadcrumbPage = $breadcrumbPage ?? 'Overview';
?>
      <!-- Sticky Topbar Navigation -->
      <header class="app-topbar">
        <div class="d-flex align-items-center gap-3">
          <!-- Mobile Hamburger Toggle -->
          <button type="button" class="btn btn-glass btn-icon d-lg-none" id="mobileSidebarToggle" aria-label="Toggle mobile menu">
            <i class="bi bi-list fs-5"></i>
          </button>

          <!-- Breadcrumbs -->
          <div class="d-none d-sm-block">
            <div class="breadcrumb-custom">
              <a href="/leadflow/dashboard">LeadFlow</a>
              <span class="separator"><i class="bi bi-chevron-right fs-8"></i></span>
              <span class="text-secondary"><?= htmlspecialchars($breadcrumbCategory) ?></span>
              <span class="separator"><i class="bi bi-chevron-right fs-8"></i></span>
              <span class="text-cyan"><?= htmlspecialchars($breadcrumbPage) ?></span>
            </div>
          </div>
        </div>

        <!-- Global Search -->
        <div class="topbar-search d-none d-md-block">
          <i class="bi bi-search search-icon"></i>
          <input type="text" id="globalSearchInput" class="form-control" placeholder="Search leads, companies, reps...">
          <span class="search-shortcut">Ctrl+K</span>
        </div>

        <!-- Topbar Action Items -->
        <div class="topbar-actions">
          <!-- Notification Dropdown -->
          <div class="dropdown">
            <button class="btn btn-glass btn-icon notification-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Notifications">
              <i class="bi bi-bell fs-6"></i>
              <span class="notification-indicator" id="unreadNotificationCount"></span>
            </button>
            <div class="dropdown-menu dropdown-menu-end dropdown-menu-dark-custom" style="width: 320px;">
              <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom border-white border-opacity-10">
                <span class="fw-semibold text-main fs-7">Notifications</span>
                <a href="#" class="fs-8 text-cyan" id="markNotificationsReadBtn">Mark all as read</a>
              </div>
              <div class="py-1">
                <a href="/leadflow/followups" class="dropdown-item dropdown-item-custom">
                  <div class="badge-dot bg-warning mt-1 me-2 notification-dot"></div>
                  <div>
                    <div class="text-main fs-7 fw-semibold">Upcoming Call with Sarah</div>
                    <div class="text-muted fs-8">In 45 minutes · TechCorp Solutions</div>
                  </div>
                </a>
                <a href="/leadflow/leads" class="dropdown-item dropdown-item-custom">
                  <div class="badge-dot bg-cyan mt-1 me-2 notification-dot"></div>
                  <div>
                    <div class="text-main fs-7 fw-semibold">New Inbound Lead</div>
                    <div class="text-muted fs-8">David Kim downloaded Whitepaper</div>
                  </div>
                </a>
                <a href="/leadflow/leads" class="dropdown-item dropdown-item-custom">
                  <div class="badge-dot bg-success mt-1 me-2 notification-dot"></div>
                  <div>
                    <div class="text-main fs-7 fw-semibold">Lead Converted!</div>
                    <div class="text-muted fs-8">Fintech Hub UK closed for $48k ARR</div>
                  </div>
                </a>
              </div>
            </div>
          </div>

          <!-- Quick Action Button -->
          <a href="/leadflow/leads" class="btn btn-primary-glow btn-sm d-none d-sm-inline-flex">
            <i class="bi bi-plus-lg"></i>
            <span>Add Lead</span>
          </a>

          <!-- User Profile Dropdown -->
          <div class="dropdown">
            <button class="btn btn-glass p-1 ps-2 pe-3 d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
              <div class="avatar avatar-sm"><?= $initials ?></div>
              <span class="fs-7 fw-semibold text-main d-none d-sm-inline"><?= $userName ?></span>
              <i class="bi bi-chevron-down fs-8 text-muted"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end dropdown-menu-dark-custom">
              <li class="px-3 py-2 border-bottom border-white border-opacity-10">
                <div class="fw-semibold text-main fs-7"><?= $userName ?></div>
                <div class="text-muted fs-8"><?= $userEmail ?></div>
                <span class="badge bg-purple-subtle text-purple fs-8 mt-1"><?= $userRole ?></span>
              </li>
              <li>
                <a class="dropdown-item dropdown-item-custom" href="/leadflow/settings">
                  <i class="bi bi-person"></i> My Profile
                </a>
              </li>
              <li>
                <a class="dropdown-item dropdown-item-custom" href="/leadflow/settings">
                  <i class="bi bi-gear"></i> Account Settings
                </a>
              </li>
              <li><hr class="dropdown-divider"></li>
              <li>
                <a class="dropdown-item dropdown-item-custom text-danger-custom" href="/leadflow/logout">
                  <i class="bi bi-box-arrow-right"></i> Sign Out
                </a>
              </li>
            </ul>
          </div>
        </div>
      </header>

