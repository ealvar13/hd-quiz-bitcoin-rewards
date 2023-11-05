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
            url: hdq_data.ajaxurl, // Use 'hdq_data' instead of 'my_ajax_object'
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

async function generateBolt11(email, totalSats) {
    try {
        // Fetch LNURLPay base URL
        const baseUrl = await getPayUrl(email); // getPayUrl returns the base URL for LNURLPay
        if (!baseUrl) throw new Error("Could not get LNURLPay base URL.");

        // Remove the '.well-known' part from the base URL
        const cleanUrl = baseUrl.replace('/.well-known', '');

        // Append '/callback' to the clean URL to form the callback URL
        const callbackUrl = `${cleanUrl}/callback`;

        // Determine the amount to send in millisatoshis (totalSats * 1000)
        let amount = totalSats * 1000; // Convert satoshis to millisatoshis

        // Make a GET request to the callback URL with the amount parameter
        const payquery = `${callbackUrl}?amount=${amount}`;
        const prData = await getUrl(payquery); // getUrl should handle the GET request and return the response

        // Process the payment request response
        if (prData && prData.status === 'OK' && prData.pr) {
            return prData.pr.toUpperCase(); // Returning the BOLT11 string in uppercase if it exists
        } else {
            throw new Error(`Payment request generation failed: ${prData.reason || 'unknown reason'}`);
        }
    } catch (error) {
        console.error("Error in generating BOLT11:", error);
        return null;
    }
}





document.addEventListener("DOMContentLoaded", function() {
    let finishButton = document.querySelector(".hdq_finsh_button"); // Ensure the class name is correct
    if (finishButton) {
        finishButton.addEventListener("click", function() {
            // Retrieve the users lightning address again here
            let email = document.getElementById("lightning_address").value;

            // Timeout to allow result to be populated and to fetch quiz ID
            setTimeout(function() {
                let resultElement = document.querySelector('.hdq_result');
                if (resultElement) {
                    let scoreText = resultElement.textContent;
                    let correctAnswers = parseInt(scoreText.split(' / ')[0], 10);
                    
                    // Get the quiz ID from the finish button's data-id attribute
                    let quizID = finishButton.getAttribute('data-id');
                    
                    // Now fetch the sats per correct answer using this quiz ID
                    fetch(`/wp-json/hdq/v1/sats_per_answer/${quizID}`)
                        .then(response => response.json())
                        .then(data => {
                            let satsPerCorrect = parseInt(data.sats_per_correct_answer, 10);
                            let totalSats = correctAnswers * satsPerCorrect;
                            console.log(`Quiz ID: ${quizID}`);
                            console.log(`Quiz score: ${scoreText}`);
                            console.log(`Sats per correct answer: ${satsPerCorrect}`);
                            console.log(`Total Satoshis earned: ${totalSats}`);
                            generateBolt11(email, totalSats)
                                .then(bolt11 => {
                                    if (bolt11) {
                                        console.log(`BOLT11 Invoice: ${bolt11}`);
                                        // Here you could now display the BOLT11 invoice to the user
                                        // or perform additional actions like copying it to the clipboard
                                    } else {
                                        console.log(`Failed to generate BOLT11 Invoice.`);
                                    }
                                })
                                .catch(error => console.error('Error generating BOLT11:', error));
                        })
                        .catch(error => console.error('Error:', error));
                } else {
                    console.log('Quiz score not found.');
                }
            }, 500); // The delay in milliseconds; adjust if necessary
        });
    }
});



// // Testing this API endpoint

// let quizID = 3; // Replace this with the actual quiz ID you get from your front end logic

// fetch(`/wp-json/hdq/v1/sats_per_answer/${quizID}`)
//     .then(response => response.json())
//     .then(data => {
//         let satsPerCorrect = parseInt(data.sats_per_correct_answer, 10);
//         console.log(`Sats per correct answer for quiz ID ${quizID}: ${satsPerCorrect}`);
//         // Use satsPerCorrect here as needed
//     })
//     .catch(error => console.error('Error:', error));

