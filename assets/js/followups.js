/**
 * LeadFlow — Follow-ups Management
 * All data flows from MySQL via PHP (window.serverFollowUpsData).
 * Add/Edit/Delete/Complete persist to MySQL via fetch AJAX.
 */

let followupsData = [];
let currentFilter = 'all';
let followupToDeleteId = null;

document.addEventListener('DOMContentLoaded', () => {
  // Load data injected by PHP server
  if (window.serverFollowUpsData && Array.isArray(window.serverFollowUpsData)) {
    followupsData = [...window.serverFollowUpsData];
  }

  renderFollowups();
  updateCounts();
  setupListeners();
});

// ─────────────────────────────────────
// SETUP LISTENERS
// ─────────────────────────────────────
function setupListeners() {
  // Tab filter links
  document.querySelectorAll('.followup-tab-link').forEach(btn => {
    btn.addEventListener('click', () => {
      document.querySelectorAll('.followup-tab-link').forEach(b => b.classList.remove('active'));
      btn.classList.add('active');
      currentFilter = btn.getAttribute('data-filter') || 'all';
      renderFollowups();
    });
  });

  // Add / Edit Form Submit
  const form = document.getElementById('scheduleFollowupForm');
  if (form) {
    form.addEventListener('submit', e => {
      e.preventDefault();
      handleFollowupFormSubmit();
    });
  }

  // Confirm Delete Button
  const confirmDelBtn = document.getElementById('confirmDeleteFollowupBtn');
  if (confirmDelBtn) {
    confirmDelBtn.addEventListener('click', () => {
      if (!followupToDeleteId) return;
      deleteFollowup(followupToDeleteId);
    });
  }

  // Reset modal state when closed
  const modal = document.getElementById('scheduleFollowupModal');
  if (modal) {
    modal.addEventListener('hidden.bs.modal', resetFollowupForm);
  }
}

// ─────────────────────────────────────
// RENDER
// ─────────────────────────────────────
function renderFollowups() {
  const container  = document.getElementById('followupsListContainer');
  const emptyState = document.getElementById('followupsEmptyState');
  if (!container) return;

  const filtered = followupsData.filter(item => {
    if (currentFilter === 'all') return true;
    return item.status === currentFilter;
  });

  container.innerHTML = '';

  if (filtered.length === 0) {
    if (emptyState) emptyState.classList.remove('d-none');
    return;
  }
  if (emptyState) emptyState.classList.add('d-none');

  filtered.forEach(item => {
    const isCompleted = item.status === 'completed';
    const isOverdue   = item.status === 'overdue';

    let typeIcon = 'bi-telephone-fill';
    let typeCls  = 'text-cyan';
    if (item.activityType && (item.activityType.includes('Video') || item.activityType.includes('Meeting'))) {
      typeIcon = 'bi-camera-video-fill'; typeCls = 'text-purple';
    } else if (item.activityType && item.activityType.includes('Email')) {
      typeIcon = 'bi-envelope-fill'; typeCls = 'text-info';
    } else if (item.activityType && item.activityType.includes('Demo')) {
      typeIcon = 'bi-display'; typeCls = 'text-warning-custom';
    }

    const card = document.createElement('div');
    card.className = `followup-card d-flex gap-3 p-3 mb-3 rounded-3 border
      ${isOverdue ? 'border-danger border-opacity-50 bg-danger bg-opacity-10 overdue' : ''}
      ${isCompleted ? 'opacity-75 completed' : ''}
      ${!isOverdue && !isCompleted ? 'border-white border-opacity-10' : ''}`;
    card.id = `fu-card-${item.id}`;
    card.style.cssText = isCompleted || isOverdue ? '' : 'background: rgba(16, 23, 42, 0.4);';

    card.innerHTML = `
      <div class="pt-1">
        <input type="checkbox" class="form-check-input" ${isCompleted ? 'checked' : ''}
          title="Mark complete" onchange="toggleComplete(${item.id}, this.checked)"
          style="width:1.2em;height:1.2em;cursor:pointer;">
      </div>
      <div class="flex-grow-1">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-1">
          <div>
            <span class="fw-semibold text-main fs-6 ${isCompleted ? 'text-decoration-line-through text-muted' : ''}">${escapeHtml(item.customerName)}</span>
            <span class="text-muted fs-7 ms-1">(${escapeHtml(item.company)})</span>
            <span class="font-monospace text-cyan fs-8 ms-2">${escapeHtml(item.leadCode)}</span>
          </div>
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-dark border border-secondary border-opacity-25 fs-8 text-secondary">
              <i class="bi ${typeIcon} ${typeCls} me-1"></i>${escapeHtml(item.activityType)}
            </span>
            ${isOverdue   ? '<span class="badge bg-danger-subtle text-danger fs-8 fw-semibold"><i class="bi bi-clock-history me-1"></i>Overdue</span>' : ''}
            ${isCompleted ? '<span class="badge bg-success-subtle text-success fs-8 fw-semibold"><i class="bi bi-check-all me-1"></i>Completed</span>' : ''}
          </div>
        </div>
        <div class="text-secondary fs-7 mb-2">${escapeHtml(item.notes || 'No notes provided.')}</div>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 fs-8 text-muted border-top border-white border-opacity-10 pt-2 mt-2">
          <div class="d-flex align-items-center gap-3">
            <span><i class="bi bi-person me-1 text-cyan"></i>${escapeHtml(item.assignedTo)}</span>
            <span><i class="bi bi-calendar-event me-1 text-purple"></i>${formatDatetime(item.dueDate)}</span>
          </div>
          <div class="action-btn-group">
            <button type="button" class="action-btn btn-edit" title="Edit" onclick="openEditModal(${item.id})">
              <i class="bi bi-pencil"></i>
            </button>
            <button type="button" class="action-btn btn-delete" title="Delete" onclick="openDeleteModal(${item.id})">
              <i class="bi bi-trash"></i>
            </button>
          </div>
        </div>
      </div>
    `;
    container.appendChild(card);
  });
}

