const HDQ = {
	EL: {
		quizzes: document.getElementsByClassName("bitc_quiz"),
		results: document.getElementsByClassName("bitc_results_wrapper")[0],
		questions: document.getElementsByClassName("bitc_question"),
		next: document.getElementsByClassName("bitc_next"),
		finish: document.getElementsByClassName("bitc_finsh_button"),
		answers: document.getElementsByClassName("bitc_option"),
		loading: document.getElementsByClassName("bitc_loading_bar")[0],
		jPaginate: document.getElementsByClassName("bitc_jPaginate_button"),
	},
	VARS: {},
	init: async function () {
		console.log("Bitcoin Mastermind 1.8.12 Loaded");
		if (HDQ.EL.quizzes.length > 1) {
			for (let i = 0; i < HDQ.EL.quizzes.length; i++) {
				let html = `<p>Bitcoin Mastermind - WARNING: There is more than one quiz on this page. Due to the complexity of Bitcoin Mastermind, only one quiz should on a page at a time.</p>`;
				HDQ.EL.quizzes[i].insertAdjacentHTML("beforebegin", html);
			}
		}

		if (HDQ.VARS.timer.question === "yes" && HDQ.VARS.timer.max > 0) {
			HDQ.VARS.stop_reselect = "yes";
		}

		// when an answer has been made
		for (let i = 0; i < HDQ.EL.answers.length; i++) {
			HDQ.EL.answers[i].addEventListener("change", HDQ.validate.check);
		}

		// when finish button selected
		for (let i = 0; i < HDQ.EL.finish.length; i++) {
			HDQ.EL.finish[i].addEventListener("click", function () {
				HDQ.submit(this);
			});
		}

		// jPaginate
		for (let i = 0; i < HDQ.EL.jPaginate.length; i++) {
			HDQ.EL.jPaginate[i].addEventListener("click", HDQ.jPaginate);
		}

		// WP Pagination
		const nextButton = document.getElementsByClassName("bitc_next_page_button");
		for (let i = 0; i < nextButton.length; i++) {
			nextButton[i].addEventListener("click", function (e) {
				if (!HDQ.VARS.paginate) {
					e.preventDefault();
					HDQ.paginate(this);
				}
			});
		}

		// kb nav
		const buttons = document.getElementsByClassName("bitc_button");
		for (let i = 0; i < buttons.length; i++) {
			buttons[i].addEventListener("keyup", HDQ.keyUp);
		}

		if (HDQ.VARS.timer.max > 3) {
			if (HDQ.VARS.ads !== true) {
				const start = document.getElementsByClassName("bitc_quiz_start");
				for (let i = 0; i < start.length; i++) {
					start[i].addEventListener("click", HDQ.timer.paginate);
					start[i].addEventListener("click", HDQ.timer.init);
				}
			} else {
				HDQ.timer.init();
			}
		}

		if (!HDQ.VARS.legacy_scroll) {
			// create hidden div above quiz that we can use to offset scrolling
			const offsetDiv = `<div id = "bitc_offset_div" style = "width: 1px; height: 1px; position: relative; opactity: 0; pointer-events: none; user-select: none;z-index: 0; relative; top: -4rem; background-color:red">&nbsp;</div>`;
			document.getElementById("bitc_" + HDQ.VARS.id).insertAdjacentHTML("afterbegin", offsetDiv);
		}

		let init_actions = HDQ.VARS.init_actions;
		if (typeof init_actions != "undefined" && init_actions != null) {
			for (let i = 0; i < init_actions.length; i++) {
				console.log(init_actions[i]);
				if (typeof window[init_actions[i]] === "function") {
					await window[init_actions[i]]();
				}
			}
		}
	},
	keyUp: function (ev, el) {
		console.log(ev);
		if (typeof el === "undefined") {
			el = this;
		}
		if (ev.keyCode === 32) {
			el.click();
		}
	},
	timer: {
		init: function () {
			try {
				this.remove();
				let hdquiz_wrapper = document.getElementsByClassName("bitc_quiz")[0];

				if (hdquiz_wrapper.firstElementChild.classList.contains("bitc_results_wrapper")) {
					// results above
					let results_wrapper = hdquiz_wrapper.firstElementChild;
					let next_el = results_wrapper.nextSibling.nextSibling;
					if (next_el.classList.contains("bitc_jPaginate")) {
						next_el.getElementsByClassName("bitc_next_button")[0].click();
					}
				} else {
					// results below
					hdquiz_wrapper.firstElementChild.getElementsByClassName("bitc_next_button")[0].click();
				}
			} catch (e) {}
			let quizzes = document.getElementsByClassName("bitc_quiz");
			for (let i = 0; i < quizzes.length; i++) {
				quizzes[i].style.display = "block";
			}

			const html = `<div class = "bitc_timer"></div>`;
			document.body.insertAdjacentHTML("beforeend", html);

			HDQ.VARS.timer.active = true;
			if (HDQ.VARS.timer.question === "yes") {
				HDQ.timer.question.init();
			} else {
				HDQ.timer.quiz();
			}
		},
		quiz: function () {
			if (HDQ.VARS.timer.time > 0 && HDQ.VARS.timer.active == true) {
				let minutes = parseInt(HDQ.VARS.timer.time / 60);
				minutes = minutes < 10 ? "0" + minutes : minutes;
				let seconds = HDQ.VARS.timer.time % 60;
				seconds = seconds < 10 ? "0" + seconds : seconds;
				let t = minutes + ":" + seconds;
				jQuery(".bitc_timer").html(t);
				if (HDQ.VARS.timer.time > 10 && HDQ.VARS.timer.time < 30) {
					jQuery(".bitc_timer").addClass("bitc_timer_warning");
				} else if (HDQ.VARS.timer.time <= 10) {
					jQuery(".bitc_timer").removeClass("bitc_timer_warning");
					jQuery(".bitc_timer").addClass("bitc_timer_danger");
				}
				HDQ.VARS.timer.time = HDQ.VARS.timer.time - 1;
				setTimeout(HDQ.timer.quiz, 1000);
			} else {
				if (HDQ.VARS.timer.active == true) {
					// uh oh! Out of time
					jQuery(".bitc_timer").html("0");
					jQuery(".bitc_timer").removeClass("bitc_timer_danger");
					jQuery(".bitc_finsh_button").click(); // submit quiz for completion
					HDQ.VARS.timer.active = false;
				} else {
					// user finished in time
					jQuery(".bitc_timer").removeClass("bitc_timer_danger");
					jQuery(".bitc_timer").removeClass("bitc_timer_warning");
					jQuery(".bitc_timer").removeClass("bitc_timer_danger");
					jQuery(".bitc_timer").removeClass("bitc_timer_warning");
				}
			}
		},
		question: {
			init: async function () {
				for (let i = 0; i < HDQ.EL.answers.length; i++) {
					HDQ.EL.answers[i].disabled = true;
					HDQ.EL.answers[i].addEventListener("change", HDQ.timer.question.changed);
					let p = await HDQ.getParent(HDQ.EL.answers[i]);
					p.classList.add("bitc_disabled");
				}
				// reenable the first question answers
				let parent = await HDQ.getParent(HDQ.EL.answers[0]);
				parent.classList.add("bitc_active_question");
				let answers = parent.getElementsByClassName("bitc_option");
				p = await HDQ.getParent(answers[0]);
				p.classList.remove("bitc_disabled");

				for (let i = 0; i < answers.length; i++) {
					answers[i].disabled = false;
				}
				HDQ.timer.question.question();

				// hide jPagination until answer has been made
				for (let i = 0; i < HDQ.EL.jPaginate.length; i++) {
					HDQ.EL.jPaginate[i].style.display = "none";
				}
			},
			checkQuestion: async function (el) {
				if (el.classList.contains("bitc_question")) {
					let answers = el.getElementsByClassName("bitc_option");
					if (answers.length > 0) {
						let p = await HDQ.getParent(answers[0]);
						p.classList.remove("bitc_disabled");
						for (let i = 0; i < answers.length; i++) {
							answers[i].disabled = false;
						}
						el.classList.add("bitc_active_question");
						return "success";
					} else {
						// probably a question as title
						let next_question = el.nextSibling;
						return await HDQ.timer.question.checkQuestion(next_question);
					}
				} else {
					if (el.classList.contains("bitc_jPaginate")) {
						el.style.display = "block";
						el.firstChild.style.display = "block";
						el.firstChild.click();
						let next_question = el.nextSibling;
						return await HDQ.timer.question.checkQuestion(next_question);
					} else {
						return "complete";
					}
				}
			},
			changed: async function (ev, sap = false) {
				// reset timer
				let p = document.getElementsByClassName("bitc_active_question")[0];
				if (!p.classList.contains("bitc_question")) {
					p = HDQ.getParent(p);
				}

				// check question type
				let qt = p.getAttribute("data-type");
				if ((qt == "select_all_apply_text" && sap === false) || (qt == "select_all_apply_image" && sap === false)) {
					// does "next" button already exist?
					let n = p.getElementsByClassName("bitc_button");
					if (typeof n[0] == "undefined") {
						const html = `<div class="bitc_button" role="button" tabindex = "0" onkeyup="HDQ.keyUp(event, this)" onclick = "HDQ.timer.question.changed(this, true)" style = "display: flex; width: fit-content;">${HDQ.VARS.translations.next}</div>`;
						p.insertAdjacentHTML("beforeend", html);
					}
					return;
				} else if (qt == "select_all_apply_text" || qt == "select_all_apply_image") {
					let n = p.getElementsByClassName("bitc_button")[0];
					n.remove();
				}

				// reset timer
				HDQ.VARS.timer.time = HDQ.VARS.timer.max;

				// figure out what the next question is
				let next_question = p.nextSibling;
				let active_question = document.getElementsByClassName("bitc_active_question");
				if (active_question.length > 0) {
					active_question[0].classList.remove("bitc_active_question");
				}

				// check if question has custom time
				let t = parseInt(next_question.getAttribute("data-timer"));
				if (t > 0) {
					HDQ.VARS.timer.time = t;
				}
				let status = await HDQ.timer.question.checkQuestion(next_question);

				if (status === "complete") {
					// end the quiz
					HDQ.VARS.timer.active = false;
					jQuery(".bitc_finsh_button").click(); // submit quiz for completion
				} else {
					jQuery(".bitc_timer").removeClass("bitc_timer_danger");
					jQuery(".bitc_timer").removeClass("bitc_timer_warning");
				}
			},
			question: async function (isFirst = false) {
				if (HDQ.VARS.timer.time > 0 && HDQ.VARS.timer.active == true) {
					let minutes = parseInt(HDQ.VARS.timer.time / 60);
					minutes = minutes < 10 ? "0" + minutes : minutes;
					let seconds = HDQ.VARS.timer.time % 60;
					seconds = seconds < 10 ? "0" + seconds : seconds;
					let t = minutes + ":" + seconds;
					jQuery(".bitc_timer").html(t);
					if (HDQ.VARS.timer.time > 10 && HDQ.VARS.timer.time < 30) {
						jQuery(".bitc_timer").addClass("bitc_timer_warning");
					} else if (HDQ.VARS.timer.time <= 10) {
						jQuery(".bitc_timer").removeClass("bitc_timer_warning");
						jQuery(".bitc_timer").addClass("bitc_timer_danger");
					}
					HDQ.VARS.timer.time = HDQ.VARS.timer.time - 1;
					setTimeout(HDQ.timer.question.question, 1000);
				} else {
					if (HDQ.VARS.timer.active == true) {
						let active_question = document.getElementsByClassName("bitc_active_question");
						if (active_question.length > 0) {
							active_question = active_question[0];
							let answers = active_question.getElementsByClassName("bitc_option");
							for (let i = 0; i < answers.length; i++) {
								answers[i].disabled = true;
							}

							// figure out what the next question is
							let next_question = active_question.nextSibling;
							active_question.classList.remove("bitc_active_question");
							let status = await HDQ.timer.question.checkQuestion(next_question);

							if (status === "complete") {
								// end the quiz
								jQuery(".bitc_finsh_button").click(); // submit quiz for completion
							} else {
								// reset timer
								jQuery(".bitc_timer").html("0");
								HDQ.VARS.timer.time = HDQ.VARS.timer.max;

								jQuery(".bitc_timer").removeClass("bitc_timer_danger");
								jQuery(".bitc_timer").removeClass("bitc_timer_warning");

								setTimeout(HDQ.timer.question.question, 1000);
							}
						} else {
							console.warn("there is no active question?");
						}
					} else {
						console.log("quiz has completed");
					}
				}
			},
		},
	},
	validate: {
		all: async function () {
			for (let i = 0; i < HDQ.EL.answers.length; i++) {
				let t = await HDQ.validate.type(HDQ.EL.answers[i]);
				if (t != "") {
					let result = await HDQ.validate[t](HDQ.EL.answers[i]);
				}
			}
		},
		check: async function () {
			let t = await HDQ.validate.type(this);
			if (t != "" && t != "radio_multi") {
				if (HDQ.VARS.show_results_now === "yes" && HDQ.VARS.show_results === "yes") {
					let result = await HDQ.validate[t](this);
					HDQ.validate.extraText(result, this);
				}

				if (HDQ.VARS.stop_reselect === "yes") {
					await HDQ.validate.disable(this);
				} else {
					await HDQ.validate.checkToRadio(this);
				}
			} else {
				if (HDQ.VARS.show_results_now === "yes" && HDQ.VARS.show_results === "yes") {
					let result = await HDQ.validate[t](this, false);
					// HDQ.validate.extraText(result, this); // i just don't see a good way to do this
				}
			}
		},
		type: async function (el) {
			let t = el.getAttribute("data-type");
			if (t) {
				return t;
			} else {
				return "";
			}
		},
		checkToRadio(el) {
			let question = el.getAttribute("data-id");
			question = document.getElementById("bitc_question_" + question);
			let answers = question.querySelectorAll(".bitc_option");
			for (let i = 0; i < answers.length; i++) {
				if (answers[i] != el) {
					answers[i].checked = false;
				}
			}
		},
		disable: async function (el) {
			let question = el.getAttribute("data-id");
			question = document.getElementById("bitc_question_" + question);
			let answers = question.querySelectorAll(".bitc_option");
			for (let i = 0; i < answers.length; i++) {
				answers[i].disabled = true;
			}
		},
		extraText: async function (result, el) {
			if (HDQ.VARS.show_extra_text == "yes") {
				await showExtraText(el);
			} else {
				if (!result) {
					await showExtraText(el);
				}
			}

			async function showExtraText(el) {
				let question = el.getAttribute("data-id");
				question = document.getElementById("bitc_question_" + question);
				let extra_text = question.querySelector(".bitc_question_after_text");
				if (extra_text != null) {
					extra_text.style.display = "block";
				}
			}
		},
		text: async function (el) {
			function decodeHtml(html) {
				var txt = document.createElement("textarea");
				txt.innerHTML = html;
				return txt.value;
			}
			let value = el.value.toLocaleUpperCase().trim();
			let answers = el.getAttribute("data-answers");
			answers = decodeURIComponent(answers);
			answers = decodeHtml(answers);
			answers = JSON.parse(answers);

			for (let i = 0; i < answers.length; i++) {
				answers[i] = answers[i].toLocaleUpperCase();
			}

			let correct = isCorrect(answers);
			if (correct) {
				if (HDQ.VARS.show_results == "yes") {
					el.parentNode.classList.add("bitc_correct");
				}
			} else {
				if (HDQ.VARS.show_results == "yes") {
					el.parentNode.classList.add("bitc_wrong");
				}
				if (HDQ.VARS.mark_correct === "yes") {
					if (!el.parentNode.classList.contains("bitc_answered")) {
						let data = " - [" + answers[0] + "]";
						el.value = el.value + data;
						el.parentNode.classList.add("bitc_answered");
					}
				}
			}

			el.disabled = true;
			return correct;

			function isCorrect(answers) {
				let correct = false;
				// check for stemming
				for (let i = 0; i < answers.length; i++) {
					if (answers[i][answers[i].length - 1] == "*") {
						const a = answers[i].slice(0, -1);
						if (a === value || value.startsWith(a)) {
							correct = true;
						}
					}
				}
				if (answers.includes(value)) {
					correct = true;
				}
				return correct;
			}
		},
		radio: async function (el) {
			let correct = false;
			let row = el.parentNode.parentNode.parentNode;
			if (el.value == 1) {
				if (el.checked == true) {
					correct = true;
					if (HDQ.VARS.show_results == "yes") {
						row.classList.add("bitc_correct");
					}
				}
			} else {
				if (el.checked == true) {
					if (HDQ.VARS.show_results == "yes") {
						row.classList.add("bitc_wrong");
					}
				}
			}

			let question = el.getAttribute("data-id");
			question = document.getElementById("bitc_question_" + question);
			let answers = question.querySelectorAll(".bitc_option");
			for (let i = 0; i < answers.length; i++) {
				if (HDQ.VARS.mark_correct === "yes") {
					if (answers[i].value == 1) {
						row = answers[i].parentNode.parentNode.parentNode;
						row.classList.add("bitc_correct_not_selected");
					}
				}
				answers[i].disabled = true;
			}
			return correct;
		},
		image: async function (el) {
			let correct = false;
			let row = el.parentNode.parentNode.parentNode.parentNode;
			if (el.value == 1) {
				if (el.checked == true) {
					correct = true;
					if (HDQ.VARS.show_results == "yes") {
						row.classList.add("bitc_correct");
					}
				}
			} else {
				if (el.checked == true) {
					if (HDQ.VARS.show_results == "yes") {
						row.classList.add("bitc_wrong");
					}
				}
			}

			let question = el.getAttribute("data-id");
			question = document.getElementById("bitc_question_" + question);
			let answers = question.querySelectorAll(".bitc_option");
			for (let i = 0; i < answers.length; i++) {
				if (HDQ.VARS.mark_correct === "yes") {
					if (answers[i].value == 1) {
						row = answers[i].parentNode.parentNode.parentNode.parentNode;
						row.classList.add("bitc_correct_not_selected");
					}
				}
				answers[i].disabled = true;
			}
			return correct;
		},
		radio_multi: async function (el, show_results = true) {
			let correct = false;
			let row = el.parentNode.parentNode.parentNode;
			if (el.value == 1) {
				if (el.checked == true) {
					correct = true;
					if (HDQ.VARS.show_results == "yes") {
						row.classList.add("bitc_correct");
					}
				}
			} else {
				if (el.checked == true) {
					correct = false;
					if (HDQ.VARS.show_results == "yes") {
						row.classList.add("bitc_wrong");
					}
				}
			}

			let question = el.getAttribute("data-id");
			question = document.getElementById("bitc_question_" + question);
			let answers = question.querySelectorAll(".bitc_option");
			for (let i = 0; i < answers.length; i++) {
				if (HDQ.VARS.mark_correct === "yes" && show_results) {
					if (answers[i].value == 1) {
						row = answers[i].parentNode.parentNode.parentNode;
						row.classList.add("bitc_correct_not_selected");
					}
				}
				if (show_results) {
					answers[i].disabled = true;
				} else {
					if (!show_results) {
						if (answers[i].value == 0 && answers[i].checked) {
							// don't let users change a false answer
							HDQ.validate.radio_multi(el);
							HDQ.validate.extraText(false, el); // TODO: make better compat with the "always show extra text" feature
							break;
						} else if (HDQ.VARS.mark_correct && answers[i].value == 1 && answers[i].checked) {
							answers[i].disabled = true;
						}
					}
				}
			}
			return correct;
		},
	},
	getResult: {
		text_based: async function (answers) {
			let result = await HDQ.validate.text(answers[0]);
			HDQ.validate.extraText(result, answers[0]);
			return result;
		},
		multiple_choice_text: async function (answers) {
			for (let i = 0; i < answers.length; i++) {
				if (answers[i].checked == true) {
					let result = await HDQ.validate.radio(answers[i]);
					if (result) {
						HDQ.validate.extraText(true, answers[0]);
						return 1;
					}
				}
			}
			HDQ.validate.extraText(false, answers[0]);
			return 0;
		},
		multiple_choice_image: async function (answers) {
			for (let i = 0; i < answers.length; i++) {
				if (answers[i].checked == true) {
					let result = await HDQ.validate.image(answers[i]);
					if (result) {
						HDQ.validate.extraText(true, answers[0]);
						return 1;
					}
				}
			}
			HDQ.validate.extraText(false, answers[0]);
			return 0;
		},
		select_all_apply_text: async function (answers) {
			let results = [];
			for (let i = 0; i < answers.length; i++) {
				results.push({ checked: answers[i].checked, v: answers[i].value });
			}
			HDQ.validate.extraText(false, answers[0]);
			for (let i = 0; i < results.length; i++) {
				if (results[i].v == 1 && results[i].checked == false) {
					if (HDQ.VARS.mark_correct != "yes" && HDQ.VARS.show_results == "yes") {
						bitc_show_part_wrong(answers);
					}
					return 0;
					break;
				}
				if (results[i].v == 0 && results[i].checked == true) {
					if (HDQ.VARS.mark_correct != "yes" && HDQ.VARS.show_results == "yes") {
						bitc_show_part_wrong(answers);
					}
					return 0;
					break;
				}
			}
			return 1;

			function bitc_show_part_wrong(answers) {
				// if the user got part of the question right,
				// Visually show that even though the selected answer was correct,
				// the entire answer set is incorrect
				for (let i = 0; i < answers.length; i++) {
					if (answers[i].value == 1 && answers[i].checked == true) {
						answers[i].parentElement.parentElement.parentElement.classList.remove("bitc_correct");
						answers[i].parentElement.parentElement.parentElement.classList.add("bitc_correct_not_selected");
					}
				}
			}
		},
		select_all_apply_image: async function (answers) {
			let results = [];
			for (let i = 0; i < answers.length; i++) {
				results.push({ checked: answers[i].checked, v: answers[i].value });
			}
			HDQ.validate.extraText(false, answers[0]);
			for (let i = 0; i < results.length; i++) {
				if (results[i].v == 1 && results[i].checked == false) {
					if (HDQ.VARS.mark_correct != "yes" && HDQ.VARS.show_results == "yes") {
						bitc_show_part_wrong(answers);
					}
					return 0;
					break;
				}
				if (results[i].v == 0 && results[i].checked == true) {
					if (HDQ.VARS.mark_correct != "yes" && HDQ.VARS.show_results == "yes") {
						bitc_show_part_wrong(answers);
					}
					return 0;
					break;
				}
			}
			return 1;

			function bitc_show_part_wrong(answers) {
				// if the user got part of the question right,
				// Visually show that even though the selected answer was correct,
				// the entire answer set is incorrect
				for (let i = 0; i < answers.length; i++) {
					if (answers[i].value == 1 && answers[i].checked == true) {
						answers[i].parentElement.parentElement.parentElement.classList.remove("bitc_correct");
						answers[i].parentElement.parentElement.parentElement.classList.add("bitc_correct_not_selected");
					}
				}
			}
		},
	},
	calculateScore: async function () {
		let total_score = 0;
		let total_questions = 0;
		let cs = document.getElementById("bitc_current_score");
		let tq = document.getElementById("bitc_total_questions");
		if (cs != null && tq != null) {
			total_score = parseInt(cs.value);
			total_questions = parseInt(tq.value);
		}
		total_questions += parseInt(HDQ.EL.questions.length);

		for (let i = 0; i < HDQ.EL.questions.length; i++) {
			let t = HDQ.EL.questions[i].getAttribute("data-type");
			let answers = HDQ.EL.questions[i].querySelectorAll(".bitc_option");
			if (answers.length > 0) {
				total_score += await HDQ.getResult[t](answers);
			} else {
				total_questions -= 1;
			}
		}

		HDQ.VARS.bitc_score = [parseInt(total_score), parseInt(total_questions)];
		return HDQ.VARS.bitc_score;
	},
	submit: async function (el) {
		if (el === null || typeof el.getAttribute("id") === "undfined") {
			el = document.getElementsByClassName("bitc_finsh_button")[0];
		}

		if (el.classList.contains("bitc_complete")) {
			return;
		}

		HDQ.VARS.timer.active = false;

		// start visual feedback
		let quiz_ID = el.getAttribute("data-id");
		el.innerHTML = "...";
		el.classList.add("bitc_complete");
		jQuery(el).fadeOut("slow");
		HDQ.EL.loading.classList.add("bitc_animate");

		// hide all buttons
		jQuery(".bitc_jPaginate_button").fadeOut();

		// show all questions in case of jPagination
		if (HDQ.VARS.hide_questions !== "yes") {
			jQuery(".bitc_question").fadeIn();
		}

		// validate all answers
		await HDQ.validate.all();
		// figure out the score
		let score = await HDQ.calculateScore();
		let data = score[0] + " / " + score[1];

		// update results section
		if (jQuery(".bitc_results_inner .bitc_result .bitc_result_percent")[0]) {
			let bitc_results_percent = (parseFloat(HDQ.VARS.bitc_score[0]) / parseFloat(HDQ.VARS.bitc_score[1])) * 100;
			bitc_results_percent = Math.ceil(bitc_results_percent);
			data = '<span class = "bitc_result_fraction">' + data + '</span> - <span class = "bitc_result_percent">' + bitc_results_percent + "%</span>";
		}
		jQuery(".bitc_results_inner .bitc_result").html(data);

		let pass_percent = 0;
		pass_percent = score[0] / score[1];
		pass_percent = pass_percent * 100;
		if (pass_percent >= HDQ.VARS.pass_percent) {
			jQuery(".bitc_result_pass").show();
		} else {
			jQuery(".bitc_result_fail").show();
		}

		if (HDQ.VARS.share_results === "yes") {
			HDQ.share();
		}
		jQuery(".bitc_results_wrapper").fadeIn();

		if (typeof HDQ.VARS.submit_actions != undefined && HDQ.VARS.submit_actions != null) {
			for (let i = 0; i < HDQ.VARS.submit_actions.length; i++) {
				await HDQ.submitAction(HDQ.VARS.submit_actions[i]);
			}
		}

		if (HDQ.VARS.hide_questions === "yes") {
			jQuery(".bitc_question").fadeOut();
		}

		setTimeout(function () {
			HDQ.scroll();
		}, 1000);
	},
	submitAction: async function (action) {
		console.log("onSumbit action: " + action);
		let data = {};
		// if this is also a JS function, store data
		if (typeof window[action] !== "undefined") {
			let extra = {};
			data[action] = await window[action]();
			data.extra = data[action]; // for legacy
		}
		// small delay since this isn't syncronous
		setTimeout(function () {
			data.quizID = HDQ.VARS.id;
			data.score = HDQ.VARS.bitc_score;
			// send data to admin-ajax
			console.log(action);
			console.log(data);
			jQuery.ajax({
				type: "POST",
				data: {
					action: action,
					data: data,
				},
				url: HDQ.VARS.ajax,
				success: function (res) {
					console.log(res);
				},
			});
		}, 100);
	},
	share: function () {
		function create_social_share() {
			create_twitter();
			create_webshare();

			function create_webshare() {
				const el = document.getElementsByClassName("bitc_share_other")[0];
				if (el && typeof el !== "undefined") {
					try {
						if (!navigator.canShare) {
							el.remove();
						}
					} catch (err) {
						el.remove();
					}

					el.addEventListener("click", async function () {
						let text = HDQ.VARS.share_text;
						let score = HDQ.VARS.bitc_score[0] + "/" + HDQ.VARS.bitc_score[1];
						text = text.replaceAll("%score%", score);
						text = text.replaceAll("%quiz%", HDQ.VARS.name);

						const data = {
							title: "Bitcoin Mastermind",
							text: text,
							url: HDQ.VARS.permalink,
						};

						try {
							await navigator.share(data);
						} catch (err) {
							console.warn(err);
						}
					});
				}
			}

			function create_twitter() {
				let baseURL = "https://twitter.com/intent/tweet";
				let text = HDQ.VARS.share_text;
				let score = HDQ.VARS.bitc_score[0] + "/" + HDQ.VARS.bitc_score[1];
				text = text.replaceAll("%score%", score);
				text = text.replaceAll("%quiz%", HDQ.VARS.name);

				if (HDQ.VARS.twitter != "") {
					baseURL += "?screen_name=" + HDQ.VARS.twitter;
				} else {
					baseURL += "?";
				}
				text = "&text=" + encodeURI(text);
				let url = "&url=" + encodeURI(HDQ.VARS.permalink);
				let hashtags = "&hashtags=hdquiz";

				let shareLink = baseURL + text + url + hashtags;
				jQuery(".bitc_twitter").attr("href", shareLink);
			}
		}
		create_social_share();
	},
	jPaginate: function () {
		let bitc_form_id = jQuery(this).attr("data-id");
		jQuery(".bitc_jPaginate .bitc_next_button").removeClass("bitc_next_selected");
		jQuery(this).addClass("bitc_next_selected");

		jQuery("#bitc_" + bitc_form_id + " .bitc_jPaginate:visible")
			.prevAll("#bitc_" + bitc_form_id + " .bitc_question")
			.hide();

		let bitc_class_styles = ["layout_left", "layout_left_full", "layout_right", "layout_right_full"];
		let style = "block";
		let q_style = document.getElementById("bitc_" + bitc_form_id).getElementsByClassName("bitc_quiz")[0].classList;
		let set_style = [];
		for (let i = 0; i < q_style.length; i++) {
			set_style.push(q_style[i]);
		}
		for (let i = 0; i < bitc_class_styles.length; i++) {
			if (set_style.includes(bitc_class_styles[i])) {
				style = "grid";
			}
		}

		jQuery("#bitc_" + bitc_form_id + " .bitc_jPaginate:eq(" + parseInt(HDQ.VARS.jPage) + ")")
			.nextUntil("#bitc_" + bitc_form_id + " .bitc_jPaginate ")
			.show()
			.css("display", style);
		jQuery(".bitc_results_wrapper").hide(); // in case the results are below the quiz
		HDQ.VARS.jPage = parseInt(HDQ.VARS.jPage + 1);

		if (HDQ.VARS.jPage === HDQ.EL.jPaginate.length) {
			jQuery(".bitc_finsh_button").removeClass("bitc_hidden");
		}

		jQuery(this).parent().hide();

		jQuery("#bitc_" + bitc_form_id + " .bitc_jPaginate:eq(" + parseInt(HDQ.VARS.jPage) + ")").show();

		const results_wrapper = jQuery(".bitc_question:visible");
		if (results_wrapper.length == 0) {
			return;
		}
		setTimeout(function () {
			if (!HDQ.VARS.legacy_scroll) {
				// results_wrapper[0].scrollIntoView({ behavior: "smooth", block: "start", inline: "nearest" });
				document.getElementById("bitc_offset_div").scrollIntoView({
					behavior: "smooth",
					block: "start",
					inline: "nearest",
				});
			} else {
				HDQ.scroll_legacy.question();
			}
		}, 100);
	},
	paginate: async function (el) {
		// get load values
		let score = await HDQ.calculateScore();
		console.log(score);
		let href = el.getAttribute("href");
		href = href + score[0] + "&totalQuestions=" + score[1];
		el.setAttribute("href", href);
		HDQ.VARS.paginate = true;
		el.click();
	},
	get_quiz_parent_container: async function (element, includeHidden) {
		var style = getComputedStyle(element);
		var excludeStaticParent = style.position === "absolute";
		var overflowRegex = includeHidden ? /(auto|scroll|hidden)/ : /(auto|scroll)/;

		if (style.position === "fixed") return document.body;
		for (var parent = element; (parent = parent.parentElement); ) {
			style = getComputedStyle(parent);
			if (excludeStaticParent && style.position === "static") {
				continue;
			}
			if (overflowRegex.test(style.overflow + style.overflowY + style.overflowX)) return parent;
		}
		return document.body;
	},
	scroll_legacy: {
		results: function () {
			// this is super not accurate, but covers most themes.
			setTimeout(async function () {
				let bitc_quiz_container = document.querySelector("#bitc_" + HDQ.VARS.id);
				bitc_quiz_container = jQuery(await HDQ.get_quiz_parent_container(bitc_quiz_container));
				console.log("container:");
				console.log(bitc_quiz_container);

				if (bitc_quiz_container[0].tagName === "DIV") {
					bitc_top = jQuery(bitc_quiz_container).scrollTop() + jQuery(".bitc_results_wrapper").offset().top - jQuery(".bitc_results_wrapper").height() / 2 - 100;
					console.log("bitc_top: " + bitc_top);
					jQuery(bitc_quiz_container).animate(
						{
							scrollTop: bitc_top,
						},
						550
					);
					jQuery("html,body").animate(
						{
							scrollTop: bitc_top,
						},
						550
					);
				} else {
					let overflowH = jQuery("html").css("overflow");
					let overflowB = jQuery("body").css("overflow");
					let rest = false;
					if (overflowH.indexOf("hidden") >= 0 || overflowB.indexOf("hidden") >= 0) {
						rest = true;
					}

					jQuery("html,body").css("overflow", "initial");

					jQuery("html,body").animate(
						{
							scrollTop: jQuery(".bitc_results_wrapper").offset().top - 100,
						},
						550
					);

					if (rest) {
						setTimeout(function () {
							jQuery("html").css("overflow", overflowH);
							jQuery("body").css("overflow", overflowB);
						}, 550);
					}
				}
			}, 50);
		},
		question: function () {
			setTimeout(async function () {
				let bitc_quiz_container = document.querySelector("#bitc_" + HDQ.VARS.id);
				bitc_quiz_container = jQuery(await HDQ.get_quiz_parent_container(bitc_quiz_container));

				if (bitc_quiz_container[0].tagName === "DIV") {
					bitc_top = jQuery(bitc_quiz_container).scrollTop() + jQuery(".bitc_question:visible").offset().top - jQuery(".bitc_question:visible").height() / 2 - 100;
					jQuery(bitc_quiz_container).animate(
						{
							scrollTop: bitc_top,
						},
						550
					);
				} else {
					let overflowH = jQuery("html").css("overflow");
					let overflowB = jQuery("body").css("overflow");
					let rest = false;
					if (overflowH.indexOf("hidden") >= 0 || overflowB.indexOf("hidden") >= 0) {
						rest = true;
					}

					jQuery("html,body").css("overflow", "initial");

					jQuery("html,body").animate(
						{
							scrollTop: jQuery(".bitc_question:visible").offset().top - 100,
						},
						550
					);

					if (rest) {
						setTimeout(function () {
							jQuery("html").css("overflow", overflowH);
							jQuery("body").css("overflow", overflowB);
						}, 550);
					}
				}
			}, 50);
		},
	},
	scroll: function () {
		setTimeout(function () {
			if (HDQ.VARS.results_position === "above" && !HDQ.VARS.legacy_scroll) {
				document.getElementById("bitc_offset_div").scrollIntoView({
					behavior: "smooth",
					block: "start",
					inline: "nearest",
				});
			} else {
				const results_wrapper = document.getElementsByClassName("bitc_results_wrapper")[0];
				results_wrapper.scrollIntoView({
					behavior: "smooth",
					block: "center",
					inline: "nearest",
				});
			}
		}, 300);
	},
	// gets the parent question of an element
	getParent: async function (el) {
		let p = el.parentNode;
		if (p.classList.contains("bitc_question")) {
			return p;
		} else {
			p = HDQ.getParent(p);
		}
		return p;
	},
};

