/**
 * LeadFlow — Premium SaaS Lead Management System
 * Core UI Logic, State Helpers, and Toast Notifications
 */

// Default Seed Data for Interactive Client-Side Demo
const DEFAULT_LEADS = [
  {
    id: 'LF-1042',
    name: 'Sarah Jenkins',
    email: 'sarah.j@techcorp.io',
    phone: '+1 (555) 234-8901',
    company: 'TechCorp Solutions',
    source: 'Website Form',
    status: 'Interested',
    priority: 'High',
    assignedTo: 'Alex Morgan',
    createdAt: '2026-10-18',
    notes: 'Interested in the Enterprise Tier. Requested custom demo for 50+ seats.',
    followUpDate: '2026-10-25T14:30'
  },
  {
    id: 'LF-1041',
    name: 'Marcus Vance',
    email: 'm.vance@apexlogistics.com',
    phone: '+1 (555) 489-1234',
    company: 'Apex Logistics',
    source: 'LinkedIn Outreach',
    status: 'Contacted',
    priority: 'Medium',
    assignedTo: 'Elena Rostova',
    createdAt: '2026-10-19',
    notes: 'Had initial discovery call. Sent product brochure and case studies.',
    followUpDate: '2026-10-26T11:00'
  },
  {
    id: 'LF-1040',
    name: 'David Kim',
    email: 'dkim@pulsecreative.co',
    phone: '+1 (555) 872-6543',
    company: 'Pulse Creative',
    source: 'Google Ads',
    status: 'New',
    priority: 'Medium',
    assignedTo: 'Alex Morgan',
    createdAt: '2026-10-22',
    notes: 'Downloaded whitepaper on CRM automation. Needs initial reach-out.',
    followUpDate: '2026-10-24T16:00'
  },
  {
    id: 'LF-1039',
    name: 'Emma Watson-Reid',
    email: 'emma@fintechhub.net',
    phone: '+1 (555) 312-9876',
    company: 'Fintech Hub UK',
    source: 'Referral',
    status: 'Converted',
    priority: 'High',
    assignedTo: 'David Miller',
    createdAt: '2026-10-10',
    notes: 'Annual contract closed for $48,000 ARR. Onboarding scheduled.',
    followUpDate: '2026-10-28T10:00'
  },
  {
    id: 'LF-1038',
    name: 'Robert Hastings',
    email: 'rhastings@cloudscale.dev',
    phone: '+1 (555) 671-2299',
    company: 'CloudScale Inc',
    source: 'Cold Email',
    status: 'Lost',
    priority: 'Low',
    assignedTo: 'Elena Rostova',
    createdAt: '2026-10-05',
    notes: 'Chose competitor due to existing legacy ERP integration constraints.',
    followUpDate: ''
  },
  {
    id: 'LF-1037',
    name: 'Priya Sharma',
    email: 'priya.s@aegiscloud.in',
    phone: '+91 98765 43210',
    company: 'Aegis Security',
    source: 'Website Form',
    status: 'Interested',
    priority: 'High',
    assignedTo: 'Alex Morgan',
    createdAt: '2026-10-20',
    notes: 'Evaluating security compliance certifications (SOC2, ISO).',
    followUpDate: '2026-10-25T16:30'
  },
  {
    id: 'LF-1036',
    name: 'Thomas Becker',
    email: 't.becker@bavaria-auto.de',
    phone: '+49 89 1234 5678',
    company: 'Bavaria Mobility',
    source: 'Partner Event',
    status: 'Contacted',
    priority: 'Low',
    assignedTo: 'David Miller',
    createdAt: '2026-10-14',
    notes: 'Met at SaaS Expo 2026. Needs follow-up with enterprise deck.',
    followUpDate: '2026-10-27T09:00'
  },
  {
    id: 'LF-1035',
    name: 'Claire Dupont',
    email: 'cdupont@lumina-retail.fr',
    phone: '+33 1 42 68 55 00',
    company: 'Lumina Retail',
    source: 'Google Ads',
    status: 'New',
    priority: 'Medium',
    assignedTo: 'Elena Rostova',
    createdAt: '2026-10-23',
    notes: 'Multi-store retail chain looking for centralized lead tracking.',
    followUpDate: '2026-10-24T18:00'
  }
];

