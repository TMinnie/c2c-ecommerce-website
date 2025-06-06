
$(document).ready(function () {
  const modal = new bootstrap.Modal(document.getElementById('buyerProfileModal'));

  // Load buyer data when modal opens
  $('#buyerProfileModal').on('show.bs.modal', function () {
    $.get('sellerdash/get_buyer_info.php', function (data) {
      if (data && !data.error) {
        $('#shippingAddress1').val(data.shippingAddress1 || '');
        $('#shippingAddress2').val(data.shippingAddress2 || '');
        $('#paymentDetails').val(data.paymentDetails || '');
      }
    }, 'json');
  });

  // Handle form submission
  $('#buyerProfileForm').on('submit', function (e) {
    e.preventDefault();
    const formData = $(this).serialize();

    $.post('save_buyer_profile.php', formData, function (response) {
      if (response.success) {
        $('#buyerSuccess').removeClass('d-none');
        setTimeout(() => {
          modal.hide();
          location.reload(); // or redirect if needed
        }, 1500);
      }
    }, 'json');
  });
});
