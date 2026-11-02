/**
 * LeadFlow — Premium SaaS Lead Management System
 * Leads Management, Data Grid, Filters, Pagination, & MySQL Backend Bridge
 */

let leadsData = [];
let filteredLeads = [];
let currentPage = 1;
const itemsPerPage = 6;
let currentSort = { column: 'createdAt', direction: 'desc' };
let leadToDeleteId = null;

// Initialize on DOM Ready
document.addEventListener('DOMContentLoaded', () => {
  loadLeads();
  setupEventListeners();
  setupAvatarPreview();
});

// Load leads: Prefer live MySQL data injected by server
function loadLeads() {
  if (window.serverLeadsData && Array.isArray(window.serverLeadsData)) {
    leadsData = [...window.serverLeadsData];
  } else {
    const stored = localStorage.getItem('leadflow_leads');
    if (stored) {
      try {
        leadsData = JSON.parse(stored);
      } catch (e) {
        leadsData = (typeof DEFAULT_LEADS !== 'undefined') ? DEFAULT_LEADS : [];
      }
    } else {
      leadsData = (typeof DEFAULT_LEADS !== 'undefined') ? [...DEFAULT_LEADS] : [];
    }
  }
  applyFilters();
}

// Save leads to localStorage as client cache
function saveLeads() {
  localStorage.setItem('leadflow_leads', JSON.stringify(leadsData));
}

// Setup Event Listeners
function setupEventListeners() {
  // Search input
  const searchInput = document.getElementById('tableSearchInput') || document.getElementById('globalSearchInput');
  if (searchInput) {
    searchInput.addEventListener('input', () => {
      currentPage = 1;
      applyFilters();
    });
  }

  // Filter selects
  const statusFilter = document.getElementById('filterStatus');
  const priorityFilter = document.getElementById('filterPriority');
  const employeeFilter = document.getElementById('filterEmployee');

  if (statusFilter) statusFilter.addEventListener('change', () => { currentPage = 1; applyFilters(); });
  if (priorityFilter) priorityFilter.addEventListener('change', () => { currentPage = 1; applyFilters(); });
  if (employeeFilter) employeeFilter.addEventListener('change', () => { currentPage = 1; applyFilters(); });

  // Reset Filters Button
  const resetBtn = document.getElementById('resetFiltersBtn');
  if (resetBtn) {
    resetBtn.addEventListener('click', () => {
      if (searchInput) searchInput.value = '';
      if (statusFilter) statusFilter.value = 'all';
      if (priorityFilter) priorityFilter.value = 'all';
      if (employeeFilter) employeeFilter.value = 'all';
      currentPage = 1;
      applyFilters();
      if (typeof showToast === 'function') {
        showToast('Filters Cleared', 'Displaying all available leads.', 'info');
      }
    });
  }

  // Select All Checkbox
  const selectAll = document.getElementById('selectAllLeads');
  if (selectAll) {
    selectAll.addEventListener('change', (e) => {
      const isChecked = e.target.checked;
      const checkboxes = document.querySelectorAll('.lead-row-checkbox');
      checkboxes.forEach(cb => { cb.checked = isChecked; });
      updateBatchToolbar();
    });
  }

  // Add / Edit Lead Form Submit
  const leadForm = document.getElementById('leadModalForm');
  if (leadForm) {
    leadForm.addEventListener('submit', handleLeadFormSubmit);
  }

  // Delete Lead Confirmation Button
  const confirmDeleteBtn = document.getElementById('confirmDeleteLeadBtn');
  if (confirmDeleteBtn) {
    confirmDeleteBtn.addEventListener('click', () => {
      if (!leadToDeleteId) return;

      const targetId = leadToDeleteId;
      const formData = new FormData();
      formData.append('id', targetId);

      // Perform backend deletion in MySQL
      fetch('/leadflow/leads/delete', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          leadsData = leadsData.filter(l => l.id !== targetId && l.db_id != targetId);
          saveLeads();
          applyFilters();

          const deleteModalEl = document.getElementById('deleteLeadModal');
          if (deleteModalEl && window.bootstrap) {
            const modalInstance = bootstrap.Modal.getInstance(deleteModalEl);
            if (modalInstance) modalInstance.hide();
          }

          if (typeof showToast === 'function') {
            showToast('Lead Deleted', data.message || 'Lead removed from MySQL database.', 'danger');
          }
          leadToDeleteId = null;
        } else {
          if (typeof showToast === 'function') {
            showToast('Deletion Failed', data.error || 'Could not delete lead record.', 'warning');
          }
        }
      })
      .catch(() => {
        // Fallback local deletion
        leadsData = leadsData.filter(l => l.id !== targetId && l.db_id != targetId);
        saveLeads();
        applyFilters();
        const deleteModalEl = document.getElementById('deleteLeadModal');
        if (deleteModalEl && window.bootstrap) {
          const modalInstance = bootstrap.Modal.getInstance(deleteModalEl);
          if (modalInstance) modalInstance.hide();
        }
        if (typeof showToast === 'function') {
          showToast('Lead Deleted', `Lead removed.`, 'danger');
        }
        leadToDeleteId = null;
      });
    });
  }

  // Export CSV Button
  const exportCsvBtn = document.getElementById('exportLeadsCsvBtn');
  if (exportCsvBtn) {
    exportCsvBtn.addEventListener('click', () => {
      window.location.href = '/leadflow/leads/export';
    });
  }

  // Batch Delete Button
  const batchDeleteBtn = document.getElementById('batchDeleteBtn');
  if (batchDeleteBtn) {
    batchDeleteBtn.addEventListener('click', () => {
      const selectedIds = getSelectedLeadIds();
      if (selectedIds.length === 0) return;

      if (!confirm(`Are you sure you want to delete ${selectedIds.length} selected leads?`)) return;

      // Delete each on backend
      const deletePromises = selectedIds.map(id => {
        const fd = new FormData();
        fd.append('id', id);
        return fetch('/leadflow/leads/delete', {
          method: 'POST',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          body: fd
        });
      });

      Promise.all(deletePromises)
        .then(() => {
          leadsData = leadsData.filter(l => !selectedIds.includes(l.id) && !selectedIds.includes(String(l.db_id)));
          saveLeads();
          applyFilters();
          updateBatchToolbar();
          if (typeof showToast === 'function') {
            showToast('Batch Action', `Successfully removed ${selectedIds.length} leads.`, 'danger');
          }
        })
        .catch(() => {
          leadsData = leadsData.filter(l => !selectedIds.includes(l.id));
          saveLeads();
          applyFilters();
          updateBatchToolbar();
        });
    });
  }
}

