<!-- Buyer Setup Modal -->
<div class="modal fade" id="buyerSetupModal" tabindex="-1" aria-labelledby="buyerSetupModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="setup_buyer.php" method="POST" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="buyerSetupModalLabel">Complete Your Buyer Profile to Continue</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">

        <div class="mb-3">
          <label for="shippingAddress" class="form-label">Street Address </label>
          <input type="text" class="form-control" name="shippingAddress" placeholder="Eg. 12 Rose Street" required>
        </div>

        <div class="row mb-3">
          <div class="col-8">
            <label for="city" class="form-label">City </label>
            <input type="text" class="form-control" name="city" placeholder="Eg. Bloemfontein" required>
          </div>
          <div class="col-4">
            <label for="postalCode" class="form-label">Postal Code</label>
            <input type="text" class="form-control" name="postalCode" placeholder="xxxx" pattern="\d{4}">
          </div>
        </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-secondary">Save and Continue</button>
        </div>

        <input type="hidden" name="productID"
          value="<?php echo isset($product['productID']) ? $product['productID'] : ''; ?>">


    </form>
  </div>
</div>