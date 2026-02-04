<?php
require_once 'functions.php';

$message = '';
$formData = [];
$editMode = false;
$editFilename = '';

// Handle form submission
if ($_POST) {
    if (isset($_POST['action'])) {
        if ($_POST['action'] === 'save') {
            $data = sanitizeInput($_POST);
            unset($data['action']); // Remove action from saved data
            
            if (isset($_POST['edit_filename']) && !empty($_POST['edit_filename'])) {
                // Update existing record
                if (updateFormData($_POST['edit_filename'], $data)) {
                    $message = '<div class="alert alert-success">Invoice updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-error">Failed to update invoice.</div>';
                }
            } else {
                // Auto-generate invoice number if not set or empty
                if (empty($data['invoice_number'])) {
                    $data['invoice_number'] = generateInvoiceNumber();
                }
                
                // Save new record
                $filename = saveFormData('invoice', $data);
                if ($filename) {
                    $message = '<div class="alert alert-success">Invoice saved successfully! Invoice #' . $data['invoice_number'] . '</div>';
                } else {
                    $message = '<div class="alert alert-error">Failed to save invoice.</div>';
                }
            }
        } elseif ($_POST['action'] === 'save_as') {
            // Save As: Create new invoice with auto-generated number
            $data = sanitizeInput($_POST);
            unset($data['action']); // Remove action from saved data
            unset($data['edit_filename']); // Remove edit reference
            
            // Generate new invoice number
            $data['invoice_number'] = generateInvoiceNumber();
            
            // Save as new record
            $filename = saveFormData('invoice', $data);
            if ($filename) {
                $message = '<div class="alert alert-success">Invoice saved as new! Invoice #' . $data['invoice_number'] . '</div>';
                // Update form to show new invoice data
                $formData = $data;
                $editMode = false;
                $editFilename = '';
                $nextInvoiceNumber = $data['invoice_number']; // Set to the newly created invoice number
            } else {
                $message = '<div class="alert alert-error">Failed to save invoice.</div>';
            }
        }
    }
}

// Handle edit mode
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $record = loadFormData($_GET['edit']);
    if ($record) {
        $formData = $record['data'];
        $editMode = true;
        $editFilename = $_GET['edit'];
    }
}

