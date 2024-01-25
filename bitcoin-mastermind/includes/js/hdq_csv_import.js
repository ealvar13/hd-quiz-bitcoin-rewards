const HDQ = {
	EL: {
		upload: document.getElementById("hdq_csv_file_upload"),
		begin: document.getElementById("hdq_start_csv_upload"),
		log: document.getElementById("hdq_message_logs"),
	},
	VARS: {
		file: null,
		nonce: document.getElementById("hdq_tools_nonce").value,
		current: 0,
		total: 0,
	},
	init: function () {
		console.log("HDQ: Importer loaded");
		if (HDQ.EL.upload != null) {
			HDQ.EL.upload.addEventListener("change", function (e) {
				HDQ.VARS.file = this.files[0];
				let filename = HDQ.EL.upload.value.split("\\").pop();
				jQuery(".hdq_file_label").html(filename);
			});
		}
	},
	uploadCSV: function () {
		let upload = new Upload(HDQ.VARS.file);
		upload.doUpload();
	},
	parseNext: function (path, total = null) {
		HDQ.VARS.current = parseInt(HDQ.VARS.current) + 1;
		if (total != null) {
			HDQ.VARS.total = parseInt(total);
		}

		let p = path;
		jQuery.ajax({
			type: "POST",
			data: {
				action: "hdq_parse_csv_data",
				nonce: HDQ.VARS.nonce,
				start: HDQ.VARS.current,
				path: path,
			},
			url: ajaxurl,
			success: function (data) {
				if (HDQ.VARS.current >= HDQ.VARS.total) {
					HDQ.VARS.current = HDQ.VARS.total;
				}
				if (HDQ.VARS.current != HDQ.VARS.total) {
					let item = `<div class = "hdq_log_item">added ${HDQ.VARS.current} / ${HDQ.VARS.total} questions</div>`;
					HDQ.EL.log.insertAdjacentHTML("afterbegin", item);
					setTimeout(function () {
						HDQ.parseNext(p);
					}, 2000); // delay to stop overloading slow servers
				} else {
					let item = `<div class = "hdq_log_item" style = "color:darkseagreen">ALL QUESTIONS HAVE BEEN ADDED<br/><br/>added ${HDQ.VARS.total} / ${HDQ.VARS.total} questions</div>`;
					HDQ.EL.log.insertAdjacentHTML("afterbegin", item);
				}
			},
			error: function () {
				let item = `<div class = "hdq_log_item" style = "color:darkred">THERE WAS A SERVER ERROR ADDING ONE OF YOUR QUIZZES</div>`;
				HDQ.EL.log.insertAdjacentHTML("afterbegin", item);
			},
		});
	},
};
HDQ.init();
