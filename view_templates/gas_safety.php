<?php
// Gas Safety Record View Template
$data = $record['data'];
?>

<div class="data-section">
  <h2 class="section-title">Gas Safety Record Details</h2>
  
  <div class="data-grid">
    <!-- Header Information -->
    <div class="data-group">
      <h4>Record Information</h4>
      <div class="data-item">
        <div class="data-label">Inspection Date</div>
        <div class="data-value"><?= htmlspecialchars($data['inspection_date'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Reference</div>
        <div class="data-value"><?= htmlspecialchars($data['reference'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Gas Safe Reg No</div>
        <div class="data-value"><?= htmlspecialchars($data['gas_safe_reg_no'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Serial No</div>
        <div class="data-value"><?= htmlspecialchars($data['serial_no'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Business Details -->
    <div class="data-group">
      <h4>Registered Business</h4>
      <div class="data-item">
        <div class="data-label">Name</div>
        <div class="data-value"><?= htmlspecialchars($data['business_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Address</div>
        <div class="data-value">
          <?php
          $address = array_filter([
            $data['business_address1'] ?? '',
            $data['business_address2'] ?? '',
            $data['business_address3'] ?? ''
          ]);
          echo $address ? htmlspecialchars(implode(', ', $address)) : '<span class="empty-value">Not specified</span>';
          ?>
        </div>
      </div>
      <div class="data-item">
        <div class="data-label">Postcode</div>
        <div class="data-value"><?= htmlspecialchars($data['business_postcode'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Contact</div>
        <div class="data-value"><?= htmlspecialchars($data['business_contact'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Landlord Details -->
    <div class="data-group">
      <h4>Landlord / Homeowner</h4>
      <div class="data-item">
        <div class="data-label">Name</div>
        <div class="data-value"><?= htmlspecialchars($data['landlord_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Address</div>
        <div class="data-value">
          <?php
          $address = array_filter([
            $data['landlord_address1'] ?? '',
            $data['landlord_address2'] ?? '',
            $data['landlord_address3'] ?? '',
            $data['landlord_address4'] ?? ''
          ]);
          echo $address ? htmlspecialchars(implode(', ', $address)) : '<span class="empty-value">Not specified</span>';
          ?>
        </div>
      </div>
      <div class="data-item">
        <div class="data-label">Postcode</div>
        <div class="data-value"><?= htmlspecialchars($data['landlord_postcode'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Contact</div>
        <div class="data-value"><?= htmlspecialchars($data['landlord_contact'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Site Details -->
    <div class="data-group">
      <h4>Site Details</h4>
      <div class="data-item">
        <div class="data-label">Name</div>
        <div class="data-value"><?= htmlspecialchars($data['site_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Address</div>
        <div class="data-value">
          <?php
          $address = array_filter([
            $data['site_address1'] ?? '',
            $data['site_address2'] ?? '',
            $data['site_address3'] ?? '',
            $data['site_address4'] ?? ''
          ]);
          echo $address ? htmlspecialchars(implode(', ', $address)) : '<span class="empty-value">Not specified</span>';
          ?>
        </div>
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

    <!-- Final Check Results -->
    <div class="data-group">
      <h4>Final Check Results</h4>
      <div class="data-item">
        <div class="data-label">Gas Tightness Test</div>
        <div class="data-value"><?= htmlspecialchars($data['gas_tightness_test'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Equipotential Bonding</div>
        <div class="data-value"><?= htmlspecialchars($data['equipotential_bonding'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Emergency Control</div>
        <div class="data-value"><?= htmlspecialchars($data['emergency_control'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Gas Installation Pipework</div>
        <div class="data-value"><?= htmlspecialchars($data['gas_installation_pipework'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">CO Alarm</div>
        <div class="data-value"><?= htmlspecialchars($data['co_alarm'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Smoke Alarm</div>
        <div class="data-value"><?= htmlspecialchars($data['smoke_alarm'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Notes</div>
        <div class="data-value"><?= htmlspecialchars($data['notes'] ?? '') ?: '<span class="empty-value">No notes</span>' ?></div>
      </div>
    </div>

    <!-- Signatures -->
    <div class="data-group">
      <h4>Signatures & Dates</h4>
      <div class="data-item">
        <div class="data-label">Next Inspection Due</div>
        <div class="data-value"><?= htmlspecialchars($data['next_inspection_due'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Issued By (Engineer)</div>
        <div class="data-value"><?= htmlspecialchars($data['issued_by_engineer'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Gas Safe Licence</div>
        <div class="data-value"><?= htmlspecialchars($data['issued_by_licence'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Issued Date</div>
        <div class="data-value"><?= htmlspecialchars($data['issued_date'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Received By</div>
        <div class="data-value"><?= htmlspecialchars($data['received_by_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Received Date</div>
        <div class="data-value"><?= htmlspecialchars($data['received_date'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>
  </div>

  <!-- Appliance Details -->
  <?php if (isset($data['appliance_location']) && is_array($data['appliance_location'])): ?>
    <div class="data-section">
      <h3 class="section-title">Appliance Details</h3>
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
          <thead>
            <tr style="background: #f8f9fa;">
              <th style="padding: 12px; text-align: left; border: 1px solid #e9ecef;">Location</th>
              <th style="padding: 12px; text-align: left; border: 1px solid #e9ecef;">Type</th>
              <th style="padding: 12px; text-align: left; border: 1px solid #e9ecef;">Manufacturer</th>
              <th style="padding: 12px; text-align: left; border: 1px solid #e9ecef;">Model</th>
              <th style="padding: 12px; text-align: center; border: 1px solid #e9ecef;">Owned by Landlord</th>
              <th style="padding: 12px; text-align: center; border: 1px solid #e9ecef;">Inspected?</th>
              <th style="padding: 12px; text-align: center; border: 1px solid #e9ecef;">Flue Type</th>
            </tr>
          </thead>
          <tbody>
            <?php for ($i = 0; $i < count($data['appliance_location']); $i++): ?>
            <tr>
              <td style="padding: 12px; border: 1px solid #e9ecef;">
                <?= htmlspecialchars($data['appliance_location'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef;">
                <?= htmlspecialchars($data['appliance_type'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef;">
                <?= htmlspecialchars($data['appliance_manufacturer'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef;">
                <?= htmlspecialchars($data['appliance_model'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['appliance_owned_by_landlord'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['appliance_inspected'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['appliance_flue_type'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <!-- Inspection Details -->
  <?php if (isset($data['inspection_operating_pressure']) && is_array($data['inspection_operating_pressure'])): ?>
    <div class="data-section">
      <h3 class="section-title">Inspection Details</h3>
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px;">
          <thead>
            <tr style="background: #f8f9fa;">
              <th style="padding: 8px; text-align: left; border: 1px solid #e9ecef;">Operating Pressure</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">Safety Devices</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">Ventilation</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">Flue Condition</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">Flue Operation</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">Combustion</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">Serviced?</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">Safe to Use?</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">Visual Only?</th>
            </tr>
          </thead>
          <tbody>
            <?php for ($i = 0; $i < count($data['inspection_operating_pressure']); $i++): ?>
            <tr>
              <td style="padding: 8px; border: 1px solid #e9ecef;">
                <?= htmlspecialchars($data['inspection_operating_pressure'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['inspection_safety_devices'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['inspection_ventilation'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['inspection_flue_condition'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['inspection_flue_operation'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['inspection_combustion_reading'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['inspection_appliance_serviced'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['inspection_appliance_safe'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['inspection_visual_only'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>

  <!-- Combined Table Data -->
  <?php if (isset($data['combined_defects']) && is_array($data['combined_defects'])): ?>
    <div class="data-section">
      <h3 class="section-title">Defects / Remedial / Label & Warning / Combustion Performance</h3>
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px;">
          <thead>
            <tr style="background: #f8f9fa;">
              <th style="padding: 8px; text-align: left; border: 1px solid #e9ecef;">Defects Identified</th>
              <th style="padding: 8px; text-align: left; border: 1px solid #e9ecef;">Remedial Work</th>
              <th style="padding: 8px; text-align: left; border: 1px solid #e9ecef;">Label & Warning</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">Low CO</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">Low CO₂</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">High CO</th>
              <th style="padding: 8px; text-align: center; border: 1px solid #e9ecef;">High CO₂</th>
            </tr>
          </thead>
          <tbody>
            <?php for ($i = 0; $i < count($data['combined_defects']); $i++): ?>
            <tr>
              <td style="padding: 8px; border: 1px solid #e9ecef;">
                <?= htmlspecialchars($data['combined_defects'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef;">
                <?= htmlspecialchars($data['combined_remedial'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef;">
                <?= htmlspecialchars($data['combined_label_warning'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['combined_low_co'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['combined_low_co2'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['combined_high_co'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
              <td style="padding: 8px; border: 1px solid #e9ecef; text-align: center;">
                <?= htmlspecialchars($data['combined_high_co2'][$i] ?? '') ?: '<span class="empty-value">-</span>' ?>
              </td>
            </tr>
            <?php endfor; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>