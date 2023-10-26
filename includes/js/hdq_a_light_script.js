function validateLightningAddress(event) {
    const email = document.getElementById("lightning_address").value;
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    
    if (!emailRegex.test(email)) {
        alert("Please enter a valid lightning address format.");
        event.preventDefault(); // Stop the form submission
    } else {
        // If it's a valid lightning address, make the AJAX call
        jQuery.ajax({
            url: my_ajax_object.ajaxurl, // This will be enqueued in the next step
            type: 'POST',
            data: {
                action: 'store_lightning_address',
                address: email
            },
            success: function(response) {
                console.log(response); // Log server's response.
                alert("Lightning Address stored successfully.");
            }
        });
        event.preventDefault(); // Stop the form submission in either case
    }
}
