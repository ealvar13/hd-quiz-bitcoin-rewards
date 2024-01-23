/*
    HD Quiz Save Results Light admin script
    * some of these functions are currently not needed, but I
      will probably still use them later
*/

window.addEventListener("load", (event) => {
	console.log("HD Quiz Save Results Light INIT");
	hdq_a_light_start();
});

function hdq_a_light_start() {
	hdq_a_light_load_active_tab();
}

// show the default tab on load
function hdq_a_light_load_active_tab() {
	var activeTab = jQuery("#hdq_tabs .hdq_active_tab").attr("data-hdq-content");
	jQuery("#" + activeTab).addClass("hdq_tab_active");
	jQuery(".hdq_tab_active").slideDown(500);
}

jQuery(".hdq_accordion h3").click(function () {
	jQuery(this).next("div").toggle(600);
});

/* Tab navigation
------------------------------------------------------- */
jQuery("#hdq_form_wrapper").on("click", "#hdq_tabs li", function (event) {
	jQuery("#hdq_tabs li").removeClass("hdq_active_tab");
	jQuery(this).addClass("hdq_active_tab");
	var hdqContent = jQuery(this).attr("data-hdq-content");
	jQuery(".hdq_tab_active").fadeOut();
	jQuery(".hdq_tab").removeClass("hdq_tab_active");
	jQuery("#" + hdqContent)
		.delay(250)
		.fadeIn();
	jQuery("#" + hdqContent).addClass("hdq_tab_active");
});

function hdq_a_light_scroll_to_top() {
	jQuery("html").animate(
		{
			scrollTop: 0,
		},
		"slow"
	);
}

// start loading stuff
function hdq_a_light_start_load() {
	jQuery("#hdq_message").fadeOut();
	jQuery("#hdq_loading ").fadeIn();
}
// after stuff has loaded
function hdq_a_light_after_load(editor = false) {
	jQuery("#hdq_loading ").delay(600).fadeOut();
	hdq_load_active_tab();
	hdq_scroll_to_top();
}

// show message box
function hdq_a_light_show_message(message) {
	jQuery("#hdq_message").html(message);
	jQuery("#hdq_message").fadeIn();
}

// hide message
jQuery("#hdq_wrapper").on("click", "#hdq_message", function (event) {
	jQuery("#hdq_message").fadeOut();
});

// delete all results
jQuery("#hdq_wrapper").on("click", "#hdq_a_light_delete_results", function (event) {
	jQuery("#hdq_a_light_delete_results").fadeOut();

	jQuery.ajax({
		type: "POST",
		data: {
			action: "hdq_a_light_delete_results",
			nonce: jQuery("#hdq_about_options_nonce").val(),
		},
		url: ajaxurl,
		success: function (data) {
			jQuery("#hdq_a_light_delete_results").html("results deleted");
			jQuery("#hdq_tab_content").html("");
		},
		error: function () {
			jQuery("#hdq_a_light_delete_results").html("permission denied");
		},
		complete: function () {
			jQuery("#hdq_a_light_delete_results").fadeIn();
		},
	});
});

/* Export Table as CSV */
function hdq_a_light_export() {
	let csv = [];
	let rows = document.querySelectorAll("table tr");

	for (var i = 0; i < rows.length; i++) {
		let row = [],
			cols = rows[i].querySelectorAll(".hdq_a_light_table td, .hdq_a_light_table th");

		for (var j = 0; j < cols.length; j++) row.push(cols[j].innerText);

		csv.push(row.join(","));
	}
	hdq_a_light_download_csv(csv);

	function hdq_a_light_download_csv(csv) {
		console.log(csv);
		csv = csv.join("\n");
		console.log(csv);
		let csvFile = new Blob([csv], { type: "text/csv" });
		let downloadLink = document.createElement("a");
		downloadLink.download = "hdq_results.csv";
		downloadLink.href = window.URL.createObjectURL(csvFile);
		downloadLink.innerHTML = "download export";
		document.getElementById("hdq_a_light_export_csv_wrap").appendChild(downloadLink);
	}
}
document.getElementById("hdq_a_light_export_results").addEventListener("click", hdq_a_light_export);
