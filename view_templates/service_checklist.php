<?php
// Service Checklist View Template
$data = $record['data'];
?>

<div class="data-section">
  <h2 class="section-title">Service & Maintenance Checklist</h2>
  
  <div class="data-grid">
    <!-- Company Information -->
    <div class="data-group">
      <h4>Business Information</h4>
      <div class="data-item">
        <div class="data-label">Company</div>
        <div class="data-value"><?= htmlspecialchars($data['company_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Gas Safe Reg No</div>
        <div class="data-value"><?= htmlspecialchars($data['gas_safe_reg_no'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Address</div>
        <div class="data-value"><?= htmlspecialchars($data['company_address'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Postcode</div>
        <div class="data-value"><?= htmlspecialchars($data['company_postcode'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Telephone</div>
        <div class="data-value"><?= htmlspecialchars($data['company_telephone'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Engineer Information -->
    <div class="data-group">
      <h4>Engineer Information</h4>
      <div class="data-item">
        <div class="data-label">Engineer Name</div>
        <div class="data-value"><?= htmlspecialchars($data['engineer_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Licence No</div>
        <div class="data-value"><?= htmlspecialchars($data['engineer_licence'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Mobile</div>
        <div class="data-value"><?= htmlspecialchars($data['engineer_mobile'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Site Information -->
    <div class="data-group">
      <h4>Site Information</h4>
      <div class="data-item">
        <div class="data-label">Tenant/Homeowner</div>
        <div class="data-value"><?= htmlspecialchars($data['site_tenant_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Address</div>
        <div class="data-value"><?= htmlspecialchars($data['site_address'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Postcode</div>
        <div class="data-value"><?= htmlspecialchars($data['site_postcode'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Contact</div>
        <div class="data-value"><?= htmlspecialchars($data['site_contact'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Client Information -->
    <div class="data-group">
      <h4>Client Information</h4>
      <div class="data-item">
        <div class="data-label">Landlord/Agent</div>
        <div class="data-value"><?= htmlspecialchars($data['client_landlord_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Address</div>
        <div class="data-value"><?= htmlspecialchars($data['client_address'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Postcode</div>
        <div class="data-value"><?= htmlspecialchars($data['client_postcode'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Contact</div>
        <div class="data-value"><?= htmlspecialchars($data['client_contact'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Appliance Information -->
    <div class="data-group">
      <h4>Appliance Information</h4>
      <div class="data-item">
        <div class="data-label">Location</div>
        <div class="data-value"><?= htmlspecialchars($data['appliance_location'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Owned by Landlord</div>
        <div class="data-value"><?= htmlspecialchars($data['appliance_owned_by'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Type</div>
        <div class="data-value"><?= htmlspecialchars($data['appliance_type'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Model</div>
        <div class="data-value"><?= htmlspecialchars($data['appliance_model'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Flue Type</div>
        <div class="data-value"><?= htmlspecialchars($data['appliance_flue_type'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Manufacturer</div>
        <div class="data-value"><?= htmlspecialchars($data['appliance_manufacturer'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Serial No</div>
        <div class="data-value"><?= htmlspecialchars($data['appliance_serial'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Summary -->
    <div class="data-group">
      <h4>Summary</h4>
      <div class="data-item">
        <div class="data-label">Safe to Use (Yes)</div>
        <div class="data-value"><?= htmlspecialchars($data['summary_safe_yes'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Safe to Use (No)</div>
        <div class="data-value"><?= htmlspecialchars($data['summary_safe_no'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">GIUSP Classification</div>
        <div class="data-value"><?= htmlspecialchars($data['summary_giusp'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Warning Notice</div>
        <div class="data-value"><?= htmlspecialchars($data['summary_warning_notice'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Dates and Signatures -->
    <div class="data-group">
      <h4>Dates & Signatures</h4>
      <div class="data-item">
        <div class="data-label">Checks Completed</div>
        <div class="data-value"><?= htmlspecialchars($data['checks_completed_date'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Next Service Due</div>
        <div class="data-value"><?= htmlspecialchars($data['next_service_due'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Engineer Name</div>
        <div class="data-value"><?= htmlspecialchars($data['engineer_signature_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Engineer Licence</div>
        <div class="data-value"><?= htmlspecialchars($data['engineer_signature_licence'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Client Name</div>
        <div class="data-value"><?= htmlspecialchars($data['client_signature_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Client Position</div>
        <div class="data-value"><?= htmlspecialchars($data['client_signature_position'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>
  </div>

  <!-- Installation Checks -->
  <div class="data-section">
    <h3 class="section-title">Installation Checks</h3>
    <div style="overflow-x: auto;">
      <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <thead>
          <tr style="background: #f8f9fa;">
            <th style="padding: 12px; text-align: left; border: 1px solid #e9ecef;">Item</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #e9ecef;">Pass</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #e9ecef;">Fail</th>
            <th style="padding: 12px; text-align: center; border: 1px solid #e9ecef;">N/A</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td style="padding: 12px; border: 1px solid #e9ecef;">Satisfactory Meter/Cylinder</td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_meter_pass'] ?? '') ?: '-' ?></td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_meter_fail'] ?? '') ?: '-' ?></td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_meter_na'] ?? '') ?: '-' ?></td>
          </tr>
          <tr>
            <td style="padding: 12px; border: 1px solid #e9ecef;">Inspection of Visible Pipework</td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_pipework_pass'] ?? '') ?: '-' ?></td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_pipework_fail'] ?? '') ?: '-' ?></td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_pipework_na'] ?? '') ?: '-' ?></td>
          </tr>
          <tr>
            <td style="padding: 12px; border: 1px solid #e9ecef;">ECV Access and Operation</td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_ecv_pass'] ?? '') ?: '-' ?></td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_ecv_fail'] ?? '') ?: '-' ?></td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_ecv_na'] ?? '') ?: '-' ?></td>
          </tr>
          <tr>
            <td style="padding: 12px; border: 1px solid #e9ecef;">Protective Equipotential Bonding</td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_bonding_pass'] ?? '') ?: '-' ?></td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_bonding_fail'] ?? '') ?: '-' ?></td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_bonding_na'] ?? '') ?: '-' ?></td>
          </tr>
          <tr>
            <td style="padding: 12px; border: 1px solid #e9ecef;">Tightness Test</td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_tightness_pass'] ?? '') ?: '-' ?></td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_tightness_fail'] ?? '') ?: '-' ?></td>
            <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;"><?= htmlspecialchars($data['inst_tightness_na'] ?? '') ?: '-' ?></td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>

  <!-- Technical Measurements -->
  <div class="data-section">
    <h3 class="section-title">Technical Measurements</h3>
    <div class="data-grid">
      <div class="data-group">
        <h4>Heat Input / Operating Pressure</h4>
        <div class="data-item">
          <div class="data-label">kW</div>
          <div class="data-value"><?= htmlspecialchars($data['heat_input_kw'] ?? '') ?: '<span class="empty-value">Not recorded</span>' ?></div>
        </div>
        <div class="data-item">
          <div class="data-label">kW/h</div>
          <div class="data-value"><?= htmlspecialchars($data['heat_input_kwh'] ?? '') ?: '<span class="empty-value">Not recorded</span>' ?></div>
        </div>
        <div class="data-item">
          <div class="data-label">mbar</div>
          <div class="data-value"><?= htmlspecialchars($data['operating_pressure_mbar'] ?? '') ?: '<span class="empty-value">Not recorded</span>' ?></div>
        </div>
      </div>

      <div class="data-group">
        <h4>Combustion Analysis</h4>
        <div class="data-item">
          <div class="data-label">CO</div>
          <div class="data-value"><?= htmlspecialchars($data['combustion_co'] ?? '') ?: '<span class="empty-value">Not recorded</span>' ?></div>
        </div>
        <div class="data-item">
          <div class="data-label">CO₂%/Ratio</div>
          <div class="data-value"><?= htmlspecialchars($data['combustion_co2'] ?? '') ?: '<span class="empty-value">Not recorded</span>' ?></div>
        </div>
        <div class="data-item">
          <div class="data-label">O₂%</div>
          <div class="data-value"><?= htmlspecialchars($data['combustion_o2'] ?? '') ?: '<span class="empty-value">Not recorded</span>' ?></div>
        </div>
      </div>
    </div>
  </div>
</div>