// ─────────────────────────────────────
// FORM SUBMIT — ADD OR EDIT
// ─────────────────────────────────────
function handleFollowupFormSubmit() {
  const idEl    = document.getElementById('fuId');
  const isEdit  = Boolean(idEl && idEl.value);

  const leadId  = document.getElementById('fuLeadId').value;
  const type    = document.getElementById('fuType').value;
  const status  = document.getElementById('fuStatus').value;
  const dueDate = document.getElementById('fuDueDate').value;
  const notes   = document.getElementById('fuNotes').value.trim();

  if (!leadId || !dueDate) {
    if (typeof showToast === 'function') showToast('Validation', 'Lead and Due Date are required.', 'warning');
    return;
  }

  const fd = new FormData();
  if (isEdit) fd.append('id', idEl.value);
  fd.append('lead_id', leadId);
  fd.append('activity_type', type);
  fd.append('status', status);
  fd.append('due_date', dueDate);
  fd.append('notes', notes);

  const endpoint = isEdit
    ? '/leadflow/followups/update'
    : '/leadflow/followups/store';

  fetch(endpoint, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: fd
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const fu = data.followup;
      const norm = normalizeFu(fu);

      if (isEdit) {
        const idx = followupsData.findIndex(f => f.id === norm.id);
        if (idx !== -1) followupsData[idx] = norm;
      } else {
        followupsData.unshift(norm);
      }

      renderFollowups();
      updateCounts();
      closeModal('scheduleFollowupModal');
      resetFollowupForm();
      if (typeof showToast === 'function')
        showToast(isEdit ? 'Updated' : 'Scheduled', data.message, 'success');
    } else {
      if (typeof showToast === 'function')
        showToast('Error', data.error || 'Operation failed.', 'danger');
    }
  })
  .catch(() => {
    if (typeof showToast === 'function')
      showToast('Network Error', 'Could not reach the server.', 'danger');
  });
}

// ─────────────────────────────────────
// TOGGLE COMPLETE
// ─────────────────────────────────────
function toggleComplete(id, isChecked) {
  const fd = new FormData();
  fd.append('id', id);

  fetch('/leadflow/followups/complete', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: fd
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      const item = followupsData.find(f => f.id === id);
      if (item) item.status = data.newStatus;
      renderFollowups();
      updateCounts();
      if (typeof showToast === 'function')
        showToast('Status Updated', `Follow-up marked as ${data.newStatus}.`, 'success');
    } else {
      if (typeof showToast === 'function')
        showToast('Error', data.error || 'Toggle failed.', 'danger');
      // Revert checkbox
      const card = document.getElementById(`fu-card-${id}`);
      if (card) {
        const cb = card.querySelector('input[type=checkbox]');
        if (cb) cb.checked = !isChecked;
      }
    }
  })
  .catch(() => {
    if (typeof showToast === 'function') showToast('Network Error', 'Server unreachable.', 'danger');
  });
}

