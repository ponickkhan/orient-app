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
                    $message = '<div class="alert alert-success">Service checklist updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-error">Failed to update service checklist.</div>';
                }
            } else {
                // Save new record
                $filename = saveFormData('service_checklist', $data);
                if ($filename) {
                    $message = '<div class="alert alert-success">Service checklist saved successfully! Filename: ' . $filename . '</div>';
                } else {
                    $message = '<div class="alert alert-error">Failed to save service checklist.</div>';
                }
            }
        } elseif ($_POST['action'] === 'save_as') {
            // Save as new record (copy)
            $data = sanitizeInput($_POST);
            unset($data['action']);
            unset($data['edit_filename']); // Remove edit reference to create new
            
            // Generate new serial number for the copy
            $data['appliance_serial'] = generateSerialNumber('service_checklist');
            
            $filename = saveFormData('service_checklist', $data);
            if ($filename) {
                $message = '<div class="alert alert-success">Checklist saved as new copy! Filename: ' . $filename . '</div>';
                // Redirect to edit the new record
                header('Location: service_checklist.php?edit=' . $filename);
                exit;
            } else {
                $message = '<div class="alert alert-error">Failed to save checklist as new copy.</div>';
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

// Generate serial number for new records
$autoSerialNo = !$editMode ? generateSerialNumber('service_checklist') : ($formData['appliance_serial'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Service & Maintenance Checklist — Print-Optimized</title>
<style>
  /* ===== Page & Core ===== */
  @page { size: A4 portrait; margin: 7mm; }
  html, body { height: 100%; }
  body{
    margin:0;
    font-family: Arial, Helvetica, sans-serif;
    color:#111;
    background:#fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
    line-height:1.15;
  }
  :root{
    --ink:#1b1e24;
    --line:#2e5aa6;
    --muted:#6c727f;
    --head-bg:#fffec7;
    --vtitle-bg:#2e5aa6;
    --vtitle-fg:#fff;
    --bar:#2e5aa6;
    --bar-fg:#fff;
  }

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

  .toolbar{
    position: sticky; top:0; z-index:50;
    background:#f5f7fb; border-bottom:1px solid #d7dce8;
    padding:8px; display:flex; gap:8px; justify-content:flex-end;
  }
  .btn{ appearance:none; border:1px solid #cad2e2; background:#fff; padding:8px 12px; border-radius:8px; font-weight:700; cursor:pointer; text-decoration: none; display: inline-block; }
  .btn.primary{ background:#2e5aa6; color:#fff; border-color:#2e5aa6; }
  .btn.success{ background:#28a745; color:#fff; border-color:#28a745; }
  .btn.info{ background:#17a2b8; color:#fff; border-color:#17a2b8; }

  .sheet{ width: calc(210mm - 16mm); min-height: calc(297mm - 16mm); margin: 8mm auto; }

  /* ===== Blocks & Tables ===== */
  .block{ border:2px solid #2e5aa6; border-radius:3px; margin-top:6px; }
  table{ 
    width:100%; 
    border-collapse:collapse; 
    table-layout:fixed; 
    font-size:12px; 
    border:2px solid #2e5aa6;
    box-shadow: 0 0 0 1px #2e5aa6;
  }
  th, td{ 
    border:1px solid #2e5aa6; 
    padding:4px 6px; 
    vertical-align:middle; 
    background:#fff;
    box-shadow: inset 0 0 0 1px #2e5aa6;
  }
  th{ background:#fffec7; text-align:left; font-weight:700; }
  .tight th, .tight td{ padding:3px 4px; }
  .small td, .small th{ font-size:11px; }
  .center{ text-align:center; }
  .muted{ color:var(--muted); }
  .nowrap{ white-space:nowrap; }

  /* ===== Title band ===== */
  .titleband{
    display:grid; grid-template-columns:1fr auto; align-items:center; gap:8px;
    border:2px solid #2e5aa6; padding:8px 10px; margin-bottom:6px;
    background:#fff;
  }
  .titleband h1{ margin:0; font-size:20px; letter-spacing:.2px; }
  .brand{ font-weight:900; font-size:22px; letter-spacing:1px; display:flex; align-items:center; gap:8px; }
  .brand .dot{ width:22px; height:22px; border-radius:50%; background:#000; display:inline-block; }

  /* ===== Vertical title cell ===== */
  .vtitle{
    background: #2e5aa6;
    color: #fff;
    font-weight: 900;
    letter-spacing: .5px;
    writing-mode: vertical-rl;
    transform: rotate(180deg);
    text-align: center;
    vertical-align: middle;
    padding: 0;
    width: 22px; min-width: 22px;
    box-shadow: none;
    border: 1px solid #2e5aa6;
  }
  .vtitle span{ display:inline-block; line-height:1.2; margin:auto; }

  .row2{ display:grid; grid-template-columns: 1fr 1fr; gap:6px; }
  .row3{ display:grid; grid-template-columns: 1fr 1fr 1fr; gap:6px; }

  .sigbox{ height:34px; border:1px dashed #2e5aa6; background:#fff; }

  input[type="text"], input[type="date"], select{
    width:100%; border:0; background:transparent; font:inherit; color:#111;
    -webkit-text-fill-color:#111;
    padding:0;
    text-align: inherit;
  }
  select{ padding:0; }

  /* Center the PF/NA inputs visually */
  .pfna { text-align:center; }
  .pfna input[type="text"]{ text-align:center; }

  /* ===== Safety split bar ===== */
  .splitbar{
    background: #2e5aa6;
    color: #fff;
    display:grid;
    grid-template-columns: 32% 68%;
    align-items:center;
    font-weight:800;
    letter-spacing:.2px;
    font-size:12px;
    line-height:1.1;
  }
  .splitbar .left, .splitbar .right{
    padding:4px 6px;
    border-right:1px solid rgba(255,255,255,.25);
    text-align:center;
  }
  .splitbar .right{ border-right:0; }
  /* ===== Compact inline metrics (flattened height) ===== */
  .metrics-inline{
    display:flex;
    gap:4px;
    align-items:center;
    font-size:10px;
    flex-wrap:nowrap;         /* keep single line */
  }
  .metrics-inline .label{
    color:var(--muted);
    white-space:nowrap;
    line-height:1;            /* compact label */
  }
  .metrics-inline .mini{
    width:32px; min-width:32px;
    padding:0;
    text-align:center;
    height:18px;              /* match normal row */
    font-size:10px;
    line-height:18px;         /* vertically center text */
    border:0;
    background:transparent;
  }

  /* Reduce padding for the metrics cell itself to keep height tight */
  td.metrics-cell { padding-top:2px; padding-bottom:2px; }

  /* ===== Print styles ===== */
  @media print {
    /* Hide non-printable elements */
    .toolbar, .alert, a.btn { display: none !important; }
    .sheet > div:first-of-type { display: none !important; }
    .muted { display: none !important; }
    
    @page { 
      margin: 5mm; 
      size: A4 portrait; 
    }
    
    html, body, *, *::before, *::after { 
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
      color-adjust: exact !important;
    }
    
    body { 
      font-size: 9px;
      line-height: 1.2;
    }
    
    .sheet { 
      width: 100%; 
      max-width: 100%;
      margin: 0;
      padding: 0;
    }
    
    .titleband {
      border: 1px solid #2e5aa6 !important;
      box-shadow: 0 0 0 1px #2e5aa6 !important;
      padding: 6px 8px !important;
      margin-bottom: 4px !important;
      page-break-after: avoid;
      background: #fff !important;
    }
    
    .titleband h1 { 
      font-size: 14px;
      margin: 0;
    }
    
    .block {
      border: 1px solid #2e5aa6 !important;
      box-shadow: 0 0 0 1px #2e5aa6 !important;
      margin-top: 3px !important;
      margin-bottom: 3px !important;
      page-break-inside: avoid;
    }
    
    table { 
      font-size: 9px;
      width: 100%;
      border-collapse: collapse !important;
      border: 1px solid #2e5aa6 !important;
      box-shadow: 0 0 0 1px #2e5aa6 !important;
      page-break-inside: avoid;
    }
    
    tr { 
      page-break-inside: avoid !important;
      page-break-after: auto;
    }
    
    th, td { 
      border: 0.5px solid #2e5aa6 !important;
      box-shadow: inset 0 0 0 0.5px #2e5aa6 !important;
      padding: 2px 4px !important;
      background: #fff !important;
    }
    
    th {
      background: #fffec7 !important;
      font-weight: 700 !important;
    }
    
    .tight th, .tight td {
      padding: 2px 3px !important;
      border: 0.5px solid #2e5aa6 !important;
      box-shadow: inset 0 0 0 0.5px #2e5aa6 !important;
    }
    
    .small th, .small td {
      font-size: 8.5px !important;
      border: 0.5px solid #2e5aa6 !important;
      box-shadow: inset 0 0 0 0.5px #2e5aa6 !important;
    }
    
    .vtitle {
      background: #2e5aa6 !important;
      color: #fff !important;
      border: 1px solid #2e5aa6 !important;
      box-shadow: inset 0 0 0 1px #2e5aa6 !important;
      writing-mode: vertical-rl;
      transform: rotate(180deg);
      width: 20px !important;
      min-width: 20px !important;
      padding: 4px 2px !important;
    }
    
    .splitbar {
      background: #2e5aa6 !important;
      color: #fff !important;
      border: 1px solid #2e5aa6 !important;
    }
    
    .splitbar .left, .splitbar .right {
      padding: 3px 4px !important;
    }
    
    .sigbox {
      border: 0.5px dashed #2e5aa6 !important;
      box-shadow: inset 0 0 0 0.5px #2e5aa6 !important;
      height: 24px !important;
      background: #fff !important;
      overflow: hidden !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
    }
    
    .sigbox img {
      max-height: 22px !important;
      max-width: 100% !important;
      width: auto !important;
      height: auto !important;
      object-fit: contain !important;
    }
    
    input[type="text"], 
    input[type="date"], 
    select {
      border: none !important;
      border-bottom: 0.5px solid #2e5aa6 !important;
      background: transparent !important;
      -webkit-appearance: none !important;
      appearance: none !important;
      color: #111 !important;
      -webkit-text-fill-color: #111 !important;
      padding: 0 !important;
      margin: 0 !important;
      font-size: inherit !important;
      line-height: inherit !important;
    }
    
    .metrics-inline {
      font-size: 8px !important;
    }
    
    .metrics-inline .mini {
      font-size: 8px !important;
      height: 16px !important;
      line-height: 16px !important;
    }
    
    .brand img {
      max-height: 60px !important;
      width: auto !important;
    }
    
    .row2, .row3 {
      gap: 3px !important;
      margin-bottom: 3px !important;
    }
  }

</style>
</head>
<body>

<div class="toolbar" role="toolbar" aria-label="Actions">
  <button class="btn" type="button" onclick="document.querySelector('form').reset()">Reset</button>
  <button class="btn success" type="button" onclick="document.querySelector('form').submit();">Save Checklist</button>
  <?php if ($editMode): ?>
  <button class="btn" type="button" onclick="document.querySelector('input[name=action]').value='save_as'; document.querySelector('form').submit();" style="background:#17a2b8; color:#fff;">Save As New</button>
  <?php endif; ?>
  <a href="manage_data.php" class="btn info">View Saved Records</a>
  <button class="btn primary" type="button" onclick="window.print()">Print / Save PDF</button>
</div>

<div class="sheet">
  <?php if ($message): ?>
    <?= $message ?>
  <?php endif; ?>
  
  <div style="display:flex; gap:16px; margin-bottom:24px; justify-content:center;">
    <a href="index.php" class="btn">Home</a>
    <a href="invoice.php" class="btn">Invoice</a>
    <a href="service_checklist.php" class="btn primary">Service &amp; Maintenance Checklist</a>
  </div>

<form method="POST" action="">
  <input type="hidden" name="action" value="save">
  <?php if ($editMode): ?>
    <input type="hidden" name="edit_filename" value="<?= htmlspecialchars($editFilename) ?>">
    <div class="alert alert-info">Editing existing checklist: <?= htmlspecialchars($editFilename) ?></div>
  <?php endif; ?>

  <!-- Title -->
  <div class="titleband">
    <h1>SERVICE &amp; MAINTENANCE CHECKLIST</h1>
    <div class="brand">
      <img src="logo.png" alt="Logo" style="height:80px; width:auto; display:inline-block; vertical-align:middle;">
    </div>
  </div>

  <div class="muted" style="font-size:11px; margin-bottom:6px;">
    Safety checks in accordance with the Gas Safety (Installation and Use) Regulations can be recorded on this checklist on the basis that, if not applicable, certain results are based on visual inspection only. Detailed inspections have not been undertaken unless specifically recorded. This checklist is not designed to be used as a Landlord/Homeowner Gas Safety Record (CPG17) or Gas Safety Record (CPG18).
  </div>

  <!-- BUSINESS -->
  <div class="block">
    <table class="tight small">
      <colgroup><col style="width:22px"><col style="width:14%"><col style="width:36%"><col style="width:14%"><col style="width:36%"></colgroup>
      <tr>
        <td class="vtitle" rowspan="4"><span>BUSINESS</span></td>
        <th>Company</th><td><input type="text" name="company_name" value="<?= htmlspecialchars($formData['company_name'] ?? 'ORIENT Gas Engineers LTD.') ?>"></td>
        <th>Gas Safe Reg No</th><td><input type="text" name="gas_safe_reg_no" value="<?= htmlspecialchars($formData['gas_safe_reg_no'] ?? '927879') ?>"></td>
      </tr>
      <tr>
        <th>Address</th><td><input type="text" name="company_address" value="<?= htmlspecialchars($formData['company_address'] ?? '45 Chalk Pit Avenue, Orpington') ?>"></td>
        <th>Engineer Name</th><td><input type="text" name="engineer_name" value="<?= htmlspecialchars($formData['engineer_name'] ?? '') ?>"></td>
      </tr>
      <tr>
        <th>Postcode</th><td><input type="text" name="company_postcode" value="<?= htmlspecialchars($formData['company_postcode'] ?? 'BR5 3JJ') ?>"></td>
        <th>Licence No</th><td><input type="text" name="engineer_licence" value="<?= htmlspecialchars($formData['engineer_licence'] ?? '') ?>"></td>
      </tr>
      <tr>
        <th>Telephone</th><td><input type="text" name="company_telephone" value="<?= htmlspecialchars($formData['company_telephone'] ?? '+44 7795 999196') ?>"></td>
        <th>Mobile</th><td><input type="text" name="engineer_mobile" value="<?= htmlspecialchars($formData['engineer_mobile'] ?? '') ?>"></td>
      </tr>
    </table>
  </div>

  <!-- SITE + CLIENT -->
  <div class="row2">
    <div class="block">
      <table class="tight small">
        <colgroup><col style="width:22px"><col style="width:24%"><col></colgroup>
        <tr>
          <td class="vtitle" rowspan="4"><span>SITE</span></td>
          <th>Tenant/Homeowner Name</th><td><input type="text" name="site_tenant_name" value="<?= htmlspecialchars($formData['site_tenant_name'] ?? '') ?>"></td>
        </tr>
        <tr><th>Address</th><td><input type="text" name="site_address" value="<?= htmlspecialchars($formData['site_address'] ?? '') ?>"></td></tr>
        <tr><th>Postcode</th><td><input type="text" name="site_postcode" value="<?= htmlspecialchars($formData['site_postcode'] ?? '') ?>"></td></tr>
        <tr><th>Contact</th><td><input type="text" name="site_contact" value="<?= htmlspecialchars($formData['site_contact'] ?? '') ?>" placeholder="Phone / Email"></td></tr>
      </table>
    </div>
    <div class="block">
      <table class="tight small">
        <colgroup><col style="width:22px"><col style="width:24%"><col></colgroup>
        <tr>
          <td class="vtitle" rowspan="4"><span>CLIENT</span></td>
          <th>Landlord/Agent Name</th><td><input type="text" name="client_landlord_name" value="<?= htmlspecialchars($formData['client_landlord_name'] ?? '') ?>"></td>
        </tr>
        <tr><th>Address</th><td><input type="text" name="client_address" value="<?= htmlspecialchars($formData['client_address'] ?? '') ?>"></td></tr>
        <tr><th>Postcode</th><td><input type="text" name="client_postcode" value="<?= htmlspecialchars($formData['client_postcode'] ?? '') ?>"></td></tr>
        <tr><th>Contact</th><td><input type="text" name="client_contact" value="<?= htmlspecialchars($formData['client_contact'] ?? '') ?>" placeholder="Phone / Email"></td></tr>
      </table>
    </div>
  </div>

  <!-- INSTALLATION + APPLIANCE (brief, side by side) -->
  <div class="row2">
    <!-- INSTALLATION checklist -->
    <div class="block">
      <table class="tight small">
        <colgroup>
          <col style="width:22px"><!-- vtitle -->
          <col><!-- item -->
          <col style="width:10%"><!-- pass -->
          <col style="width:10%"><!-- fail -->
          <col style="width:10%"><!-- na -->
        </colgroup>
        <tr>
          <td class="vtitle" rowspan="6"><span>INSTALLATION</span></td>
          <th>Item</th><th class="center">Pass</th><th class="center">Fail</th><th class="center">N/A</th>
        </tr>
        <tr>
          <td>Satisfactory Meter/Cylinder</td>
          <td class="pfna"><input type="text" name="inst_meter_pass" value="<?= htmlspecialchars($formData['inst_meter_pass'] ?? 'Pass') ?>"></td>
          <td class="pfna"><input type="text" name="inst_meter_fail" value="<?= htmlspecialchars($formData['inst_meter_fail'] ?? '') ?>"></td>
          <td class="pfna"><input type="text" name="inst_meter_na" value="<?= htmlspecialchars($formData['inst_meter_na'] ?? '') ?>"></td>
        </tr>
        <tr>
          <td>Inspection of Visible Pipework</td>
          <td class="pfna"><input type="text" name="inst_pipework_pass" value="<?= htmlspecialchars($formData['inst_pipework_pass'] ?? 'Pass') ?>"></td>
          <td class="pfna"><input type="text" name="inst_pipework_fail" value="<?= htmlspecialchars($formData['inst_pipework_fail'] ?? '') ?>"></td>
          <td class="pfna"><input type="text" name="inst_pipework_na" value="<?= htmlspecialchars($formData['inst_pipework_na'] ?? '') ?>"></td>
        </tr>
        <tr>
          <td>ECV Access and Operation</td>
          <td class="pfna"><input type="text" name="inst_ecv_pass" value="<?= htmlspecialchars($formData['inst_ecv_pass'] ?? 'Pass') ?>"></td>
          <td class="pfna"><input type="text" name="inst_ecv_fail" value="<?= htmlspecialchars($formData['inst_ecv_fail'] ?? '') ?>"></td>
          <td class="pfna"><input type="text" name="inst_ecv_na" value="<?= htmlspecialchars($formData['inst_ecv_na'] ?? '') ?>"></td>
        </tr>
        <tr>
          <td>Protective Equipotential Bonding</td>
          <td class="pfna"><input type="text" name="inst_bonding_pass" value="<?= htmlspecialchars($formData['inst_bonding_pass'] ?? 'Pass') ?>"></td>
          <td class="pfna"><input type="text" name="inst_bonding_fail" value="<?= htmlspecialchars($formData['inst_bonding_fail'] ?? '') ?>"></td>
          <td class="pfna"><input type="text" name="inst_bonding_na" value="<?= htmlspecialchars($formData['inst_bonding_na'] ?? '') ?>"></td>
        </tr>
        <tr>
          <td>Tightness Test</td>
          <td class="pfna"><input type="text" name="inst_tightness_pass" value="<?= htmlspecialchars($formData['inst_tightness_pass'] ?? 'Pass') ?>"></td>
          <td class="pfna"><input type="text" name="inst_tightness_fail" value="<?= htmlspecialchars($formData['inst_tightness_fail'] ?? '') ?>"></td>
          <td class="pfna"><input type="text" name="inst_tightness_na" value="<?= htmlspecialchars($formData['inst_tightness_na'] ?? '') ?>"></td>
        </tr>
      </table>
    </div>

    <!-- APPLIANCE (brief) -->
    <div class="block">
      <table class="tight small">
        <colgroup><col style="width:22px"><col style="width:38%"><col></colgroup>
        <tr>
          <td class="vtitle" rowspan="7"><span>APPLIANCE</span></td>
          <th>Location</th><td><input type="text" name="appliance_location" value="<?= htmlspecialchars($formData['appliance_location'] ?? '') ?>" placeholder="e.g., Kitchen"></td>
        </tr>
        <tr>
          <th>Owned by Landlord/Homeowner</th>
          <td>
            <select name="appliance_owned_by">
              <option value="Yes" <?= ($formData['appliance_owned_by'] ?? 'Yes') == 'Yes' ? 'selected' : '' ?>>Yes</option>
              <option value="No" <?= ($formData['appliance_owned_by'] ?? '') == 'No' ? 'selected' : '' ?>>No</option>
            </select>
          </td>
        </tr>
        <tr><th>Type</th><td><input type="text" name="appliance_type" value="<?= htmlspecialchars($formData['appliance_type'] ?? '') ?>" placeholder="Boiler / Fire / Hob"></td></tr>
        <tr><th>Model</th><td><input type="text" name="appliance_model" value="<?= htmlspecialchars($formData['appliance_model'] ?? '') ?>"></td></tr>
        <tr>
          <th>Chimney/Flue Type</th>
          <td>
            <select name="appliance_flue_type">
              <option value="Flueless" <?= ($formData['appliance_flue_type'] ?? 'Flueless') == 'Flueless' ? 'selected' : '' ?>>Flueless</option>
              <option value="Open Flue" <?= ($formData['appliance_flue_type'] ?? '') == 'Open Flue' ? 'selected' : '' ?>>Open Flue</option>
              <option value="Room-Sealed" <?= ($formData['appliance_flue_type'] ?? '') == 'Room-Sealed' ? 'selected' : '' ?>>Room-Sealed</option>
            </select>
          </td>
        </tr>
        <tr><th>Manufacturer</th><td><input type="text" name="appliance_manufacturer" value="<?= htmlspecialchars($formData['appliance_manufacturer'] ?? '') ?>"></td></tr>
        <tr><th>Serial No</th><td><input type="text" name="appliance_serial" value="<?= htmlspecialchars($formData['appliance_serial'] ?? $autoSerialNo) ?>" readonly style="background-color: #f0f0f0; cursor: not-allowed;"></td></tr>
      </table>
    </div>
  </div>

  <!-- SAFETY CHECKS (single horizontal bar, no inputs) -->
  <div class="block">
    <div class="splitbar">
      <div class="left">SAFETY CHECKS</div>
      <div class="right">Safety Related Defects Identified and any Remedial Action Taken</div>
    </div>
  </div>

  <!-- BIG APPLIANCE SECTION -->
  <div class="block">
    <table class="tight small">
      <colgroup>
        <col style="width:22px"><!-- vtitle -->
        <col><!-- item -->
        <col style="width:10%"><!-- pass -->
        <col style="width:10%"><!-- fail -->
        <col style="width:10%"><!-- na -->
        <col style="width:22%"><!-- comments -->
      </colgroup>
      <tr>
        <td class="vtitle" rowspan="21"><span>APPLIANCE</span></td>
        <th>Item</th>
        <th class="center">Pass</th>
        <th class="center">Fail</th>
        <th class="center">N/A</th>
        <th>Comments</th>
      </tr>

      <tr>
        <td>Gas Connection and Isolation</td>
        <td class="pfna"><input type="text" name="app_gas_connection_pass" value="<?= htmlspecialchars($formData['app_gas_connection_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_gas_connection_fail" value="<?= htmlspecialchars($formData['app_gas_connection_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_gas_connection_na" value="<?= htmlspecialchars($formData['app_gas_connection_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_gas_connection_comments" value="<?= htmlspecialchars($formData['app_gas_connection_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Electrical Connection &amp; Isolation</td>
        <td class="pfna"><input type="text" name="app_electrical_connection_pass" value="<?= htmlspecialchars($formData['app_electrical_connection_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_electrical_connection_fail" value="<?= htmlspecialchars($formData['app_electrical_connection_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_electrical_connection_na" value="<?= htmlspecialchars($formData['app_electrical_connection_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_electrical_connection_comments" value="<?= htmlspecialchars($formData['app_electrical_connection_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Water Connection &amp; Isolation</td>
        <td class="pfna"><input type="text" name="app_water_connection_pass" value="<?= htmlspecialchars($formData['app_water_connection_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_water_connection_fail" value="<?= htmlspecialchars($formData['app_water_connection_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_water_connection_na" value="<?= htmlspecialchars($formData['app_water_connection_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_water_connection_comments" value="<?= htmlspecialchars($formData['app_water_connection_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Overall Condition, Stability and Controls Operation</td>
        <td class="pfna"><input type="text" name="app_overall_condition_pass" value="<?= htmlspecialchars($formData['app_overall_condition_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_overall_condition_fail" value="<?= htmlspecialchars($formData['app_overall_condition_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_overall_condition_na" value="<?= htmlspecialchars($formData['app_overall_condition_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_overall_condition_comments" value="<?= htmlspecialchars($formData['app_overall_condition_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Visual Inspection of Heat Exchanger</td>
        <td class="pfna"><input type="text" name="app_heat_exchanger_pass" value="<?= htmlspecialchars($formData['app_heat_exchanger_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_heat_exchanger_fail" value="<?= htmlspecialchars($formData['app_heat_exchanger_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_heat_exchanger_na" value="<?= htmlspecialchars($formData['app_heat_exchanger_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_heat_exchanger_comments" value="<?= htmlspecialchars($formData['app_heat_exchanger_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Burner and Injectors</td>
        <td class="pfna"><input type="text" name="app_burner_injectors_pass" value="<?= htmlspecialchars($formData['app_burner_injectors_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_burner_injectors_fail" value="<?= htmlspecialchars($formData['app_burner_injectors_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_burner_injectors_na" value="<?= htmlspecialchars($formData['app_burner_injectors_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_burner_injectors_comments" value="<?= htmlspecialchars($formData['app_burner_injectors_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Fans</td>
        <td class="pfna"><input type="text" name="app_fans_pass" value="<?= htmlspecialchars($formData['app_fans_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_fans_fail" value="<?= htmlspecialchars($formData['app_fans_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_fans_na" value="<?= htmlspecialchars($formData['app_fans_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_fans_comments" value="<?= htmlspecialchars($formData['app_fans_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Ignition</td>
        <td class="pfna"><input type="text" name="app_ignition_pass" value="<?= htmlspecialchars($formData['app_ignition_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_ignition_fail" value="<?= htmlspecialchars($formData['app_ignition_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_ignition_na" value="<?= htmlspecialchars($formData['app_ignition_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_ignition_comments" value="<?= htmlspecialchars($formData['app_ignition_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Flame Picture</td>
        <td class="pfna"><input type="text" name="app_flame_picture_pass" value="<?= htmlspecialchars($formData['app_flame_picture_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_flame_picture_fail" value="<?= htmlspecialchars($formData['app_flame_picture_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_flame_picture_na" value="<?= htmlspecialchars($formData['app_flame_picture_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_flame_picture_comments" value="<?= htmlspecialchars($formData['app_flame_picture_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Correct Safety Device(s) Operation</td>
        <td class="pfna"><input type="text" name="app_safety_devices_pass" value="<?= htmlspecialchars($formData['app_safety_devices_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_safety_devices_fail" value="<?= htmlspecialchars($formData['app_safety_devices_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_safety_devices_na" value="<?= htmlspecialchars($formData['app_safety_devices_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_safety_devices_comments" value="<?= htmlspecialchars($formData['app_safety_devices_comments'] ?? '') ?>"></td>
      </tr>

      <!-- SPECIAL: Heat Input / Operating Pressure (metrics only) -->
      <tr>
        <td>Heat Input / Operating Pressure</td>
        <td class="pfna"><input type="text" name="app_heat_input_pass" value="<?= htmlspecialchars($formData['app_heat_input_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_heat_input_fail" value="<?= htmlspecialchars($formData['app_heat_input_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_heat_input_na" value="<?= htmlspecialchars($formData['app_heat_input_na'] ?? '') ?>"></td>
        <td class="metrics-cell">
          <div class="metrics-inline">
            <span class="label">kW</span><input class="mini" type="text" name="heat_input_kw" value="<?= htmlspecialchars($formData['heat_input_kw'] ?? '') ?>">
            <span class="label">kW/h</span><input class="mini" type="text" name="heat_input_kwh" value="<?= htmlspecialchars($formData['heat_input_kwh'] ?? '') ?>">
            <span class="label">mbar</span><input class="mini" type="text" name="operating_pressure_mbar" value="<?= htmlspecialchars($formData['operating_pressure_mbar'] ?? '') ?>">
          </div>
        </td>
      </tr>

      <tr>
        <td>Seals including Appliance Casing</td>
        <td class="pfna"><input type="text" name="app_seals_casing_pass" value="<?= htmlspecialchars($formData['app_seals_casing_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_seals_casing_fail" value="<?= htmlspecialchars($formData['app_seals_casing_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_seals_casing_na" value="<?= htmlspecialchars($formData['app_seals_casing_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_seals_casing_comments" value="<?= htmlspecialchars($formData['app_seals_casing_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Condensate Trap/Disposal</td>
        <td class="pfna"><input type="text" name="app_condensate_trap_pass" value="<?= htmlspecialchars($formData['app_condensate_trap_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_condensate_trap_fail" value="<?= htmlspecialchars($formData['app_condensate_trap_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_condensate_trap_na" value="<?= htmlspecialchars($formData['app_condensate_trap_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_condensate_trap_comments" value="<?= htmlspecialchars($formData['app_condensate_trap_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Pressure/Temperature Relief Valve</td>
        <td class="pfna"><input type="text" name="app_relief_valve_pass" value="<?= htmlspecialchars($formData['app_relief_valve_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_relief_valve_fail" value="<?= htmlspecialchars($formData['app_relief_valve_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_relief_valve_na" value="<?= htmlspecialchars($formData['app_relief_valve_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_relief_valve_comments" value="<?= htmlspecialchars($formData['app_relief_valve_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Return Air/Plenum</td>
        <td class="pfna"><input type="text" name="app_return_air_pass" value="<?= htmlspecialchars($formData['app_return_air_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_return_air_fail" value="<?= htmlspecialchars($formData['app_return_air_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_return_air_na" value="<?= htmlspecialchars($formData['app_return_air_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_return_air_comments" value="<?= htmlspecialchars($formData['app_return_air_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Fireplace Catchment Space and Closure Plate</td>
        <td class="pfna"><input type="text" name="app_fireplace_catchment_pass" value="<?= htmlspecialchars($formData['app_fireplace_catchment_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_fireplace_catchment_fail" value="<?= htmlspecialchars($formData['app_fireplace_catchment_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_fireplace_catchment_na" value="<?= htmlspecialchars($formData['app_fireplace_catchment_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_fireplace_catchment_comments" value="<?= htmlspecialchars($formData['app_fireplace_catchment_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Flue Flow &amp; Spillage Test</td>
        <td class="pfna"><input type="text" name="app_flue_flow_pass" value="<?= htmlspecialchars($formData['app_flue_flow_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_flue_flow_fail" value="<?= htmlspecialchars($formData['app_flue_flow_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_flue_flow_na" value="<?= htmlspecialchars($formData['app_flue_flow_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_flue_flow_comments" value="<?= htmlspecialchars($formData['app_flue_flow_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Satisfactory Chimney/Flue</td>
        <td class="pfna"><input type="text" name="app_chimney_flue_pass" value="<?= htmlspecialchars($formData['app_chimney_flue_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_chimney_flue_fail" value="<?= htmlspecialchars($formData['app_chimney_flue_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_chimney_flue_na" value="<?= htmlspecialchars($formData['app_chimney_flue_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_chimney_flue_comments" value="<?= htmlspecialchars($formData['app_chimney_flue_comments'] ?? '') ?>"></td>
      </tr>
      <tr>
        <td>Satisfactory Ventilation</td>
        <td class="pfna"><input type="text" name="app_ventilation_pass" value="<?= htmlspecialchars($formData['app_ventilation_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_ventilation_fail" value="<?= htmlspecialchars($formData['app_ventilation_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_ventilation_na" value="<?= htmlspecialchars($formData['app_ventilation_na'] ?? '') ?>"></td>
        <td><input type="text" name="app_ventilation_comments" value="<?= htmlspecialchars($formData['app_ventilation_comments'] ?? '') ?>"></td>
      </tr>

      <!-- SPECIAL: Final Combustion (metrics only) -->
      <tr>
        <td>Final Combustion Analyser Reading</td>
        <td class="pfna"><input type="text" name="app_combustion_pass" value="<?= htmlspecialchars($formData['app_combustion_pass'] ?? 'Pass') ?>"></td>
        <td class="pfna"><input type="text" name="app_combustion_fail" value="<?= htmlspecialchars($formData['app_combustion_fail'] ?? '') ?>"></td>
        <td class="pfna"><input type="text" name="app_combustion_na" value="<?= htmlspecialchars($formData['app_combustion_na'] ?? '') ?>"></td>
        <td class="metrics-cell">
          <div class="metrics-inline">
            <span class="label">CO</span><input class="mini" type="text" name="combustion_co" value="<?= htmlspecialchars($formData['combustion_co'] ?? '') ?>">
            <span class="label">CO₂%/Ratio</span><input class="mini" type="text" name="combustion_co2" value="<?= htmlspecialchars($formData['combustion_co2'] ?? '') ?>">
            <span class="label">O₂%</span><input class="mini" type="text" name="combustion_o2" value="<?= htmlspecialchars($formData['combustion_o2'] ?? '') ?>">
          </div>
        </td>
      </tr>
    </table>
  </div>

  <!-- SUMMARY -->
  <div class="block">
    <table class="tight small">
      <colgroup>
        <col style="width:22px">
        <col style="width:16%"><col style="width:12%"><col style="width:12%">
        <col style="width:16%"><col style="width:12%"><col style="width:20%">
      </colgroup>
      <tr>
        <td class="vtitle"><span>SUMMARY</span></td>
        <th>Safe to Use</th>
        <td class="center"><input type="text" name="summary_safe_yes" value="<?= htmlspecialchars($formData['summary_safe_yes'] ?? 'Yes') ?>" placeholder="Yes"></td>
        <td class="center"><input type="text" name="summary_safe_no" value="<?= htmlspecialchars($formData['summary_safe_no'] ?? '') ?>" placeholder="No"></td>
        <th>GIUSP Classification</th>
        <td class="center"><input type="text" name="summary_giusp" value="<?= htmlspecialchars($formData['summary_giusp'] ?? '') ?>" placeholder="None / AR / ID"></td>
        <th>Warning/Advisory Notice</th>
        <td class="center"><input type="text" name="summary_warning_notice" value="<?= htmlspecialchars($formData['summary_warning_notice'] ?? '') ?>" placeholder="Serial No"></td>
      </tr>
    </table>
  </div>

  <!-- FOOTER: DATE / ENGINEER / CLIENT -->
  <div class="row3">
    <div class="block">
      <table class="tight small">
        <colgroup><col style="width:22px"><col style="width:42%"><col></colgroup>
        <tr>
          <td class="vtitle" rowspan="2"><span>DATE</span></td>
          <th>Checks Completed</th><td><input type="date" name="checks_completed_date" value="<?= htmlspecialchars(!empty($formData['checks_completed_date']) ? $formData['checks_completed_date'] : date('Y-m-d')) ?>"></td>
        </tr>
        <tr><th>Next Service/Maintenance Due</th><td><input type="date" name="next_service_due" value="<?= htmlspecialchars(!empty($formData['next_service_due']) ? $formData['next_service_due'] : date('Y-m-d', strtotime('+1 year'))) ?>"></td></tr>
      </table>
    </div>

    <div class="block">
      <table class="tight small">
        <colgroup><col style="width:22px"><col style="width:28%"><col></colgroup>
        <tr>
          <td class="vtitle" rowspan="2"><span>ENGINEER</span></td>
          <th>Sign</th><td><div class="sigbox"><img src="signature.png" alt="Signature" style="height:32px; max-width:100%; object-fit:contain;"></div></td>
        </tr>
        <tr><th>Name</th><td><input type="text" name="engineer_signature_name" value="<?= htmlspecialchars($formData['engineer_signature_name'] ?? '') ?>"></td></tr>
      </table>
    </div>

    <div class="block">
      <table class="tight small">
        <colgroup><col style="width:22px"><col style="width:28%"><col></colgroup>
        <tr>
          <td class="vtitle" rowspan="3"><span>CLIENT</span></td>
          <th>Sign</th><td><div class="sigbox"></div></td>
        </tr>
        <tr><th>Name</th><td><input type="text" name="client_signature_name" value="<?= htmlspecialchars($formData['client_signature_name'] ?? '') ?>"></td></tr>
        <tr><th>Position</th><td><input type="text" name="client_signature_position" value="<?= htmlspecialchars($formData['client_signature_position'] ?? '') ?>"></td></tr>
      </table>
    </div>
  </div>

  <div class="muted" style="font-size:10px; margin-top:6px;">
    © Orient Gas Engineers LTD.
  </div>

</form>
</div>

</body>
</html>