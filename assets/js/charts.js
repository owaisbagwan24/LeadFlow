/**
 * LeadFlow — Premium SaaS Lead Management System
 * Chart.js Visualizations with Electric Purple & Cyan Aesthetics
 */

// Shared Dark Chart Defaults
const chartDefaults = {
  color: '#94A3B8',
  font: {
    family: "'Inter', sans-serif",
    size: 12
  },
  plugins: {
    legend: {
      labels: {
        color: '#F8FAFC',
        font: { family: "'Inter', sans-serif", size: 12, weight: 500 },
        usePointStyle: true,
        padding: 16
      }
    },
    tooltip: {
      backgroundColor: 'rgba(21, 30, 51, 0.95)',
      titleColor: '#F8FAFC',
      bodyColor: '#94A3B8',
      borderColor: 'rgba(124, 58, 237, 0.4)',
      borderWidth: 1,
      padding: 12,
      cornerRadius: 8,
      boxPadding: 6,
      usePointStyle: true
    }
  }
};

// Initialize Dashboard Charts
function initDashboardCharts() {
  if (typeof Chart === 'undefined') return;

  // 1. Lead Acquisition & Conversion Trends (Dashboard)
  const trendsCtx = document.getElementById('leadTrendsChart');
  if (trendsCtx) {
    const ctx = trendsCtx.getContext('2d');

    // Create Purple Gradient
    const purpleGrad = ctx.createLinearGradient(0, 0, 0, 300);
    purpleGrad.addColorStop(0, 'rgba(124, 58, 237, 0.35)');
    purpleGrad.addColorStop(1, 'rgba(124, 58, 237, 0.01)');

    // Create Cyan Gradient
    const cyanGrad = ctx.createLinearGradient(0, 0, 0, 300);
    cyanGrad.addColorStop(0, 'rgba(34, 211, 238, 0.35)');
    cyanGrad.addColorStop(1, 'rgba(34, 211, 238, 0.01)');

    new Chart(trendsCtx, {
      type: 'line',
      data: {
        labels: ['May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct'],
        datasets: [
          {
            label: 'Total Leads Acquired',
            data: [120, 165, 195, 230, 275, 340],
            borderColor: '#7C3AED',
            backgroundColor: purpleGrad,
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#7C3AED',
            pointBorderColor: '#0B1020',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
          },
          {
            label: 'Converted Customers',
            data: [28, 42, 55, 68, 82, 108],
            borderColor: '#22D3EE',
            backgroundColor: cyanGrad,
            borderWidth: 2.5,
            fill: true,
            tension: 0.4,
            pointBackgroundColor: '#22D3EE',
            pointBorderColor: '#0B1020',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
          }
        ]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: chartDefaults.plugins,
        scales: {
          x: {
            grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
            ticks: { color: '#94A3B8' }
          },
          y: {
            grid: { color: 'rgba(255, 255, 255, 0.05)', drawBorder: false },
            ticks: { color: '#94A3B8' }
          }
        }
      }
    });
  }

  // 2. Lead Status Distribution (Doughnut)
  const statusCtx = document.getElementById('leadStatusChart');
  if (statusCtx) {
    const dynamicData = (window.leadFlowChartData && Array.isArray(window.leadFlowChartData.statusCounts))
      ? window.leadFlowChartData.statusCounts
      : [28, 35, 22, 25, 10];

    new Chart(statusCtx, {
      type: 'doughnut',
      data: {
        labels: ['New', 'Contacted', 'Interested', 'Converted', 'Lost'],
        datasets: [{
          data: dynamicData,
          backgroundColor: [
            '#22D3EE', // New - Cyan
            '#6366F1', // Contacted - Indigo
            '#A855F7', // Interested - Purple
            '#22C55E', // Converted - Green
            '#F43F5E'  // Lost - Rose
          ],
          borderColor: '#151E33',
          borderWidth: 3,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
          ...chartDefaults.plugins,
          legend: {
            position: 'bottom',
            labels: {
              color: '#94A3B8',
              usePointStyle: true,
              padding: 12,
              font: { family: "'Inter', sans-serif", size: 11 }
            }
          }
        }
      }
    });
  }
}

// Initialize Reports & Analytics Page Charts
function initReportsCharts() {
  if (typeof Chart === 'undefined') return;

  const rd = window.reportData || {};

  // 1. Pipeline Status Doughnut (Reports page)
  const statusCtxR = document.getElementById('leadsByStatusChart');
  if (statusCtxR) {
    const labels = rd.statusLabels && rd.statusLabels.length ? rd.statusLabels : ['New','Contacted','Interested','Converted','Lost'];
    const values = rd.statusValues && rd.statusValues.length ? rd.statusValues : [0,0,0,0,0];
    new Chart(statusCtxR, {
      type: 'doughnut',
      data: {
        labels,
        datasets: [{
          data: values,
          backgroundColor: ['#22D3EE','#6366F1','#A855F7','#22C55E','#F43F5E'],
          borderColor: '#151E33',
          borderWidth: 3,
          hoverOffset: 6
        }]
      },
      options: {
        responsive: true, maintainAspectRatio: false, cutout: '68%',
        plugins: {
          ...chartDefaults.plugins,
          legend: { position: 'bottom', labels: { color: '#94A3B8', usePointStyle: true, padding: 10, font: { size: 11 } } }
        }
      }
    });
  }

  // 2. Leads by Source (Horizontal Bar)
  const sourceCtx = document.getElementById('leadsBySourceChart');
  if (sourceCtx) {
    const labels = rd.sourceLabels && rd.sourceLabels.length ? rd.sourceLabels : ['No data'];
    const values = rd.sourceValues && rd.sourceValues.length ? rd.sourceValues : [0];
    new Chart(sourceCtx, {
      type: 'bar',
      data: {
        labels,
        datasets: [{
          label: 'Leads Generated',
          data: values,
          backgroundColor: ['#7C3AED','#22D3EE','#38BDF8','#22C55E','#F59E0B','#EC4899','#6366F1'],
          borderRadius: 6, borderSkipped: false
        }]
      },
      options: {
        indexAxis: 'y', responsive: true, maintainAspectRatio: false,
        plugins: { ...chartDefaults.plugins, legend: { display: false } },
        scales: {
          x: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94A3B8' } },
          y: { grid: { display: false }, ticks: { color: '#F8FAFC' } }
        }
      }
    });
  }

  // 3. Sales Rep Performance (Grouped Bar)
  const repCtx = document.getElementById('repPerformanceChart');
  if (repCtx) {
    const labels = rd.repNames && rd.repNames.length ? rd.repNames : ['No reps'];
    const converted = rd.repConverted && rd.repConverted.length ? rd.repConverted : [0];
    const active    = rd.repActive && rd.repActive.length ? rd.repActive : [0];
    new Chart(repCtx, {
      type: 'bar',
      data: {
        labels,
        datasets: [
          { label: 'Converted Leads', data: converted, backgroundColor: '#22D3EE', borderRadius: 6 },
          { label: 'Active Pipeline', data: active, backgroundColor: 'rgba(124,58,237,0.6)', borderRadius: 6 }
        ]
      },
      options: {
        responsive: true, maintainAspectRatio: false,
        plugins: chartDefaults.plugins,
        scales: {
          x: { grid: { display: false }, ticks: { color: '#F8FAFC' } },
          y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94A3B8' } }
        }
      }
    });
  }
}

document.addEventListener('DOMContentLoaded', () => {
  initDashboardCharts();
  initReportsCharts();
});