const DEFAULT_FOLLOWUPS = [
  {
    id: 'FU-201',
    leadId: 'LF-1042',
    customerName: 'Sarah Jenkins',
    company: 'TechCorp Solutions',
    assignedTo: 'Alex Morgan',
    type: 'Video Call',
    dueDate: '2026-10-24T14:30', // Today
    status: 'upcoming',
    notes: 'Enterprise platform architecture walkthrough with CTO.'
  },
  {
    id: 'FU-202',
    leadId: 'LF-1040',
    customerName: 'David Kim',
    company: 'Pulse Creative',
    assignedTo: 'Alex Morgan',
    type: 'Phone Call',
    dueDate: '2026-10-24T16:00', // Today
    status: 'upcoming',
    notes: 'Initial qualification call on CRM integration needs.'
  },
  {
    id: 'FU-203',
    leadId: 'LF-1035',
    customerName: 'Claire Dupont',
    company: 'Lumina Retail',
    assignedTo: 'Elena Rostova',
    type: 'Email',
    dueDate: '2026-10-23T11:00', // Overdue
    status: 'overdue',
    notes: 'Send European compliance documentation and pricing matrix.'
  },
  {
    id: 'FU-204',
    leadId: 'LF-1037',
    customerName: 'Priya Sharma',
    company: 'Aegis Security',
    assignedTo: 'Alex Morgan',
    type: 'Video Call',
    dueDate: '2026-10-25T16:30',
    status: 'upcoming',
    notes: 'SOC2 & HIPAA security review meeting.'
  },
  {
    id: 'FU-205',
    leadId: 'LF-1039',
    customerName: 'Emma Watson-Reid',
    company: 'Fintech Hub UK',
    assignedTo: 'David Miller',
    type: 'Meeting',
    dueDate: '2026-10-18T10:00',
    status: 'completed',
    notes: 'Final contract countersigned. Transferred to Onboarding team.'
  }
];

const DEFAULT_USERS = [
  {
    id: 'USR-1',
    name: 'Alex Morgan',
    email: 'alex.morgan@leadflow.io',
    role: 'Admin',
    department: 'Sales Leadership',
    assignedLeads: 42,
    status: 'Active',
    joined: 'Jan 2025',
    avatar: 'AM'
  },
  {
    id: 'USR-2',
    name: 'Elena Rostova',
    email: 'elena.r@leadflow.io',
    role: 'Employee',
    department: 'Inside Sales',
    assignedLeads: 28,
    status: 'Active',
    joined: 'Mar 2025',
    avatar: 'ER'
  },
  {
    id: 'USR-3',
    name: 'David Miller',
    email: 'david.m@leadflow.io',
    role: 'Employee',
    department: 'Enterprise Sales',
    assignedLeads: 35,
    status: 'Active',
    joined: 'Feb 2025',
    avatar: 'DM'
  },
  {
    id: 'USR-4',
    name: 'Sophia Chen',
    email: 'sophia.c@leadflow.io',
    role: 'Employee',
    department: 'Inbound SDR',
    assignedLeads: 19,
    status: 'Active',
    joined: 'May 2025',
    avatar: 'SC'
  },
  {
    id: 'USR-5',
    name: 'Liam O’Connor',
    email: 'liam.o@leadflow.io',
    role: 'Employee',
    department: 'Field Sales',
    assignedLeads: 12,
    status: 'Inactive',
    joined: 'Jun 2025',
    avatar: 'LO'
  }
];

// Seed localStorage if not present
function initializeDemoData() {
  if (!localStorage.getItem('leadflow_leads')) {
    localStorage.setItem('leadflow_leads', JSON.stringify(DEFAULT_LEADS));
  }
  if (!localStorage.getItem('leadflow_followups')) {
    localStorage.setItem('leadflow_followups', JSON.stringify(DEFAULT_FOLLOWUPS));
  }
  if (!localStorage.getItem('leadflow_users')) {
    localStorage.setItem('leadflow_users', JSON.stringify(DEFAULT_USERS));
  }
}

// Reset Demo Data
function resetDemoData() {
  localStorage.setItem('leadflow_leads', JSON.stringify(DEFAULT_LEADS));
  localStorage.setItem('leadflow_followups', JSON.stringify(DEFAULT_FOLLOWUPS));
  localStorage.setItem('leadflow_users', JSON.stringify(DEFAULT_USERS));
  showToast('Demo Data Reset', 'All records have been restored to initial sample state.', 'success');
  setTimeout(() => window.location.reload(), 1200);
}

