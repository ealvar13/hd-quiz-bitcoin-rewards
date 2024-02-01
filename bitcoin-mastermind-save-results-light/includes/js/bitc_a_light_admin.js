/*
    Bitcoin Mastermind Save Results Light admin script
    * some of these functions are currently not needed, but I
      will probably still use them later
*/

window.addEventListener("load", (event) => {
	console.log("Bitcoin Mastermind Save Results Light INIT");
	bitc_a_light_start();
});

function bitc_a_light_start() {
	bitc_a_light_load_active_tab();
}

// show the default tab on load
function bitc_a_light_load_active_tab() {
	var activeTab = jQuery("#bitc_tabs .bitc_active_tab").attr("data-hdq-content");
	jQuery("#" + activeTab).addClass("bitc_tab_active");
	jQuery(".bitc_tab_active").slideDown(500);
}

jQuery(".bitc_accordion h3").click(function () {
	jQuery(this).next("div").toggle(600);
});

/* Tab navigation
------------------------------------------------------- */
jQuery("#bitc_form_wrapper").on("click", "#bitc_tabs li", function (event) {
	jQuery("#bitc_tabs li").removeClass("bitc_active_tab");
	jQuery(this).addClass("bitc_active_tab");
	var hdqContent = jQuery(this).attr("data-hdq-content");
	jQuery(".bitc_tab_active").fadeOut();
	jQuery(".bitc_tab").removeClass("bitc_tab_active");
	jQuery("#" + hdqContent)
		.delay(250)
		.fadeIn();
	jQuery("#" + hdqContent).addClass("bitc_tab_active");
});

function bitc_a_light_scroll_to_top() {
	jQuery("html").animate(
		{
			scrollTop: 0,
		},
		"slow"
	);
}

// start loading stuff
function bitc_a_light_start_load() {
	jQuery("#bitc_message").fadeOut();
	jQuery("#bitc_loading ").fadeIn();
}
// after stuff has loaded
function bitc_a_light_after_load(editor = false) {
	jQuery("#bitc_loading ").delay(600).fadeOut();
	bitc_load_active_tab();
	bitc_scroll_to_top();
}

// show message box
function bitc_a_light_show_message(message) {
	jQuery("#bitc_message").html(message);
	jQuery("#bitc_message").fadeIn();
}

// hide message
jQuery("#bitc_wrapper").on("click", "#bitc_message", function (event) {
	jQuery("#bitc_message").fadeOut();
});

// delete all results
jQuery("#bitc_wrapper").on("click", "#bitc_a_light_delete_results", function (event) {

	var userConfirmed = confirm('Are you sure you want to proceed? This action cannot be undone.');
	  // Check if the user clicked "OK"
  if (userConfirmed) {
  	
		jQuery("#bitc_a_light_delete_results").fadeOut();

		jQuery.ajax({
			type: "POST",
			data: {
				action: "bitc_a_light_delete_results",
				nonce: jQuery("#bitc_about_options_nonce").val(),
			},
			url: ajaxurl,
			success: function (data) {
				jQuery("#bitc_a_light_delete_results").html("Results Deleted");
				jQuery("#bitc_tab_content").html("");
			},
			error: function () {
				jQuery("#bitc_a_light_delete_results").html("permission denied");
			},
			complete: function () {
				jQuery("#bitc_a_light_delete_results").fadeIn();
			},
		});
	}


});