// Filter and Sort Data
function applyFilters() {
  const searchInput = document.getElementById('tableSearchInput') || document.getElementById('globalSearchInput');
  const searchTerm = (searchInput ? searchInput.value : '').toLowerCase().trim();
  const statusVal = document.getElementById('filterStatus')?.value || 'all';
  const priorityVal = document.getElementById('filterPriority')?.value || 'all';
  const employeeVal = document.getElementById('filterEmployee')?.value || 'all';

  filteredLeads = leadsData.filter(lead => {
    // Search match
    const leadCode = (lead.id || '').toLowerCase();
    const leadName = (lead.name || '').toLowerCase();
    const leadEmail = (lead.email || '').toLowerCase();
    const leadPhone = (lead.phone || '').toLowerCase();
    const leadCompany = (lead.company || '').toLowerCase();

    const matchesSearch = !searchTerm ||
      leadName.includes(searchTerm) ||
      leadEmail.includes(searchTerm) ||
      leadPhone.includes(searchTerm) ||
      leadCompany.includes(searchTerm) ||
      leadCode.includes(searchTerm);

    // Status match
    const matchesStatus = statusVal === 'all' || (lead.status || '').toLowerCase() === statusVal.toLowerCase();

    // Priority match
    const matchesPriority = priorityVal === 'all' || (lead.priority || '').toLowerCase() === priorityVal.toLowerCase();

    // Employee match
    const matchesEmployee = employeeVal === 'all' 
      || (lead.assignedTo || '').toLowerCase() === employeeVal.toLowerCase()
      || String(lead.assignedToId || '') === employeeVal;

    return matchesSearch && matchesStatus && matchesPriority && matchesEmployee;
  });

  // Sort
  filteredLeads.sort((a, b) => {
    let valA = a[currentSort.column] || '';
    let valB = b[currentSort.column] || '';

    if (typeof valA === 'string') valA = valA.toLowerCase();
    if (typeof valB === 'string') valB = valB.toLowerCase();

    if (valA < valB) return currentSort.direction === 'asc' ? -1 : 1;
    if (valA > valB) return currentSort.direction === 'asc' ? 1 : -1;
    return 0;
  });

  renderTable();
  renderPagination();
  updateStatsCounts();
}

