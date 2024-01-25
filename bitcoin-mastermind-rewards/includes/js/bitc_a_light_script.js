// Function for the modal that displays user instructions at the start of the quiz
function setupModal() {
    // Function to open the modal
    function openModal() {
        jQuery('#la-modal').show();
    }

    // Function to close the modal
    function closeModal() {
        jQuery('#la-modal').hide();
    }

    // Event listener for the close button of the modal
    jQuery('.la-close').click(function() {
        closeModal();
    });

    // Event listener for the 'Start Quiz' button in the modal
    jQuery('#la-start-quiz').click(function() {
        closeModal();
    });

    // Automatically open the modal when the function is called
    openModal();
}

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

    // Retrieve the quiz ID from the finish button's data-id attribute
    const finishButton = document.querySelector(".bitc_finsh_button");
    const quizID = finishButton ? finishButton.getAttribute('data-id') : null;
    console.log(`Quiz ID from validateLightningAddress: ${quizID}`);

    if (!emailRegex.test(email)) {
        alert("Please enter a valid lightning address format.");
        event.preventDefault(); // Stop the form submission
        return;
    }

    const isValidLightningAddress = await validateLightningAddressWithUrl(email);

    if (!isValidLightningAddress) {
        alert("Invalid or Inactive Lightning Address.");
        event.preventDefault(); // Stop the form submission
        return;
    }

    // If it's a valid lightning address, make the AJAX call
    jQuery.ajax({
        url: bitc_data.ajaxurl,
        type: 'POST',
        data: {
            action: 'store_lightning_address',
            address: email,
            quiz_id: quizID // Correctly pass quiz_id
        },
        success: function(response) {
            console.log(response); // Log server's response.
            alert(response); // Show the server response instead of a static message
        }
    });
    event.preventDefault(); // Stop the form submission in either case
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

function sendPaymentRequest(bolt11, quizID, lightningAddress) {
    return fetch(bitc_data.ajaxurl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: new URLSearchParams({
            'action': 'pay_bolt11_invoice',
            'bolt11': bolt11,
            'quiz_id': quizID,
            'lightning_address': lightningAddress
        })
    })
    .then(response => response.json())
    .then(data => {
        console.log('Raw payment response data:', data); // Added for debugging
        if (data && data.details && data.details.status === "Complete") {
            console.log('Payment Successful:', data.details);
            return { success: true, data: data.details };
        } else {
            console.log('Payment Not Successful:', data.details || data);
            return { success: false, data: data.details || data };
        }
    })
    .catch(error => {
        console.error('Error in Payment Request:', error);
        return { success: false, error: error };
    });
}

async function saveQuizResults(lightningAddress, quizResult, satoshisEarned, quizName, sendSuccess, satoshisSent, quizID) {
    try {
        console.log(`Sending AJAX request with Quiz ID: ${quizID}`);
        const response = await fetch(bitc_data.ajaxurl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: new URLSearchParams({
                'action': 'bitc_save_quiz_results',
                'lightning_address': lightningAddress,
                'quiz_result': quizResult,
                'satoshis_earned': satoshisEarned,
                'quiz_id': quizID,
                'quiz_name': quizName,
                'send_success': sendSuccess,
                'satoshis_sent': satoshisSent
            })
        });
        const data = await response.json();
        console.log('Quiz results saved:', data);

        // Check if the response contains the satoshis sent and update the modal
        if (data && data.satoshis_sent !== undefined) {
            jQuery('#step-reward').text(`You earned ${data.satoshis_sent} Satoshis.`);
        }

        return data;
    } catch (error) {
        console.error('Error saving quiz results:', error);
        return null;
    }
}

// Function to manage the steps indicator modal
function setupStepsIndicatorModal() {
    // Function to open the steps indicator modal
    function openStepsModal() {
        jQuery('#steps-modal').show();
    }

    // Function to close the steps indicator modal
    function closeStepsModal() {
        jQuery('#steps-modal').hide();
    }

    // Event listener for the close button of the steps indicator modal
    jQuery('.close-modal').click(function() {
        closeStepsModal();
    });

    // Event listener for the "Close" button of the modal
    jQuery('#close-steps-modal').click(function() {
        closeStepsModal();
    });

    return {
        openStepsModal,
        closeStepsModal
    };
}

// Call setupStepsIndicatorModal and store the returned functions
const { openStepsModal, closeStepsModal } = setupStepsIndicatorModal();


document.addEventListener("DOMContentLoaded", function() {
    // Set up modals
    setupModal();
    const { openStepsModal } = setupStepsIndicatorModal(); // Removed closeStepsModal from destructuring

    let finishButton = document.querySelector(".bitc_finsh_button");
    if (finishButton) {
        finishButton.addEventListener("click", function() {
            openStepsModal(); // Open the modal when Finish button is clicked
            jQuery('#step-calculating').addClass('active-step'); // Set the first step as active

            let email = document.getElementById("lightning_address").value;
            let quizName = document.querySelector(".wp-block-post-title").textContent;
            let quizID = finishButton.getAttribute('data-id');

            setTimeout(function() {
                let resultElement = document.querySelector('.bitc_result');
                if (resultElement) {
                    let scoreText = resultElement.textContent;
                    let correctAnswers = parseInt(scoreText.split(' / ')[0], 10);

                    fetch(`/wp-json/hdq/v1/sats_per_answer/${quizID}`)
                    .then(response => response.json())
                    .then(data => {
                        let satsPerCorrect = parseInt(data.sats_per_correct_answer, 10);
                        let totalSats = correctAnswers * satsPerCorrect;

                        jQuery('#step-generating').addClass('active-step');

                        getBolt11(email, totalSats)
                        .then(bolt11 => {
                            if (bolt11) {
                                jQuery('#step-sending').addClass('active-step');

                                sendPaymentRequest(bolt11, quizID, email)
                                .then(paymentResponse => {
                                    let paymentSuccessful = paymentResponse.success;
                                    let satoshisToSend = paymentSuccessful ? totalSats : 0;
                                    jQuery('#step-result').addClass('active-step').text(paymentSuccessful ? 'Payment Successful! Enjoy your free sats.' : 'Payment Failed');
                                    jQuery('#step-reward').addClass('active-step');

                                    saveQuizResults(email, scoreText, totalSats, quizName, paymentSuccessful ? 1 : 0, satoshisToSend, quizID);
                                })
                                .catch(error => {
                                    console.error('Error paying BOLT11 Invoice:', error);
                                    jQuery('#step-result').addClass('active-step').text('Payment Failed');
                                });
                            } else {
                                console.log(`Failed to generate BOLT11 Invoice.`);
                            }
                        })
                        .catch(error => {
                            console.error('Error generating BOLT11:', error);
                        });
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                } else {
                    console.log('Quiz score not found.');
                }
            }, 500);
        });
    }
});

