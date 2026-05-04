   // ── AUTH MODAL ──
  const overlay = document.getElementById('authOverlay');

  function openAuth(tab = 'signin') {
    switchTab(tab);
    overlay.classList.add('open');
    document.body.style.overflow = 'hidden';
    // reset success screens
    document.getElementById('si-success').classList.remove('show');
    document.getElementById('rg-success').classList.remove('show');
    // focus first input after transition
    setTimeout(() => {
      const panel = tab === 'signin' ? 'si-email' : 'rg-first';
      const el = document.getElementById(panel);
      if (el) el.focus();
    }, 350);
  }

  function closeAuth() {
    overlay.classList.remove('open');
    document.body.style.overflow = '';
  }

  function handleOverlayClick(e) {
    if (e.target === overlay) closeAuth();
  }

  // close on Escape
  document.addEventListener('keydown', e => { if (e.key === 'Escape') closeAuth(); });

  function switchTab(tab) {
    document.querySelectorAll('.auth-tab').forEach(t => t.classList.remove('active'));
    document.querySelectorAll('.auth-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    document.getElementById('panel-' + tab).classList.add('active');
  }

  // ── PASSWORD TOGGLE ──
  function togglePw(inputId, btn) {
    const inp = document.getElementById(inputId);
    if (inp.type === 'password') { inp.type = 'text'; btn.textContent = '🙈'; }
    else { inp.type = 'password'; btn.textContent = '👁'; }
  }

  // ── PASSWORD STRENGTH ──
  function checkPwStrength(val) {
    const fill = document.getElementById('pw-fill');
    const label = document.getElementById('pw-label');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;
    const configs = [
      { w: '0%',   bg: 'transparent', lbl: '' },
      { w: '25%',  bg: '#e05555',      lbl: 'Weak' },
      { w: '50%',  bg: '#e08a30',      lbl: 'Fair' },
      { w: '75%',  bg: '#c9a84c',      lbl: 'Good' },
      { w: '100%', bg: '#4caf7e',      lbl: 'Strong ✓' },
    ];
    const c = configs[score];
    fill.style.width = c.w;
    fill.style.background = c.bg;
    label.textContent = c.lbl;
    label.style.color = c.bg;
  }

  // ── FIELD VALIDATION ──
  function validateEmail(val) { return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val.trim()); }

  function showErr(id, show) {
    const el = document.getElementById(id);
    if (!el) return;
    const input = el.previousElementSibling?.tagName === 'INPUT' ? el.previousElementSibling
                : el.closest('.pw-wrap') ? el.closest('.pw-wrap').querySelector('input')
                : el.previousElementSibling?.querySelector('input') || null;
    el.classList.toggle('show', show);
    if (input) input.classList.toggle('error', show);
  }

  // ── SIGN IN ──
  function handleSignIn() {
    const email = document.getElementById('si-email').value;
    const pw    = document.getElementById('si-password').value;
    let valid = true;
    if (!validateEmail(email)) { showErr('si-email-err', true); valid = false; } else showErr('si-email-err', false);
    if (!pw.trim()) { showErr('si-pw-err', true); valid = false; } else showErr('si-pw-err', false);
    if (!valid) return;

    const btn = document.getElementById('si-submit');
    btn.classList.add('loading'); btn.disabled = true;

    // Simulate API call
    setTimeout(() => {
      btn.classList.remove('loading'); btn.disabled = false;
      document.getElementById('si-success').classList.add('show');
      // hide form fields
      document.querySelectorAll('#panel-signin .auth-field, #panel-signin .auth-forgot, #panel-signin .auth-submit, #panel-signin .auth-divider, #panel-signin .auth-socials, #panel-signin .auth-switch').forEach(el => el.style.display = 'none');
      showToast('✓ Signed in successfully!');
    }, 1600);
  }

  // ── REGISTER ──
  function handleRegister() {
    const first = document.getElementById('rg-first').value;
    const last  = document.getElementById('rg-last').value;
    const email = document.getElementById('rg-email').value;
    const pw    = document.getElementById('rg-password').value;
    const confirm = document.getElementById('rg-confirm').value;
    const terms = document.getElementById('rg-terms').checked;
    let valid = true;

    if (!first.trim()) { showErr('rg-first-err', true); valid = false; } else showErr('rg-first-err', false);
    if (!last.trim())  { showErr('rg-last-err',  true); valid = false; } else showErr('rg-last-err', false);
    if (!validateEmail(email)) { showErr('rg-email-err', true); valid = false; } else showErr('rg-email-err', false);
    if (pw.length < 8) { showErr('rg-pw-err', true); valid = false; } else showErr('rg-pw-err', false);
    if (confirm !== pw || !confirm) { showErr('rg-confirm-err', true); valid = false; } else showErr('rg-confirm-err', false);
    if (!terms) { showToast('⚠ Please agree to the Terms of Service.'); valid = false; }
    if (!valid) return;

    const btn = document.getElementById('rg-submit');
    btn.classList.add('loading'); btn.disabled = true;

    // Simulate API call
    setTimeout(() => {
      btn.classList.remove('loading'); btn.disabled = false;
      document.getElementById('rg-success').classList.add('show');
      document.querySelectorAll('#panel-register .auth-field, #panel-register .auth-row, #panel-register .auth-check, #panel-register .auth-submit, #panel-register .auth-divider, #panel-register .auth-socials, #panel-register .auth-switch').forEach(el => el.style.display = 'none');
      showToast('✓ Account created! Check your email.');
    }, 1800);
  }

  // ── SOCIAL AUTH ──
  function handleSocial(provider) {
    showToast('↗ Redirecting to ' + provider + '…');
    // In production: window.location.href = '/auth/' + provider.toLowerCase();
  }

  // ── TOAST ──
  function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 3500);
  }