// Render Leads Table
function renderTable() {
  const tbody = document.getElementById('leadsTableBody');
  const emptyState = document.getElementById('leadsEmptyState');
  const tableContainer = document.getElementById('leadsTableContainer');

  if (!tbody) return;

  tbody.innerHTML = '';

  if (filteredLeads.length === 0) {
    if (emptyState) emptyState.classList.remove('d-none');
    if (tableContainer) tableContainer.classList.add('d-none');
    return;
  }

  if (emptyState) emptyState.classList.add('d-none');
  if (tableContainer) tableContainer.classList.remove('d-none');

  const startIndex = (currentPage - 1) * itemsPerPage;
  const pageLeads = filteredLeads.slice(startIndex, startIndex + itemsPerPage);

  pageLeads.forEach(lead => {
    const tr = document.createElement('tr');
    tr.setAttribute('data-id', lead.id);

    const statusBadgeClass = getStatusBadgeClass(lead.status);
    const priorityBadgeClass = getPriorityBadgeClass(lead.priority);
    const initials = getInitials(lead.name);

    tr.innerHTML = `
      <td>
        <input type="checkbox" class="form-check-input lead-row-checkbox" value="${lead.id}" onchange="updateBatchToolbar()">
      </td>
      <td>
        <span class="font-monospace text-cyan fw-semibold fs-7">${escapeHtml(lead.id)}</span>
      </td>
      <td>
        <div class="d-flex align-items-center gap-2">
          <div class="avatar avatar-sm">${initials}</div>
          <div>
            <div class="fw-semibold text-main">${escapeHtml(lead.name)}</div>
            <div class="text-muted fs-8">${escapeHtml(lead.company)}</div>
          </div>
        </div>
      </td>
      <td>
        <a href="mailto:${escapeHtml(lead.email)}" class="text-secondary fs-7 text-decoration-none">
          ${escapeHtml(lead.email)}
        </a>
      </td>
      <td>
        <a href="tel:${escapeHtml(lead.phone || '')}" class="text-secondary fs-7 text-decoration-none">
          ${escapeHtml(lead.phone || '—')}
        </a>
      </td>
      <td>
        <span class="text-secondary fs-7">${escapeHtml(lead.assignedTo || 'Unassigned')}</span>
      </td>
      <td>
        <span class="badge-lead ${statusBadgeClass}">
          <span class="badge-dot"></span>
          ${escapeHtml(lead.status)}
        </span>
      </td>
      <td>
        <span class="badge-lead ${priorityBadgeClass}">
          ${escapeHtml(lead.priority)}
        </span>
      </td>
      <td>
        <span class="text-muted fs-8">${escapeHtml(lead.createdAt || '')}</span>
      </td>
      <td class="text-end">
        <div class="action-btn-group justify-content-end">
          <button type="button" class="action-btn btn-view" title="View Details" onclick="viewLead('${lead.id}')">
            <i class="bi bi-eye"></i>
          </button>
          <button type="button" class="action-btn btn-edit" title="Edit Lead" onclick="editLead('${lead.id}')">
            <i class="bi bi-pencil"></i>
          </button>
          <button type="button" class="action-btn btn-delete" title="Delete Lead" onclick="promptDeleteLead('${lead.id}')">
            <i class="bi bi-trash"></i>
          </button>
        </div>
      </td>
    `;

    tbody.appendChild(tr);
  });

  const selectAll = document.getElementById('selectAllLeads');
  if (selectAll) selectAll.checked = false;
  updateBatchToolbar();
}

