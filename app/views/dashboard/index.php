<?php
$total = $metrics['totalLeads'] ?: 1;
$contactedPct = round((($statusDistribution['Contacted'] ?? 0) / $total) * 100);
$interestedPct = round((($statusDistribution['Interested'] ?? 0) / $total) * 100);
$convertedPct = round((($statusDistribution['Converted'] ?? 0) / $total) * 100);
?>
<script>
window.leadFlowChartData = {
  statusCounts: [
    <?= (int)($statusDistribution['New'] ?? 0) ?>,
    <?= (int)($statusDistribution['Contacted'] ?? 0) ?>,
    <?= (int)($statusDistribution['Interested'] ?? 0) ?>,
    <?= (int)($statusDistribution['Converted'] ?? 0) ?>,
    <?= (int)($statusDistribution['Lost'] ?? 0) ?>
  ]
};
</script>

      <!-- Main Dashboard Page Body -->
      <main class="page-content">
        <!-- Page Header -->
        <div class="page-header">
          <div>
            <h1 class="page-title">Executive Pipeline Overview</h1>
            <p class="text-secondary fs-7 mb-0">Live MySQL metrics, sales pipeline velocity, and conversion insights.</p>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-purple-subtle text-purple border border-purple border-opacity-25 px-3 py-2 rounded-pill fs-8">
              <i class="bi bi-database-check text-cyan me-1"></i> Live MySQL Connected
            </span>
            <a href="/leadflow/reports" class="btn btn-cyan-glow">
              <i class="bi bi-download"></i>
              <span>Export Report</span>
            </a>
          </div>
        </div>

        <!-- 5 KPI Summary Cards Row -->
        <div class="row g-3 mb-4">
          <!-- Total Leads -->
          <div class="col-xl col-md-4 col-sm-6">
            <div class="kpi-card kpi-card-purple">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fs-8 fw-semibold text-uppercase">Total Leads</span>
                <div class="kpi-icon-box kpi-icon-purple">
                  <i class="bi bi-people"></i>
                </div>
              </div>
              <div class="kpi-value mb-2" id="kpiTotalLeads"><?= number_format($metrics['totalLeads'] ?? 0) ?></div>
              <div class="d-flex align-items-center gap-2">
                <span class="trend-badge trend-up">
                  <i class="bi bi-database"></i> Live Database
                </span>
                <span class="text-muted fs-8">all records</span>
              </div>
            </div>
          </div>

          <!-- New Leads -->
          <div class="col-xl col-md-4 col-sm-6">
            <div class="kpi-card kpi-card-cyan">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fs-8 fw-semibold text-uppercase">New Inbound</span>
                <div class="kpi-icon-box kpi-icon-cyan">
                  <i class="bi bi-sparkles"></i>
                </div>
              </div>
              <div class="kpi-value mb-2" id="kpiNewLeads"><?= number_format($metrics['newLeads'] ?? 0) ?></div>
              <div class="d-flex align-items-center gap-2">
                <span class="trend-badge trend-up">
                  <i class="bi bi-arrow-up-short"></i> Inbound
                </span>
                <span class="text-muted fs-8">awaiting contact</span>
              </div>
            </div>
          </div>

          <!-- Contacted Leads -->
          <div class="col-xl col-md-4 col-sm-6">
            <div class="kpi-card kpi-card-amber">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fs-8 fw-semibold text-uppercase">Contacted</span>
                <div class="kpi-icon-box kpi-icon-amber">
                  <i class="bi bi-telephone-outbound"></i>
                </div>
              </div>
              <div class="kpi-value mb-2" id="kpiContactedLeads"><?= number_format($metrics['contactedLeads'] ?? 0) ?></div>
              <div class="d-flex align-items-center gap-2">
                <span class="trend-badge trend-up">
                  <i class="bi bi-arrow-up-short"></i> In Outreach
                </span>
                <span class="text-muted fs-8">active dialogue</span>
              </div>
            </div>
          </div>

          <!-- Converted Leads -->
          <div class="col-xl col-md-6 col-sm-6">
            <div class="kpi-card kpi-card-green">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fs-8 fw-semibold text-uppercase">Converted</span>
                <div class="kpi-icon-box kpi-icon-green">
                  <i class="bi bi-check2-circle"></i>
                </div>
              </div>
              <div class="kpi-value mb-2" id="kpiConvertedLeads"><?= number_format($metrics['convertedLeads'] ?? 0) ?></div>
              <div class="d-flex align-items-center gap-2">
                <span class="trend-badge trend-up">
                  <i class="bi bi-trophy"></i> Won Deals
                </span>
                <span class="text-muted fs-8">closed contracts</span>
              </div>
            </div>
          </div>

          <!-- Conversion Rate -->
          <div class="col-xl col-md-6 col-sm-12">
            <div class="kpi-card kpi-card-cyan">
              <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary fs-8 fw-semibold text-uppercase">Conversion Rate</span>
                <div class="kpi-icon-box kpi-icon-cyan">
                  <i class="bi bi-pie-chart"></i>
                </div>
              </div>
              <div class="kpi-value mb-2 text-cyan"><?= $metrics['conversionRate'] ?? 0 ?>%</div>
              <div class="d-flex align-items-center gap-2">
                <span class="trend-badge trend-up">
                  <i class="bi bi-clock-history"></i> <?= (int)($metrics['pendingFollowups'] ?? 0) ?> Pending
                </span>
                <span class="text-muted fs-8">follow-ups</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Charts Row -->
        <div class="row g-4 mb-4">
          <!-- Acquisition & Conversion Trends Line Chart -->
          <div class="col-lg-8">
            <div class="saas-card p-3 p-md-4 h-100">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div>
                  <h3 class="h6 fw-bold text-main mb-1">Lead Acquisition & Conversion Velocity</h3>
                  <p class="text-secondary fs-8 mb-0">Monthly comparison of generated leads versus closed contracts</p>
                </div>
                <div class="badge bg-purple-subtle text-purple border border-purple border-opacity-25 px-2 py-1 fs-8">
                  <i class="bi bi-graph-up me-1"></i> Live Performance
                </div>
              </div>
              <div style="height: 300px; position: relative;">
                <canvas id="leadTrendsChart"></canvas>
              </div>
            </div>
          </div>

          <!-- Lead Status Distribution Doughnut Chart -->
          <div class="col-lg-4">
            <div class="saas-card p-3 p-md-4 h-100">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                  <h3 class="h6 fw-bold text-main mb-1">Pipeline Distribution</h3>
                  <p class="text-secondary fs-8 mb-0">Real-time proportion of active leads by stage</p>
                </div>
              </div>
              <div style="height: 240px; position: relative;">
                <canvas id="leadStatusChart"></canvas>
              </div>
              <div class="d-flex justify-content-around text-center pt-3 border-top border-white border-opacity-10 mt-2">
                <div>
                  <div class="text-cyan fw-bold fs-7"><?= $contactedPct ?>%</div>
                  <div class="text-muted fs-8">Contacted</div>
                </div>
                <div>
                  <div class="text-purple fw-bold fs-7"><?= $interestedPct ?>%</div>
                  <div class="text-muted fs-8">Interested</div>
                </div>
                <div>
                  <div class="text-success-custom fw-bold fs-7"><?= $convertedPct ?>%</div>
                  <div class="text-muted fs-8">Converted</div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Leads & Upcoming Follow-ups Row -->
        <div class="row g-4 mb-4">
          <!-- Recent Leads Table Preview -->
          <div class="col-lg-8">
            <div class="saas-card h-100">
              <div class="p-3 p-md-4 border-bottom border-white border-opacity-10 d-flex align-items-center justify-content-between">
                <div>
                  <h3 class="h6 fw-bold text-main mb-1">Recent Opportunities</h3>
                  <p class="text-secondary fs-8 mb-0">Latest prospects fetched directly from MySQL database</p>
                </div>
                <a href="/leadflow/leads" class="btn btn-glass btn-sm">
                  <span>View All Leads</span>
                  <i class="bi bi-arrow-right"></i>
                </a>
              </div>

              <div class="table-responsive-custom">
                <table class="table table-saas">
                  <thead>
                    <tr>
                      <th>Customer Name</th>
                      <th>Company</th>
                      <th>Assigned Rep</th>
                      <th>Status</th>
                      <th>Priority</th>
                      <th class="text-end">Action</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php if (!empty($recentLeads)): ?>
                      <?php foreach ($recentLeads as $lead): ?>
                        <?php
                          $lInitials = '';
                          foreach (explode(' ', trim($lead['name'])) as $p) {
                              if (!empty($p)) $lInitials .= strtoupper($p[0]);
                          }
                          $lInitials = substr($lInitials, 0, 2) ?: 'L';

                          $statusClass = 'badge-status-new';
                          if ($lead['status'] === 'Contacted') $statusClass = 'badge-status-contacted';
                          if ($lead['status'] === 'Interested') $statusClass = 'badge-status-interested';
                          if ($lead['status'] === 'Converted') $statusClass = 'badge-status-converted';
                          if ($lead['status'] === 'Lost') $statusClass = 'badge-status-lost';

                          $priorityClass = 'badge-priority-medium';
                          if ($lead['priority'] === 'High') $priorityClass = 'badge-priority-high';
                          if ($lead['priority'] === 'Low') $priorityClass = 'badge-priority-low';
                        ?>
                        <tr>
                          <td>
                            <div class="d-flex align-items-center gap-2">
                              <div class="avatar avatar-sm"><?= htmlspecialchars($lInitials) ?></div>
                              <span class="fw-semibold text-main"><?= htmlspecialchars($lead['name']) ?></span>
                            </div>
                          </td>
                          <td class="text-secondary"><?= htmlspecialchars($lead['company']) ?></td>
                          <td class="text-secondary"><?= htmlspecialchars($lead['assigned_to_name'] ?? 'Unassigned') ?></td>
                          <td>
                            <span class="badge-lead <?= $statusClass ?>">
                              <span class="badge-dot"></span> <?= htmlspecialchars($lead['status']) ?>
                            </span>
                          </td>
                          <td><span class="badge-lead <?= $priorityClass ?>"><?= htmlspecialchars($lead['priority']) ?></span></td>
                          <td class="text-end">
                            <a href="/leadflow/leads" class="btn btn-glass btn-icon" style="width:28px; height:28px;" title="View details">
                              <i class="bi bi-chevron-right fs-8"></i>
                            </a>
                          </td>
                        </tr>
                      <?php endforeach; ?>
                    <?php else: ?>
                      <tr>
                        <td colspan="6" class="text-center text-muted py-4">No recent leads found in database.</td>
                      </tr>
                    <?php endif; ?>
                  </tbody>
                </table>
              </div>
            </div>
          </div>

          <!-- Upcoming Follow-ups Panel -->
          <div class="col-lg-4">
            <div class="saas-card h-100 p-3 p-md-4">
              <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                  <h3 class="h6 fw-bold text-main mb-1">Upcoming Follow-ups</h3>
                  <p class="text-secondary fs-8 mb-0">Scheduled outreach reminders from MySQL</p>
                </div>
                <a href="/leadflow/followups" class="fs-8 text-cyan">View all</a>
              </div>

              <?php if (!empty($upcomingFollowups)): ?>
                <?php foreach ($upcomingFollowups as $fu): ?>
                  <?php
                    $isOverdue = ($fu['status'] === 'overdue' || (strtotime($fu['due_date']) < time() && $fu['status'] !== 'completed'));
                    $boxStyle = $isOverdue 
                        ? 'background: rgba(244, 63, 94, 0.06); border: 1px solid rgba(244, 63, 94, 0.25);'
                        : 'background: rgba(16, 23, 42, 0.7); border: 1px solid rgba(255, 255, 255, 0.06);';
                    $badgeClass = $isOverdue ? 'bg-danger-subtle text-danger' : 'bg-purple-subtle text-purple';
                    $iconClass = 'bi-telephone text-purple';
                    if (stripos($fu['activity_type'], 'Video') !== false) $iconClass = 'bi-camera-video text-cyan';
                    if (stripos($fu['activity_type'], 'Email') !== false) $iconClass = 'bi-envelope text-warning';
                    if (stripos($fu['activity_type'], 'Meeting') !== false) $iconClass = 'bi-people text-success';
                  ?>
                  <div class="p-3 mb-2 rounded-3" style="<?= $boxStyle ?>">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-1">
                      <div class="fw-semibold <?= $isOverdue ? 'text-danger' : 'text-main' ?> fs-7"><?= htmlspecialchars($fu['customer_name']) ?></div>
                      <span class="badge <?= $badgeClass ?> fs-8">
                        <?= $isOverdue ? 'Overdue' : date('M d, g:i A', strtotime($fu['due_date'])) ?>
                      </span>
                    </div>
                    <div class="text-secondary fs-8 mb-2"><?= htmlspecialchars($fu['notes'] ?: $fu['company_name']) ?></div>
                    <div class="d-flex align-items-center justify-content-between fs-8 text-muted">
                      <span><i class="bi <?= $iconClass ?> me-1"></i><?= htmlspecialchars($fu['activity_type']) ?></span>
                      <a href="/leadflow/followups" class="<?= $isOverdue ? 'text-danger' : 'text-cyan' ?>">Open</a>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="text-center text-muted py-4">No upcoming follow-up reminders.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <!-- Recent Activities Section -->
        <div class="row g-4">
          <div class="col-12">
            <div class="saas-card p-3 p-md-4">
              <div class="d-flex align-items-center justify-content-between mb-3 border-bottom border-white border-opacity-10 pb-2">
                <div>
                  <h3 class="h6 fw-bold text-main mb-1">Recent Activity Audit Trail</h3>
                  <p class="text-secondary fs-8 mb-0">Real-time team updates logged to MySQL <code>lead_activities</code></p>
                </div>
                <span class="badge bg-cyan-subtle text-cyan fs-8">Live Log</span>
              </div>

              <?php if (!empty($recentActivities)): ?>
                <div class="d-flex flex-column gap-2">
                  <?php foreach ($recentActivities as $act): ?>
                    <div class="p-2 px-3 rounded-2 d-flex align-items-center justify-content-between" style="background: rgba(16, 23, 42, 0.4); border: 1px solid rgba(255, 255, 255, 0.04);">
                      <div class="d-flex align-items-center gap-3">
                        <div class="badge-dot bg-cyan"></div>
                        <div>
                          <span class="text-main fw-semibold fs-7"><?= htmlspecialchars($act['user_name'] ?? 'User') ?></span>
                          <span class="text-muted fs-8 mx-1">&bull;</span>
                          <span class="badge bg-purple-subtle text-purple fs-8"><?= htmlspecialchars($act['action']) ?></span>
                          <span class="text-secondary fs-8 ms-2">on <strong><?= htmlspecialchars($act['lead_name'] ?? 'Lead') ?></strong> (<?= htmlspecialchars($act['lead_code'] ?? '') ?>)</span>
                        </div>
                      </div>
                      <div class="text-muted fs-8">
                        <?= htmlspecialchars(date('M d, g:i A', strtotime($act['created_at']))) ?>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              <?php else: ?>
                <div class="text-center text-muted py-3">No activity logs recorded yet.</div>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </main>

  <!-- Reusable Add/Edit Lead Modal -->
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
                <label for="leadName" class="form-label">Customer Name <span class="text-danger">*</span></label>
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
                <input type="text" class="form-control" id="leadCompany" name="company" placeholder="e.g. Acme Corp" required>
              </div>

              <div class="col-md-4">
                <label for="leadSource" class="form-label">Lead Acquisition Source</label>
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
                <label for="leadStatus" class="form-label">Initial Status</label>
                <select class="form-select" id="leadStatus" name="status">
                  <option value="New">New</option>
                  <option value="Contacted">Contacted</option>
                  <option value="Interested">Interested</option>
                  <option value="Converted">Converted</option>
                  <option value="Lost">Lost</option>
                </select>
              </div>

              <div class="col-md-4">
                <label for="leadPriority" class="form-label">Priority Level</label>
                <select class="form-select" id="leadPriority" name="priority">
                  <option value="Low">Low</option>
                  <option value="Medium" selected>Medium</option>
                  <option value="High">High</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="leadAssignedTo" class="form-label">Assigned Sales Rep</label>
                <select class="form-select" id="leadAssignedTo" name="assigned_to">
                  <option value="Alex Morgan">Alex Morgan (Admin)</option>
                  <option value="Elena Rostova">Elena Rostova</option>
                  <option value="David Miller">David Miller</option>
                  <option value="Sophia Chen">Sophia Chen</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="leadFollowUpDate" class="form-label">Next Follow-up Reminder</label>
                <input type="datetime-local" class="form-control" id="leadFollowUpDate" name="follow_up_date">
              </div>

              <div class="col-12">
                <label for="leadNotes" class="form-label">Lead Qualification Notes</label>
                <textarea class="form-control" id="leadNotes" name="notes" rows="3" placeholder="Key customer pain points, budget constraints, timeline, or meeting takeaways..."></textarea>
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
