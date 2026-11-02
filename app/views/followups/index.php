<?php
// JSON for JS: Inject real follow-ups from MySQL
$fuJson = json_encode(array_map(function($f) {
    return [
        'id'            => (int)$f['id'],
        'leadId'        => (int)$f['lead_id'],
        'leadCode'      => $f['lead_code'] ?? '',
        'customerName'  => $f['customer_name'] ?? 'Unknown',
        'company'       => $f['company_name'] ?? '—',
        'activityType'  => $f['activity_type'],
        'dueDate'       => $f['due_date'],
        'status'        => $f['status'],
        'assignedTo'    => $f['assigned_to_name'] ?? 'Unassigned',
        'notes'         => $f['notes'] ?? '',
        'completedAt'   => $f['completed_at'] ?? null
    ];
}, $followupsList ?? []), JSON_HEX_TAG | JSON_HEX_APOS);

$counts    = $tabCounts   ?? ['all'=>0,'upcoming'=>0,'overdue'=>0,'completed'=>0];
$activeTab = $activeTab   ?? 'all';

// Build leads options for the modal dropdown
$leadsOptions = '';
foreach (($leadsList ?? []) as $l) {
    $leadsOptions .= '<option value="' . (int)$l['id'] . '">'
        . htmlspecialchars($l['lead_code'] . ' – ' . $l['name']) . '</option>';
}

// Flash messages
$flashSuccess = $_SESSION['flash_success'] ?? null;
$flashError   = $_SESSION['flash_error']   ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<script>window.serverFollowUpsData = <?= $fuJson ?>;</script>

<!-- Main Follow-ups Page Body -->
<main class="page-content">

  <?php if ($flashSuccess): ?>
    <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
      <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($flashSuccess) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>
  <?php if ($flashError): ?>
    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
      <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($flashError) ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <!-- Page Header -->
  <div class="page-header">
    <div>
      <h1 class="page-title">Follow-up Activities</h1>
      <p class="text-secondary fs-7 mb-0">Track every scheduled call, meeting and email outreach.</p>
    </div>
    <div class="d-flex gap-2">
      <a href="/leadflow/followups/export" class="btn btn-glass btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export CSV
      </a>
      <button type="button" class="btn btn-primary-glow" data-bs-toggle="modal" data-bs-target="#scheduleFollowupModal">
        <i class="bi bi-plus-circle"></i>
        <span>Schedule New Follow-up</span>
      </button>
    </div>
  </div>

  <!-- Filter Tabs & Stats Bar -->
  <div class="saas-card p-3 mb-4">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
      <ul class="nav nav-pills nav-pills-custom" role="tablist">
        <li class="nav-item">
          <button class="nav-link followup-tab-link <?= $activeTab==='all' ? 'active' : '' ?>" data-filter="all">
            All Activities <span class="badge bg-dark ms-1" id="countAllFollowups"><?= $counts['all'] ?></span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link followup-tab-link <?= $activeTab==='upcoming' ? 'active' : '' ?>" data-filter="upcoming">
            Upcoming <span class="badge bg-purple-subtle text-purple ms-1" id="countUpcomingFollowups"><?= $counts['upcoming'] ?></span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link followup-tab-link <?= $activeTab==='overdue' ? 'active' : '' ?>" data-filter="overdue">
            Overdue <span class="badge bg-danger-subtle text-danger ms-1" id="countOverdueFollowups"><?= $counts['overdue'] ?></span>
          </button>
        </li>
        <li class="nav-item">
          <button class="nav-link followup-tab-link <?= $activeTab==='completed' ? 'active' : '' ?>" data-filter="completed">
            Completed <span class="badge bg-success-subtle text-success ms-1" id="countCompletedFollowups"><?= $counts['completed'] ?></span>
          </button>
        </li>
      </ul>
      <div class="d-flex align-items-center gap-2">
        <span class="fs-8 text-muted"><i class="bi bi-info-circle me-1"></i>Check the box to mark a task completed.</span>
      </div>
    </div>
  </div>

  <!-- Follow-ups List Container -->
  <div class="saas-card p-3 p-md-4">
    <!-- Empty State -->
    <div id="followupsEmptyState" class="empty-state d-none">
      <div class="empty-state-icon">
        <i class="bi bi-check2-circle text-success-custom"></i>
      </div>
      <h3 class="h5 fw-bold text-main mb-1">No Follow-ups in this View</h3>
      <p class="text-secondary fs-7 col-md-6 mx-auto mb-3">
        You're all caught up! No reminders pending in this category.
      </p>
      <button class="btn btn-primary-glow btn-sm" data-bs-toggle="modal" data-bs-target="#scheduleFollowupModal">
        <i class="bi bi-calendar-plus me-1"></i> Schedule One Now
      </button>
    </div>

    <!-- Dynamic List Container -->
    <div id="followupsListContainer"></div>
  </div>