// ─────────────────────────────────────
// EDIT MODAL
// ─────────────────────────────────────
function openEditModal(id) {
  const item = followupsData.find(f => f.id === id);
  if (!item) return;

  document.getElementById('fuId').value       = item.id;
  document.getElementById('fuLeadId').value   = item.leadId;
  document.getElementById('fuType').value     = item.activityType;
  document.getElementById('fuStatus').value  = item.status;
  document.getElementById('fuNotes').value   = item.notes;

  // Format dueDate for datetime-local input  (YYYY-MM-DDTHH:MM)
  if (item.dueDate) {
    const d = new Date(item.dueDate);
    if (!isNaN(d)) {
      const pad = n => String(n).padStart(2, '0');
      document.getElementById('fuDueDate').value =
        `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
    }
  }

  document.getElementById('followupModalTitle').textContent  = 'Edit Follow-up';
  document.getElementById('followupSubmitLabel').textContent = 'Save Changes';

  const modalEl = document.getElementById('scheduleFollowupModal');
  if (modalEl && window.bootstrap) {
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
  }
}

function resetFollowupForm() {
  const form = document.getElementById('scheduleFollowupForm');
  if (form) form.reset();
  const idEl = document.getElementById('fuId');
  if (idEl) idEl.value = '';
  const title = document.getElementById('followupModalTitle');
  if (title) title.textContent = 'Schedule Follow-up';
  const label = document.getElementById('followupSubmitLabel');
  if (label) label.textContent = 'Schedule Activity';
}

// ─────────────────────────────────────
// DELETE
// ─────────────────────────────────────
function openDeleteModal(id) {
  followupToDeleteId = id;
  const modalEl = document.getElementById('deleteFollowupModal');
  if (modalEl && window.bootstrap) {
    bootstrap.Modal.getOrCreateInstance(modalEl).show();
  }
}

function deleteFollowup(id) {
  const fd = new FormData();
  fd.append('id', id);

  fetch('/leadflow/followups/delete', {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: fd
  })
  .then(r => r.json())
  .then(data => {
    if (data.success) {
      followupsData = followupsData.filter(f => f.id !== id);
      renderFollowups();
      updateCounts();
      closeModal('deleteFollowupModal');
      followupToDeleteId = null;
      if (typeof showToast === 'function')
        showToast('Deleted', 'Follow-up removed.', 'danger');
    } else {
      if (typeof showToast === 'function')
        showToast('Error', data.error || 'Delete failed.', 'danger');
    }
  });
}

// ─────────────────────────────────────
// COUNTS UPDATE
// ─────────────────────────────────────
function updateCounts() {
  const set = (id, val) => { const el = document.getElementById(id); if (el) el.textContent = val; };
  set('countAllFollowups',       followupsData.length);
  set('countUpcomingFollowups',  followupsData.filter(f => f.status === 'upcoming').length);
  set('countOverdueFollowups',   followupsData.filter(f => f.status === 'overdue').length);
  set('countCompletedFollowups', followupsData.filter(f => f.status === 'completed').length);
}

// ─────────────────────────────────────
// UTILITIES
// ─────────────────────────────────────
function normalizeFu(f) {
  return {
    id:           parseInt(f.id),
    leadId:       parseInt(f.lead_id || f.leadId),
    leadCode:     f.lead_code || f.leadCode || '',
    customerName: f.customer_name || f.customerName || 'Unknown',
    company:      f.company_name  || f.company      || '—',
    activityType: f.activity_type || f.activityType || 'Phone Call',
    dueDate:      f.due_date      || f.dueDate      || '',
    status:       f.status,
    assignedTo:   f.assigned_to_name || f.assignedTo || 'Unassigned',
    notes:        f.notes || '',
    completedAt:  f.completed_at  || f.completedAt  || null
  };
}

function formatDatetime(str) {
  if (!str) return 'No due date';
  const d = new Date(str);
  if (isNaN(d)) return str;
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
    + ' ' + d.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function closeModal(id) {
  const el = document.getElementById(id);
  if (el && window.bootstrap) {
    const m = bootstrap.Modal.getInstance(el);
    if (m) m.hide();
  }
}

// escapeHtml may be defined in app.js; define fallback
if (typeof escapeHtml === 'undefined') {
  window.escapeHtml = function(str) {
    if (!str) return '';
    return String(str)
      .replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')
      .replace(/"/g,'&quot;').replace(/'/g,'&#39;');
  };
}