// Show Toast Notification
function showToast(title, message, type = 'info') {
  let container = document.getElementById('toastContainer');
  if (!container) {
    container = document.createElement('div');
    container.id = 'toastContainer';
    container.className = 'toast-container-custom';
    document.body.appendChild(container);
  }

  const toast = document.createElement('div');
  toast.className = `toast-custom toast-${type}`;

  let iconClass = 'bi-info-circle-fill';
  if (type === 'success') iconClass = 'bi-check-circle-fill';
  if (type === 'warning') iconClass = 'bi-exclamation-triangle-fill';
  if (type === 'danger') iconClass = 'bi-x-circle-fill';

  toast.innerHTML = `
    <i class="bi ${iconClass} toast-icon"></i>
    <div class="toast-content">
      <div class="toast-title">${title}</div>
      <div class="toast-message">${message}</div>
    </div>
    <button type="button" class="toast-close" aria-label="Close">&times;</button>
  `;

  toast.querySelector('.toast-close').addEventListener('click', () => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(50px)';
    setTimeout(() => toast.remove(), 250);
  });

  container.appendChild(toast);

  // Auto dismiss after 4 seconds
  setTimeout(() => {
    if (toast.parentElement) {
      toast.style.opacity = '0';
      toast.style.transform = 'translateX(50px)';
      setTimeout(() => toast.remove(), 250);
    }
  }, 4000);
}

// Sidebar Collapse & Mobile Handlers
document.addEventListener('DOMContentLoaded', () => {
  initializeDemoData();

  // Desktop Sidebar Toggle
  const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
  if (sidebarToggleBtn) {
    sidebarToggleBtn.addEventListener('click', (e) => {
      e.preventDefault();
      document.body.classList.toggle('sidebar-collapsed');
      const isCollapsed = document.body.classList.contains('sidebar-collapsed');
      localStorage.setItem('leadflow_sidebar_collapsed', isCollapsed ? '1' : '0');
    });

    if (localStorage.getItem('leadflow_sidebar_collapsed') === '1' && window.innerWidth > 992) {
      document.body.classList.add('sidebar-collapsed');
    }
  }

  // Mobile Sidebar Toggle
  const mobileSidebarToggle = document.getElementById('mobileSidebarToggle');
  const sidebarBackdrop = document.getElementById('sidebarBackdrop');

  if (mobileSidebarToggle) {
    mobileSidebarToggle.addEventListener('click', () => {
      document.body.classList.toggle('sidebar-open');
    });
  }

  if (sidebarBackdrop) {
    sidebarBackdrop.addEventListener('click', () => {
      document.body.classList.remove('sidebar-open');
    });
  }

  // Highlight Current Nav Item
  const currentPath = window.location.pathname.replace(/\/$/, '').split('/').pop() || 'dashboard';
  const navLinks = document.querySelectorAll('.nav-link-custom');
  navLinks.forEach(link => {
    const href = (link.getAttribute('href') || '').replace(/\/$/, '').split('/').pop();
    if (href && href === currentPath) {
      link.classList.add('active');
    }
  });

  // Global Shortcut: Ctrl+K / Cmd+K to focus search
  window.addEventListener('keydown', (e) => {
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
      e.preventDefault();
      const searchInput = document.getElementById('globalSearchInput') || document.getElementById('tableSearchInput');
      if (searchInput) {
        searchInput.focus();
        showToast('Quick Search', 'Type to search leads, companies, or contacts.', 'info');
      }
    }
  });

  // Mark all notifications read demo
  const markNotificationsReadBtn = document.getElementById('markNotificationsReadBtn');
  if (markNotificationsReadBtn) {
    markNotificationsReadBtn.addEventListener('click', (e) => {
      e.preventDefault();
      const unreadBadge = document.getElementById('unreadNotificationCount');
      if (unreadBadge) unreadBadge.style.display = 'none';
      const notificationDots = document.querySelectorAll('.notification-dot');
      notificationDots.forEach(dot => dot.remove());
      showToast('Notifications Updated', 'All notifications marked as read.', 'success');
    });
  }
});

// CSV Export Utility
function downloadCSV(filename, rows) {
  const processRow = function (row) {
    let finalVal = '';
    for (let j = 0; j < row.length; j++) {
      let innerValue = row[j] === null ? '' : row[j].toString();
      if (row[j] instanceof Date) {
        innerValue = row[j].toLocaleString();
      }
      let result = innerValue.replace(/"/g, '""');
      if (result.search(/("|,|\n)/g) >= 0)
        result = '"' + result + '"';
      if (j > 0)
        finalVal += ',';
      finalVal += result;
    }
    return finalVal + '\n';
  };

  let csvFile = '';
  for (let i = 0; i < rows.length; i++) {
    csvFile += processRow(rows[i]);
  }

  const blob = new Blob([csvFile], { type: 'text/csv;charset=utf-8;' });
  const link = document.createElement('a');
  if (link.download !== undefined) {
    const url = URL.createObjectURL(blob);
    link.setAttribute('href', url);
    link.setAttribute('download', filename);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
  }
}
