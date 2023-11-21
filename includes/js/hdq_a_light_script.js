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

function getPayUrl(email) {
    try {
        const parts = email.split('@');
        const domain = parts[1];
        const username = parts[0];
        const transformUrl = `https://${domain}/.well-known/lnurlp/${username}`;
        console.log("Transformed URL:", transformUrl);
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

async function getBolt11(email, amount) {
    try {
        const purl = getPayUrl(email);
        if (!purl) throw new Error("Invalid URL generated");

        const lnurlDetails = await getUrl(purl);
        if (!lnurlDetails || !lnurlDetails.callback) throw new Error("LNURL details not found");

        let minAmount = lnurlDetails.minSendable;
        let payAmount = amount && amount * 1000 > minAmount ? amount * 1000 : minAmount;

        const payquery = `${lnurlDetails.callback}?amount=${payAmount}`;
        console.log("Amount:", amount, "Payquery:", payquery);

        const prData = await getUrl(payquery);
        if (prData && prData.pr) {
            return prData.pr.toUpperCase();
        } else {
            throw new Error(`Payment request generation failed: ${prData.reason || 'unknown reason'}`);
        }
    } catch (error) {
        console.error("Error in generating BOLT11:", error);
        return null;
    }
}
// Comment out for now while we try a server-side approach
// async function payBolt11Invoice(bolt11) {
//     const btcpayServerUrl = hdq_data.btcpayUrl;
//     const apiKey = hdq_data.btcpayApiKey;
//     const storeId = "HomCFd17Con8Gnwnocr5Dj9V35qksKLYSTq5DrNTwkad"; // Hardcoded for now
//     const cryptoCode = "BTC"; // Hardcoded as BTC

//     const requestBody = {
//         BOLT11: bolt11,
//         // You can add additional parameters like 'amount', 'maxFeePercent', etc., if needed
//     };

//     try {
//         const response = await fetch(`${btcpayServerUrl}/api/v1/stores/${storeId}/lightning/${cryptoCode}/invoices/pay`, {
//             method: 'POST',
//             headers: {
//                 'Content-Type': 'application/json',
//                 'Authorization': `token ${apiKey}`
//             },
//             body: JSON.stringify(requestBody)
//         });

//         const data = await response.json();

//         if (response.ok) {
//             console.log('Payment Successful:', data);
//             return data;
//         } else {
//             console.error('Payment Failed:', data);
//             return null;
//         }
//     } catch (error) {
//         console.error('Error in paying BOLT11 Invoice:', error);
//         return null;
//     }
// }

// Function to send payment request to your server
function sendPaymentRequest(bolt11) {
    return fetch(hdq_data.ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            'action': 'pay_bolt11_invoice',
            'bolt11': bolt11
        })
    })
    .then(response => response.json());
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

                            // Corrected: Access BTCPay Server details from hdq_data
                            // console.log(`BTCPay Server URL: ${hdq_data.btcpayUrl}`);
                            // console.log(`BTCPay Server API Key: ${hdq_data.btcpayApiKey}`);

                            getBolt11(email, totalSats)
                                .then(bolt11 => {
                                    if (bolt11) {
                                        console.log(`BOLT11 Invoice: ${bolt11}`);
                                        // Pay the BOLT11 Invoice using BTCPay Server
                                        sendPaymentRequest(bolt11)
                                            .then(paymentResponse => {
                                                if (paymentResponse) {
                                                    console.log('Payment response:', paymentResponse);
                                                    // Additional logic after successful payment
                                                } else {
                                                    console.log('Payment failed or no response.');
                                                }
                                            })
                                            .catch(error => console.error('Error paying BOLT11 Invoice:', error));
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