// Render Pagination Controls
function renderPagination() {
  const container = document.getElementById('leadsPagination');
  const infoText = document.getElementById('leadsPaginationInfo');
  if (!container) return;

  const totalPages = Math.ceil(filteredLeads.length / itemsPerPage) || 1;
  const startItem = filteredLeads.length === 0 ? 0 : (currentPage - 1) * itemsPerPage + 1;
  const endItem = Math.min(currentPage * itemsPerPage, filteredLeads.length);

  if (infoText) {
    infoText.textContent = `Showing ${startItem} to ${endItem} of ${filteredLeads.length} leads`;
  }

  container.innerHTML = '';

  // Prev Button
  const prevLi = document.createElement('li');
  prevLi.innerHTML = `
    <button class="page-link-custom ${currentPage === 1 ? 'disabled' : ''}" aria-label="Previous">
      <i class="bi bi-chevron-left"></i>
    </button>
  `;
  prevLi.querySelector('button').addEventListener('click', () => {
    if (currentPage > 1) {
      currentPage--;
      renderTable();
      renderPagination();
    }
  });
  container.appendChild(prevLi);

  // Page Numbers
  for (let i = 1; i <= totalPages; i++) {
    const li = document.createElement('li');
    li.innerHTML = `
      <button class="page-link-custom ${i === currentPage ? 'active' : ''}">${i}</button>
    `;
    const btn = li.querySelector('button');
    btn.addEventListener('click', () => {
      currentPage = i;
      renderTable();
      renderPagination();
    });
    container.appendChild(li);
  }

  // Next Button
  const nextLi = document.createElement('li');
  nextLi.innerHTML = `
    <button class="page-link-custom ${currentPage === totalPages ? 'disabled' : ''}" aria-label="Next">
      <i class="bi bi-chevron-right"></i>
    </button>
  `;
  nextLi.querySelector('button').addEventListener('click', () => {
    if (currentPage < totalPages) {
      currentPage++;
      renderTable();
      renderPagination();
    }
  });
  container.appendChild(nextLi);
}

// Sort Column Handler
function sortColumn(columnName) {
  if (currentSort.column === columnName) {
    currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
  } else {
    currentSort.column = columnName;
    currentSort.direction = 'asc';
  }
  applyFilters();
}

// Add or Edit Lead Form Submission via MySQL Backend
function handleLeadFormSubmit(e) {
  e.preventDefault();
  const form = e.target;
  const idInput = document.getElementById('leadFormId');
  const isEditing = Boolean(idInput && idInput.value);

  const name = document.getElementById('leadName').value.trim();
  const email = document.getElementById('leadEmail').value.trim();
  const phone = document.getElementById('leadPhone').value.trim();
  const company = document.getElementById('leadCompany').value.trim();
  const source = document.getElementById('leadSource').value;
  const status = document.getElementById('leadStatus').value;
  const priority = document.getElementById('leadPriority').value;
  const assignedToSelect = document.getElementById('leadAssignedTo');
  const assignedToId = assignedToSelect ? assignedToSelect.value : '';
  const assignedToText = assignedToSelect && assignedToSelect.selectedIndex >= 0 
    ? assignedToSelect.options[assignedToSelect.selectedIndex].text.replace(/\s*\(.*?\)/, '').trim()
    : 'Unassigned';
  const followUpDate = document.getElementById('leadFollowUpDate').value;
  const notes = document.getElementById('leadNotes').value.trim();

  if (!name || !email || !company) {
    if (typeof showToast === 'function') {
      showToast('Validation Error', 'Please fill in all required fields (Name, Email, Company).', 'warning');
    }
    return;
  }

  const formData = new FormData();
  if (isEditing) {
    formData.append('id', idInput.value);
  }
  formData.append('name', name);
  formData.append('email', email);
  formData.append('phone', phone);
  formData.append('company', company);
  formData.append('lead_source', source);
  formData.append('status', status);
  formData.append('priority', priority);
  formData.append('assigned_to_id', assignedToId);
  formData.append('follow_up_date', followUpDate);
  formData.append('notes', notes);

  const endpoint = isEditing ? '/leadflow/leads/update' : '/leadflow/leads/store';

  fetch(endpoint, {
    method: 'POST',
    headers: { 'X-Requested-With': 'XMLHttpRequest' },
    body: formData
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      if (isEditing) {
        const existingIndex = leadsData.findIndex(l => l.id === idInput.value || l.db_id == idInput.value);
        if (existingIndex !== -1) {
          leadsData[existingIndex] = {
            ...leadsData[existingIndex],
            name,
            email,
            phone,
            company,
            source,
            status,
            priority,
            assignedTo: assignedToText,
            assignedToId,
            followUpDate,
            notes
          };
        }
        if (typeof showToast === 'function') {
          showToast('Lead Updated', `Lead ${name} was updated successfully in MySQL.`, 'success');
        }
      } else {
        const newLead = {
          id: data.lead?.lead_code || ('LF-' + Math.floor(1000 + Math.random() * 9000)),
          db_id: data.lead?.id,
          name,
          email,
          phone,
          company,
          source,
          status,
          priority,
          assignedTo: assignedToText,
          assignedToId,
          createdAt: new Date().toISOString().split('T')[0],
          notes,
          followUpDate
        };
        leadsData.unshift(newLead);
        if (typeof showToast === 'function') {
          showToast('Lead Created', `New opportunity for ${company} saved to MySQL!`, 'success');
        }
      }

      saveLeads();
      applyFilters();

      // Close modal
      const modalEl = document.getElementById('addEditLeadModal');
      if (modalEl && window.bootstrap) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl);
        if (modalInstance) modalInstance.hide();
      }

      form.reset();
    } else {
      if (typeof showToast === 'function') {
        showToast('Error', data.error || 'Server error occurred.', 'warning');
      }
    }
  })
  .catch(err => {
    console.error(err);
    if (typeof showToast === 'function') {
      showToast('Network Error', 'Could not reach server. Submitting via standard form...', 'warning');
    }
    form.submit();
  });
}