let bitc_locals = {};
if (typeof bitc_local_vars != "undefined") {
	bitc_locals = JSON.parse(bitc_local_vars);
	async function bitc_INIT() {
		// set init vars
		async function hdqSetInitVars() {
			HDQ.VARS = {
				ajax: bitc_locals.bitc_ajax,
				featured_image: bitc_locals.bitc_featured_image,
				pass_percent: bitc_locals.bitc_pass_percent,
				id: bitc_locals.bitc_quiz_id,
				name: bitc_locals.bitc_quiz_name,
				permalink: bitc_locals.bitc_quiz_permalink,
				mark_correct: bitc_locals.bitc_results_correct,
				hide_questions: bitc_locals.bitc_hide_questions,
				share_results: bitc_locals.bitc_share_results,
				show_extra_text: bitc_locals.bitc_show_extra_text,
				show_results: bitc_locals.bitc_show_results,
				show_results_now: bitc_locals.bitc_show_results_now,
				results_position: bitc_locals.bitc_results_position,
				stop_reselect: bitc_locals.bitc_stop_answer_reselect,
				submit_actions: bitc_locals.bitc_submit,
				init_actions: bitc_locals.bitc_init,
				timer: {
					time: bitc_locals.bitc_timer,
					max: bitc_locals.bitc_timer,
					question: bitc_locals.bitc_timer_question,
					active: false,
				},
				twitter: bitc_locals.bitc_twitter_handle,
				ads: bitc_locals.bitc_use_ads,
				bitc_score: [],
				jPage: 0,
				paginate: false,
				legacy_scroll: bitc_locals.bitc_legacy_scroll,
				translations: bitc_locals.bitc_translations,
				share_text: bitc_locals.bitc_share_text,
			};
		}
		await hdqSetInitVars();
		HDQ.init();
	}
	bitc_INIT();
}

// TODO: check to see if this integration still works well
/* FB APP - Only used if APP ID was provided */
jQuery("#bitc_fb_sharer").on("click", function () {
	let bitc_score = jQuery(".bitc_result").text();
	let text = HDQ.VARS.share_text;
	text = text.replaceAll("%score%", bitc_score);
	text = text.replaceAll("%quiz%", HDQ.VARS.name);
	FB.ui(
		{
			method: "share",
			href: HDQ.VARS.permalink,
			hashtag: "#bitcoin-mastermind",
			quote: text, // Note: It looks like Meta depricated sending custom text altogether :(
		},
		function (res) {
			console.log(res);
		}
	);
});

