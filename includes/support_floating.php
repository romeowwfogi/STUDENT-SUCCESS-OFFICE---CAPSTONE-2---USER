<?php
// Floating Contact Support widget
?>
<style>
  .support-floating {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1400;
    font-family: inherit;
  }

  .support-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    border: none;
    border-radius: 999px;
    color: #ffffff;
    background: linear-gradient(145deg, #16aa19, #136515);
    box-shadow: rgba(0, 0, 0, 0.35) 0px 5px 15px;
    cursor: pointer;
    transition: transform 0.15s ease, filter 0.15s ease;
  }

  .support-btn:hover { 
    transform: translateY(-1px); 
    filter: drop-shadow(-2px 9px 5px #000000);
  }

  .support-btn:active { 
    box-shadow: inset 2px 2px 5px rgba(0,0,0,0.5);
    transform: translateY(1px);
  }

  .support-btn-icon { 
    display: inline-flex; 
  }

  .support-btn-label { 
    font-weight: 600; 
  }

  .support-panel {
    position: fixed;
    bottom: 88px;
    right: 24px;
    width: 340px;
    max-width: calc(100vw - 48px);
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.12);
    overflow: hidden;
    display: none;
  }

  .support-panel.open { display: block; }

  .support-panel-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 12px 14px;
    border-bottom: 1px solid #e5e7eb;
    background: #fafafa;
  }

  .support-panel-title { font-size: 14px; font-weight: 600; }

  .support-close {
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    width: 32px;
    height: 32px;
    cursor: pointer;
    font-size: 18px;
  }

  .support-tabs { display: flex; gap: 6px; padding: 8px; border-bottom: 1px solid #e5e7eb; }
  .support-tab {
    flex: 1;
    background: #f3f4f6;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 8px 10px;
    font-size: 13px;
    cursor: pointer;
  }
  .support-tab.active { background: #e8f5e9; border-color: #cfe9d1; }

  .support-panel-body { padding: 12px 14px; }

  .support-desc { font-size: 13px; color: #374151; }

  .faq-item { border: 1px solid #e5e7eb; border-radius: 10px; margin-bottom: 8px; overflow: hidden; }
  .faq-q { background: #f9fafb; padding: 10px 12px; font-weight: 600; cursor: pointer; }
  .faq-a { padding: 10px 12px; display: none; font-size: 13px; color: #374151; }
  .faq-item.open .faq-a { display: block; }

  .support-faqs-list { max-height: 240px; overflow: auto; margin-bottom: 8px; }
  .faq-loading, .faq-empty { font-size: 13px; color: #6b7280; padding: 6px 2px; }
  .faq-loader { display: flex; align-items: center; gap: 8px; color: #6b7280; font-size: 13px; }
  .faq-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid #e5e7eb;
    border-top-color: #16aa19;
    border-radius: 50%;
    animation: faqSpin 0.8s linear infinite;
  }
  @keyframes faqSpin { to { transform: rotate(360deg); } }
  .support-seeall {
    display: inline-block;
    padding: 8px 10px;
    font-size: 13px;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    cursor: pointer;
  }
  .support-seeall:hover { background: #f3f4f6; }

  .support-form { display: grid; gap: 10px; }
  .support-input, .support-textarea {
    width: 100%;
    padding: 10px;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    font-size: 13px;
  }
  .support-textarea { min-height: 100px; resize: vertical; }

  .support-send {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    background: linear-gradient(145deg, #16aa19, #136515);
    color: #fff;
    border: none;
    border-radius: 10px;
    cursor: pointer;
  }

  .support-note { font-size: 12px; color: #6b7280; }

  .support-alert { margin-top: 8px; font-size: 13px; }
  .support-alert.success { color: #166534; }
  .support-alert.error { color: #dc2626; }

  /* FAQ Modal */
  .support-modal {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.5);
    justify-content: center;
    align-items: center;
    z-index: 1600;
  }
  .support-modal.open { display: flex; }
  .support-modal-content {
    background: #fff;
    width: 92%;
    max-width: 520px;
    max-height: 85vh;
    border-radius: 14px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }
  .support-modal-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 14px 18px;
    border-bottom: 1px solid #e5e7eb;
    background: #fafafa;
  }
  .support-modal-title { font-size: 16px; font-weight: 600; margin: 0; }
  .support-modal-close {
    background: #f3f4f6;
    border: none;
    border-radius: 8px;
    width: 32px;
    height: 32px;
    cursor: pointer;
    font-size: 18px;
  }
  .support-modal-body { padding: 12px 16px; font-size: 14px; color: #111827; }

  @media (max-width: 480px) {
    .support-btn { padding: 10px 14px; }
    .support-btn-label { display: none; }
    .support-panel { width: 92vw; right: 4vw; }
  }
</style>

<div class="support-floating" aria-live="polite">
  <div class="support-panel" id="supportPanel" aria-hidden="true" role="dialog" aria-labelledby="supportPanelTitle">
    <div class="support-panel-header">
      <span id="supportPanelTitle" class="support-panel-title">Need help?</span>
      <button class="support-close" id="supportClose" aria-label="Close support panel">×</button>
    </div>
    <div class="support-tabs" role="tablist">
      <button class="support-tab active" id="tabFaqs" role="tab" aria-controls="supportFaqs" aria-selected="true">FAQs</button>
      <button class="support-tab" id="tabEmail" role="tab" aria-controls="supportEmail" aria-selected="false">Send Email</button>
    </div>
    <div class="support-panel-body">
      <!-- FAQs -->
      <div id="supportFaqs" role="tabpanel" aria-labelledby="tabFaqs">
        <div id="supportFaqsList" class="support-faqs-list">
          <div class="faq-loading">Loading top FAQs…</div>
        </div>
        <div class="support-note">Top FAQs show here. Click a question to view details.</div>
        <button id="supportFaqsSeeAll" class="support-seeall" type="button">See All FAQs</button>
      </div>

      <!-- Email Support Form -->
      <div id="supportEmail" role="tabpanel" aria-labelledby="tabEmail" style="display:none;">
        <form id="supportEmailForm" class="support-form">
          <?php
            // Build full name if available from parent page
            $supportName = '';
            if (isset($first_name) || isset($last_name) || isset($middle_name) || isset($suffix)) {
                $parts = [];
                if (!empty($first_name)) $parts[] = $first_name;
                if (!empty($middle_name)) $parts[] = $middle_name;
                if (!empty($last_name)) $parts[] = $last_name;
                if (!empty($suffix)) $parts[] = $suffix;
                $supportName = trim(implode(' ', $parts));
            }
            $supportEmail = isset($EMAIL_ADDRESS) ? $EMAIL_ADDRESS : '';
          ?>
          <input class="support-input" type="text" id="supportEmailName" placeholder="Your name" value="<?php echo htmlspecialchars($supportName); ?>" required />
          <input class="support-input" type="email" id="supportEmailAddress" placeholder="Your email" value="<?php echo htmlspecialchars($supportEmail); ?>" required />
          <input class="support-input" type="text" id="supportEmailSubject" placeholder="Subject" required />
          <textarea class="support-textarea" id="supportEmailMessage" placeholder="Describe your issue" required></textarea>
          <button type="submit" class="support-send" id="supportSendBtn">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M22 2L11 13" />
              <path d="M22 2L15 22L11 13L2 9L22 2" />
            </svg>
            Send
          </button>
          <div id="supportAlert" class="support-alert" aria-live="polite"></div>
        </form>
      </div>
    </div>
  </div>
  <button class="support-btn" id="supportToggle" aria-controls="supportPanel" aria-expanded="false" title="Contact Support">
    <span class="support-btn-icon" aria-hidden="true">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="10" />
        <path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 2.5-3 4" />
        <line x1="12" y1="17" x2="12" y2="17" />
      </svg>
    </span>
    <span class="support-btn-label">Help</span>
  </button>
</div>

<!-- FAQ Modal -->
<div id="supportFaqModal" class="support-modal" aria-hidden="true">
  <div class="support-modal-content" role="dialog" aria-modal="true" aria-labelledby="supportFaqModalTitle">
    <div class="support-modal-header">
      <h3 id="supportFaqModalTitle" class="support-modal-title">FAQ</h3>
      <button id="supportFaqModalClose" class="support-modal-close" title="Close">×</button>
    </div>
    <div id="supportFaqModalBody" class="support-modal-body"></div>
  </div>
  </div>

<script>
  (function() {
    const panel = document.getElementById('supportPanel');
    const toggle = document.getElementById('supportToggle');
    const closeBtn = document.getElementById('supportClose');
    const tabFaqs = document.getElementById('tabFaqs');
    const tabEmail = document.getElementById('tabEmail');
    const sectionFaqs = document.getElementById('supportFaqs');
    const sectionEmail = document.getElementById('supportEmail');
    const faqsList = document.getElementById('supportFaqsList');
    const seeAllBtn = document.getElementById('supportFaqsSeeAll');
    const form = document.getElementById('supportEmailForm');
    const defaultName = <?php echo json_encode($supportName ?? ""); ?>;
    const defaultEmail = <?php echo json_encode($supportEmail ?? ""); ?>;
    const nameInput = document.getElementById('supportEmailName');
    const emailInput = document.getElementById('supportEmailAddress');
    const subjectInput = document.getElementById('supportEmailSubject');
    const messageInput = document.getElementById('supportEmailMessage');
    const alertEl = document.getElementById('supportAlert');

    // FAQ Modal elements
    const faqModal = document.getElementById('supportFaqModal');
    const faqModalClose = document.getElementById('supportFaqModalClose');
    const faqModalTitle = document.getElementById('supportFaqModalTitle');
    const faqModalBody = document.getElementById('supportFaqModalBody');

    if (!panel || !toggle || !closeBtn) return;

    function openPanel() {
      panel.classList.add('open');
      panel.setAttribute('aria-hidden', 'false');
      toggle.setAttribute('aria-expanded', 'true');
      // Lazy-load top FAQs on first open
      if (!topFaqsLoaded) {
        loadTopFaqs();
      }
    }

    function closePanel() {
      panel.classList.remove('open');
      panel.setAttribute('aria-hidden', 'true');
      toggle.setAttribute('aria-expanded', 'false');
    }

    // Toggle panel
    toggle.addEventListener('click', function() {
      if (panel.classList.contains('open')) {
        closePanel();
      } else {
        openPanel();
      }
    });
    closeBtn.addEventListener('click', closePanel);
    document.addEventListener('keydown', function(e) { if (e.key === 'Escape') closePanel(); });
    document.addEventListener('click', function(e) {
      if (!panel.classList.contains('open')) return;
      const withinWidget = e.target.closest('.support-floating');
      if (!withinWidget) closePanel();
    });

    // Tabs
    function activateTab(name) {
      if (name === 'faqs') {
        tabFaqs.classList.add('active');
        tabEmail.classList.remove('active');
        sectionFaqs.style.display = '';
        sectionEmail.style.display = 'none';
        tabFaqs.setAttribute('aria-selected', 'true');
        tabEmail.setAttribute('aria-selected', 'false');
        if (!topFaqsLoaded) {
          loadTopFaqs();
        }
      } else {
        tabEmail.classList.add('active');
        tabFaqs.classList.remove('active');
        sectionEmail.style.display = '';
        sectionFaqs.style.display = 'none';
        tabEmail.setAttribute('aria-selected', 'true');
        tabFaqs.setAttribute('aria-selected', 'false');
      }
    }
    tabFaqs.addEventListener('click', () => activateTab('faqs'));
    tabEmail.addEventListener('click', () => activateTab('email'));

    // Utilities
    function escapeHtml(str) {
      if (typeof str !== 'string') return '';
      return str
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
    }

    function openFaqModal(title, bodyHtml) {
      faqModalTitle.textContent = title || 'FAQ';
      faqModalBody.innerHTML = bodyHtml || '';
      faqModal.classList.add('open');
      faqModal.setAttribute('aria-hidden', 'false');
    }

    async function incrementFaqRead(id) {
      try {
        await fetch('../api/increment_faq_read.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id })
        });
      } catch (_) { /* ignore */ }
    }

    let topFaqsLoaded = false;
    async function loadTopFaqs() {
      try {
        faqsList.innerHTML = '<div class="faq-loader"><span class="faq-spinner" aria-hidden="true"></span><span>Loading top FAQs…</span></div>';
        const res = await fetch('../api/get_faqs_top.php', { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        const faqs = (data && (data.faqs || data.data)) || [];
        if (!res.ok || !Array.isArray(faqs)) throw new Error(data && data.message ? data.message : 'Failed to load FAQs');
        if (faqs.length === 0) {
          faqsList.innerHTML = '<div class="faq-empty">No FAQs available.</div>';
        } else {
          faqsList.innerHTML = faqs.map(f => (
            `<div class="faq-item" data-id="${f.id}">
               <div class="faq-q">${escapeHtml(f.question || '')}</div>
             </div>`
          )).join('');
          // Attach click handlers
          faqsList.querySelectorAll('.faq-item').forEach(item => {
            item.addEventListener('click', () => {
              const id = parseInt(item.getAttribute('data-id'), 10);
              const faq = faqs.find(x => x.id === id);
              const body = `<div>${escapeHtml(faq && faq.answer ? faq.answer : '')}</div>`;
              openFaqModal(faq && faq.question ? faq.question : 'FAQ', body);
              if (id > 0) incrementFaqRead(id);
            });
          });
        }
        topFaqsLoaded = true;
      } catch (err) {
        faqsList.innerHTML = `<div class="faq-empty">${escapeHtml(err.message || 'Failed to load FAQs')}</div>`;
      }
    }

    async function loadAllFaqsModal() {
      // Show loader immediately in modal while fetching
      openFaqModal('All FAQs', '<div class="faq-loader"><span class="faq-spinner" aria-hidden="true"></span><span>Loading all FAQs…</span></div>');
      try {
        const res = await fetch('../api/get_faqs_all.php', { headers: { 'Accept': 'application/json' } });
        const data = await res.json();
        const faqs = (data && (data.faqs || data.data)) || [];
        if (!res.ok || !Array.isArray(faqs)) throw new Error(data && data.message ? data.message : 'Failed to load FAQs');
        const listHtml = faqs.map(f => (
          `<div class="faq-item" data-id="${f.id}">
             <div class="faq-q">${escapeHtml(f.question || '')}</div>
             <div class="faq-a">${escapeHtml(f.answer || '')}</div>
           </div>`
        )).join('');
        faqModalBody.innerHTML = listHtml || '<div class="faq-empty">No FAQs available.</div>';
        // Toggle open in modal and increment read count when opening
        faqModalBody.querySelectorAll('.faq-item').forEach(item => {
          item.addEventListener('click', () => {
            const isOpen = item.classList.contains('open');
            faqModalBody.querySelectorAll('.faq-item').forEach(i => i.classList.remove('open'));
            if (!isOpen) {
              item.classList.add('open');
              const id = parseInt(item.getAttribute('data-id'), 10);
              if (id > 0) incrementFaqRead(id);
            }
          });
        });
      } catch (err) {
        faqModalBody.innerHTML = `<div class="faq-empty">${escapeHtml(err.message || 'Failed to load FAQs')}</div>`;
      }
    }

    if (seeAllBtn) {
      seeAllBtn.addEventListener('click', loadAllFaqsModal);
    }

    function closeFaqModal() {
      faqModal.classList.remove('open');
      faqModal.setAttribute('aria-hidden', 'true');
    }
    faqModalClose.addEventListener('click', closeFaqModal);
    faqModal.addEventListener('click', (e) => { if (e.target === faqModal) closeFaqModal(); });
    document.addEventListener('keydown', (e) => { if (e.key === 'Escape') closeFaqModal(); });

    // Email submission
    function setAlert(type, msg) {
      alertEl.textContent = msg;
      alertEl.className = 'support-alert ' + (type || '');
    }

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      setAlert('', '');
      const name = (nameInput.value || '').trim();
      const email = (emailInput.value || '').trim();
      const subject = (subjectInput.value || '').trim();
      const message = (messageInput.value || '').trim();

      // Basic validation
      if (!name) {
        setAlert('error', 'Please enter your name.');
        return;
      }
      const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
      if (!emailRegex.test(email)) {
        setAlert('error', 'Please enter a valid email address.');
        return;
      }
      if (!subject || !message) {
        setAlert('error', 'Subject and message are required.');
        return;
      }

      const btn = document.getElementById('supportSendBtn');
      btn.disabled = true;
      btn.textContent = 'Sending...';

      try {
        const res = await fetch('../api/send_support_email.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ name, email_address: email, subject, message })
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) throw new Error(data.message || 'Failed to send.');
        setAlert('success', data.message || 'Message sent successfully.');
        form.reset();
        // Restore defaults after reset
        if (defaultName) nameInput.value = defaultName;
        if (defaultEmail) emailInput.value = defaultEmail;
      } catch (err) {
        setAlert('error', err.message || 'Something went wrong.');
      } finally {
        btn.disabled = false;
        btn.textContent = 'Send';
      }
    });
  })();
</script>