<?php
// Compute initials helper
function userInitials($name) {
    $parts = array_filter(explode(' ', trim($name)));
    $init = '';
    foreach ($parts as $p) $init .= strtoupper($p[0]);
    return substr($init, 0, 2) ?: 'U';
}

$isAdmin = (($_SESSION['user_role'] ?? '') === 'Admin');
$flashSuccess = $flashSuccess ?? null;
$flashError   = $flashError   ?? null;
?>
<!-- Main Users Page Body -->
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
      <h1 class="page-title">Users & Team Access</h1>
      <p class="text-secondary fs-7 mb-0">Manage team roles, account activation states and access permissions.</p>
    </div>
    <?php if ($isAdmin): ?>
    <div>
      <button type="button" class="btn btn-primary-glow" data-bs-toggle="modal" data-bs-target="#addUserModal">
        <i class="bi bi-person-plus"></i>
        <span>Add New Member</span>
      </button>
    </div>
    <?php endif; ?>
  </div>

  <!-- Team Stats Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-3 col-sm-6">
      <div class="kpi-card kpi-card-purple">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary fs-8 fw-semibold text-uppercase">Total Members</span>
          <div class="kpi-icon-box kpi-icon-purple"><i class="bi bi-people-fill"></i></div>
        </div>
        <div class="kpi-value mb-1"><?= (int)($totalUsers ?? 0) ?></div>
        <span class="text-muted fs-8">Registered accounts</span>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="kpi-card kpi-card-purple">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary fs-8 fw-semibold text-uppercase">Administrators</span>
          <div class="kpi-icon-box kpi-icon-purple"><i class="bi bi-shield-shaded"></i></div>
        </div>
        <div class="kpi-value mb-1 text-purple"><?= (int)($adminCount ?? 0) ?></div>
        <span class="text-muted fs-8">Full platform permissions</span>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="kpi-card kpi-card-cyan">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary fs-8 fw-semibold text-uppercase">Sales Reps</span>
          <div class="kpi-icon-box kpi-icon-cyan"><i class="bi bi-headset"></i></div>
        </div>
        <div class="kpi-value mb-1 text-cyan"><?= (int)($empCount ?? 0) ?></div>
        <span class="text-muted fs-8">Standard employee accounts</span>
      </div>
    </div>
    <div class="col-md-3 col-sm-6">
      <div class="kpi-card kpi-card-green">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary fs-8 fw-semibold text-uppercase">Active Accounts</span>
          <div class="kpi-icon-box kpi-icon-green"><i class="bi bi-card-checklist"></i></div>
        </div>
        <div class="kpi-value mb-1 text-success-custom"><?= (int)($activeCount ?? 0) ?></div>
        <span class="text-muted fs-8">Ready for assignment</span>
      </div>
    </div>
  </div>

  <!-- Users Table Card -->
  <div class="saas-card">
    <div class="p-3 border-bottom border-white border-opacity-10 d-flex flex-wrap align-items-center justify-content-between gap-3">
      <div class="d-flex align-items-center gap-2">
        <i class="bi bi-shield-check text-cyan fs-5"></i>
        <span class="fw-semibold text-main fs-6">Registered Team Members</span>
      </div>
      <?php if (!$isAdmin): ?>
      <div class="badge bg-warning-subtle text-warning fs-8">
        <i class="bi bi-eye me-1"></i>View Only — Admin access required to manage users
      </div>
      <?php endif; ?>
    </div>

    <div class="table-responsive-custom">
      <table class="table table-saas">
        <thead>
          <tr>
            <th>Team Member</th>
            <th>Email</th>
            <th>Role</th>
            <th>Status</th>
            <th>Joined</th>
            <?php if ($isAdmin): ?><th class="text-end">Actions</th><?php endif; ?>
          </tr>
        </thead>
        <tbody id="usersTableBody">
          <?php if (!empty($usersList)): ?>
            <?php foreach ($usersList as $u): ?>
              <?php
                $uInit    = userInitials($u['name']);
                $isUAdmin = ($u['role'] === 'Admin');
                $isActive = ($u['status'] === 'Active');
                $isSelf   = ((int)$u['id'] === (int)($_SESSION['user_id'] ?? 0));
              ?>
              <tr id="user-row-<?= $u['id'] ?>">
                <td>
                  <div class="d-flex align-items-center gap-2">
                    <div class="avatar avatar-sm <?= $isActive ? 'avatar-online' : '' ?>">
                      <?= htmlspecialchars($uInit) ?>
                    </div>
                    <div>
                      <div class="fw-semibold text-main">
                        <?= htmlspecialchars($u['name']) ?>
                        <?php if ($isSelf): ?>
                          <span class="badge bg-cyan-subtle text-cyan fs-8 ms-1">You</span>
                        <?php endif; ?>
                      </div>
                      <div class="text-muted fs-8"><?= $isUAdmin ? 'Administrator' : 'Sales Representative' ?></div>
                    </div>
                  </div>
                </td>
                <td class="text-secondary fs-7"><?= htmlspecialchars($u['email']) ?></td>
                <td>
                  <span class="badge-lead <?= $isUAdmin ? 'badge-role-admin' : 'badge-role-employee' ?>">
                    <i class="bi <?= $isUAdmin ? 'bi-shield-lock-fill' : 'bi-person-fill' ?> me-1"></i>
                    <?= htmlspecialchars($u['role']) ?>
                  </span>
                </td>
                <td>
                  <span class="badge-lead <?= $isActive ? 'badge-status-converted' : 'badge-status-lost' ?>"
                        id="status-badge-<?= $u['id'] ?>">
                    <span class="badge-dot"></span> <?= htmlspecialchars($u['status']) ?>
                  </span>
                </td>
                <td class="text-muted fs-8"><?= htmlspecialchars(date('M d, Y', strtotime($u['created_at']))) ?></td>
                <?php if ($isAdmin): ?>
                <td class="text-end">
                  <div class="action-btn-group justify-content-end">
                    <button type="button" class="action-btn btn-edit" title="Edit User"
                      onclick="openEditUserModal(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>', '<?= htmlspecialchars(addslashes($u['email'])) ?>', '<?= $u['role'] ?>')">
                      <i class="bi bi-pencil"></i>
                    </button>
                    <?php if (!$isSelf): ?>
                    <button type="button" class="action-btn <?= $isActive ? 'btn-view' : 'btn-edit' ?>" title="<?= $isActive ? 'Deactivate' : 'Activate' ?>"
                      onclick="toggleUserStatus(<?= $u['id'] ?>, '<?= $u['status'] ?>')">
                      <i class="bi <?= $isActive ? 'bi-toggle-on' : 'bi-toggle-off' ?>"></i>
                    </button>
                    <button type="button" class="action-btn btn-delete" title="Delete User"
                      onclick="openDeleteUserModal(<?= $u['id'] ?>, '<?= htmlspecialchars(addslashes($u['name'])) ?>')">
                      <i class="bi bi-trash"></i>
                    </button>
                    <?php endif; ?>
                  </div>
                </td>
                <?php endif; ?>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">No team members registered yet.</td>
            </tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</main>

