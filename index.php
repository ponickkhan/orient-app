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
                    $message = '<div class="alert alert-success">Record updated successfully!</div>';
                } else {
                    $message = '<div class="alert alert-error">Failed to update record.</div>';
                }
            } else {
                // Save new record
                $filename = saveFormData('gas_safety', $data);
                if ($filename) {
                    $message = '<div class="alert alert-success">Record saved successfully! Filename: ' . $filename . '</div>';
                } else {
                    $message = '<div class="alert alert-error">Failed to save record.</div>';
                }
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Domestic Landlord / Homeowner Gas Safety Record</title>
<style>
  /* ---------- PAGE + COLORS ---------- */
  @page { size: A4 landscape; margin: 8mm; }
  html, body { height: 100%; }
  body {
    margin: 0;
    font-family: Arial, Helvetica, sans-serif;
    color:#000;
    background:#fff;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
  }
  :root{
    --blue:#2e5aa6;        /* section bars & due banner */
    --line:#2e5aa6;        /* outer grid */
    --cell:#ffffff;        /* base cell */
    --head:#fffec7;        /* header cells */
    --pale:#fff6b8;        /* pale highlight */
  }

  /* Alerts */
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

  /* Top action toolbar */
  .toolbar {
    position: sticky; top: 0;
    background: #fffec7;
    border-bottom: 1px solid #bfb981;
    padding: 8px; display:flex; gap:8px; justify-content:flex-end; z-index: 50;
  }
  .btn { appearance:none; border:1px solid #b7c3e8; background:#fff; padding:10px 14px; border-radius:8px; font-weight:700; cursor:pointer; text-decoration: none; display: inline-block; }
  .btn.primary { background:#2e5aa6; color:#fff; border-color:#2e5aa6; }
  .btn.success { background:#28a745; color:#fff; border-color:#28a745; }
  .btn.info { background:#17a2b8; color:#fff; border-color:#17a2b8; }

  .sheet { width: calc(297mm - 16mm); height: auto; margin: 8mm auto; box-sizing: border-box; }

  .titleband { display:grid; grid-template-columns: 1fr auto; align-items:end; gap:10px; margin-bottom:6px; padding-bottom:3px; }
  h1 { font-size:22px; margin:0; }

  /* Blocks & tables */
  .block { border:2px solid var(--line); border-radius:3px; overflow:hidden; margin-top:6px; }
  select { background:#fff !important; }
  .bar {
    background:var(--blue); color:#fff; font-weight:700; font-size:13px; padding:4px 6px;
    display:flex; align-items:center; justify-content:center; position:relative; min-height:28px;
  }
  .bar .title { pointer-events:none; }
  .bar .actions { position:absolute; right:6px; display:flex; gap:6px; }

  table { width:100%; border-collapse:collapse; table-layout:fixed; font-size:12px; }
  th, td { border:1px solid var(--line); padding:2px 4px; background:var(--cell); vertical-align:middle; }
  th { background:var(--head); text-align:left; font-weight:700; }
  .ylw { background: var(--pale) !important; }
  .nowrap th, .nowrap td { white-space:nowrap; }

  .hdr { margin-top:6px; }
  .row3 { display:grid; grid-template-columns:1fr 1fr 1fr; gap:6px; }
  .row2 { display:grid; grid-template-columns:1fr 1fr; gap:6px; }
  .band-2-1 { display:grid; grid-template-columns: 2fr 1fr; gap:6px; }  /* 2:1 split */
  .row3-mini { display:grid; grid-template-columns: 1fr 1fr 1fr; gap:6px; }

  .sigbox { height: 38px; }
  .note { font-size:8px; margin-top:3px; }

  /* Compact variants */
  .tight td, .tight th { padding:3px 4px; }
  .tiny td, .tiny th { padding:2px 3px; font-size:10.5px; }
  .wrap2 th{ white-space:normal; line-height:1.2; }

  /* Due banner */
  .due {
    background: var(--blue);
    color:#fff;
    border:2px solid var(--line);
    border-radius:3px;
    padding:8px 6px;
    display:flex; flex-direction:column; align-items:center; justify-content:center; gap:6px;
    min-height:74px; text-align:center;
  }
  .due .big { font-size:18px; font-weight:800; }
  .due .date-field{
    border-bottom:1px solid rgba(255,255,255,.85);
    color:#fff; -webkit-text-fill-color:#fff;
  }
  .due input[type="date"]::-webkit-datetime-edit,
  .due input[type="date"]::-webkit-datetime-edit-fields-wrapper,
  .due input[type="date"]::-webkit-datetime-edit-text,
  .due input[type="date"]::-webkit-datetime-edit-month-field,
  .due input[type="date"]::-webkit-datetime-edit-day-field,
  .due input[type="date"]::-webkit-datetime-edit-year-field { color:#fff; }
  .due input[type="date"]::-webkit-calendar-picker-indicator { filter: invert(1) brightness(2); }

  /* Mini blocks */
  .mini-block .bar { font-size:11px; min-height:22px; padding:3px 4px; }
  .mini-block table th, .mini-block table td { padding:2px 3px; font-size:10.5px; }
  .due.mini { min-height:48px; padding:6px 4px; margin-top: 5px; }
  .due.mini .big { font-size:14px; }

  /* NEW: Make Issued/Received mini tables flexible and print-safe (stacked rows) */
  .mini-block.flex table { table-layout: auto; }
  .mini-block.flex th { width: 38%; white-space: nowrap; }
  .mini-block.flex td { width: 62%; }
  .mini-block.flex input[type="text"],
  .mini-block.flex input[type="date"] { font-size: 10.5px; min-width: 0; }

  /* Delete button & narrow actions col */
  .actions-col { width:24px; text-align:center; }
  .del-btn {
    appearance:none; background:transparent; border:0; color:#b70000;
    font-size:16px; line-height:1; cursor:pointer; font-weight:700;
  }
  .del-btn:hover { color:#ff0000; }

  /* PRINT OVERRIDES: reclaim actions column space and keep names/dates readable */
  @media print {
    .toolbar, .add-btn, .del-btn, a.btn, .alert { display:none !important; }
    select::-ms-expand { display: none; }
    select { -webkit-appearance: none; -moz-appearance: none; appearance: none; background: #fff !important; border: none !important; box-shadow: none !important; }
    .sigbox img[alt="Signature"] { max-height:24px !important; }
    .hdr img[alt="Logo"] { top:-45px !important; }
    @page { size: A4 landscape; margin: 6mm; }
    th.actions-col, td.actions-col { display:none !important; }
    /* Hide the <col> for actions to avoid empty space */
    #applianceTable col.col-actions { display:none !important; }

    body { font-size: 9.4px; }
    h1 { font-size: 16.5px; }
    table { font-size: 9.4px; }
    .titleband { margin-bottom:4px; }

    .block { border-width:1.2px; margin-top:4px; }
    .bar { font-size:10.5px; padding:3px 4px; min-height:20px; }

    th, td { padding:2px 3px; }
    .tight td, .tight th { padding:2px 3px; }
    .tiny td, .tiny th { padding:1px 2px; font-size:8.8px; }

    .row3, .row2, .band-2-1, .row3-mini { gap:3px; margin-bottom:6px; }
    .sigbox { height:22px; }
    .due { min-height:48px; }
    .due .big { font-size:12px; }

    .block, table, tr { page-break-inside: avoid; }
    .sheet { margin: 0; width: 297mm; }

    /* Tighter spacing for better single-page fit */
    .block { margin-top:3px; margin-bottom:3px; }
    table { margin-bottom:4px; }
    
    /* Print-specific tuning for the stacked mini blocks */
    .mini-block.flex table { font-size: 8px; }
    .mini-block.flex th, .mini-block.flex td { padding: 1px 2px; }
    .mini-block.flex input[type="text"],
    .mini-block.flex input[type="date"] { font-size: 8px; }
    
    /* Optimize input sizing */
    input[type="text"], input[type="date"], textarea, select { 
      font-size:8.8px !important; 
      line-height:1.1 !important; 
    }
    
    /* Compact note section */
    .note { font-size:7px; line-height:1.2; margin-top:6px; }
  }

  input[type="text"], input[type="date"], textarea {
    width:100%; border:0; background:transparent; font:inherit; color:#000; -webkit-text-fill-color:#000;
  }
  .date-field{ border-bottom:1px solid var(--line); padding-bottom:2px; min-height:18px; }
  textarea { resize:vertical; }
  .add-btn { appearance:none; border:1px solid #b7c3e8; background:#fff; padding:6px 10px; border-radius:6px; font-weight:700; cursor:pointer; }
</style>
</head>
<body>

<div class="toolbar" role="toolbar" aria-label="Actions">
  <button class="btn" type="button" onclick="document.querySelector('form').reset(); renumberAll();">Reset</button>
  <button class="btn success" type="button" onclick="document.querySelector('form').submit();">Save Record</button>
  <a href="manage_data.php" class="btn info">View Saved Records</a>
  <button class="btn primary" type="button" onclick="window.print()">Print / Save PDF</button>
</div>

<div class="sheet">
  <?php if ($message): ?>
    <?= $message ?>
  <?php endif; ?>
  
  <div style="display:flex; gap:16px; margin-bottom:24px; justify-content:center;">
    <a href="index.php" class="btn primary">Home</a>
    <a href="invoice.php" class="btn">Invoice</a>
    <a href="service_checklist.php" class="btn">Service &amp; Maintenance Checklist</a>
  </div>

  <form method="POST" action="">
    <input type="hidden" name="action" value="save">
    <?php if ($editMode): ?>
      <input type="hidden" name="edit_filename" value="<?= htmlspecialchars($editFilename) ?>">
      <div class="alert alert-info">Editing existing record: <?= htmlspecialchars($editFilename) ?></div>
    <?php endif; ?>
    
    <div class="titleband">
      <h1>Domestic Landlord / Homeowner Gas Safety Record</h1>
    </div>

    <!-- Header strip -->
    <table class="hdr">
      <colgroup>
        <col style="width:12%"><col style="width:23%"><col style="width:12%"><col style="width:23%"><col style="width:12%"><col style="width:18%">
      </colgroup>
      <tr>
        <th>Date:</th>
        <td><input class="date-field" type="date" name="inspection_date" value="<?= htmlspecialchars($formData['inspection_date'] ?? date('Y-m-d')) ?>"></td>
        <th>Ref:</th>
        <td><input type="text" name="reference" value="<?= htmlspecialchars($formData['reference'] ?? '') ?>"></td>
        <td colspan="2" rowspan="2" style="text-align:right; vertical-align:middle; background:transparent; border:none; padding:0; position:relative; height:1px; min-width:120px;">
          <div style="position:relative; height:1px; min-width:120px;">
            <img src="logo.png" alt="Logo" style="position:absolute; right:0; top:-45px; max-height:70px; max-width:200px; z-index:2;">
          </div>
        </td>
      </tr>
      <tr>
        <th>Gas Safe Reg No:</th>
        <td><input type="text" name="gas_safe_reg_no" value="<?= htmlspecialchars($formData['gas_safe_reg_no'] ?? '927879') ?>"></td>
        <th>Serial no:</th>
        <td><input type="text" name="serial_no" value="<?= htmlspecialchars($formData['serial_no'] ?? 'GAUK00764057') ?>"></td>
      </tr>
    </table>

    <!-- Top three blocks -->
    <div class="row3">
      <div class="block">
        <div class="bar"><span class="title">Details of Registered Business</span></div>
        <table>
          <colgroup><col style="width:28%"><col></colgroup>
          <tr><th>Name:</th><td><input type="text" name="business_name" value="<?= htmlspecialchars($formData['business_name'] ?? 'Orient Gas Engineers LTD') ?>"></td></tr>
          <tr><th>Address:</th><td><input type="text" name="business_address1" value="<?= htmlspecialchars($formData['business_address1'] ?? '45 Chalk Pit Avenue') ?>"></td></tr>
          <tr><th></th><td><input type="text" name="business_address2" value="<?= htmlspecialchars($formData['business_address2'] ?? 'Orpington') ?>"></td></tr>
          <tr><th></th><td><input type="text" name="business_address3" value="<?= htmlspecialchars($formData['business_address3'] ?? 'Kent') ?>"></td></tr>
          <tr><th>Postcode:</th><td><input type="text" name="business_postcode" value="<?= htmlspecialchars($formData['business_postcode'] ?? 'BR5 3JJ') ?>"></td></tr>
          <tr><th>Contact Number:</th><td><input type="text" name="business_contact" value="<?= htmlspecialchars($formData['business_contact'] ?? '+44 7795 999196') ?>"></td></tr>
        </table>
      </div>

      <div class="block">
        <div class="bar"><span class="title">Details of Landlord / Homeowner (or agent where appropriate)</span></div>
        <table>
          <colgroup><col style="width:28%"><col></colgroup>
          <tr><th>Name:</th><td><input type="text" name="landlord_name" value="<?= htmlspecialchars($formData['landlord_name'] ?? '') ?>"></td></tr>
          <tr><th>Address:</th><td><input type="text" name="landlord_address1" value="<?= htmlspecialchars($formData['landlord_address1'] ?? '') ?>"></td></tr>
          <tr><th></th><td><input type="text" name="landlord_address2" value="<?= htmlspecialchars($formData['landlord_address2'] ?? '') ?>"></td></tr>
          <tr><th></th><td><input type="text" name="landlord_address3" value="<?= htmlspecialchars($formData['landlord_address3'] ?? '') ?>"></td></tr>
          <tr><th></th><td><input type="text" name="landlord_address4" value="<?= htmlspecialchars($formData['landlord_address4'] ?? '') ?>"></td></tr>
          <tr><th>Postcode:</th><td><input type="text" name="landlord_postcode" value="<?= htmlspecialchars($formData['landlord_postcode'] ?? '') ?>"></td></tr>
          <tr><th>Contact Number:</th><td><input type="text" name="landlord_contact" value="<?= htmlspecialchars($formData['landlord_contact'] ?? '') ?>"></td></tr>
        </table>
      </div>

      <div class="block">
        <div class="bar"><span class="title">Details of Site</span></div>
        <table>
          <colgroup><col style="width:28%"><col></colgroup>
          <tr><th>Name:</th><td><input type="text" name="site_name" value="<?= htmlspecialchars($formData['site_name'] ?? '') ?>"></td></tr>
          <tr><th>Address:</th><td><input type="text" name="site_address1" value="<?= htmlspecialchars($formData['site_address1'] ?? '') ?>"></td></tr>
          <tr><th></th><td><input type="text" name="site_address2" value="<?= htmlspecialchars($formData['site_address2'] ?? '') ?>"></td></tr>
          <tr><th></th><td><input type="text" name="site_address3" value="<?= htmlspecialchars($formData['site_address3'] ?? '') ?>"></td></tr>
          <tr><th></th><td><input type="text" name="site_address4" value="<?= htmlspecialchars($formData['site_address4'] ?? '') ?>"></td></tr>
          <tr><th>Postcode:</th><td><input type="text" name="site_postcode" value="<?= htmlspecialchars($formData['site_postcode'] ?? '') ?>"></td></tr>
          <tr><th>Contact Number:</th><td><input type="text" name="site_contact" value="<?= htmlspecialchars($formData['site_contact'] ?? '') ?>"></td></tr>
        </table>
      </div>
    </div>

    <!-- Appliance Details and Final Check Results -->
    <div class="band-2-1">
      <div class="block">
        <div class="bar">
          <span class="title">Appliance Details</span>
          <div class="actions">
            <button type="button" class="add-btn" id="btnAddAppliance">+ Add appliance</button>
          </div>
        </div>

        <table id="applianceTable" class="tight">
          <colgroup>
            <col style="width:4%"><!-- # -->
            <col style="width:14%"><col style="width:14%"><col style="width:16%"><col style="width:16%">
            <col style="width:14%"><col style="width:14%"><col style="width:14%">
            <col class="col-actions" style="width:24px"><!-- actions -->
          </colgroup>
          <thead>
            <tr>
              <th>#</th><th>Location</th><th>Type</th><th>Manufacturer</th><th>Model</th>
              <th>Owned by Landlord</th><th>Appliance Inspected?</th><th>Flue Type</th>
              <th class="actions-col">×</th>
            </tr>
          </thead>
          <tbody>
            <!-- Will be populated by JavaScript -->
          </tbody>
        </table>

        <template id="applianceRowTemplate">
          <tr>
            <td class="row-idx"></td>
            <td><input type="text" name="appliance_location[]"></td>
            <td><input type="text" name="appliance_type[]"></td>
            <td><input type="text" name="appliance_manufacturer[]"></td>
            <td><input type="text" name="appliance_model[]"></td>
            <td><input type="text" name="appliance_owned_by_landlord[]"></td>
            <td><input type="text" name="appliance_inspected[]"></td>
            <td>
              <select name="appliance_flue_type[]">
                <option value="Flueless">Flueless</option>
                <option value="Open Flue">Open Flue</option>
              </select>
            </td>
            <td class="actions-col"><button type="button" class="del-btn" aria-label="Delete">×</button></td>
          </tr>
        </template>
      </div>

      <!-- Final check results (moved to right side) -->
      <div class="block">
        <div class="bar"><span class="title">Final check results</span></div>
        <table class="tight">
          <colgroup><col style="width:64%"><col style="width:36%"></colgroup>
          <tr><th>Outcome of gas tightness test</th><td><input type="text" name="gas_tightness_test" value="<?= htmlspecialchars($formData['gas_tightness_test'] ?? 'Pass') ?>"></td></tr>
          <tr><th>Is the main protective equipotential bonding satisfactory?</th><td><input type="text" name="equipotential_bonding" value="<?= htmlspecialchars($formData['equipotential_bonding'] ?? 'Yes') ?>"></td></tr>
          <tr><th>Is the emergency control accessible?</th><td><input type="text" name="emergency_control" value="<?= htmlspecialchars($formData['emergency_control'] ?? 'Yes') ?>"></td></tr>
          <tr><th>Satisfactory visual inspection of gas installation pipework?</th><td><input type="text" name="gas_installation_pipework" value="<?= htmlspecialchars($formData['gas_installation_pipework'] ?? 'Yes') ?>"></td></tr>
          <tr><th>CO alarm fitted and working?</th><td><input type="text" name="co_alarm" value="<?= htmlspecialchars($formData['co_alarm'] ?? 'Yes') ?>"></td></tr>
          <tr><th>Smoke/fire alarm fitted and working?</th><td><input type="text" name="smoke_alarm" value="<?= htmlspecialchars($formData['smoke_alarm'] ?? 'N/A') ?>"></td></tr>
          <tr><th>Notes</th><td><input type="text" name="notes" value="<?= htmlspecialchars($formData['notes'] ?? '') ?>" placeholder="Enter notes"></td></tr>
        </table>
      </div>
    </div>

    <!-- Inspection Details (FULL WIDTH) -->
    <div class="block">
      <div class="bar">
        <span class="title">Inspection Details</span>
        <div class="actions">
          <button type="button" class="add-btn" id="btnAddInspection">+ Add inspection</button>
        </div>
      </div>
      <table id="inspectionTable" class="tight tiny wrap2">
        <colgroup>
          <col style="width:4%"><!-- # -->
          <col style="width:16%">
          <col style="width:11%">
          <col style="width:11%">
          <col style="width:13%">
          <col style="width:11%">
          <col style="width:13%">
          <col style="width:8%">
          <col style="width:10%">
          <col style="width:9%">
          <col style="width:5%"><!-- actions -->
        </colgroup>
        <thead>
          <tr>
            <th>#</th>
            <th>Operating Pressure (mbars) or heat input (kW/h)</th>
            <th>Safety devices<br>operating correctly?</th>
            <th>Satisfactory<br>Ventilation?</th>
            <th>Visual condition of<br>flue & termination</th>
            <th>Flue operation<br>checks.</th>
            <th>Combustion analyser<br>reading.</th>
            <th>Was appliance<br>serviced?</th>
            <th>Is appliance safe<br>to use?</th>
            <th>Visual inspection<br>only?</th>
            <th class="actions-col">×</th>
          </tr>
        </thead>
        <tbody>
          <!-- Will be populated by JavaScript -->
        </tbody>
      </table>
      <template id="inspectionRowTemplate">
        <tr>
          <td class="row-idx"></td>
          <td><input type="text" name="inspection_operating_pressure[]"></td>
          <td><input type="text" name="inspection_safety_devices[]"></td>
          <td><input type="text" name="inspection_ventilation[]"></td>
          <td><input type="text" name="inspection_flue_condition[]"></td>
          <td><input type="text" name="inspection_flue_operation[]"></td>
          <td><input type="text" name="inspection_combustion_reading[]"></td>
          <td><input type="text" name="inspection_appliance_serviced[]"></td>
          <td><input type="text" name="inspection_appliance_safe[]"></td>
          <td><input type="text" name="inspection_visual_only[]"></td>
          <td class="actions-col"><button type="button" class="del-btn" aria-label="Delete">×</button></td>
        </tr>
      </template>
    </div>

    <!-- Combined Table -->
    <div class="block">
      <div class="bar">
        <span class="title">Defects / Remedial / Label &amp; Warning / Combustion Performance</span>
        <div class="actions">
          <button type="button" class="add-btn" id="btnAddCombined">+ Add row</button>
        </div>
      </div>
      <table class="tight wrap2" id="combinedTable">
        <colgroup>
          <col style="width:5%"><!-- # -->
          <col style="width:19%"><!-- Defects -->
          <col style="width:19%"><!-- Remedial -->
          <col style="width:17%"><!-- Label & Warning -->
          <col style="width:10%"><!-- Low CO -->
          <col style="width:10%"><!-- Low CO2 -->
          <col style="width:10%"><!-- High CO -->
          <col style="width:10%"><!-- High CO2 -->
          <col style="width:5%"><!-- actions -->
        </colgroup>
        <thead>
          <tr>
            <th>#</th>
            <th>Defects Identified</th>
            <th>Remedial Work Details</th>
            <th>Label &amp; Warning Notice</th>
            <th colspan="2">Low</th>
            <th colspan="2">High</th>
            <th class="actions-col">×</th>
          </tr>
          <tr>
            <th></th><th></th><th></th><th></th>
            <th>CO</th><th>CO₂&nbsp;Ratio</th>
            <th>CO</th><th>CO₂&nbsp;Ratio</th>
            <th class="actions-col"></th>
          </tr>
        </thead>
        <tbody>
          <!-- Will be populated by JavaScript -->
        </tbody>
      </table>

      <template id="combinedRowTemplate">
        <tr>
          <td class="row-idx"></td>
          <td><input type="text" name="combined_defects[]"></td>
          <td><input type="text" name="combined_remedial[]"></td>
          <td><input type="text" name="combined_label_warning[]"></td>
          <td><input type="text" name="combined_low_co[]"></td>
          <td><input type="text" name="combined_low_co2[]"></td>
          <td><input type="text" name="combined_high_co[]"></td>
          <td><input type="text" name="combined_high_co2[]"></td>
          <td class="actions-col"><button type="button" class="del-btn" aria-label="Delete">×</button></td>
        </tr>
      </template>
    </div>



    <!-- Next Inspection + Signatures -->
    <div class="row3-mini">
      <div class="due mini">
        <div><strong>Next Inspection Is Due Before:</strong></div>
        <div class="big">
          <input class="date-field" type="date" name="next_inspection_due" value="<?= htmlspecialchars($formData['next_inspection_due'] ?? date('Y-m-d', strtotime('+1 year'))) ?>" style="text-align:center;border:0;background:transparent;font:inherit">
        </div>
      </div>

      <div class="block mini-block flex">
        <div class="bar"><span class="title">Record Issued By</span></div>
        <table class="tight">
          <colgroup><col><col></colgroup>
          <tr>
            <th>Signature</th>
            <td><div class="sigbox"><img src="signature.png" alt="Signature" style="max-height:28px; max-width:120px;"></div></td>
          </tr>
          <tr>
            <th>Gas Engineer</th>
            <td><input type="text" name="issued_by_engineer" value="<?= htmlspecialchars($formData['issued_by_engineer'] ?? 'AKM ZAHURUL Islam') ?>"></td>
          </tr>
          <tr>
            <th>Gas Safe Licence</th>
            <td><input type="text" name="issued_by_licence" value="<?= htmlspecialchars($formData['issued_by_licence'] ?? '5388937') ?>"></td>
          </tr>
          <tr>
            <th>Date</th>
            <td><input class="date-field" type="date" name="issued_date" value="<?= htmlspecialchars($formData['issued_date'] ?? date('Y-m-d')) ?>"></td>
          </tr>
        </table>
      </div>

      <div class="block mini-block flex">
        <div class="bar"><span class="title">Received By</span></div>
        <table class="tight">
          <colgroup><col><col></colgroup>
          <tr>
            <th>Signature</th>
            <td><div class="sigbox"></div></td>
          </tr>
          <tr>
            <th>Name</th>
            <td><input type="text" name="received_by_name" value="<?= htmlspecialchars($formData['received_by_name'] ?? '') ?>"></td>
          </tr>
          <tr>
            <th>Position</th>
            <td><input type="text" name="received_by_position" value="<?= htmlspecialchars($formData['received_by_position'] ?? '') ?>"></td>
          </tr>
          <tr>
            <th>Date</th>
            <td><input class="date-field" type="date" name="received_date" value="<?= htmlspecialchars($formData['received_date'] ?? '') ?>"></td>
          </tr>
        </table>
      </div>
    </div>

    <div class="note">
      This record can be used to document the outcome of checks and tests required by The Gas Safety (Installation and Use) Regulations 1998 as amended by the Gas Safety (Installation and Use) (Amendment) Regulations 2018. Some of the outcomes are as a result of visual inspection only and are recorded where appropriate. Unless specifically recorded no detailed inspection of the flue lining, construction or integrity has been performed. Registered Business / engineer details can be checked at www.gassaferegister.co.uk or by calling 0800 408 5500.
    </div>
  </form>
</div>

<script>
  // Complete JavaScript for dynamic table management
  const btnAddAppliance = document.getElementById('btnAddAppliance');
  const btnAddInspection = document.getElementById('btnAddInspection');
  const btnAddCombined = document.getElementById('btnAddCombined');

  const appTable = document.getElementById('applianceTable');
  const appTbody = appTable?.querySelector('tbody');
  const appTpl = document.getElementById('applianceRowTemplate');

  const insTable = document.getElementById('inspectionTable');
  const insTbody = insTable?.querySelector('tbody');
  const insTpl = document.getElementById('inspectionRowTemplate');

  const combTable = document.getElementById('combinedTable');
  const combTbody = combTable?.querySelector('tbody');
  const combTpl = document.getElementById('combinedRowTemplate');

  function renumberTbody(tbody){
    if (!tbody) return;
    [...tbody.querySelectorAll('tr')].forEach((tr, i)=>{
      const c = tr.querySelector('.row-idx');
      if (c) c.textContent = String(i + 1);
    });
  }
  
  function renumberAll(){
    renumberTbody(appTbody);
    renumberTbody(insTbody);
    renumberTbody(combTbody);
  }

  function addApplianceAndInspection(){
    if (appTpl && appTbody){ appTbody.appendChild(appTpl.content.firstElementChild.cloneNode(true)); }
    if (insTpl && insTbody){ insTbody.appendChild(insTpl.content.firstElementChild.cloneNode(true)); }
    renumberAll();
  }
  
  function addCombinedRow(){
    if (combTpl && combTbody){
      combTbody.appendChild(combTpl.content.firstElementChild.cloneNode(true));
      renumberAll();
    }
  }

  function deleteSyncedRow(sourceTbody, otherTbody, rowEl){
    if (!rowEl || !sourceTbody) return;
    const rows = Array.from(sourceTbody.querySelectorAll('tr'));
    const idx = rows.indexOf(rowEl);
    if (idx === -1) return;
    rowEl.remove();
    if (otherTbody){
      const otherRows = Array.from(otherTbody.querySelectorAll('tr'));
      if (idx >= 0 && idx < otherRows.length){ otherRows[idx].remove(); }
    }
    renumberAll();
  }
  
  function deleteRow(tbody, rowEl){
    if (!rowEl || !tbody) return;
    rowEl.remove();
    renumberAll();
  }

  if (appTbody){
    appTbody.addEventListener('click', (e)=>{
      const b = e.target.closest('.del-btn'); if (!b) return;
      deleteSyncedRow(appTbody, insTbody, b.closest('tr'));
    });
  }
  
  if (insTbody){
    insTbody.addEventListener('click', (e)=>{
      const b = e.target.closest('.del-btn'); if (!b) return;
      deleteSyncedRow(insTbody, appTbody, b.closest('tr'));
    });
  }
  
  if (combTbody){
    combTbody.addEventListener('click', (e)=>{
      const b = e.target.closest('.del-btn'); if (!b) return;
      deleteRow(combTbody, b.closest('tr'));
    });
  }

  if (btnAddAppliance){ btnAddAppliance.addEventListener('click', addApplianceAndInspection); }
  if (btnAddCombined){ btnAddCombined.addEventListener('click', addCombinedRow); }

  // Load existing data if in edit mode
  <?php if ($editMode && !empty($formData)): ?>
    // Load appliance data
    <?php if (isset($formData['appliance_location']) && is_array($formData['appliance_location'])): ?>
      <?php for ($i = 0; $i < count($formData['appliance_location']); $i++): ?>
        addApplianceAndInspection();
        const appRow<?= $i ?> = appTbody.children[<?= $i ?>];
        const insRow<?= $i ?> = insTbody.children[<?= $i ?>];
        
        // Populate appliance data
        appRow<?= $i ?>.querySelector('[name="appliance_location[]"]').value = <?= json_encode($formData['appliance_location'][$i] ?? '') ?>;
        appRow<?= $i ?>.querySelector('[name="appliance_type[]"]').value = <?= json_encode($formData['appliance_type'][$i] ?? '') ?>;
        appRow<?= $i ?>.querySelector('[name="appliance_manufacturer[]"]').value = <?= json_encode($formData['appliance_manufacturer'][$i] ?? '') ?>;
        appRow<?= $i ?>.querySelector('[name="appliance_model[]"]').value = <?= json_encode($formData['appliance_model'][$i] ?? '') ?>;
        appRow<?= $i ?>.querySelector('[name="appliance_owned_by_landlord[]"]').value = <?= json_encode($formData['appliance_owned_by_landlord'][$i] ?? '') ?>;
        appRow<?= $i ?>.querySelector('[name="appliance_inspected[]"]').value = <?= json_encode($formData['appliance_inspected'][$i] ?? '') ?>;
        appRow<?= $i ?>.querySelector('[name="appliance_flue_type[]"]').value = <?= json_encode($formData['appliance_flue_type'][$i] ?? 'Flueless') ?>;
        
        // Populate inspection data
        if (insRow<?= $i ?>) {
          insRow<?= $i ?>.querySelector('[name="inspection_operating_pressure[]"]').value = <?= json_encode($formData['inspection_operating_pressure'][$i] ?? '') ?>;
          insRow<?= $i ?>.querySelector('[name="inspection_safety_devices[]"]').value = <?= json_encode($formData['inspection_safety_devices'][$i] ?? '') ?>;
          insRow<?= $i ?>.querySelector('[name="inspection_ventilation[]"]').value = <?= json_encode($formData['inspection_ventilation'][$i] ?? '') ?>;
          insRow<?= $i ?>.querySelector('[name="inspection_flue_condition[]"]').value = <?= json_encode($formData['inspection_flue_condition'][$i] ?? '') ?>;
          insRow<?= $i ?>.querySelector('[name="inspection_flue_operation[]"]').value = <?= json_encode($formData['inspection_flue_operation'][$i] ?? '') ?>;
          insRow<?= $i ?>.querySelector('[name="inspection_combustion_reading[]"]').value = <?= json_encode($formData['inspection_combustion_reading'][$i] ?? '') ?>;
          insRow<?= $i ?>.querySelector('[name="inspection_appliance_serviced[]"]').value = <?= json_encode($formData['inspection_appliance_serviced'][$i] ?? '') ?>;
          insRow<?= $i ?>.querySelector('[name="inspection_appliance_safe[]"]').value = <?= json_encode($formData['inspection_appliance_safe'][$i] ?? '') ?>;
          insRow<?= $i ?>.querySelector('[name="inspection_visual_only[]"]').value = <?= json_encode($formData['inspection_visual_only'][$i] ?? '') ?>;
        }
      <?php endfor; ?>
    <?php endif; ?>
    
    // Load combined table data
    <?php if (isset($formData['combined_defects']) && is_array($formData['combined_defects'])): ?>
      <?php for ($i = 0; $i < count($formData['combined_defects']); $i++): ?>
        addCombinedRow();
        const combRow<?= $i ?> = combTbody.children[<?= $i ?>];
        combRow<?= $i ?>.querySelector('[name="combined_defects[]"]').value = <?= json_encode($formData['combined_defects'][$i] ?? '') ?>;
        combRow<?= $i ?>.querySelector('[name="combined_remedial[]"]').value = <?= json_encode($formData['combined_remedial'][$i] ?? '') ?>;
        combRow<?= $i ?>.querySelector('[name="combined_label_warning[]"]').value = <?= json_encode($formData['combined_label_warning'][$i] ?? '') ?>;
        combRow<?= $i ?>.querySelector('[name="combined_low_co[]"]').value = <?= json_encode($formData['combined_low_co'][$i] ?? '') ?>;
        combRow<?= $i ?>.querySelector('[name="combined_low_co2[]"]').value = <?= json_encode($formData['combined_low_co2'][$i] ?? '') ?>;
        combRow<?= $i ?>.querySelector('[name="combined_high_co[]"]').value = <?= json_encode($formData['combined_high_co'][$i] ?? '') ?>;
        combRow<?= $i ?>.querySelector('[name="combined_high_co2[]"]').value = <?= json_encode($formData['combined_high_co2'][$i] ?? '') ?>;
      <?php endfor; ?>
    <?php endif; ?>
  <?php else: ?>
    // Initialize with default rows for new forms
    addApplianceAndInspection();
    addCombinedRow();
    addCombinedRow();
  <?php endif; ?>
  
  renumberAll();
</script>
</body>
</html>