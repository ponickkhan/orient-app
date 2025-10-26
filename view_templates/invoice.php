<?php
// Invoice View Template
$data = $record['data'];
?>

<div class="data-section">
  <h2 class="section-title">Invoice Details</h2>
  
  <div class="data-grid">
    <!-- Invoice Information -->
    <div class="data-group">
      <h4>Invoice Information</h4>
      <div class="data-item">
        <div class="data-label">Invoice Number</div>
        <div class="data-value"><?= htmlspecialchars($data['invoice_number'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Invoice Date</div>
        <div class="data-value"><?= htmlspecialchars($data['invoice_date'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Due Date</div>
        <div class="data-value"><?= htmlspecialchars($data['due_date'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Client Information -->
    <div class="data-group">
      <h4>Bill To</h4>
      <div class="data-item">
        <div class="data-label">Client Name</div>
        <div class="data-value"><?= htmlspecialchars($data['client_name'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
      <div class="data-item">
        <div class="data-label">Address</div>
        <div class="data-value">
          <?php
          $address = array_filter([
            $data['client_address1'] ?? '',
            $data['client_address2'] ?? '',
            $data['client_city'] ?? ''
          ]);
          echo $address ? htmlspecialchars(implode(', ', $address)) : '<span class="empty-value">Not specified</span>';
          ?>
        </div>
      </div>
      <div class="data-item">
        <div class="data-label">Postcode</div>
        <div class="data-value"><?= htmlspecialchars($data['client_postcode'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>

    <!-- Service Address -->
    <div class="data-group">
      <h4>Service Address</h4>
      <div class="data-item">
        <div class="data-label">Address</div>
        <div class="data-value">
          <?php
          $address = array_filter([
            $data['service_address1'] ?? '',
            $data['service_address2'] ?? '',
            $data['service_city'] ?? ''
          ]);
          echo $address ? htmlspecialchars(implode(', ', $address)) : '<span class="empty-value">Not specified</span>';
          ?>
        </div>
      </div>
      <div class="data-item">
        <div class="data-label">Postcode</div>
        <div class="data-value"><?= htmlspecialchars($data['service_postcode'] ?? '') ?: '<span class="empty-value">Not specified</span>' ?></div>
      </div>
    </div>
  </div>

  <!-- Invoice Items -->
  <?php if (isset($data['item_description']) && is_array($data['item_description'])): ?>
    <div class="data-section">
      <h3 class="section-title">Invoice Items</h3>
      <div style="overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
          <thead>
            <tr style="background: #f8f9fa;">
              <th style="padding: 12px; text-align: left; border: 1px solid #e9ecef;">Description</th>
              <th style="padding: 12px; text-align: center; border: 1px solid #e9ecef;">Quantity</th>
              <th style="padding: 12px; text-align: right; border: 1px solid #e9ecef;">Rate</th>
              <th style="padding: 12px; text-align: right; border: 1px solid #e9ecef;">Amount</th>
            </tr>
          </thead>
          <tbody>
            <?php 
            $subtotal = 0;
            for ($i = 0; $i < count($data['item_description']); $i++): 
              $description = $data['item_description'][$i] ?? '';
              $quantity = floatval($data['item_quantity'][$i] ?? 0);
              $rate = floatval($data['item_rate'][$i] ?? 0);
              $amount = $quantity * $rate;
              $subtotal += $amount;
            ?>
            <tr>
              <td style="padding: 12px; border: 1px solid #e9ecef; vertical-align: top;">
                <?= htmlspecialchars($description) ?: '<span class="empty-value">No description</span>' ?>
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef; text-align: center;">
                <?= number_format($quantity, 2) ?>
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef; text-align: right;">
                £<?= number_format($rate, 2) ?>
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef; text-align: right; font-weight: 600;">
                £<?= number_format($amount, 2) ?>
              </td>
            </tr>
            <?php endfor; ?>
          </tbody>
          <tfoot style="background: #f8f9fa;">
            <tr>
              <td colspan="3" style="padding: 12px; border: 1px solid #e9ecef; text-align: right; font-weight: 600;">
                Subtotal:
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef; text-align: right; font-weight: 600;">
                £<?= number_format($subtotal, 2) ?>
              </td>
            </tr>
            <tr>
              <td colspan="3" style="padding: 12px; border: 1px solid #e9ecef; text-align: right; font-weight: 600;">
                VAT (20%):
              </td>
              <td style="padding: 12px; border: 1px solid #e9ecef; text-align: right; font-weight: 600;">
                £<?= number_format($subtotal * 0.20, 2) ?>
              </td>
            </tr>
            <tr style="background: #2e5aa6; color: white;">
              <td colspan="3" style="padding: 12px; border: 1px solid #2e5aa6; text-align: right; font-weight: 600; font-size: 1.1em;">
                Total:
              </td>
              <td style="padding: 12px; border: 1px solid #2e5aa6; text-align: right; font-weight: 600; font-size: 1.1em;">
                £<?= number_format($subtotal * 1.20, 2) ?>
              </td>
            </tr>
          </tfoot>
        </table>
      </div>
    </div>
  <?php else: ?>
    <div class="data-section">
      <h3 class="section-title">Invoice Items</h3>
      <div class="empty-value" style="text-align: center; padding: 20px;">
        No items found in this invoice.
      </div>
    </div>
  <?php endif; ?>
</div>