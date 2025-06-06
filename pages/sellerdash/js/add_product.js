console.log("Seller dashboard script loaded");
document.addEventListener('DOMContentLoaded', () => {
    // Form submission handler
    document.getElementById('productForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        // Validate required fields
        const pName = document.getElementById('pName').value.trim();
        const pDescription = document.getElementById('pDescription').value.trim();
        const pPrice = document.getElementById('pPrice').value.trim();
        const pCategory = document.getElementById('pCategory').value;

        if (!pName || !pDescription || !pPrice || !pCategory) {
            alert('Please fill in all fields.');
            return;
        }

        // Create FormData object to handle file uploads
        const formData = new FormData();
        formData.append('pName', pName);
        formData.append('pDescription', pDescription);
        formData.append('pPrice', pPrice);
        formData.append('pCategory', pCategory);
        formData.append('imagePath', document.getElementById('imagePath').files[0]); // Handle file upload

        // Send AJAX request to add product
        fetch('sellerdash/add_product.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert('Product added successfully');
                document.getElementById('productForm').reset(); // Reset the form
                location.reload(); // Reload page to refresh products list
            } else {
                alert('Error adding product: ' + (data.error || 'Unknown error'));
            }
        })
        .catch(err => {
            console.error('Error:', err);
            alert('An error occurred while adding the product.');
        });
    });
});