<!-- ════════════════════════════════════════
     ADD USER MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-content-custom">
      <div class="modal-header modal-header-custom">
        <h5 class="modal-title modal-title-custom">
          <i class="bi bi-person-plus text-purple me-2"></i>Add Team Member
        </h5>
        <button type="button" class="btn-close-custom" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <form id="addUserForm" method="POST" action="/leadflow/users/store">
        <div class="modal-body modal-body-custom">
          <div class="alert alert-dark border border-secondary border-opacity-25 py-2 px-3 mb-3 fs-8 text-secondary">
            <i class="bi bi-shield-lock text-cyan me-1"></i> Role assignment is restricted to Administrators only.
          </div>
          <div class="row g-3">
            <div class="col-12">
              <label for="newUserName" class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="newUserName" name="name" placeholder="e.g. Rachel Adams" required>
            </div>
            <div class="col-12">
              <label for="newUserEmail" class="form-label">Company Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="newUserEmail" name="email" placeholder="rachel@leadflow.io" required>
            </div>
            <div class="col-md-6">
              <label for="newUserRole" class="form-label">System Role <span class="text-danger">*</span></label>
              <select class="form-select" id="newUserRole" name="role" required>
                <option value="Employee" selected>Employee (Sales Rep)</option>
                <option value="Admin">Admin (Full Access)</option>
              </select>
            </div>
            <div class="col-md-6">
              <label for="newUserTempPass" class="form-label">Password <span class="text-danger">*</span></label>
              <input type="password" class="form-control" id="newUserTempPass" name="password" placeholder="Min 8 characters" required>
            </div>
          </div>
        </div>
        <div class="modal-footer modal-footer-custom">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-glow">
            <i class="bi bi-person-check"></i>
            <span>Create Account</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════
     EDIT USER MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-content-custom">
      <div class="modal-header modal-header-custom">
        <h5 class="modal-title modal-title-custom">
          <i class="bi bi-pencil-square text-cyan me-2"></i>Edit Team Member
        </h5>
        <button type="button" class="btn-close-custom" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <form id="editUserForm" method="POST" action="/leadflow/users/update">
        <input type="hidden" id="editUserId" name="id" value="">
        <div class="modal-body modal-body-custom">
          <div class="row g-3">
            <div class="col-12">
              <label for="editUserName" class="form-label">Full Name <span class="text-danger">*</span></label>
              <input type="text" class="form-control" id="editUserName" name="name" required>
            </div>
            <div class="col-12">
              <label for="editUserEmail" class="form-label">Email <span class="text-danger">*</span></label>
              <input type="email" class="form-control" id="editUserEmail" name="email" required>
            </div>
            <div class="col-12">
              <label for="editUserRole" class="form-label">Role</label>
              <select class="form-select" id="editUserRole" name="role">
                <option value="Employee">Employee</option>
                <option value="Admin">Admin</option>
              </select>
            </div>
          </div>
        </div>
        <div class="modal-footer modal-footer-custom">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-primary-glow">
            <i class="bi bi-check-lg"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════
     DELETE USER CONFIRMATION MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content modal-content-custom">
      <div class="modal-header modal-header-custom">
        <h5 class="modal-title modal-title-custom text-danger">
          <i class="bi bi-exclamation-triangle me-2"></i>Delete User
        </h5>
        <button type="button" class="btn-close-custom" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body modal-body-custom">
        <p class="text-secondary fs-7">Are you sure you want to delete <strong id="deleteUserName" class="text-main"></strong>? This action cannot be undone.</p>
      </div>
      <div class="modal-footer modal-footer-custom">
        <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteUserBtn">Delete User</button>
      </div>
    </div>
  </div>