// Open Modal for New Lead
function openAddLeadModal() {
  const modalEl = document.getElementById('addEditLeadModal');
  const title = document.getElementById('leadModalTitle');
  const form = document.getElementById('leadModalForm');
  const idInput = document.getElementById('leadFormId');

  if (title) title.innerHTML = '<i class="bi bi-person-plus text-purple me-2"></i>Add New Lead';
  if (form) form.reset();
  if (idInput) idInput.value = '';

  if (modalEl && window.bootstrap) {
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  }
}

// Open Modal to Edit Existing Lead
function editLead(id) {
  const lead = leadsData.find(l => l.id === id || l.db_id == id);
  if (!lead) return;

  const modalEl = document.getElementById('addEditLeadModal');
  const title = document.getElementById('leadModalTitle');
  const idInput = document.getElementById('leadFormId');

  if (title) title.innerHTML = `<i class="bi bi-pencil-square text-cyan me-2"></i>Edit Lead (${escapeHtml(lead.id)})`;
  if (idInput) idInput.value = lead.id;

  // Fill form fields
  document.getElementById('leadName').value = lead.name || '';
  document.getElementById('leadEmail').value = lead.email || '';
  document.getElementById('leadPhone').value = lead.phone || '';
  document.getElementById('leadCompany').value = lead.company || '';
  document.getElementById('leadSource').value = lead.source || 'Website Form';
  document.getElementById('leadStatus').value = lead.status || 'New';
  document.getElementById('leadPriority').value = lead.priority || 'Medium';
  
  const assignedSelect = document.getElementById('leadAssignedTo');
  if (assignedSelect) {
    if (lead.assignedToId) {
      assignedSelect.value = lead.assignedToId;
    } else {
      // Find by text
      for (let i = 0; i < assignedSelect.options.length; i++) {
        if (assignedSelect.options[i].text.includes(lead.assignedTo)) {
          assignedSelect.selectedIndex = i;
          break;
        }
      }
    }
  }

  document.getElementById('leadFollowUpDate').value = lead.followUpDate || '';
  document.getElementById('leadNotes').value = lead.notes || '';

  if (modalEl && window.bootstrap) {
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  }
}

