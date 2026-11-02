/**
 * LeadFlow — Premium SaaS Lead Management System
 * Authentication & Form Validation Logic
 */

document.addEventListener('DOMContentLoaded', () => {
  // Show/Hide Password Toggles
  const togglePasswordBtns = document.querySelectorAll('.toggle-password-btn');
  togglePasswordBtns.forEach(btn => {
    btn.addEventListener('click', () => {
      const targetId = btn.getAttribute('data-target');
      const input = document.getElementById(targetId);
      const icon = btn.querySelector('i');
      if (input) {
        if (input.type === 'password') {
          input.type = 'text';
          if (icon) {
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
          }
        } else {
          input.type = 'password';
          if (icon) {
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
          }
        }
      }
    });
  });

  // Password Strength Meter (Register Page)
  const regPasswordInput = document.getElementById('regPassword');
  const strengthText = document.getElementById('strengthText');
  const strengthSteps = document.querySelectorAll('.strength-step');

  if (regPasswordInput && strengthSteps.length > 0) {
    regPasswordInput.addEventListener('input', () => {
      const val = regPasswordInput.value;
      let score = 0;

      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      // Reset steps
      strengthSteps.forEach((step, idx) => {
        step.className = 'strength-step';
        if (score > 0 && idx < score) {
          if (score === 1) step.classList.add('weak');
          else if (score === 2) step.classList.add('fair');
          else if (score === 3) step.classList.add('good');
          else if (score >= 4) step.classList.add('strong');
        }
      });

      if (strengthText) {
        if (val.length === 0) strengthText.textContent = 'Password must be at least 8 characters';
        else if (score === 1) strengthText.textContent = 'Weak: Add numbers & capital letters';
        else if (score === 2) strengthText.textContent = 'Fair: Add special symbols (@, #, !)';
        else if (score === 3) strengthText.textContent = 'Good: Very close to strong';
        else if (score >= 4) strengthText.textContent = 'Strong: Excellent password protection';
      }
    });
  }

  // Quick Demo Credential Helper Chip
  const demoCredChip = document.getElementById('demoCredChip');
  if (demoCredChip) {
    demoCredChip.addEventListener('click', () => {
      const emailInput = document.getElementById('loginEmail');
      const passInput = document.getElementById('loginPassword');
      if (emailInput && passInput) {
        emailInput.value = 'admin@leadflow.io';
        passInput.value = 'LeadFlow2026!';
        if (typeof showToast === 'function') {
          showToast('Demo Credentials Applied', 'Click Sign In to access the dashboard.', 'info');
        }
      }
    });
  }

  // Handle Login Form Submit Frontend Validation
  const loginForm = document.getElementById('loginForm');
  if (loginForm) {
    loginForm.addEventListener('submit', (e) => {
      const email = document.getElementById('loginEmail');
      const pass = document.getElementById('loginPassword');
      const submitBtn = document.getElementById('loginSubmitBtn');

      let isValid = true;

      if (!email.value || !email.value.includes('@')) {
        email.classList.add('is-invalid');
        isValid = false;
      } else {
        email.classList.remove('is-invalid');
      }

      if (!pass.value || pass.value.length < 4) {
        pass.classList.add('is-invalid');
        isValid = false;
      } else {
        pass.classList.remove('is-invalid');
      }

      if (!isValid) {
        e.preventDefault();
        return;
      }

      if (submitBtn) {
        submitBtn.innerHTML = `
          <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          Authenticating...
        `;
      }
    });
  }

  // Handle Registration Form Submit Frontend Validation
  const registerForm = document.getElementById('registerForm');
  if (registerForm) {
    registerForm.addEventListener('submit', (e) => {
      const name = document.getElementById('regName');
      const email = document.getElementById('regEmail');
      const pass = document.getElementById('regPassword');
      const confirmPass = document.getElementById('regConfirmPassword');
      const terms = document.getElementById('regTerms');
      const submitBtn = document.getElementById('regSubmitBtn');

      let isValid = true;

      if (!name || !name.value.trim()) {
        if (name) name.classList.add('is-invalid');
        isValid = false;
      } else {
        name.classList.remove('is-invalid');
      }

      if (!email || !email.value || !email.value.includes('@')) {
        if (email) email.classList.add('is-invalid');
        isValid = false;
      } else {
        email.classList.remove('is-invalid');
      }

      if (!pass || !pass.value || pass.value.length < 8) {
        if (pass) pass.classList.add('is-invalid');
        isValid = false;
      } else {
        pass.classList.remove('is-invalid');
      }

      if (!confirmPass || pass.value !== confirmPass.value) {
        if (confirmPass) confirmPass.classList.add('is-invalid');
        isValid = false;
      } else {
        confirmPass.classList.remove('is-invalid');
      }

      if (!terms || !terms.checked) {
        if (terms) terms.classList.add('is-invalid');
        isValid = false;
      } else {
        terms.classList.remove('is-invalid');
      }

      if (!isValid) {
        e.preventDefault();
        return;
      }

      if (submitBtn) {
        submitBtn.innerHTML = `
          <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
          Creating Account...
        `;
      }
    });
  }
});
