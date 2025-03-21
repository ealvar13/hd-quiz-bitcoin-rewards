// Escape HTML for safety
function escapeHtml(unsafestr) {
  return unsafestr
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

// 🎊 Confetti animation
function displayConfetti() {
  const duration = 3 * 1000,
    animationEnd = Date.now() + duration,
    defaults = { startVelocity: 30, spread: 360, ticks: 60, zIndex: 0 };

  function randomInRange(min, max) {
    return Math.random() * (max - min) + min;
  }

  const interval = setInterval(() => {
    const timeLeft = animationEnd - Date.now();
    if (timeLeft <= 0) return clearInterval(interval);

    const particleCount = 50 * (timeLeft / duration);
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

// 🟡 Lightning address validation + session storage
async function validateLightningAddress(event) {
  const email = document.getElementById("lightning_address").value;
  const emailRegex = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
  const finishButton = document.querySelector(".bitc_finsh_button");
  const quizID = finishButton ? finishButton.getAttribute("data-id") : null;

  if (!emailRegex.test(email)) {
    alert("Please enter a valid lightning address format.");
    event.preventDefault();
    return;
  }

  // Store it in PHP session
  jQuery.ajax({
    url: bitc_data.ajaxurl,
    type: "POST",
    data: {
      action: "store_lightning_address",
      address: escapeHtml(email),
      quiz_id: quizID,
    },
    success: function () {
      const saveButton = document.getElementById("bitc_save_settings");
      if (saveButton) {
        saveButton.style.setProperty("background-color", "#000000", "important");
        saveButton.value = "Saved";
        saveButton.style.animation = "none";
      }
    },
  });

  event.preventDefault();
}

// 💡 Setup steps modal open/close
function setupStepsIndicatorModal() {
  const openStepsModal = () => jQuery("#steps-modal").show();
  const closeStepsModal = () => jQuery("#steps-modal").hide();

  jQuery(".la-close").click(closeStepsModal);
  jQuery("#close-steps-modal").click(closeStepsModal);

  return { openStepsModal, closeStepsModal };
}

// ✅ Main payout handler
document.addEventListener("DOMContentLoaded", () => {
  const { openStepsModal } = setupStepsIndicatorModal();
  const finishBtn = document.querySelector(".bitc_finsh_button");
  if (!finishBtn) return;

  finishBtn.addEventListener("click", async () => {
    const lightningAddressInput = document.getElementById("lightning_address");
    if (!lightningAddressInput || !lightningAddressInput.value.trim()) {
      alert("Please enter a valid Lightning Address.");
      return;
    }

    const lightningAddress = lightningAddressInput.value.trim();
    const quizID = finishBtn.getAttribute("data-id");
    openStepsModal();

    // Step 1: Validating & preparing results
    document.getElementById("step-calculating")?.classList.add("active-step");

    // Step 2: Generating Invoice
    document.getElementById("step-generating")?.classList.add("active-step");

    const resultsDetailsSelections = [...document.querySelectorAll(".bitc_question")].map((q) => ({
      key: q.id.replace("bitc_question_", ""),
      value: q.querySelector(".bitc_check_input:checked")?.title || q.querySelector(".bitc_label_answer")?.value || "",
    }));

    // Get secure nonce
    let nonceResponse;
    try {
      nonceResponse = await jQuery.post(bitc_data.ajaxurl, { action: "generate_bolt11_nonce" });
    } catch (err) {
      console.error("❌ Nonce generation failed:", err);
      alert("Nonce generation failed. Please try again.");
      return;
    }

    if (!nonceResponse?.success || !nonceResponse?.data) {
      alert("Nonce generation failed.");
      return;
    }

    // Step 3: Sending Payment
    document.getElementById("step-sending")?.classList.add("active-step");

    let response;
    try {
      response = await jQuery.ajax({
        url: bitc_data.ajaxurl,
        type: "POST",
        dataType: "json",
        data: {
          action: "secure_quiz_payout",
          nonce: nonceResponse.data,
          lightning_address: lightningAddress,
          quiz_id: quizID,
          results_details_selections: resultsDetailsSelections,
        },
      });
    } catch (err) {
      console.error("❌ AJAX payout request failed:", err);
      alert("Could not send payout. Please try again.");
      return;
    }

    // Step 4: Show Reward
    document.getElementById("step-reward")?.classList.add("active-step");

    // Step 5: Final Result
    const stepResult = document.getElementById("step-result");
    stepResult?.classList.add("active-step");

    if (response?.success && response?.satoshis_sent !== undefined) {
      const satsDisplay = document.getElementById("satoshis-sent-display");
      if (satsDisplay) satsDisplay.textContent = response.satoshis_sent;
      if (stepResult) stepResult.textContent = "✅ Payment Successful! Enjoy your free sats.";
      displayConfetti();
    } else {
      const errorMsg = response?.data?.message || response?.message || "❌ Something went wrong during payout.";
      const satsDisplay = document.getElementById("satoshis-sent-display");
      if (satsDisplay) satsDisplay.textContent = "0 (Error)";
      if (stepResult) stepResult.textContent = `❌ ${errorMsg}`;
      alert(`Error: ${errorMsg}`);
    }
  });
});
