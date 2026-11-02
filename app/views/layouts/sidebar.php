<?php
$activePage = $activePage ?? 'dashboard';
$user = $user ?? [
    'name' => $_SESSION['user_name'] ?? 'User',
    'role' => $_SESSION['user_role'] ?? 'Employee',
    'email' => $_SESSION['user_email'] ?? ''
];
$userName = htmlspecialchars($user['name'] ?? 'User');
$userRole = htmlspecialchars($user['role'] ?? 'Employee');
$initials = '';
foreach (explode(' ', trim($user['name'] ?? 'User')) as $p) {
    if (!empty($p)) $initials .= strtoupper($p[0]);
}
$initials = substr($initials, 0, 2) ?: 'U';
?>
    <!-- Collapsible Sidebar -->
    <aside class="app-sidebar" id="appSidebar">
      <div class="sidebar-header">
        <a href="/leadflow/dashboard" class="brand-logo">
          <div class="brand-icon">
            <i class="bi bi-intersect"></i>
          </div>
          <span class="brand-name">Lead<span class="text-cyan">Flow</span></span>
        </a>
        <button type="button" class="btn btn-glass btn-icon d-none d-lg-flex" id="sidebarToggleBtn" title="Toggle Sidebar">
          <i class="bi bi-chevron-bar-left text-muted"></i>
        </button>
      </div>

      <div class="sidebar-menu">
        <div class="menu-section-label">Main Menu</div>
        
        <a href="/leadflow/dashboard" class="nav-link-custom <?= $activePage === 'dashboard' ? 'active' : '' ?>">
          <i class="bi bi-grid-1x2-fill"></i>
          <span class="nav-text">Overview</span>
        </a>
        
        <a href="/leadflow/leads" class="nav-link-custom <?= $activePage === 'leads' ? 'active' : '' ?>">
          <i class="bi bi-people-fill"></i>
          <span class="nav-text">All Leads</span>
          <span class="badge bg-purple-subtle text-purple ms-auto nav-badge fs-8 fw-semibold" id="sidebarLeadCount"><?= (int)($sidebarLeadCount ?? 0) ?></span>
        </a>

        <a href="/leadflow/followups" class="nav-link-custom <?= $activePage === 'followups' ? 'active' : '' ?>">
          <i class="bi bi-calendar-check-fill"></i>
          <span class="nav-text">Follow-ups</span>
          <span class="badge bg-cyan-subtle text-cyan ms-auto nav-badge fs-8 fw-semibold" id="sidebarFollowupCount"><?= (int)($sidebarFollowupCount ?? 0) ?></span>
        </a>

        <div class="menu-section-label mt-3">Team & Insights</div>

        <a href="/leadflow/users" class="nav-link-custom <?= $activePage === 'users' ? 'active' : '' ?>">
          <i class="bi bi-person-badge-fill"></i>
          <span class="nav-text">Users & Team</span>
        </a>

        <a href="/leadflow/reports" class="nav-link-custom <?= $activePage === 'reports' ? 'active' : '' ?>">
          <i class="bi bi-bar-chart-fill"></i>
          <span class="nav-text">Reports & Analytics</span>
        </a>

        <a href="/leadflow/settings" class="nav-link-custom <?= $activePage === 'settings' ? 'active' : '' ?>">
          <i class="bi bi-gear-fill"></i>
          <span class="nav-text">Settings</span>
        </a>
      </div>

      <div class="sidebar-footer">
        <div class="user-mini-card">
          <div class="avatar avatar-online"><?= $initials ?></div>
          <div class="user-details overflow-hidden">
            <div class="fw-semibold text-main fs-7 text-truncate"><?= $userName ?></div>
            <div class="text-muted fs-8 text-truncate"><?= $userRole ?></div>
          </div>
          <a href="/leadflow/logout" class="btn btn-glass btn-icon ms-auto" title="Sign Out">
            <i class="bi bi-box-arrow-right text-danger-custom fs-7"></i>
          </a>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="app-main">
