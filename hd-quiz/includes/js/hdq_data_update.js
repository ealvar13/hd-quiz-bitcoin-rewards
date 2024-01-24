const HDQ_T = {
	EL: {
		update: document.getElementById("hdq_tool_update_data_start"),
		log: document.getElementById("hdq_message_logs"),
	},
	VARS: {
		ATONCE: 5, // number to do at a time. Higher == faster, but might timeout your server
		quizzes: [],
		questions: [],
		nonce: document.getElementById("hdq_tools_nonce").value,
	},
	init: function () {
		if (HDQ_T.EL.update != null) {
			HDQ_T.EL.update.addEventListener("click", HDQ_T.start);
		}
	},
	start: async function () {
		console.log("HDQ: Data update init");
		this.remove();
		let item = `<div class = "hdq_log_item">Upgrade process has begin... please do not leave this page</div>`;
		HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);

		let quizzes = this.getAttribute("data-quizzes");
		let questions = this.getAttribute("data-questions");
		HDQ_T.VARS.quizzes = [0, quizzes];
		HDQ_T.VARS.questions = [0, questions];

		await HDQ_T.sendNextData();
	},
	sendNextData: async function () {
		if (HDQ_T.VARS.quizzes[0] < HDQ_T.VARS.quizzes[1]) {
			// update quizzes
			HDQ_T.quizzes();
		} else if (HDQ_T.VARS.questions[0] < HDQ_T.VARS.questions[1]) {
			// update questions
			HDQ_T.questions();
		} else {
			HDQ_T.complete();
		}
	},
	quizzes: async function () {
		jQuery.ajax({
			type: "POST",
			data: {
				action: "hdq_tool_upgrade_quiz_data",
				atonce: HDQ_T.VARS.ATONCE,
				quizzes: HDQ_T.VARS.quizzes,
				nonce: HDQ_T.VARS.nonce,
			},
			url: ajaxurl,
			success: function (data) {
				HDQ_T.VARS.quizzes[0] += HDQ_T.VARS.ATONCE;
				if (HDQ_T.VARS.quizzes[0] > HDQ_T.VARS.quizzes[1]) {
					HDQ_T.VARS.quizzes[0] = HDQ_T.VARS.quizzes[1];
				}
				if (data == "") {
					let item = `<div class = "hdq_log_item">Updated quizzes: ${HDQ_T.VARS.quizzes[0]} / ${HDQ_T.VARS.quizzes[1]}</div>`;
					HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);
				} else {
					let item = `<div class = "hdq_log_item">ERROR: There was an error updating one of your quizzes. Moving onto the next</div>`;
					HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);
				}

				if (HDQ_T.VARS.quizzes[0] == HDQ_T.VARS.quizzes[1]) {
					let item = `<div class = "hdq_log_item" style = "color:darkseagreen">COMPLETED UPGRADING QUIZZES. STARTING QUESTIONS NOW</div>`;
					HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);
				}
				setTimeout(HDQ_T.sendNextData, 1000); // create delay so we don't send too many connection in short period;
			},
			error: function () {
				let item = `<div class = "hdq_log_item" style = "color:darkred">THERE WAS A SERVER ERROR UPDATING ONE OF YOUR QUIZZES</div>`;
				HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);
			},
		});
	},
	questions: async function () {
		jQuery.ajax({
			type: "POST",
			data: {
				action: "hdq_tool_upgrade_question_data",
				atonce: HDQ_T.VARS.ATONCE,
				questions: HDQ_T.VARS.questions,
				nonce: HDQ_T.VARS.nonce,
			},
			url: ajaxurl,
			success: function (data) {
				console.log(data);
				HDQ_T.VARS.questions[0] += HDQ_T.VARS.ATONCE;
				if (HDQ_T.VARS.questions[0] > HDQ_T.VARS.questions[1]) {
					HDQ_T.VARS.questions[0] = HDQ_T.VARS.questions[1];
				}
				if (data == "") {
					let item = `<div class = "hdq_log_item">Updated questions: ${HDQ_T.VARS.questions[0]} / ${HDQ_T.VARS.questions[1]}</div>`;
					HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);
				} else {
					let item = `<div class = "hdq_log_item" style = "color:darkred">ERROR: There was an error updating one of your questions. Moving onto the next</div>`;
					HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);
				}
				setTimeout(HDQ_T.sendNextData, 1000); // create delay so we don't send too many connection in short period;
			},
			error: function () {
				let item = `<div class = "hdq_log_item" style = "color:darkred">THERE WAS A SERVER ERROR UPDATING ONE OF YOUR QUESTIONS.</div>`;
				HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);
			},
		});
	},
	complete: function () {
		jQuery.ajax({
			type: "POST",
			data: {
				action: "hdq_tool_upgrade_question_data_complete",
				nonce: HDQ_T.VARS.nonce,
			},
			url: ajaxurl,
			success: function (data) {
				console.log(data);
				if (data == "") {
					let item = `<div class = "hdq_log_item" style = "color:darkseagreen">UPGRADE HAS COMPLETED!<br/>It is now safe to leave this page.<br/><br/></div>`;
					HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);
				} else {
					let item = `<div class = "hdq_log_item" style = "color:darkred">THERE WAS A SERVER ERROR VERIFYING THE UPDATE</div>`;
					HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);
				}
			},
			error: function () {
				let item = `<div class = "hdq_log_item" style = "color:darkred">THERE WAS A SERVER ERROR VERIFYING THE UPDATE</div>`;
				HDQ_T.EL.log.insertAdjacentHTML("afterbegin", item);
			},
		});
	},
};
HDQ_T.init();
