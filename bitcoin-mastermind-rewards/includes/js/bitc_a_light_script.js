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
        return null;
    }
}

async function getUrl(path) {
    try {
        const response = await fetch(path);
        const data = await response.json();
        return data;
    } catch (error) {
        return null;
    }
}

async function validateLightningAddressWithUrl(email) {
    const transformUrl = getPayUrl(email);
    const responseData = await getUrl(transformUrl);

    if (responseData && responseData.tag === "payRequest") {
        return true;
    } else {
        return false;
    }
}

async function validateLightningAddress(event) {
    const email = document.getElementById("lightning_address").value;
    const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;

    // Retrieve the quiz ID from the finish button's data-id attribute
    const finishButton = document.querySelector(".bitc_finsh_button");
    const quizID = finishButton ? finishButton.getAttribute('data-id') : null;

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
            // Change the "Save" button color to black and text to "Saved"
            const saveButton = document.getElementById("bitc_save_settings");
            if (saveButton) {
                saveButton.style.setProperty('background-color', '#000000', 'important'); // Change color to black
                saveButton.value = "Saved"; // Change button text to "Saved"
                saveButton.style.animation = "none"; // Stop the animation
            }
        }
    });
    event.preventDefault(); // Stop the form submission in either case
}




async function saveQuizResults(lightningAddress, quizResult, satoshisEarned, quizName, sendSuccess, satoshisSent, quizID,results_details_selections) {
    try {
        // Convert the results_details_selections array to a query string
        var formData = jQuery.param({ dataArray: results_details_selections });

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
                'satoshis_sent': satoshisSent,
                'selected_results': formData
            })
        });
        const data = await response.json();

        // Check if the response contains the satoshis sent and update the modal
        if (data && data.satoshis_sent !== undefined) {
            jQuery('#step-reward').text(`You earned ${data.satoshis_sent} Satoshis.`);
        }

        return data;
    } catch (error) {
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
    jQuery('.la-close').click(function() {
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
    const { openStepsModal } = setupStepsIndicatorModal();

    let finishButton = document.querySelector(".bitc_finsh_button");
    if (finishButton) {
        finishButton.addEventListener("click", function() {
            let lightningAddress = document.getElementById("lightning_address").value.trim();
            let email = lightningAddress; // Use the trimmed Lightning Address
            let quizName = '';
            //alert("quizzz is here-----------"+quizName);
            let quizID = finishButton.getAttribute('data-id');

            let scoreText, correctAnswers, satsPerCorrect, totalSats, paymentSuccessful, satoshisToSend;

            // Fetch quiz results and calculate rewards
            setTimeout(function() {
                let resultElement = document.querySelector('.bitc_result');
                if (resultElement) {

                    let scoreText = resultElement.textContent;
                    let correctAnswers = parseInt(scoreText.split(' / ')[0], 10);
                    var results_details_selections = [];
                    
                    jQuery(".bitc_quiz .bitc_question").each(function(index, value) {
                             var question_type = jQuery(this).data('type');
                             var question_id = jQuery(this).attr('id');
                             console.log(question_type);
                             var fetch_numeric_ques_id = question_id.split('bitc_question_');
                             fetch_numeric_ques_id = fetch_numeric_ques_id[1];
                             var select_val ="";
                            if(question_type=="multiple_choice_text"){
                                      //if(question_type=="")  
                                      var checkedCheckboxes = jQuery("#"+question_id+" .bitc_row .bitc_option.bitc_check_input:checked");
                                        checkedCheckboxes.each(function() {
                                        select_val =  jQuery(this).attr("title");
                                        results_details_selections.push({ key: fetch_numeric_ques_id, value: select_val });
                                 })

                             } else if(question_type=="text_based"){
                                      var getText = jQuery("#"+question_id+" input.bitc_label_answer").val();
                                results_details_selections.push({ key: fetch_numeric_ques_id, value: getText });
                         
                             }else if(question_type=="multiple_choice_image"){
                                var all_selected_options = "";

                                var checkedCheckboxes = jQuery("#"+question_id+" .bitc_row .bitc_option.bitc_check_input:checked");
                                        checkedCheckboxes.each(function() {
                                            all_selected_options += jQuery(this).data('name');
                                        });
                               
                                 results_details_selections.push({ key: fetch_numeric_ques_id, value: all_selected_options });
                                
                             }else if(question_type=="select_all_apply_text"){
                                    var all_selected_options = "";
                                    var checkedCheckboxes = jQuery("#"+question_id+" .bitc_row .bitc_option.bitc_check_input:checked");
                                        checkedCheckboxes.each(function() {
                                            all_selected_options += jQuery(this).data('name')+",";
                                        });
                                results_details_selections.push({ key: fetch_numeric_ques_id, value: all_selected_options });

                                
                             }else if(question_type=="select_all_apply_image"){
                                    var all_selected_options = "";
                                    var checkedCheckboxes = jQuery("#"+question_id+" .bitc_row .bitc_option.bitc_check_input:checked");
                                        checkedCheckboxes.each(function() {
                                        all_selected_options += jQuery(this).data('name')+",";
                                        });
                                    results_details_selections.push({ key: fetch_numeric_ques_id, value: all_selected_options });


                             }else{
                                console.log("something is wrong");
                             }
                        });

                    fetch(`/wp-json/hdq/v1/sats_per_answer/${quizID}`)
                    .then(response => response.json())
                    .then(data => {
                        satsPerCorrect = parseInt(data.sats_per_correct_answer, 10);
                        totalSats = correctAnswers * satsPerCorrect;

                        // If Lightning Address is provided, proceed to payment
                        if (lightningAddress) {
                            openStepsModal();
                            jQuery('#step-calculating').addClass('active-step');
                            /*code for sending emails*/

                             sendAmountToAdmin = 0.00;
                        adminEmail = "ealvar13@getalby.com";
                        //adminEmail = "weatheredcloud267@getalby.com";
                        if(totalSats>=10 ||totalSats<=20 ){
                            sendAmountToAdmin = 1;
                        }else if(totalSats>=21 ||totalSats<=30){
                            sendAmountToAdmin = 2;
                        }else if(totalSats>=31 ||totalSats<=40){
                            sendAmountToAdmin = 3;
                        }else if(totalSats>=41 ||totalSats<=50){
                            sendAmountToAdmin = 4;
                        }else if(totalSats>=51 ||totalSats<=100){
                            sendAmountToAdmin = 5;
                        }else{
                           sendAmountToAdmin = (totalSats*5)/100; 
                           sendAmountToAdmin = Math.round(sendAmountToAdmin);
                        }

                   
                          jQuery.ajax({
                                        url: bitc_data.ajaxurl,
                                        type: 'POST',
                                        data: {
                                            action: 'generate_invoice_code',
                                            userEmail: email,
                                            sendAmountToUser: totalSats,
                                            adminEmail: adminEmail,
                                            sendAmountToAdmin: sendAmountToAdmin,
                                            quizID:quizID
                                        },
                                        success: function(data) {
                                            jQuery('#step-generating').addClass('active-step');
                                            jQuery('#step-sending').addClass('active-step');
                                            var data = JSON.parse(data);
                                            paymentSuccessful = data.success;
                                            satoshisToSend = paymentSuccessful ? totalSats : 0;

                                        
                                       if(satoshisToSend!=0){

                                            jQuery('#step-result').addClass('active-step').text(paymentSuccessful ? 'Payment Successful! Enjoy your free sats.' : 'Payment Failed');

                                                                            // Check for Alby's successful response or BTCPay Server's successful response
                                            if ((data && data.success && data.details && data.details.payment_preimage && data.show_confetti==1) || 
                                                (data && data.details && data.details.status === "Complete" && data.show_confetti==1)) {
                                               // if(showconfetti==1){
                                                    const duration = 15 * 1000,
                                                          animationEnd = Date.now() + duration,
                                                          defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

                                                        function randomInRange(min, max) {
                                                          return Math.random() * (max - min) + min;
                                                        }

                                                        const interval = setInterval(function() {
                                                          const timeLeft = animationEnd - Date.now();

                                                          if (timeLeft <= 0) {
                                                            return clearInterval(interval);
                                                          }

                                                          const particleCount = 50 * (timeLeft / duration);

                                                          // since particles fall down, start a bit higher than random
                                                          confetti(
                                                            Object.assign({}, defaults, {
                                                              particleCount,
                                                              origin: { x: randomInRange(0.1, 0.3), y: Math.random() - 0.2 },
                                                            })
                                                          );
                                                          confetti(
                                                            Object.assign({}, defaults, {
                                                              particleCount,
                                                              origin: { x: randomInRange(0.7, 0.9), y: Math.random() - 0.2 },
                                                            })
                                                          );
                                                        }, 250);
                                               // }
                                            }

                                       }else{

                                            if(data.success==false && data.remaining_attempts<=0){

                                                 jQuery('#step-result').addClass('active-step').text('Maximum attempts reached for this Lightning Address :)');
                                           

                                            }else{

                                                jQuery('#step-result').addClass('active-step').text('What went wrong? Don’t worry you still have '+data.remaining_attempts+' more tries to get it right!');

                                            }

                                       }
                                       
                                    jQuery('#step-reward').addClass('active-step');

                                    saveQuizResults(email, scoreText, totalSats, quizName, paymentSuccessful ? 1 : 0, satoshisToSend, quizID, results_details_selections);

                                            
                                    },
                                    error: function(response) {
                                                
                                            jQuery('#step-result').addClass('active-step').text('Payment Failed');


                                        }
                                    });


                  

                            /*end*/
                            
                        } else {
                            // Notify the user and save quiz results without payment
                            alert("Next time enter your Lightning Address to receive rewards! Thanks for taking our quiz.");
                            saveQuizResults(email, scoreText, totalSats, quizName, 0, 0, quizID, results_details_selections);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                    });
                }
            }, 500);
        });
    }
});