</div>

<script>
let userToDeleteId = null;

function openEditUserModal(id, name, email, role) {
  document.getElementById('editUserId').value   = id;
  document.getElementById('editUserName').value  = name;
  document.getElementById('editUserEmail').value = email;
  document.getElementById('editUserRole').value  = role;
  const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editUserModal'));
  modal.show();
}

function openDeleteUserModal(id, name) {
  userToDeleteId = id;
  document.getElementById('deleteUserName').textContent = name;
  const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('deleteUserModal'));
  modal.show();
}

document.getElementById('confirmDeleteUserBtn')?.addEventListener('click', () => {
  if (!userToDeleteId) return;
  const fd = new FormData();
  fd.append('id', userToDeleteId);
  fetch('/leadflow/users/delete', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: fd
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const row = document.getElementById('user-row-' + userToDeleteId);
      if (row) row.remove();
      bootstrap.Modal.getInstance(document.getElementById('deleteUserModal'))?.hide();
      if (typeof showToast === 'function') showToast('Deleted', data.message, 'danger');
    } else {
      if (typeof showToast === 'function') showToast('Error', data.error || 'Delete failed.', 'warning');
    }
    userToDeleteId = null;
  });
});

function toggleUserStatus(id, currentStatus) {
  const fd = new FormData();
  fd.append('id', id);
  fetch('/leadflow/users/toggle-status', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: fd
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const badge = document.getElementById('status-badge-' + id);
      if (badge) {
        const isNowActive = data.newStatus === 'Active';
        badge.className = 'badge-lead ' + (isNowActive ? 'badge-status-converted' : 'badge-status-lost');
        badge.innerHTML = '<span class="badge-dot"></span> ' + data.newStatus;
      }
      if (typeof showToast === 'function')
        showToast('Status Changed', 'Account is now ' + data.newStatus + '.', 'success');
    } else {
      if (typeof showToast === 'function') showToast('Error', data.error || 'Failed.', 'warning');
    }
  });
}
</script>
