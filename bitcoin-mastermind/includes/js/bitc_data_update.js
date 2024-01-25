const bitc_T = {
	EL: {
		update: document.getElementById("bitc_tool_update_data_start"),
		log: document.getElementById("bitc_message_logs"),
	},
	VARS: {
		ATONCE: 5, // number to do at a time. Higher == faster, but might timeout your server
		quizzes: [],
		questions: [],
		nonce: document.getElementById("bitc_tools_nonce").value,
	},
	init: function () {
		if (bitc_T.EL.update != null) {
			bitc_T.EL.update.addEventListener("click", bitc_T.start);
		}
	},
	start: async function () {
		console.log("HDQ: Data update init");
		this.remove();
		let item = `<div class = "bitc_log_item">Upgrade process has begin... please do not leave this page</div>`;
		bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);

		let quizzes = this.getAttribute("data-quizzes");
		let questions = this.getAttribute("data-questions");
		bitc_T.VARS.quizzes = [0, quizzes];
		bitc_T.VARS.questions = [0, questions];

		await bitc_T.sendNextData();
	},
	sendNextData: async function () {
		if (bitc_T.VARS.quizzes[0] < bitc_T.VARS.quizzes[1]) {
			// update quizzes
			bitc_T.quizzes();
		} else if (bitc_T.VARS.questions[0] < bitc_T.VARS.questions[1]) {
			// update questions
			bitc_T.questions();
		} else {
			bitc_T.complete();
		}
	},
	quizzes: async function () {
		jQuery.ajax({
			type: "POST",
			data: {
				action: "bitc_tool_upgrade_quiz_data",
				atonce: bitc_T.VARS.ATONCE,
				quizzes: bitc_T.VARS.quizzes,
				nonce: bitc_T.VARS.nonce,
			},
			url: ajaxurl,
			success: function (data) {
				bitc_T.VARS.quizzes[0] += bitc_T.VARS.ATONCE;
				if (bitc_T.VARS.quizzes[0] > bitc_T.VARS.quizzes[1]) {
					bitc_T.VARS.quizzes[0] = bitc_T.VARS.quizzes[1];
				}
				if (data == "") {
					let item = `<div class = "bitc_log_item">Updated quizzes: ${bitc_T.VARS.quizzes[0]} / ${bitc_T.VARS.quizzes[1]}</div>`;
					bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);
				} else {
					let item = `<div class = "bitc_log_item">ERROR: There was an error updating one of your quizzes. Moving onto the next</div>`;
					bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);
				}

				if (bitc_T.VARS.quizzes[0] == bitc_T.VARS.quizzes[1]) {
					let item = `<div class = "bitc_log_item" style = "color:darkseagreen">COMPLETED UPGRADING QUIZZES. STARTING QUESTIONS NOW</div>`;
					bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);
				}
				setTimeout(bitc_T.sendNextData, 1000); // create delay so we don't send too many connection in short period;
			},
			error: function () {
				let item = `<div class = "bitc_log_item" style = "color:darkred">THERE WAS A SERVER ERROR UPDATING ONE OF YOUR QUIZZES</div>`;
				bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);
			},
		});
	},
	questions: async function () {
		jQuery.ajax({
			type: "POST",
			data: {
				action: "bitc_tool_upgrade_question_data",
				atonce: bitc_T.VARS.ATONCE,
				questions: bitc_T.VARS.questions,
				nonce: bitc_T.VARS.nonce,
			},
			url: ajaxurl,
			success: function (data) {
				console.log(data);
				bitc_T.VARS.questions[0] += bitc_T.VARS.ATONCE;
				if (bitc_T.VARS.questions[0] > bitc_T.VARS.questions[1]) {
					bitc_T.VARS.questions[0] = bitc_T.VARS.questions[1];
				}
				if (data == "") {
					let item = `<div class = "bitc_log_item">Updated questions: ${bitc_T.VARS.questions[0]} / ${bitc_T.VARS.questions[1]}</div>`;
					bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);
				} else {
					let item = `<div class = "bitc_log_item" style = "color:darkred">ERROR: There was an error updating one of your questions. Moving onto the next</div>`;
					bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);
				}
				setTimeout(bitc_T.sendNextData, 1000); // create delay so we don't send too many connection in short period;
			},
			error: function () {
				let item = `<div class = "bitc_log_item" style = "color:darkred">THERE WAS A SERVER ERROR UPDATING ONE OF YOUR QUESTIONS.</div>`;
				bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);
			},
		});
	},
	complete: function () {
		jQuery.ajax({
			type: "POST",
			data: {
				action: "bitc_tool_upgrade_question_data_complete",
				nonce: bitc_T.VARS.nonce,
			},
			url: ajaxurl,
			success: function (data) {
				console.log(data);
				if (data == "") {
					let item = `<div class = "bitc_log_item" style = "color:darkseagreen">UPGRADE HAS COMPLETED!<br/>It is now safe to leave this page.<br/><br/></div>`;
					bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);
				} else {
					let item = `<div class = "bitc_log_item" style = "color:darkred">THERE WAS A SERVER ERROR VERIFYING THE UPDATE</div>`;
					bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);
				}
			},
			error: function () {
				let item = `<div class = "bitc_log_item" style = "color:darkred">THERE WAS A SERVER ERROR VERIFYING THE UPDATE</div>`;
				bitc_T.EL.log.insertAdjacentHTML("afterbegin", item);
			},
		});
	},
};
bitc_T.init();
