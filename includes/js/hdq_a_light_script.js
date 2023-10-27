function getPayUrl(email) {
    try {
        const parts = email.split('@');
        const domain = parts[1];
        const username = parts[0];
        const transformUrl = `https://${domain}/.well-known/lnurlp/${username}`;
        return transformUrl;
    } catch (error) {
        console.error("Exception, possibly malformed LN Address:", error);
        return null;
    }
}

async function getUrl(path) {
    try {
        const response = await fetch(path);
        const data = await response.json();
        return data;
    } catch (error) {
        console.error("Failed to fetch from the URL:", error);
        return null;
    }
}

async function validateLightningAddressWithUrl(email) {
    const transformUrl = getPayUrl(email);
    const responseData = await getUrl(transformUrl);

    if (responseData && responseData.tag === "payRequest") {
        console.log("Valid Lightning Address!");
        return true;
    } else {
        console.log("Invalid or Inactive Lightning Address.");
        return false;
    }
}

async function validateLightningAddress(event) {
    const email = document.getElementById("lightning_address").value;
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
    
    if (!emailRegex.test(email)) {
        alert("Please enter a valid lightning address format.");
        event.preventDefault(); // Stop the form submission
    } else {
        const isValidLightningAddress = await validateLightningAddressWithUrl(email);

        if (!isValidLightningAddress) {
            alert("Invalid or Inactive Lightning Address.");
            event.preventDefault(); // Stop the form submission
            return;
        }

        // If it's a valid lightning address, make the AJAX call
        jQuery.ajax({
            url: my_ajax_object.ajaxurl,
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
