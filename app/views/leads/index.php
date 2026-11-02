<?php
$serializedLeads = array_map(function($l) {
    return [
        'id' => $l['lead_code'],
        'db_id' => (int)$l['id'],
        'name' => $l['name'],
        'email' => $l['email'],
        'phone' => $l['phone'] ?? '',
        'company' => $l['company'],
        'source' => $l['lead_source'],
        'status' => $l['status'],
        'priority' => $l['priority'],
        'assignedTo' => $l['assigned_to_name'] ?? 'Unassigned',
        'assignedToId' => $l['assigned_to_id'],
        'createdAt' => date('Y-m-d', strtotime($l['created_at'])),
        'notes' => $l['notes'] ?? '',
        'followUpDate' => !empty($l['follow_up_date']) ? date('Y-m-d\TH:i', strtotime($l['follow_up_date'])) : ''
    ];
}, $leadsList ?? []);
?>
<script>
window.serverLeadsData = <?= json_encode($serializedLeads, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;
</script>

      <!-- Main Leads Management Page Body -->
      <main class="page-content">
        <!-- Feedback Alerts -->
        <?php if (!empty($_SESSION['flash_success'])): ?>
          <div class="alert alert-success border-0 bg-success-subtle text-success py-2 px-3 mb-3 fs-8 d-flex align-items-center gap-2">
            <i class="bi bi-check-circle-fill fs-6 flex-shrink-0"></i>
            <div><?= htmlspecialchars($_SESSION['flash_success']) ?></div>
          </div>
          <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['flash_error'])): ?>
          <div class="alert alert-danger border-0 bg-danger-subtle text-danger py-2 px-3 mb-3 fs-8 d-flex align-items-center gap-2">
            <i class="bi bi-exclamation-triangle-fill fs-6 flex-shrink-0"></i>
            <div><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
          </div>
          <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>

        <!-- Page Header -->
        <div class="page-header">
          <div>
            <h1 class="page-title">Lead Management</h1>
            <p class="text-secondary fs-7 mb-0">Search, filter, update, and manage your complete sales opportunity database.</p>
          </div>
          <div class="d-flex align-items-center gap-2">
            <a href="/leadflow/leads/export" class="btn btn-glass" id="exportLeadsCsvBtn">
              <i class="bi bi-filetype-csv text-cyan"></i>
              <span>Export CSV</span>
            </a>
            <button type="button" class="btn btn-primary-glow" onclick="openAddLeadModal()">
              <i class="bi bi-plus-circle"></i>
              <span>Create Lead</span>
            </button>
          </div>
        </div>

        <!-- Filter & Search Toolbar Card -->
        <div class="saas-card p-3 mb-4">
          <div class="row g-3 align-items-center">
            <!-- Search Field -->
            <div class="col-lg-4 col-md-12">
              <div class="position-relative">
                <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-3 text-muted"></i>
                <input type="text" id="tableSearchInput" class="form-control ps-5" placeholder="Search by name, email, phone, or company...">
              </div>
            </div>

            <!-- Status Filter -->
            <div class="col-lg-2 col-sm-4">
              <select class="form-select" id="filterStatus" aria-label="Filter by Status">
                <option value="all">All Statuses</option>
                <option value="New">New</option>
                <option value="Contacted">Contacted</option>
                <option value="Interested">Interested</option>
                <option value="Converted">Converted</option>
                <option value="Lost">Lost</option>
              </select>
            </div>

            <!-- Priority Filter -->
            <div class="col-lg-2 col-sm-4">
              <select class="form-select" id="filterPriority" aria-label="Filter by Priority">
                <option value="all">All Priorities</option>
                <option value="High">High</option>
                <option value="Medium">Medium</option>
                <option value="Low">Low</option>
              </select>
            </div>

            <!-- Assigned Rep Filter -->
            <div class="col-lg-2 col-sm-4">
              <select class="form-select" id="filterEmployee" aria-label="Filter by Assigned Rep">
                <option value="all">All Reps</option>
                <?php if (!empty($usersList)): ?>
                  <?php foreach ($usersList as $u): ?>
                    <option value="<?= $u['id'] ?>"><?= htmlspecialchars($u['name']) ?></option>
                  <?php endforeach; ?>
                <?php endif; ?>
              </select>
            </div>

            <!-- Reset Filters -->
            <div class="col-lg-2 col-12 d-flex justify-content-lg-end">
              <button type="button" class="btn btn-glass w-100" id="resetFiltersBtn">
                <i class="bi bi-arrow-counterclockwise"></i>
                <span>Reset</span>
              </button>
            </div>
          </div>
        </div>

        <!-- Main Leads Data Table Card -->
        <div class="saas-card">
          <!-- Empty State Container -->
          <div id="leadsEmptyState" class="empty-state d-none">
            <div class="empty-state-icon">
              <i class="bi bi-search"></i>
            </div>
            <h3 class="h5 fw-bold text-main mb-1">No Matching Leads Found</h3>
            <p class="text-secondary fs-7 col-md-6 mx-auto mb-3">
              We couldn't find any lead matching your search query or selected filters. Try broadening your criteria or reset the search.
            </p>
            <button class="btn btn-glass" onclick="document.getElementById('resetFiltersBtn').click()">
              <i class="bi bi-arrow-counterclockwise me-1"></i> Clear Filters
            </button>
          </div>

          <!-- Table Container -->
          <div id="leadsTableContainer" class="table-responsive-custom">
            <table class="table table-saas">
              <thead>
                <tr>
                  <th style="width: 40px;">
                    <input type="checkbox" class="form-check-input" id="selectAllLeads" title="Select All">
                  </th>
                  <th class="table-sortable" onclick="sortColumn('id')">
                    Lead ID <i class="bi bi-arrow-down-up"></i>
                  </th>
                  <th class="table-sortable" onclick="sortColumn('name')">
                    Customer Name <i class="bi bi-arrow-down-up"></i>
                  </th>
                  <th>Email</th>
                  <th>Phone</th>
                  <th>Assigned Rep</th>
                  <th class="table-sortable" onclick="sortColumn('status')">
                    Status <i class="bi bi-arrow-down-up"></i>
                  </th>
                  <th class="table-sortable" onclick="sortColumn('priority')">
                    Priority <i class="bi bi-arrow-down-up"></i>
                  </th>
                  <th class="table-sortable" onclick="sortColumn('createdAt')">
                    Created Date <i class="bi bi-arrow-down-up"></i>
                  </th>
                  <th class="text-end">Actions</th>
                </tr>
              </thead>
              <tbody id="leadsTableBody">
                <!-- Dynamically populated via assets/js/leads.js with live MySQL data -->
              </tbody>
            </table>
          </div>

          <!-- Table Footer: Pagination & Counts -->
          <div class="p-3 border-top border-white border-opacity-10 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="text-secondary fs-8" id="leadsPaginationInfo">
              Showing 1 to 6 of 8 leads
            </div>
            <nav aria-label="Leads table navigation">
              <ul class="pagination-custom" id="leadsPagination">
                <!-- Dynamically populated -->
              </ul>
            </nav>
          </div>
        </div>
      </main>

  <!-- Floating Batch Actions Toolbar -->
  <div class="batch-toolbar" id="batchToolbar">
    <span class="fs-8 fw-semibold text-main" id="batchSelectedCount">0 Selected</span>
    <div class="vr bg-secondary opacity-50 my-1"></div>
    <a href="/leadflow/leads/export" class="btn btn-cyan-glow btn-sm">
      <i class="bi bi-download"></i> Export Leads
    </a>
    <button type="button" class="btn btn-danger-custom btn-sm" id="batchDeleteBtn">
      <i class="bi bi-trash"></i> Delete Selected
    </button>
  </div>

  <!-- Add/Edit Lead Modal -->
  <div class="modal fade" id="addEditLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content modal-content-custom">
        <div class="modal-header modal-header-custom">
          <h5 class="modal-title modal-title-custom" id="leadModalTitle">
            <i class="bi bi-person-plus text-purple me-2"></i>Add New Lead
          </h5>
          <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>
        <form id="leadModalForm" method="POST" action="/leadflow/leads/store">
          <input type="hidden" id="leadFormId" name="id">
          
          <div class="modal-body modal-body-custom">
            <div class="row g-3">
              <div class="col-md-6">
                <label for="leadName" class="form-label">Full Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="leadName" name="name" placeholder="e.g. Sarah Jenkins" required>
              </div>

              <div class="col-md-6">
                <label for="leadEmail" class="form-label">Email Address <span class="text-danger">*</span></label>
                <input type="email" class="form-control" id="leadEmail" name="email" placeholder="sarah@company.com" required>
              </div>

              <div class="col-md-6">
                <label for="leadPhone" class="form-label">Phone Number</label>
                <input type="text" class="form-control" id="leadPhone" name="phone" placeholder="+1 (555) 000-0000">
              </div>

              <div class="col-md-6">
                <label for="leadCompany" class="form-label">Company Name <span class="text-danger">*</span></label>
                <input type="text" class="form-control" id="leadCompany" name="company" placeholder="e.g. TechCorp Solutions" required>
              </div>

              <div class="col-md-4">
                <label for="leadSource" class="form-label">Lead Source</label>
                <select class="form-select" id="leadSource" name="lead_source">
                  <option value="Website Form">Website Form</option>
                  <option value="LinkedIn Outreach">LinkedIn Outreach</option>
                  <option value="Google Ads">Google Ads</option>
                  <option value="Referral">Partner Referral</option>
                  <option value="Cold Outreach">Cold Outreach</option>
                  <option value="Partner Event">Partner Event</option>
                </select>
              </div>

              <div class="col-md-4">
                <label for="leadStatus" class="form-label">Pipeline Status</label>
                <select class="form-select" id="leadStatus" name="status">
                  <option value="New">New</option>
                  <option value="Contacted">Contacted</option>
                  <option value="Interested">Interested</option>
                  <option value="Converted">Converted</option>
                  <option value="Lost">Lost</option>
                </select>
              </div>

              <div class="col-md-4">
                <label for="leadPriority" class="form-label">Priority</label>
                <select class="form-select" id="leadPriority" name="priority">
                  <option value="Low">Low</option>
                  <option value="Medium" selected>Medium</option>
                  <option value="High">High</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="leadAssignedTo" class="form-label">Assigned Representative</label>
                <select class="form-select" id="leadAssignedTo" name="assigned_to_id">
                  <option value="">Unassigned</option>
                  <?php if (!empty($usersList)): ?>
                    <?php foreach ($usersList as $u): ?>
                      <option value="<?= $u['id'] ?>">
                        <?= htmlspecialchars($u['name']) ?> (<?= htmlspecialchars($u['role']) ?>)
                      </option>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </select>
              </div>

              <div class="col-md-6">
                <label for="leadFollowUpDate" class="form-label">Next Follow-up Due</label>
                <input type="datetime-local" class="form-control" id="leadFollowUpDate" name="follow_up_date">
              </div>

              <div class="col-12">
                <label for="leadNotes" class="form-label">Lead Notes & Context</label>
                <textarea class="form-control" id="leadNotes" name="notes" rows="3" placeholder="Context, requirements, budget, next steps..."></textarea>
              </div>
            </div>
          </div>
          
          <div class="modal-footer modal-footer-custom">
            <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary-glow">
              <i class="bi bi-check-lg"></i>
              <span>Save Lead Record</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <!-- View Lead Detail Modal / Drawer -->
  <div class="modal fade" id="viewLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content modal-content-custom">
        <div class="modal-header modal-header-custom">
          <div class="d-flex align-items-center gap-3">
            <div class="avatar avatar-lg" id="viewLeadAvatar">SJ</div>
            <div>
              <h5 class="modal-title modal-title-custom" id="viewLeadName">Sarah Jenkins</h5>
              <div class="text-secondary fs-7" id="viewLeadCompany">TechCorp Solutions</div>
            </div>
          </div>
          <button type="button" class="btn-close-custom" data-bs-dismiss="modal" aria-label="Close">
            <i class="bi bi-x-lg"></i>
          </button>
        </div>

        <div class="modal-body modal-body-custom">
          <!-- Status & Priority Chips -->
          <div class="d-flex align-items-center justify-content-between mb-4 p-3 rounded-3" style="background: rgba(16, 23, 42, 0.6); border: 1px solid rgba(255, 255, 255, 0.06);">
            <div>
              <div class="text-muted fs-8 mb-1">Lead ID</div>
              <span class="font-monospace text-cyan fw-bold fs-7" id="viewLeadId">LF-1042</span>
            </div>
            <div>
              <div class="text-muted fs-8 mb-1">Status</div>
              <span id="viewLeadStatusBadge" class="badge-lead badge-status-interested">
                <span class="badge-dot"></span> Interested
              </span>
            </div>
            <div>
              <div class="text-muted fs-8 mb-1">Priority</div>
              <span id="viewLeadPriorityBadge" class="badge-lead badge-priority-high">High</span>
            </div>
          </div>

          <!-- Contact Info List -->
          <div class="d-flex flex-column gap-3 mb-4">
            <div class="d-flex align-items-center justify-content-between">
              <span class="text-muted fs-7"><i class="bi bi-envelope me-2 text-cyan"></i>Email</span>
              <a href="#" id="viewLeadEmailLink" class="text-main fs-7 fw-medium"><span id="viewLeadEmail">sarah@techcorp.io</span></a>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="text-muted fs-7"><i class="bi bi-telephone me-2 text-cyan"></i>Phone</span>
              <a href="#" id="viewLeadPhoneLink" class="text-main fs-7 fw-medium"><span id="viewLeadPhone">+1 (555) 234-8901</span></a>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="text-muted fs-7"><i class="bi bi-funnel me-2 text-purple"></i>Acquisition Source</span>
              <span class="text-main fs-7 fw-medium" id="viewLeadSource">Website Form</span>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="text-muted fs-7"><i class="bi bi-person-badge me-2 text-purple"></i>Assigned Rep</span>
              <span class="text-main fs-7 fw-medium" id="viewLeadAssigned">Alex Morgan</span>
            </div>
            <div class="d-flex align-items-center justify-content-between">
              <span class="text-muted fs-7"><i class="bi bi-clock-history me-2 text-muted"></i>Created Date</span>
              <span class="text-main fs-7 fw-medium" id="viewLeadDate">2026-10-18</span>
            </div>
          </div>

          <!-- Notes Card -->
          <div>
            <div class="text-muted fs-8 mb-2 text-uppercase fw-semibold">Qualification Notes</div>
            <div class="p-3 rounded-3 text-secondary fs-7" style="background: rgba(11, 16, 32, 0.6); border: 1px solid rgba(255, 255, 255, 0.05);" id="viewLeadNotes">
              Interested in Enterprise Tier for 50+ users.
            </div>
          </div>
        </div>

        <div class="modal-footer modal-footer-custom">
          <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Close</button>
          <a href="#" id="modalDirectEmailLink" class="btn btn-cyan-glow">
            <i class="bi bi-chat-dots"></i>
            <span>Contact Lead</span>
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div class="modal fade" id="deleteLeadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
      <div class="modal-content modal-content-custom">
        <div class="modal-body modal-body-custom text-center pt-4">
          <div class="empty-state-icon mb-3" style="background: rgba(244, 63, 94, 0.15); border-color: rgba(244, 63, 94, 0.3);">
            <i class="bi bi-exclamation-triangle text-danger-custom"></i>
          </div>
          <h5 class="h6 fw-bold text-main mb-2">Delete Lead Record?</h5>
          <p class="text-secondary fs-8 mb-4">
            Are you sure you want to remove <strong id="deleteLeadTargetName" class="text-main">this lead</strong>? This action cannot be undone.
          </p>
          <div class="d-flex align-items-center justify-content-center gap-2">
            <button type="button" class="btn btn-glass btn-sm" data-bs-dismiss="modal">Cancel</button>
            <button type="button" class="btn btn-danger-custom btn-sm" id="confirmDeleteLeadBtn">Confirm Delete</button>
          </div>
        </div>
      </div>
    </div>
  </div>
