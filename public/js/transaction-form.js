// Shared transaction form logic (extracted from Maker view)
document.addEventListener('DOMContentLoaded', function(){
  // Helpers
  function q(sel, ctx){ return (ctx||document).querySelector(sel); }
  function qa(sel, ctx){ return Array.from((ctx||document).querySelectorAll(sel)); }
  function formatPeso(num){ return '₱' + Number(num || 0).toFixed(2); }

  // Toggle visual checked state
  window.toggleCheckItem = function(el){
    const wrap = el.closest('.check-item, .remit-check-item, .svc-check, .svc-check-label');
    if (wrap && wrap.classList) wrap.classList.toggle('checked', el.checked);
    const row = el.closest('.cert-cart-row');
    if (row && row.classList) row.classList.toggle('checked', el.checked);
  };

  window.toggleRemitOther = function(el){
    const f = document.getElementById('remit-other-field');
    if (f) f.classList.toggle('show', !!el.checked);
  };

  // Certification / copy / reproduction cart
  function attachCertCartEvents(){
    const cart = document.getElementById('cert-cart');
    const amountInput = document.getElementById('amount-input');
    if (!cart || !amountInput) return;

    const svcRows = qa('.cert-cart-row', cart);
    svcRows.forEach(row => {
      const chk = q('input.svc-chk', row) || q('input[type="checkbox"]', row);
      const qty = q('.qty-input', row);
      const decr = q('.qty-decr', row);
      const incr = q('.qty-incr', row);
      const svc = row.dataset.service;

      if (chk) chk.addEventListener('change', function(){ toggleCheckItem(this); computeCertTotal(); if (qty) qty.disabled = !this.checked; });
      if (qty) {
        qty.addEventListener('input', function(){ this.value = Math.max(1, parseInt(this.value||1,10)); computeCertTotal(); });
      }
      if (decr) decr.addEventListener('click', function(){ if (qty) { qty.value = Math.max(1, parseInt(qty.value||1,10)-1); computeCertTotal(); } });
      if (incr) incr.addEventListener('click', function(){ if (qty) { qty.value = Math.max(1, parseInt(qty.value||1,10)+1); computeCertTotal(); } });
    });

    // init
    computeCertTotal();
  }

  function computeCertTotal(){
    const cart = document.getElementById('cert-cart');
    const amountInput = document.getElementById('amount-input');
    if (!cart || !amountInput) return;
    let total = 0;
    qa('.cert-cart-row', cart).forEach(row => {
      const svc = row.dataset.service;
      const priceText = q('.svc-price', row)?.textContent || '0';
      const price = parseFloat((priceText||'').replace(/[^0-9\.]+/g,'')||0);
      const chk = q('input[type="checkbox"]', row);
      const qty = q('.qty-input', row);
      let sub = 0;
      if (chk && chk.checked) {
        const qn = Math.max(1, parseInt(qty ? qty.value||1 : 1, 10));
        sub = price * qn;
      }
      const subEl = row.querySelector('.svc-sub'); if (subEl) subEl.textContent = formatPeso(sub);
      total += sub;
    });

    // add filing/inspection subtotal if present
    const filingSubEl = document.querySelector('.svc-sub[data-service="filing_fee"]');
    const filingSub = filingSubEl ? parseFloat((filingSubEl.textContent||'').replace(/[^0-9\.]+/g,'')||0) : 0;
    total += filingSub;

    amountInput.value = total.toFixed(2);
  }

  // Filing fee + inspection
  function attachFilingFeeEvents(){
    const filingCheck = document.getElementById('fee-filing-check');
    const filingQty = document.getElementById('filing-qty');
    const filingDecr = document.getElementById('filing-qty-decr');
    const filingIncr = document.getElementById('filing-qty-incr');
    const inspectionCheck = document.getElementById('fee-inspection-check');
    const inspectionOptionsWrap = document.getElementById('inspection-options');
    const inspOptions = inspectionOptionsWrap ? Array.from(inspectionOptionsWrap.querySelectorAll('.inspection-option')) : [];
    const amountInput = document.getElementById('amount-input');
    if (!filingCheck || !filingQty || !inspectionCheck || !inspectionOptionsWrap || !amountInput) return;

    filingCheck.addEventListener('change', function(){ filingQty.disabled = !this.checked; toggleCheckItem(this); computeFilingTotal(); computeCertTotal(); });
    filingQty.addEventListener('input', function(){ this.value = Math.max(1, parseInt(this.value||1,10)); computeFilingTotal(); computeCertTotal(); });
    filingDecr.addEventListener('click', function(){ filingQty.value = Math.max(1, parseInt(filingQty.value||1,10)-1); computeFilingTotal(); computeCertTotal(); });
    filingIncr.addEventListener('click', function(){ filingQty.value = Math.max(1, parseInt(filingQty.value||1,10)+1); computeFilingTotal(); computeCertTotal(); });

    inspectionCheck.addEventListener('change', function(){
      inspectionOptionsWrap.style.display = this.checked ? 'flex' : 'none';
      inspOptions.forEach(opt=>{
        const chk = opt.querySelector('.insp-opt-chk');
        const qty = opt.querySelector('.insp-qty-input');
        if (chk) chk.disabled = !this.checked;
        if (qty) { qty.disabled = !this.checked || !chk.checked; }
        if (!this.checked) { if (chk) chk.checked = false; opt.classList.remove('checked'); }
      });
      toggleCheckItem(this); computeFilingTotal(); computeCertTotal();
    });

    inspOptions.forEach(opt=>{
      const chk = opt.querySelector('.insp-opt-chk');
      const qty = opt.querySelector('.insp-qty-input');
      const decr = opt.querySelector('.insp-qty-decr');
      const incr = opt.querySelector('.insp-qty-incr');
      if (chk) chk.addEventListener('change', function(){ if (qty) qty.disabled = !this.checked; toggleCheckItem(this); computeFilingTotal(); computeCertTotal(); });
      if (qty) qty.addEventListener('input', function(){ this.value = Math.max(1, parseInt(this.value||1,10)); computeFilingTotal(); computeCertTotal(); });
      if (decr) decr.addEventListener('click', function(){ if (qty) { qty.value = Math.max(1, parseInt(qty.value||1,10)-1); computeFilingTotal(); computeCertTotal(); } });
      if (incr) incr.addEventListener('click', function(){ if (qty) { qty.value = Math.max(1, parseInt(qty.value||1,10)+1); computeFilingTotal(); computeCertTotal(); } });
    });

    // init
    filingQty.disabled = !filingCheck.checked;
    inspOptions.forEach(opt=>{
      const chk = opt.querySelector('.insp-opt-chk');
      const qty = opt.querySelector('.insp-qty-input');
      if (chk) chk.disabled = !inspectionCheck.checked;
      if (qty) qty.disabled = !inspectionCheck.checked || !chk.checked;
    });
    inspectionOptionsWrap.style.display = inspectionCheck.checked ? 'flex' : 'none';
    updateFilingSubtotals();
  }

  function computeFilingTotal(){
    const filingCheck = document.getElementById('fee-filing-check');
    const filingQty = document.getElementById('filing-qty');
    const inspectionCheck = document.getElementById('fee-inspection-check');
    const inspectionOptionsWrap = document.getElementById('inspection-options');
    const inspOptions = inspectionOptionsWrap ? Array.from(inspectionOptionsWrap.querySelectorAll('.inspection-option')) : [];
    const amountInput = document.getElementById('amount-input');
    let total = 0;
    if (filingCheck && filingCheck.checked) {
      const fq = Math.max(1, parseInt(filingQty.value||1,10));
      const sub = 2000 * fq;
      total += sub;
      const filingSubEl = document.querySelector('.svc-sub[data-service="filing_fee"]');
      if (filingSubEl) filingSubEl.textContent = formatPeso(sub);
    } else {
      const filingSubEl = document.querySelector('.svc-sub[data-service="filing_fee"]');
      if (filingSubEl) filingSubEl.textContent = formatPeso(0);
    }
    if (inspectionCheck && inspectionCheck.checked) {
      let inspSum = 0;
      inspOptions.forEach(opt=>{
        const chk = opt.querySelector('.insp-opt-chk');
        const price = parseFloat(opt.dataset.price || 0);
        const qtyEl = opt.querySelector('.insp-qty-input');
        const q = Math.max(1, parseInt(qtyEl ? qtyEl.value||1 : 1,10));
        let sub = 0;
        if (chk && chk.checked) { sub = price * q; inspSum += sub; }
        const optSubEl = opt.querySelector('.opt-sub'); if (optSubEl) optSubEl.textContent = formatPeso(sub);
      });
      total += inspSum;
      const inspSubEl = document.querySelector('.svc-sub[data-service="inspection_cost"]');
      if (inspSubEl) inspSubEl.textContent = formatPeso(inspSum);
    } else {
      inspOptions.forEach(opt=>{ const optSubEl = opt.querySelector('.opt-sub'); if (optSubEl) optSubEl.textContent = formatPeso(0); });
      const inspSubEl = document.querySelector('.svc-sub[data-service="inspection_cost"]');
      if (inspSubEl) inspSubEl.textContent = formatPeso(0);
    }
    // amountInput updated by computeCertTotal which calls this
    return total;
  }

  function updateFilingSubtotals(){
    const fChk = document.getElementById('fee-filing-check');
    const fQty = document.getElementById('filing-qty');
    const inspectionOptionsWrap = document.getElementById('inspection-options');
    if (fChk && fQty) {
      const subEl = document.querySelector('.svc-sub[data-service="filing_fee"]');
      if (subEl) subEl.textContent = fChk.checked ? formatPeso(2000 * Math.max(1, parseInt(fQty.value||1,10))) : formatPeso(0);
    }
    if (inspectionOptionsWrap) {
      const iChk = document.getElementById('fee-inspection-check');
      const insp = Array.from(inspectionOptionsWrap.querySelectorAll('.inspection-option'));
      let sum = 0;
      insp.forEach(opt=>{
        const chk = opt.querySelector('.insp-opt-chk');
        const qty = opt.querySelector('.insp-qty-input');
        const price = parseFloat(opt.dataset.price||0);
        if (chk && chk.checked) {
          const q = Math.max(1, parseInt(qty ? qty.value||1 : 1,10));
          sum += price * q;
        }
      });
      const inspSubEl = document.querySelector('.svc-sub[data-service="inspection_cost"]');
      if (inspSubEl) inspSubEl.textContent = formatPeso(sum);
    }
  }

  // Initialize if elements are present
  if (document.getElementById('cert-cart')) attachCertCartEvents();
  if (document.getElementById('filing-inspection-fees')) attachFilingFeeEvents();

  // CASH BOND: compute amount = area_hectares * zonal_value * 0.025
  function computeCashBond() {
    const area = parseFloat((document.querySelector('input[name="area_hectares"]')||{}).value||0) || 0;
    const zonal = parseFloat((document.querySelector('input[name="zonal_value"]')||{}).value||0) || 0;
    const amountInput = document.getElementById('amount-input');
    const formula = document.getElementById('cash-bond-formula');
    if (!amountInput) return;
    const amt = (area * zonal * 0.025) || 0;
    amountInput.value = amt.toFixed(2);
    if (formula) formula.style.display = (area > 0 && zonal > 0) ? 'block' : formula.style.display;
    return amt;
  }

  // Wire up cash bond input listeners if present
  const areaInp = document.querySelector('input[name="area_hectares"]');
  const zonalInp = document.querySelector('input[name="zonal_value"]');
  if (areaInp) areaInp.addEventListener('input', () => { computeCashBond(); });
  if (zonalInp) zonalInp.addEventListener('input', () => { computeCashBond(); });

  // CLIENT-SIDE VALIDATION + PREVIEW (define only if not already present)
  if (typeof validateField !== 'function') {
    const __validators = {
      numeric:     v => /^\d+(?:\.\d+)?$/.test(String(v).trim()),
      alphanumeric:v => /^[A-Za-z0-9\-\_\s]+$/.test(String(v).trim()),
      tel:         v => /^[0-9+\-\s()]+$/.test(String(v).trim())
    };

    window.validateField = function(input) {
      const rule  = input.dataset.validate;
      const field = input.closest('.field');
      if (!rule) return true;
      const val = String(input.value || '').trim();
      let ok = true;
      if (input.required && val === '') ok = false;
      else if (val !== '') {
        const fn = __validators[rule];
        if (fn) ok = fn(val);
      }
      if (!ok) {
        field.classList.add('invalid');
        let em = field.querySelector('.error-msg');
        if (!em) { em = document.createElement('div'); em.className = 'error-msg'; field.appendChild(em); }
        em.textContent =
          rule === 'numeric'      ? 'This field requires a numeric value.' :
          rule === 'alphanumeric' ? 'Only letters, numbers, spaces, dash and underscore are allowed.' :
          rule === 'tel'          ? 'Please enter a valid contact number.' : 'Invalid value.';
      } else {
        field.classList.remove('invalid');
        const em = field.querySelector('.error-msg'); if (em) em.remove();
      }
      return ok;
    };

    document.querySelectorAll('[data-validate]').forEach(inp => {
      inp.addEventListener('input', () => validateField(inp));
      inp.addEventListener('blur',  () => validateField(inp));
    });

    // Build preview data (adapted from Maker view)
    window.buildPreviewData = function() {
      const form = document.getElementById('payment-form');
      const obj = {};
      if (!form) return obj;
      Array.from(form.elements).forEach(el => {
        if (!el.name) return;
        if (el.name.startsWith('_')) return;
        const extra = el.closest('.extra-fields');
        if (extra && !extra.classList.contains('show')) return;
        if (el.disabled) return;
        if (el.type === 'submit' || el.type === 'button') return;
        const name = el.name;
        if (el.type === 'checkbox') {
          if (name.endsWith('[]')) {
            const base = name.replace(/\[\]$/, '');
            if (!obj[base]) obj[base] = [];
            if (el.checked) obj[base].push(el.value);
          } else {
            if (el.checked) obj[name] = true;
          }
          return;
        }
        if (el.type === 'radio') { if (el.checked) obj[name] = el.value; return; }
        if (el.type === 'number') {
          const v = el.value === '' ? null : Number(el.value);
          if (v !== null && !Number.isNaN(v)) obj[name] = v;
          return;
        }
        if (el.value !== null && String(el.value).trim() !== '') obj[name] = el.value;
      });
      // txn label
      const txnLabel = document.getElementById('form-txn-name')?.textContent || '';
      if (txnLabel) obj._txnLabel = txnLabel;
      // ensure fund_type included
      const hf = document.getElementById('hidden-fund-type'); if (hf && hf.value) obj.fund_type = obj.fund_type || hf.value;
      return obj;
    };

    // Show preview modal (create minimal modal if none exists)
    window.openPreviewWithData = function(data) {
      const modal = document.getElementById('preview-overlay');
      if (!modal) return;
      const summaryEl = modal.querySelector('.preview-summary');
      if (!summaryEl) return;
      summaryEl.innerHTML = '';

      const labelMap = {
        transaction_type: 'Transaction type',
        _txnLabel: 'Transaction',
        fund_type: 'Fund type',
        amount: 'Amount',
        payment_mode: 'Payment mode',
        name: 'Name',
        contact: 'Contact',
        address: 'Address',
        email: 'Email',
        op_number: 'Order of Payment No.'
      };

      const order = ['_txnLabel','transaction_type','fund_type','amount','payment_mode','name','contact','address','email','op_number'];

      function renderRow(label, value) {
        const row = document.createElement('div');
        row.style.display = 'flex';
        row.style.justifyContent = 'space-between';
        row.style.padding = '10px 6px';
        row.style.borderBottom = '1px solid rgba(0,0,0,0.06)';
        const left = document.createElement('div'); left.style.color = 'rgba(0,0,0,0.6)'; left.style.fontSize='0.9rem'; left.textContent = label;
        const right = document.createElement('div'); right.style.fontWeight = '700'; right.style.fontSize='0.95rem'; right.textContent = value;
        row.appendChild(left); row.appendChild(right);
        summaryEl.appendChild(row);
      }

      function fmtAmount(v){ try{ return (typeof v === 'number') ? '₱' + v.toFixed(2) : ('₱' + parseFloat(v).toFixed(2)); }catch(e){ return v; } }

      const rendered = new Set();
      order.forEach(k => {
        if (data[k] !== undefined && data[k] !== null) {
          let v = data[k];
          if (k === 'amount') v = fmtAmount(v);
          if (Array.isArray(v)) v = v.join(', ');
          renderRow(labelMap[k] || k, v);
          rendered.add(k);
        }
      });

      // Render selected Certification/Copy/Reproduction services with qty when applicable
      const svcOrder = ['certification','copy_fee','reproduction_cost'];
      const selected = Array.isArray(data.cert_type) ? data.cert_type : [];
      svcOrder.forEach(key => {
        if (!selected.includes(key)) return;
        let qtyVal = null;
        if (key === 'copy_fee') qtyVal = data.copy_count !== undefined ? data.copy_count : null;
        else {
          const qn = 'svc_' + key + '_qty';
          qtyVal = data[qn] !== undefined ? data[qn] : null;
        }
        if (qtyVal !== null && qtyVal !== undefined && String(qtyVal).trim() !== '') {
          renderRow(key.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()), qtyVal);
        } else {
          renderRow(key.replace(/_/g,' ').replace(/\b\w/g,c=>c.toUpperCase()), 1);
        }
      });
      rendered.add('cert_type'); rendered.add('copy_count');

      // Filing fee aggregate
      if (data.fee_filing_check) {
        const fq = (data.filing_qty !== undefined) ? Number(data.filing_qty) : 1;
        const filingTotal = fq * 2000;
        renderRow('Filing Fee', fq + ' — ' + fmtAmount(filingTotal));
        rendered.add('fee_filing_check'); rendered.add('filing_qty');
      }

      // Inspection breakdown
      if (Array.isArray(data.inspection_option) && data.inspection_option.length) {
        let totQty = 0, totPrice = 0;
        data.inspection_option.forEach(val => {
          const qn = 'inspection_qty_' + String(val);
          const q = data[qn] !== undefined ? Number(data[qn]) : 1;
          totQty += q; totPrice += Number(val) * q;
        });
        renderRow('Inspection Cost', totQty + ' — ' + fmtAmount(totPrice));
        data.inspection_option.forEach(val => {
          const qn = 'inspection_qty_' + String(val);
          const q = data[qn] !== undefined ? Number(data[qn]) : 1;
          renderRow('  • ' + fmtAmount(Number(val)), q + ' — ' + fmtAmount(Number(val) * q));
          rendered.add(qn);
        });
        rendered.add('inspection_option');
      }

      function titleCase(s){ return String(s||'').split(/\s+/).map(w=> w ? (w.charAt(0).toUpperCase()+w.slice(1)) : '').join(' ').trim(); }

      Object.keys(data).forEach(k => {
        if (rendered.has(k)) return;
        const v = data[k];
        if (v === null || v === undefined) return;
        if (Array.isArray(v) && v.length === 0) return;
        if (String(v).trim() === '') return;
        if (/^svc_[a-z0-9_]+$/.test(k)) return;
        let displayLabel = labelMap[k] || titleCase(k.replace(/_/g,' '));
        let displayValue;
        if (Array.isArray(v)) displayValue = v.join(', ');
        else if (typeof v === 'boolean') displayValue = v ? 'Yes' : 'No';
        else displayValue = v;
        renderRow(displayLabel, displayValue);
      });

      try {
        const pretty = JSON.stringify(data, null, 2);
        const b64 = btoa(unescape(encodeURIComponent(pretty)));
        const hidden = modal.querySelector('.preview-encoded'); if (hidden) hidden.value = b64;
      } catch (e) {}

      modal.classList.add('open'); document.body.style.overflow = 'hidden';
    };

    window.closePreview = function(){ const modal = document.getElementById('preview-overlay'); if (!modal) return; modal.classList.remove('open'); document.body.style.overflow=''; };

    // Confirm submit handler used by Maker preview modal
    window.confirmSubmitFromPreview = function() {
      window.closePreview?.();
      const form = document.getElementById('payment-form'); if (form) form.submit();
    };

    // Intercept submit for reviewer form if present
    const paymentForm = document.getElementById('payment-form');
    if (paymentForm && paymentForm.dataset.role === 'reviewer') {
      paymentForm.addEventListener('submit', function(e){
        e.preventDefault();
        let firstInvalid = null;
        this.querySelectorAll('[data-validate]').forEach(i => {
          const extra = i.closest('.extra-fields');
          if (extra && !extra.classList.contains('show')) return;
          if (!validateField(i) && !firstInvalid) firstInvalid = i;
        });
        if (firstInvalid) { firstInvalid.focus(); return; }
        const groups = this.querySelectorAll('[data-require-one]');
        for (const g of groups) {
          const extra = g.closest('.extra-fields');
          if (extra) { if (!extra.classList.contains('show')) continue; }
          else { const txnVal = document.getElementById('txn-select')?.value || ''; if (txnVal !== 'certification_copy_fee') continue; }
          const anyChecked = Array.from(g.querySelectorAll('input[type="checkbox"]')).some(cb => cb.checked);
          if (!anyChecked) { const firstCb = g.querySelector('input[type="checkbox"]'); if (firstCb) firstCb.focus(); alert('Please select at least one option.'); return; }
        }
        const data = buildPreviewData(); openPreviewWithData(data);
      });
    }
  }

  // Recompute totals when external calls change fields
  document.addEventListener('input', function(e){ if (e.target && (e.target.name === 'area_hectares' || e.target.name === 'zonal_value')) { /* placeholder */ } });

  // Expose compute for external use
  window.computeCertTotal = computeCertTotal;
  window.computeFilingTotal = computeFilingTotal;
});