// Generate next invoice number for new invoices
if (!$editMode && !isset($nextInvoiceNumber)) {
    $nextInvoiceNumber = generateInvoiceNumber();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>ORIENT Gas Engineers LTD — Invoice Generator</title>
<style>
  /* ===== Page & Base ===== */
  @page { size: A4 portrait; margin: 12mm; }
  :root{
    --ink:#1b1e24;
    --blue:#2e5aa6;
    --line:#cfd6e6;
    --line-dark:#2e5aa6;
    --muted:#6c7280;
    --bg:#ffffff;
    --chip:#eef3ff;
  }
  html, body { height:100%; }
  body {
    margin:0;
    color:var(--ink);
    background:var(--bg);
    font-family: system-ui, -apple-system, "Segoe UI", Roboto, Arial, Helvetica, sans-serif;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
  }
  .wrap { max-width: 900px; margin: 24px auto; padding: 0 16px 40px; }

  /* ===== Alerts ===== */
  .alert {
    padding: 12px;
    margin: 10px 0;
    border-radius: 5px;
    font-weight: bold;
  }
  .alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
  }
  .alert-error {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
  }
  .alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
  }

  /* ===== Toolbar (not printed) ===== */
  .toolbar {
    position: sticky; top:0; z-index:50;
    background:#f7f9ff; border-bottom:1px solid var(--line);
    display:flex; gap:8px; padding:10px; justify-content:flex-end;
  }
  .btn {
    appearance:none; border:1px solid var(--line); background:#fff;
    padding:10px 14px; border-radius:10px; font-weight:700; cursor:pointer; text-decoration: none; display: inline-block;
  }
  .btn.primary { background:var(--blue); color:#fff; border-color:var(--blue); }
  .btn.success { background:#28a745; color:#fff; border-color:#28a745; }
  .btn.info { background:#17a2b8; color:#fff; border-color:#17a2b8; }
  .btn.danger { color:#b00020; border-color:#f1c7cd; background:#fff; }
  .btn.small { padding:6px 10px; border-radius:8px; font-size:13px; }

  /* ===== Invoice Canvas ===== */
  .sheet { background:#fff; border:1px solid var(--line); border-radius:14px; overflow:hidden; }
  .header {
    display:grid; grid-template-columns: 1.5fr 1fr; gap:24px;
    padding:16px 20px 12px;
    align-items: start;
    background:#f8f9fa;
  }
  .brand { display:flex; flex-direction:column; align-items:flex-start; gap:10px; }
  .logo { width:60px; height:60px; border-radius:8px; object-fit:cover; }
  .brand-info h1 { margin:0 0 6px; font-size:18px; color:var(--ink); font-weight:700; }
  .brand-info p { margin:3px 0; color:var(--ink); font-size:13px; line-height:1.5; }

  .invoice-meta { text-align:right; }
  .invoice-title { font-size:32px; font-weight:700; color:var(--ink); margin:0 0 12px; letter-spacing:-0.5px; }
  .meta-row { display:flex; justify-content:space-between; align-items:center; margin:6px 0; }
  .meta-label { font-weight:600; color:#6c757d; font-size:13px; text-align:left; }
  .meta-value { font-weight:600; color:var(--ink); text-align:right; font-size:13px; }

  /* ===== Billing Section ===== */
  .billing {
    padding:16px 20px; border-bottom:1px solid var(--line);
  }
  .billing-block { max-width: 400px; border:1px solid var(--line); padding:0; }
  .billing-block h3 { margin:0 0 8px; font-size:13px; color:#6c757d; background:#e9ecef; padding:5px 10px; font-weight:600; }
  .billing-block p { margin:1px 0; font-size:13px; color:var(--ink); line-height:1.5; }
  .billing-block input { padding-left:10px; }

  /* ===== Items Table ===== */
  .items { padding:0; }
  .items-table { width:100%; border-collapse:collapse; }
  .items-table th { 
    background:#e9ecef; padding:10px 12px; text-align:left; 
    font-weight:600; color:#495057; border-bottom:1px solid var(--line); font-size:13px;
  }
  .items-table td { 
    padding:10px 12px; border-bottom:1px solid var(--line);
    vertical-align:top; font-size:13px;
  }
  .items-table input, .items-table textarea {
    width:100%; border:none; background:transparent; font:inherit;
  }
  .qty-input, .rate-input, .amount-display { text-align:right; }

  /* ===== Totals ===== */
  .totals {
    padding:16px 20px; border-top:1px solid var(--line);
    display:flex; justify-content:flex-end;
  }
  .totals-table { width:300px; }
  .totals-table td { padding:6px 16px; font-size:14px; }
  .totals-table .label { text-align:right; font-weight:600; color:#495057; }
  .totals-table .value { text-align:right; font-weight:700; color:var(--ink); }
  .totals-table .total-row { border-top:1px solid var(--line); }
  .totals-table .total-row .label { font-weight:700; color:var(--ink); font-size:15px; }
  .totals-table .total-row .value { color:var(--ink); font-size:16px; font-weight:700; }

  /* ===== Payment Details ===== */
  .payment-details {
    padding:16px 20px; border-top:1px solid var(--line);
    background:#f8f9fa;
  }
  .payment-details h3 { margin:0 0 8px; font-size:15px; color:var(--ink); font-weight:700; }
  .payment-details p { margin:4px 0 10px; font-size:13px; color:var(--ink); line-height:1.5; }
  .payment-grid { display:grid; grid-template-columns: repeat(3, 1fr); gap:12px; margin-top:8px; }
  .payment-item { }
  .payment-item .label { font-weight:600; color:var(--ink); font-size:13px; margin-bottom:1px; }
  .payment-item .value { color:var(--ink); font-size:13px; }

  /* ===== Footer ===== */
  .footer {
    padding:20px; border-top:1px solid var(--line);
    text-align:center; color:var(--ink); font-size:15px; font-weight:600;
  }

  /* ===== Print Styles ===== */
  @media print {
    .toolbar, .alert { display:none !important; }
    .wrap { margin:0; max-width:none; padding:0; }
    .sheet { border:none; border-radius:0; }
    
    /* Hide all buttons and action columns */
    .add-item-btn, .remove-item-btn, button, .btn { display:none !important; }
    .items-table th:last-child, .items-table td:last-child { display:none !important; }
    
    /* Make inputs look like plain text */
    input[type="text"], input[type="date"], input[type="email"], input[type="number"], textarea {
      border:none !important;
      background:transparent !important;
      padding:0 !important;
      -webkit-appearance:none !important;
      -moz-appearance:textfield !important;
      appearance:none !important;
      white-space:nowrap !important;
    }
    
    /* Hide number input spinners - comprehensive */
    input[type="number"]::-webkit-inner-spin-button,
    input[type="number"]::-webkit-outer-spin-button {
      -webkit-appearance:none !important;
      appearance:none !important;
      margin:0 !important;
      display:none !important;
      opacity:0 !important;
      visibility:hidden !important;
    }
    
    /* Hide date input calendar icon and controls - comprehensive */
    input[type="date"]::-webkit-calendar-picker-indicator {
      display:none !important;
      -webkit-appearance:none !important;
      appearance:none !important;
      opacity:0 !important;
      visibility:hidden !important;
      width:0 !important;
      height:0 !important;
      position:absolute !important;
    }
    
    input[type="date"]::-webkit-inner-spin-button,
    input[type="date"]::-webkit-clear-button {
      display:none !important;
      -webkit-appearance:none !important;
    }
    
    input[type="date"]::-webkit-datetime-edit-fields-wrapper,
    input[type="date"]::-webkit-datetime-edit-text,
    input[type="date"]::-webkit-datetime-edit-month-field,
    input[type="date"]::-webkit-datetime-edit-day-field,
    input[type="date"]::-webkit-datetime-edit-year-field {
      -webkit-appearance:none !important;
      padding:0 !important;
    }
    
    input:focus, textarea:focus { outline:none !important; }
    
    /* Remove placeholder text in print */
    input::placeholder, textarea::placeholder { color:transparent !important; }
    
    /* Hide empty inputs/textareas */
    input:placeholder-shown, textarea:placeholder-shown { 
      min-height:auto !important;
      height:auto !important;
    }
    
    textarea {
      resize:none !important;
      overflow:hidden !important;
      height:auto !important;
    }
    
    /* Prevent line breaks in billing address */
    .billing-block input {
      display:inline !important;
      white-space:nowrap !important;
      overflow:visible !important;
      padding-left:10px !important;
    }
    
    .billing-block br { display:none !important; }
    
    .billing-block p {
      white-space:nowrap !important;
      overflow:visible !important;
      padding-left:10px !important;
    }
    
    /* Adjust table for print */
    .items-table th:nth-child(1), .items-table td:nth-child(1) { width:55% !important; }
    .items-table th:nth-child(2), .items-table td:nth-child(2) { width:10% !important; }
    .items-table th:nth-child(3), .items-table td:nth-child(3) { width:15% !important; }
    .items-table th:nth-child(4), .items-table td:nth-child(4) { width:20% !important; }
    
    /* Navigation buttons */
    div[style*="display:flex"][style*="gap:16px"] { display:none !important; }
  }

  /* ===== Form Styles ===== */
  input[type="text"], input[type="email"], textarea {
    border:none; background:transparent; font:inherit; color:inherit; width:100% !important;
  }
  input:focus, textarea:focus { outline:1px solid var(--blue); }
  .add-item-btn, .remove-item-btn {
    appearance:none; border:1px solid var(--line); background:#fff;
    padding:6px 10px; border-radius:6px; cursor:pointer; font-size:12px;
  }
  .add-item-btn { color:var(--blue); }
  .remove-item-btn { color:#dc3545; }
</style>
</head>
<body>

<div class="toolbar">
  <button class="btn" type="button" onclick="document.querySelector('form').reset(); calculateTotals();">Reset</button>
  <button class="btn success" type="button" onclick="document.querySelector('form').submit();">Save Invoice</button>
  <?php if ($editMode): ?>
  <button class="btn info" type="button" onclick="document.querySelector('input[name=action]').value='save_as'; document.querySelector('form').submit();">Save As New</button>
  <?php endif; ?>
  <a href="manage_data.php" class="btn info">View Saved Records</a>
  <button class="btn primary" type="button" onclick="window.print()">Print / Save PDF</button>
</div>

<div class="wrap">
  <?php if ($message): ?>
    <?= $message ?>
  <?php endif; ?>
  
  <div style="display:flex; gap:16px; margin-bottom:24px; justify-content:center;">
    <a href="index.php" class="btn">Home</a>
    <a href="invoice.php" class="btn primary">Invoice</a>
    <a href="service_checklist.php" class="btn">Service &amp; Maintenance Checklist</a>
  </div>

  <div class="sheet">
    <form method="POST" action="">
      <input type="hidden" name="action" value="save">
      <?php if ($editMode): ?>
        <input type="hidden" name="edit_filename" value="<?= htmlspecialchars($editFilename) ?>">
        <div class="alert alert-info">Editing existing invoice: <?= htmlspecialchars($editFilename) ?></div>
      <?php endif; ?>
      
      <!-- Header -->
      <div class="header">
        <div class="brand">
          <img class="logo" src="logo.png" alt="Company Logo" />
          <div class="brand-info">
            <h1>ORIENT Gas Engineers LTD</h1>
            <p><span style="font-size:11px; margin-right:4px; color:#000; filter:grayscale(100%);">📍</span>45 Chalk Pit Avenue, Orpington, Kent, BR5 3JJ</p>
            <p><span style="font-size:11px; margin-right:4px; color:#000; filter:grayscale(100%);">☎</span>07552478584</p>
          </div>
        </div>
        <div class="invoice-meta">
          <h2 class="invoice-title">INVOICE</h2>
          <div class="meta-row">
            <span class="meta-label">Date</span>
            <input class="meta-value" type="text" name="invoice_date" value="<?= htmlspecialchars($formData['invoice_date'] ?? date('d/m/Y')) ?>" placeholder="DD/MM/YYYY" style="width:140px;">
          </div>
          <div class="meta-row">
            <span class="meta-label">Invoice No.</span>
            <input class="meta-value" type="text" name="invoice_number" value="<?= htmlspecialchars($formData['invoice_number'] ?? $nextInvoiceNumber ?? '00001') ?>" style="width:140px;" readonly>
          </div>
        </div>
      </div>

      <!-- Billing Information -->
      <div class="billing">
        <div class="billing-block">
          <h3>Bill to</h3>
          <input type="text" name="client_name" value="<?= htmlspecialchars($formData['client_name'] ?? '') ?>" placeholder="Client Name" style="font-weight:400; font-size:14px; margin-bottom:2px;">
          <br>
          <input type="text" name="client_address1" value="<?= htmlspecialchars($formData['client_address1'] ?? '') ?>" placeholder="Address Line 1">
          <br>
          <input type="text" name="client_address2" value="<?= htmlspecialchars($formData['client_address2'] ?? '') ?>" placeholder="Address Line 2">
          <br>
          <input type="text" name="client_city" value="<?= htmlspecialchars($formData['client_city'] ?? '') ?>" placeholder="City">
          <br>
          <input type="text" name="client_country" value="<?= htmlspecialchars($formData['client_country'] ?? '') ?>" placeholder="Country">
          <br>
          <input type="text" name="client_postcode" value="<?= htmlspecialchars($formData['client_postcode'] ?? '') ?>" placeholder="Postcode">
        </div>
      </div>

      <!-- Items Table -->
      <div class="items">
        <table class="items-table" id="itemsTable">
          <thead>
            <tr>
              <th style="width:50%">Description</th>
              <th style="width:10%; text-align:center;">Qty</th>
              <th style="width:15%; text-align:right;">Unit Price</th>
              <th style="width:15%; text-align:right;">Amount</th>
              <th style="width:10%">Action</th>
            </tr>
          </thead>
          <tbody id="itemsTableBody">
            <!-- Items will be dynamically added here -->
          </tbody>
        </table>
        
        <div style="padding:16px; border-bottom:1px solid var(--line);">
          <button type="button" class="add-item-btn" onclick="addItem()">+ Add Item</button>
        </div>
      </div>

      <!-- Totals -->
      <div class="totals">
        <table class="totals-table">
          <tr>
            <td class="label">Subtotal</td>
            <td class="value">£<span id="subtotal">0.00</span></td>
          </tr>
          <tr class="total-row">
            <td class="label">Total</td>
            <td class="value">£<span id="total">0.00</span></td>
          </tr>
        </table>
      </div>

      <!-- Payment Terms & Details -->
      <div class="payment-details">
        <h3>Payment Terms & Details</h3>
        <p>This invoice is expected to be paid immediately.</p>
        
        <div class="payment-grid">
          <div class="payment-item">
            <div class="label">Bank Name</div>
            <div class="value">Tide</div>
          </div>
          <div class="payment-item">
            <div class="label">Account Name</div>
            <div class="value">Orient Gas Engineers LTD</div>
          </div>
          <div class="payment-item">
            <div class="label">Account Number</div>
            <div class="value">20354297</div>
          </div>
          <div class="payment-item">
            <div class="label">Sort Code</div>
            <div class="value">04-06-05</div>
          </div>
          <div class="payment-item">
            <div class="label">Company Number</div>
            <div class="value">14584954</div>
          </div>
          <div class="payment-item">
            <div class="label">VAT Number</div>
            <div class="value"></div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="footer">
        <p>Thank You</p>
      </div>
    </form>
  </div>
</div>

<script>
let itemCount = 0;

function addItem() {
  itemCount++;
  const tbody = document.getElementById('itemsTableBody');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td><textarea name="item_description[]" placeholder="Enter service description" rows="2" style="resize:vertical;"></textarea></td>
    <td style="text-align:center;"><input type="text" name="item_quantity[]" value="1" class="qty-input" onchange="calculateRowTotal(this)" oninput="this.value=this.value.replace(/[^0-9]/g,'')"></td>
    <td style="text-align:right;"><input type="text" name="item_rate[]" value="0.00" class="rate-input" onchange="calculateRowTotal(this)" oninput="this.value=this.value.replace(/[^0-9.]/g,'')"></td>
    <td style="text-align:right;"><span class="amount-display">£0.00</span></td>
    <td><button type="button" class="remove-item-btn" onclick="removeItem(this)">Remove</button></td>
  `;
  tbody.appendChild(row);
}

function removeItem(btn) {
  btn.closest('tr').remove();
  calculateTotals();
}

function calculateRowTotal(input) {
  const row = input.closest('tr');
  const qty = parseFloat(row.querySelector('[name="item_quantity[]"]').value) || 0;
  const rate = parseFloat(row.querySelector('[name="item_rate[]"]').value) || 0;
  const amount = qty * rate;
  row.querySelector('.amount-display').textContent = '£' + amount.toFixed(2);
  calculateTotals();
}

function calculateTotals() {
  const rows = document.querySelectorAll('#itemsTableBody tr');
  let subtotal = 0;
  
  rows.forEach(row => {
    const qty = parseFloat(row.querySelector('[name="item_quantity[]"]').value) || 0;
    const rate = parseFloat(row.querySelector('[name="item_rate[]"]').value) || 0;
    subtotal += qty * rate;
  });
  
  const total = subtotal;
  
  document.getElementById('subtotal').textContent = subtotal.toFixed(2);
  document.getElementById('total').textContent = total.toFixed(2);
}

// Initialize with one item
addItem();

// Load existing items if in edit mode
<?php if ($editMode && !empty($formData['item_description'])): ?>
// Clear the initial item first
document.getElementById('itemsTableBody').innerHTML = '';

<?php 
$items = is_array($formData['item_description']) ? $formData['item_description'] : [$formData['item_description']];
for ($i = 0; $i < count($items); $i++): 
?>
addItem();
const row<?= $i ?> = document.querySelector('#itemsTableBody tr:last-child');
row<?= $i ?>.querySelector('[name="item_description[]"]').value = <?= json_encode($formData['item_description'][$i] ?? '') ?>;
row<?= $i ?>.querySelector('[name="item_quantity[]"]').value = <?= json_encode($formData['item_quantity'][$i] ?? '1') ?>;
row<?= $i ?>.querySelector('[name="item_rate[]"]').value = <?= json_encode($formData['item_rate'][$i] ?? '0.00') ?>;
calculateRowTotal(row<?= $i ?>.querySelector('[name="item_quantity[]"]'));
<?php endfor; ?>
<?php endif; ?>

calculateTotals();
</script>

</body>
</html>