</main>

<!-- ════════════════════════════════════════
     SCHEDULE / ADD FOLLOW-UP MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="scheduleFollowupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content modal-content-custom">
      <div class="modal-header modal-header-custom">
        <h5 class="modal-title modal-title-custom">
          <i class="bi bi-calendar-plus text-cyan me-2"></i>
          <span id="followupModalTitle">Schedule Follow-up</span>
        </h5>
        <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>
      <form id="scheduleFollowupForm">
        <input type="hidden" id="fuId" name="id" value="">
        <div class="modal-body modal-body-custom">
          <div class="row g-3">
            <div class="col-12">
              <label for="fuLeadId" class="form-label">Lead <span class="text-danger">*</span></label>
              <select class="form-select" id="fuLeadId" name="lead_id" required>
                <option value="">— Select Lead —</option>
                <?= $leadsOptions ?>
              </select>
            </div>

            <div class="col-md-6">
              <label for="fuType" class="form-label">Activity Type</label>
              <select class="form-select" id="fuType" name="activity_type">
                <option value="Phone Call">Phone Call</option>
                <option value="Video Call">Video Call</option>
                <option value="Email">Email</option>
                <option value="In-Person Meeting">In-Person Meeting</option>
                <option value="Demo">Demo</option>
              </select>
            </div>

            <div class="col-md-6">
              <label for="fuStatus" class="form-label">Status</label>
              <select class="form-select" id="fuStatus" name="status">
                <option value="upcoming">Upcoming</option>
                <option value="overdue">Overdue</option>
                <option value="completed">Completed</option>
              </select>
            </div>

            <div class="col-12">
              <label for="fuDueDate" class="form-label">Due Date <span class="text-danger">*</span></label>
              <input type="datetime-local" class="form-control" id="fuDueDate" name="due_date" required>
            </div>

            <div class="col-12">
              <label for="fuNotes" class="form-label">Notes / Agenda</label>
              <textarea class="form-control" id="fuNotes" name="notes" rows="3"
                placeholder="Outline what needs to be discussed..."></textarea>
            </div>
          </div>
        </div>

        <div class="modal-footer modal-footer-custom">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-cyan-glow">
            <i class="bi bi-calendar-check"></i>
            <span id="followupSubmitLabel">Schedule Activity</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- ════════════════════════════════════════
     DELETE CONFIRMATION MODAL
════════════════════════════════════════ -->
<div class="modal fade" id="deleteFollowupModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content modal-content-custom">
      <div class="modal-header modal-header-custom">
        <h5 class="modal-title modal-title-custom">
          <i class="bi bi-trash text-danger me-2"></i>Delete Follow-up
        </h5>
        <button type="button" class="btn-close-custom" data-bs-dismiss="modal"><i class="bi bi-x-lg"></i></button>
      </div>
      <div class="modal-body modal-body-custom">
        <p class="text-secondary fs-7 mb-0">Are you sure you want to permanently delete this follow-up reminder?</p>
      </div>
      <div class="modal-footer modal-footer-custom">
        <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmDeleteFollowupBtn">Delete</button>
      </div>
    </div>
  </div>
</div>