// View Lead Details Drawer
function viewLead(id) {
  const lead = leadsData.find(l => l.id === id || l.db_id == id);
  if (!lead) return;

  const modalEl = document.getElementById('viewLeadModal');
  if (!modalEl) return;

  document.getElementById('viewLeadId').textContent = lead.id;
  document.getElementById('viewLeadName').textContent = lead.name;
  document.getElementById('viewLeadCompany').textContent = lead.company;
  document.getElementById('viewLeadAvatar').textContent = getInitials(lead.name);

  // Status badge
  const statusBadge = document.getElementById('viewLeadStatusBadge');
  if (statusBadge) {
    statusBadge.className = `badge-lead ${getStatusBadgeClass(lead.status)}`;
    statusBadge.innerHTML = `<span class="badge-dot"></span> ${escapeHtml(lead.status)}`;
  }

  // Priority badge
  const priorityBadge = document.getElementById('viewLeadPriorityBadge');
  if (priorityBadge) {
    priorityBadge.className = `badge-lead ${getPriorityBadgeClass(lead.priority)}`;
    priorityBadge.textContent = lead.priority;
  }

  // Contact info
  const emailEl = document.getElementById('viewLeadEmail');
  const emailLink = document.getElementById('viewLeadEmailLink');
  if (emailEl) emailEl.textContent = lead.email;
  if (emailLink) emailLink.href = `mailto:${lead.email}`;

  const phoneEl = document.getElementById('viewLeadPhone');
  const phoneLink = document.getElementById('viewLeadPhoneLink');
  if (phoneEl) phoneEl.textContent = lead.phone || 'Not provided';
  if (phoneLink) phoneLink.href = lead.phone ? `tel:${lead.phone}` : '#';

  const sourceEl = document.getElementById('viewLeadSource');
  if (sourceEl) sourceEl.textContent = lead.source || 'Website Form';

  const assignedEl = document.getElementById('viewLeadAssigned');
  if (assignedEl) assignedEl.textContent = lead.assignedTo || 'Unassigned';

  const dateEl = document.getElementById('viewLeadDate');
  if (dateEl) dateEl.textContent = lead.createdAt || 'Recent';

  const notesEl = document.getElementById('viewLeadNotes');
  if (notesEl) notesEl.textContent = lead.notes || 'No qualification notes recorded yet.';

  if (window.bootstrap) {
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  }
}

// Prompt Delete Confirmation Modal
function promptDeleteLead(id) {
  leadToDeleteId = id;
  const lead = leadsData.find(l => l.id === id || l.db_id == id);
  const targetNameEl = document.getElementById('deleteLeadTargetName');
  if (targetNameEl) {
    targetNameEl.textContent = lead ? `${lead.name} (${lead.id})` : id;
  }

  const modalEl = document.getElementById('deleteLeadModal');
  if (modalEl && window.bootstrap) {
    const modal = new bootstrap.Modal(modalEl);
    modal.show();
  }
}

// Batch Actions Helpers
function updateBatchToolbar() {
  const toolbar = document.getElementById('batchToolbar');
  const countEl = document.getElementById('batchSelectedCount');
  if (!toolbar) return;

  const selectedCount = getSelectedLeadIds().length;
  if (selectedCount > 0) {
    toolbar.classList.add('active');
    if (countEl) countEl.textContent = `${selectedCount} Selected`;
  } else {
    toolbar.classList.remove('active');
  }
}

function getSelectedLeadIds() {
  const checked = document.querySelectorAll('.lead-row-checkbox:checked');
  return Array.from(checked).map(cb => cb.value);
}

function updateStatsCounts() {
  const countBadge = document.getElementById('sidebarLeadCount');
  if (countBadge) countBadge.textContent = leadsData.length;
}

function setupAvatarPreview() {
  const avatarInput = document.getElementById('leadAvatarInput');
  const avatarPreview = document.getElementById('avatarImagePreview');
  if (avatarInput && avatarPreview) {
    avatarInput.addEventListener('change', (e) => {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = (event) => { avatarPreview.src = event.target.result; };
        reader.readAsDataURL(file);
      }
    });
  }
}

// Helpers
function getStatusBadgeClass(status) {
  switch ((status || '').toLowerCase()) {
    case 'new': return 'badge-status-new';
    case 'contacted': return 'badge-status-contacted';
    case 'interested': return 'badge-status-interested';
    case 'converted': return 'badge-status-converted';
    case 'lost': return 'badge-status-lost';
    default: return 'badge-status-new';
  }
}

function getPriorityBadgeClass(priority) {
  switch ((priority || '').toLowerCase()) {
    case 'high': return 'badge-priority-high';
    case 'medium': return 'badge-priority-medium';
    case 'low': return 'badge-priority-low';
    default: return 'badge-priority-medium';
  }
}

function getInitials(name) {
  if (!name) return 'LF';
  return name.split(' ')
    .filter(Boolean)
    .map(p => p[0].toUpperCase())
    .slice(0, 2)
    .join('');
}

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&#039;');
}
