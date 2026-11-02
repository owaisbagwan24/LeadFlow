<?php
// JSON data for charts (server-side real MySQL data)
$statusLabels = json_encode(array_keys($statusDist ?? []));
$statusValues = json_encode(array_values($statusDist ?? []));
$sourceLabels = json_encode(array_keys($sourceDist ?? []));
$sourceValues = json_encode(array_values($sourceDist ?? []));

$repNames     = json_encode(array_column($repStats ?? [], 'rep_name'));
$repConverted = json_encode(array_map('intval', array_column($repStats ?? [], 'converted')));
$repActive    = json_encode(array_map('intval', array_column($repStats ?? [], 'active')));
?>

<!-- Inject real MySQL chart data for charts.js -->
<script>
window.reportData = {
  statusLabels: <?= $statusLabels ?>,
  statusValues: <?= $statusValues ?>,
  sourceLabels: <?= $sourceLabels ?>,
  sourceValues: <?= $sourceValues ?>,
  repNames:     <?= $repNames ?>,
  repConverted: <?= $repConverted ?>,
  repActive:    <?= $repActive ?>
};
</script>

<!-- Main Reports Page Body -->
<main class="page-content">
  <!-- Page Header -->
  <div class="page-header">
    <div>
      <h1 class="page-title">Sales Analytics & Intelligence</h1>
      <p class="text-secondary fs-7 mb-0">High-level insights from your live MySQL database.</p>
    </div>
    <div class="d-flex align-items-center gap-2">
      <a href="/leadflow/reports/export?type=leads" class="btn btn-glass btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Leads CSV
      </a>
      <a href="/leadflow/reports/export?type=followups" class="btn btn-cyan-glow btn-sm">
        <i class="bi bi-file-earmark-spreadsheet me-1"></i>Export Follow-ups CSV
      </a>
    </div>
  </div>

  <!-- KPI Cards — LIVE DATA -->
  <div class="row g-3 mb-4">
    <div class="col-lg-3 col-sm-6">
      <div class="kpi-card kpi-card-purple">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary fs-8 fw-semibold text-uppercase">Total Leads</span>
          <div class="kpi-icon-box kpi-icon-purple"><i class="bi bi-people"></i></div>
        </div>
        <div class="kpi-value mb-1"><?= (int)($totalLeads ?? 0) ?></div>
        <span class="text-muted fs-8">All time in database</span>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="kpi-card kpi-card-green">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary fs-8 fw-semibold text-uppercase">Converted</span>
          <div class="kpi-icon-box kpi-icon-green"><i class="bi bi-graph-up-arrow"></i></div>
        </div>
        <div class="kpi-value mb-1 text-success-custom"><?= (int)($convertedLeads ?? 0) ?></div>
        <span class="text-muted fs-8"><?= ($conversionRate ?? 0) ?>% conversion rate</span>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="kpi-card kpi-card-cyan">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary fs-8 fw-semibold text-uppercase">New Leads</span>
          <div class="kpi-icon-box kpi-icon-cyan"><i class="bi bi-stars"></i></div>
        </div>
        <div class="kpi-value mb-1 text-cyan"><?= (int)($newLeads ?? 0) ?></div>
        <span class="text-muted fs-8">Awaiting first contact</span>
      </div>
    </div>
    <div class="col-lg-3 col-sm-6">
      <div class="kpi-card kpi-card-amber">
        <div class="d-flex align-items-center justify-content-between mb-2">
          <span class="text-secondary fs-8 fw-semibold text-uppercase">Top Channel</span>
          <div class="kpi-icon-box kpi-icon-amber"><i class="bi bi-trophy"></i></div>
        </div>
        <div class="kpi-value mb-1 fs-5 text-warning-custom"><?= htmlspecialchars($topSource ?? '—') ?></div>
        <span class="text-muted fs-8">Highest lead volume</span>
      </div>
    </div>
  </div>

  <!-- Follow-up Stats Row -->
  <div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
      <div class="saas-card p-3 d-flex align-items-center gap-3">
        <div class="kpi-icon-box kpi-icon-purple"><i class="bi bi-calendar-check"></i></div>
        <div>
          <div class="text-muted fs-8 fw-semibold">Total Follow-ups</div>
          <div class="fw-bold text-main fs-5"><?= (int)($totalFU ?? 0) ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6">
      <div class="saas-card p-3 d-flex align-items-center gap-3">
        <div class="kpi-icon-box kpi-icon-cyan"><i class="bi bi-clock-history"></i></div>
        <div>
          <div class="text-muted fs-8 fw-semibold">Overdue Tasks</div>
          <div class="fw-bold text-danger fs-5"><?= (int)($overdueFU ?? 0) ?></div>
        </div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6">
      <div class="saas-card p-3 d-flex align-items-center gap-3">
        <div class="kpi-icon-box kpi-icon-green"><i class="bi bi-check-all"></i></div>
        <div>
          <div class="text-muted fs-8 fw-semibold">Completed Tasks</div>
          <div class="fw-bold text-success-custom fs-5"><?= (int)($completedFU ?? 0) ?></div>
        </div>
      </div>
    </div>
  </div>

  <!-- Charts Grid -->
  <div class="row g-4 mb-4">
    <!-- Leads by Status (Doughnut) -->
    <div class="col-lg-5">
      <div class="saas-card p-3 p-md-4 h-100">
        <div class="mb-3">
          <h3 class="h6 fw-bold text-main mb-1">Pipeline Status Breakdown</h3>
          <p class="text-secondary fs-8 mb-0">Lead count per pipeline stage</p>
        </div>
        <div style="height: 280px; position: relative;">
          <canvas id="leadsByStatusChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Leads by Source (Bar) -->
    <div class="col-lg-7">
      <div class="saas-card p-3 p-md-4 h-100">
        <div class="mb-3">
          <h3 class="h6 fw-bold text-main mb-1">Leads by Acquisition Source</h3>
          <p class="text-secondary fs-8 mb-0">Total lead volume by inbound channel</p>
        </div>
        <div style="height: 280px; position: relative;">
          <canvas id="leadsBySourceChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Rep Performance (Bar) -->
    <div class="col-lg-12">
      <div class="saas-card p-3 p-md-4">
        <div class="mb-3">
          <h3 class="h6 fw-bold text-main mb-1">Sales Representative Performance</h3>
          <p class="text-secondary fs-8 mb-0">Converted deals vs active pipeline per rep</p>
        </div>
        <div style="height: 280px; position: relative;">
          <canvas id="repPerformanceChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <!-- Detailed Leads Table -->
  <div class="saas-card mb-4">
    <div class="p-3 border-bottom border-white border-opacity-10 d-flex align-items-center justify-content-between">
      <h3 class="h6 fw-bold text-main mb-0">All Leads — Detailed View</h3>
      <a href="/leadflow/leads/export" class="btn btn-glass btn-sm">
        <i class="bi bi-download me-1"></i>Export
      </a>
    </div>
    <div class="table-responsive-custom">
      <table class="table table-saas">
        <thead>
          <tr>
            <th>Lead Code</th>
            <th>Customer</th>
            <th>Company</th>
            <th>Source</th>
            <th>Status</th>
            <th>Priority</th>
            <th>Assigned To</th>
            <th>Created</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($allLeads)): ?>
            <?php foreach ($allLeads as $l): ?>
              <tr>
                <td><span class="font-monospace text-cyan fs-8"><?= htmlspecialchars($l['lead_code']) ?></span></td>
                <td class="fw-semibold text-main"><?= htmlspecialchars($l['name']) ?></td>
                <td class="text-secondary fs-7"><?= htmlspecialchars($l['company']) ?></td>
                <td class="text-secondary fs-8"><?= htmlspecialchars($l['lead_source']) ?></td>
                <td>
                  <?php
                    $sCls = [
                      'New'=>'badge-status-new','Contacted'=>'badge-status-contacted',
                      'Interested'=>'badge-status-interested','Converted'=>'badge-status-converted',
                      'Lost'=>'badge-status-lost'
                    ][$l['status']] ?? 'badge-status-new';
                  ?>
                  <span class="badge-lead <?= $sCls ?>"><span class="badge-dot"></span><?= htmlspecialchars($l['status']) ?></span>
                </td>
                <td>
                  <?php
                    $pCls = ['Low'=>'bg-secondary','Medium'=>'bg-purple-subtle text-purple','High'=>'bg-danger-subtle text-danger'][$l['priority']] ?? 'bg-secondary';
                  ?>
                  <span class="badge <?= $pCls ?> fs-8"><?= htmlspecialchars($l['priority']) ?></span>
                </td>
                <td class="text-secondary fs-7"><?= htmlspecialchars($l['assigned_to_name'] ?? 'Unassigned') ?></td>
                <td class="text-muted fs-8"><?= htmlspecialchars(date('M d, Y', strtotime($l['created_at']))) ?></td>
              </tr>
            <?php endforeach; ?>
          <?php else: ?>
            <tr><td colspan="8" class="text-center text-muted py-4">No leads in database.</td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Sales Rep Leaderboard -->
  <?php if (!empty($repStats)): ?>
  <div class="saas-card">
    <div class="p-3 border-bottom border-white border-opacity-10">
      <h3 class="h6 fw-bold text-main mb-0">Sales Rep Leaderboard</h3>
    </div>
    <div class="table-responsive-custom">
      <table class="table table-saas">
        <thead>
          <tr>
            <th>Rank</th>
            <th>Representative</th>
            <th>Total Leads</th>
            <th>Converted</th>
            <th>Active</th>
            <th>Win Rate</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($repStats as $i => $rep): ?>
            <?php
              $rankBadge = $i === 0 ? 'bg-warning text-dark' : ($i === 1 ? 'bg-secondary text-white' : 'bg-dark text-muted');
              $initials  = '';
              foreach (explode(' ', trim($rep['rep_name'])) as $p) if ($p) $initials .= strtoupper($p[0]);
              $initials  = substr($initials, 0, 2) ?: 'U';
            ?>
            <tr>
              <td><span class="badge <?= $rankBadge ?> fw-bold fs-8">#<?= $i+1 ?></span></td>
              <td>
                <div class="d-flex align-items-center gap-2">
                  <div class="avatar avatar-sm"><?= htmlspecialchars($initials) ?></div>
                  <span class="fw-semibold text-main"><?= htmlspecialchars($rep['rep_name']) ?></span>
                </div>
              </td>
              <td class="text-main fw-semibold"><?= (int)$rep['total_leads'] ?></td>
              <td class="text-success-custom fw-semibold"><?= (int)$rep['converted'] ?></td>
              <td class="text-cyan fw-semibold"><?= (int)$rep['active'] ?></td>
              <td>
                <span class="<?= $rep['win_rate'] >= 20 ? 'text-success-custom' : 'text-warning-custom' ?> fw-bold">
                  <?= $rep['win_rate'] ?>%
                </span>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
  <?php endif; ?>
</main>
