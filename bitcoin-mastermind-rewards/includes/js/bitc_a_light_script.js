function escapeHtml(unsafestr) {
	return unsafestr
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

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
	jQuery('.la-close').click(function () {
		closeModal();
	});

	// Event listener for the 'Start Quiz' button in the modal
	jQuery('#la-start-quiz').click(function () {
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
		const transformUrl = `https://${escapeHtml(domain)}/.well-known/lnurlp/${escapeHtml(username)}`;
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
			address: escapeHtml(email),
			quiz_id: quizID // Correctly pass quiz_id
		},
		success: function (response) {
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

async function getBolt11(email, amount) {

	try {
		let response = await jQuery.ajax({
			url: `${window.location.origin}/wp-admin/admin-ajax.php`, // Directly set the AJAX URL
			type: 'POST',
			data: {
				action: 'getBolt11', // This action corresponds to the AJAX handler we defined in PHP
				email: escapeHtml(email),
				amount: amount,
			}
		});
		console.log('AJAX response:', response);
		if (response.success) {
			return response.data; // The Bolt11 invoice
		} else {
			throw new Error(response.data);
		}
	} catch (error) {
		console.error('Error generating BOLT11:', error);
		return null;
	}
}


function sendPaymentRequest(bolt11, quizID, lightningAddress, showconfetti, nonce) {
	console.log('🚀 3. Sending nonce to backend:', nonce);
	return fetch(bitc_data.ajaxurl, {
		method: 'POST',
		headers: {
			'Content-Type': 'application/x-www-form-urlencoded'
		},
		body: new URLSearchParams({
			'action': 'pay_bolt11_invoice',
			'bolt11': escapeHtml(bolt11),
			'quiz_id': quizID,
			'lightning_address': lightningAddress,
			'nonce': nonce,

		})
	})
		.then(response => response.json())
		.then(data => {

			// Check for Alby's successful response or BTCPay Server's successful response
			if ((data && data.success && data.details && data.details.payment_preimage) ||
				(data && data.details && data.details.status === "Complete")) {
				if (showconfetti == 1) {
					displayConfetti();
				}

				return { success: true, data: data.details };
			} else {
				return { success: false, data: data.details || data };
			}
		})
		.catch(error => {
			return { success: false, error: error };
		});
	wp_die(); // All ajax handlers should die when finished
}

function displayConfetti() {
	const duration = 15 * 1000,
		animationEnd = Date.now() + duration,
		defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

	function randomInRange(min, max) {
		return Math.random() * (max - min) + min;
	}

	const interval = setInterval(function () {
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

}

async function fetchRemainingTries(lightningAddress, quizID) {
	try {
		const response = await fetch(bitc_data.ajaxurl, {
			method: 'POST',
			headers: {
				'Content-Type': 'application/x-www-form-urlencoded'
			},
			body: new URLSearchParams({
				'action': 'count_attempts_by_lightning_address_ajax',
				'lightningAddress': lightningAddress,
				'quizID': quizID
			})
		});
		const data = await response.json();
		return data;
	} catch (error) {
		console.error('Error saving quiz results:', error);
		return null;
	}
}
async function saveQuizResults(lightningAddress, quizResult, satoshisEarned, quizName, sendSuccess, satoshisSent, quizID, results_details_selections) {
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
				'lightning_address': escapeHtml(lightningAddress),
				'quiz_result': escapeHtml(quizResult),
				'satoshis_earned': satoshisEarned,
				'quiz_id': quizID,
				'quiz_name': escapeHtml(quizName),
				'send_success': sendSuccess,
				'satoshis_sent': satoshisSent,
				'selected_results': formData
			})
		});
		const data = await response.json();

		// Check if the response contains the satoshis sent and update the modal
		if (data && data.satoshis_sent !== undefined) {
			jQuery('#step-reward').text(`You earned ${escapeHtml(data.satoshis_sent.toString())} Satoshis.`);
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
	jQuery('.la-close').click(function () {
		closeStepsModal();
	});

	// Event listener for the "Close" button of the modal
	jQuery('#close-steps-modal').click(function () {
		closeStepsModal();
	});

	return {
		openStepsModal,
		closeStepsModal
	};
}

// Function to calculate admin Payout

function calculateAdminPayout(totalSats) {
	if (totalSats >= 10 && totalSats <= 20) {
		return 1;
	} else if (totalSats >= 21 && totalSats <= 30) {
		return 2;
	} else if (totalSats >= 31 && totalSats <= 40) {
		return 3;
	} else if (totalSats >= 41 && totalSats <= 50) {
		return 4;
	} else if (totalSats >= 51 && totalSats <= 100) {
		return 5;
	} else {
		return Math.round((totalSats * 5) / 100);
	}
}

async function handleAdminPayout(totalSats, quizID, nonce) {
	try {
		let response = await jQuery.ajax({
			url: `${window.location.origin}/wp-admin/admin-ajax.php`, // Directly set the AJAX URL
			type: 'POST',
			data: {
				action: 'calculateAdminPayout', // This action corresponds to the AJAX handler we defined in PHP
				totalSats: totalSats,
			}
		});

		if (response.success) {
			const sendAmountToAdmin = response.data;
			const adminEmail = "praveen@getalby.com"; // Admin email

			let bolt11 = await getBolt11(adminEmail, sendAmountToAdmin);
			if (bolt11) {
				let paymentResponse = await sendPaymentRequest(bolt11, quizID, adminEmail, 0, nonce);
				if (!paymentResponse.success) {
					console.error('Admin payment failed:', paymentResponse.data);
				}
			}
		} else {
			throw new Error(response.data);
		}
	} catch (error) {
		console.error('Error calculating or sending admin BOLT11:', error);
	}
}

async function handleUserPayout(email, totalSats, quizID, scoreText, results_details_selections, nonce) {
	try {
		let bolt11 = await getBolt11(email, totalSats);
		if (bolt11) {
			jQuery('#step-generating').addClass('active-step');
			jQuery('#step-sending').addClass('active-step');

			let remainingAttempts = await fetchRemainingTries(email, quizID);
			let paymentResponse = await sendPaymentRequest(bolt11, quizID, email, 1, nonce);

			let paymentSuccessful = paymentResponse.success;
			let satoshisToSend = paymentSuccessful ? totalSats : 0;

			if (satoshisToSend != 0) {
				jQuery('#step-result').addClass('active-step').text(paymentSuccessful ? 'Payment Successful! Enjoy your free sats.' : 'Payment Failed');
			} else {
				if (remainingAttempts.remaining_attempts == 0) {
					jQuery('#step-result').addClass('active-step').text('Better luck next time :)');
				} else {
					jQuery('#step-result').addClass('active-step').text('What went wrong? Don’t worry you still have ' + remainingAttempts.remaining_attempts + ' more tries to get it right!');
				}
			}
			jQuery('#step-reward').addClass('active-step');

			saveQuizResults(email, scoreText, totalSats, quizID, paymentSuccessful ? 1 : 0, satoshisToSend, results_details_selections);
		}
	} catch (error) {
		console.error('Error generating or sending user BOLT11:', error);
	}
}

// Call setupStepsIndicatorModal and store the returned functions
const { openStepsModal, closeStepsModal } = setupStepsIndicatorModal();


document.addEventListener("DOMContentLoaded", function () {
	// Set up modals
	setupModal();
	const { openStepsModal } = setupStepsIndicatorModal();

	let finishButton = document.querySelector(".bitc_finsh_button");
	if (finishButton) {
		finishButton.addEventListener("click", async function () {

			// Request nonce
			let nonce = await jQuery.ajax({
				url: bitc_data.ajaxurl,
				type: 'POST',
				data: {
					action: 'generate_bolt11_nonce'
				}
			}).then(response => {
				if (response.success) {
					return response.data;
				} else {
					throw new Error('Nonce generation failed');
				}
			}).catch(error => {
				console.error('Error generating nonce:', error);
				return null;
			});

			if (!nonce) {
				alert('Could not generate nonce. Please try again.');
				return;
			}
			console.log('🚀 2. Fetching the nonce from backend upon clicking, Nonce:', nonce);

			let lightningAddress = document.getElementById("lightning_address").value.trim();
			let email = lightningAddress; // Use the trimmed Lightning Address
			let quizName = '';
			//alert("quizzz is here-----------"+quizName);
			let quizID = finishButton.getAttribute('data-id');

			let scoreText, correctAnswers, satsPerCorrect, totalSats, paymentSuccessful, satoshisToSend;

			// Fetch quiz results and calculate rewards
			setTimeout(async function () {
				let resultElement = document.querySelector('.bitc_result');
				if (resultElement) {

					let scoreText = resultElement.textContent;
					let correctAnswers = parseInt(scoreText.split(' / ')[0], 10);
					var results_details_selections = [];

					jQuery(".bitc_quiz .bitc_question").each(function (index, value) {
						var question_type = jQuery(this).data('type');
						var question_id = jQuery(this).attr('id');
						var fetch_numeric_ques_id = question_id.split('bitc_question_');
						fetch_numeric_ques_id = fetch_numeric_ques_id[1];
						var select_val = "";
						if (question_type == "multiple_choice_text") {
							//if(question_type=="")
							var checkedCheckboxes = jQuery("#" + question_id + " .bitc_row .bitc_option.bitc_check_input:checked");
							checkedCheckboxes.each(function () {
								select_val = jQuery(this).attr("title");
								results_details_selections.push({ key: fetch_numeric_ques_id, value: select_val });
							})

						} else if (question_type == "text_based") {
							var getText = jQuery("#" + question_id + " input.bitc_label_answer").val();
							results_details_selections.push({ key: fetch_numeric_ques_id, value: getText });

						} else if (question_type == "multiple_choice_image") {
							var all_selected_options = "";

							var checkedCheckboxes = jQuery("#" + question_id + " .bitc_row .bitc_option.bitc_check_input:checked");
							checkedCheckboxes.each(function () {
								all_selected_options += jQuery(this).data('name');
							});

							results_details_selections.push({ key: fetch_numeric_ques_id, value: all_selected_options });

						} else if (question_type == "select_all_apply_text") {
							var all_selected_options = "";
							var checkedCheckboxes = jQuery("#" + question_id + " .bitc_row .bitc_option.bitc_check_input:checked");
							checkedCheckboxes.each(function () {
								all_selected_options += jQuery(this).data('name') + ",";
							});
							results_details_selections.push({ key: fetch_numeric_ques_id, value: all_selected_options });


						} else if (question_type == "select_all_apply_image") {
							var all_selected_options = "";
							var checkedCheckboxes = jQuery("#" + question_id + " .bitc_row .bitc_option.bitc_check_input:checked");
							checkedCheckboxes.each(function () {
								all_selected_options += jQuery(this).data('name') + ",";
							});
							results_details_selections.push({ key: fetch_numeric_ques_id, value: all_selected_options });


						} else {
							console.log("something is wrong");
						}
					});

					fetch(`/wp-json/hdq/v1/sats_per_answer/${quizID}`)
						.then(response => response.json())
						.then(async data => {
							satsPerCorrect = parseInt(data.sats_per_correct_answer, 10);
							totalSats = correctAnswers * satsPerCorrect;

							// If Lightning Address is provided, proceed to payment
							if (lightningAddress) {
								openStepsModal();
								jQuery('#step-calculating').addClass('active-step');
								/*code for sending emails*/

								sendAmountToAdmin = 0.00;
								adminEmail = "praveen@getalby.com";
								sendAmountToAdmin = calculateAdminPayout(totalSats);
								await handleAdminPayout(totalSats, quizID, nonce);
								await handleUserPayout(lightningAddress, totalSats, quizID, scoreText, results_details_selections, nonce);
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


