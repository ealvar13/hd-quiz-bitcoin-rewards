const HDQ = {
	EL: {
		quizzes: document.getElementsByClassName("hdq_quiz"),
		results: document.getElementsByClassName("hdq_results_wrapper")[0],
		questions: document.getElementsByClassName("hdq_question"),
		next: document.getElementsByClassName("hdq_next"),
		finish: document.getElementsByClassName("hdq_finsh_button"),
		answers: document.getElementsByClassName("hdq_option"),
		loading: document.getElementsByClassName("hdq_loading_bar")[0],
		jPaginate: document.getElementsByClassName("hdq_jPaginate_button"),
	},
	VARS: {},
	init: async function () {
		console.log("HD Quiz 1.8.12 Loaded");
		if (HDQ.EL.quizzes.length > 1) {
			for (let i = 0; i < HDQ.EL.quizzes.length; i++) {
				let html = `<p>HD QUIZ - WARNING: There is more than one quiz on this page. Due to the complexity of HD Quiz, only one quiz should on a page at a time.</p>`;
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
		const nextButton = document.getElementsByClassName("hdq_next_page_button");
		for (let i = 0; i < nextButton.length; i++) {
			nextButton[i].addEventListener("click", function (e) {
				if (!HDQ.VARS.paginate) {
					e.preventDefault();
					HDQ.paginate(this);
				}
			});
		}

		// kb nav
		const buttons = document.getElementsByClassName("hdq_button");
		for (let i = 0; i < buttons.length; i++) {
			buttons[i].addEventListener("keyup", HDQ.keyUp);
		}

		if (HDQ.VARS.timer.max > 3) {
			if (HDQ.VARS.ads !== true) {
				const start = document.getElementsByClassName("hdq_quiz_start");
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
			const offsetDiv = `<div id = "hdq_offset_div" style = "width: 1px; height: 1px; position: relative; opactity: 0; pointer-events: none; user-select: none;z-index: 0; relative; top: -4rem; background-color:red">&nbsp;</div>`;
			document.getElementById("hdq_" + HDQ.VARS.id).insertAdjacentHTML("afterbegin", offsetDiv);
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
				let hdquiz_wrapper = document.getElementsByClassName("hdq_quiz")[0];

				if (hdquiz_wrapper.firstElementChild.classList.contains("hdq_results_wrapper")) {
					// results above
					let results_wrapper = hdquiz_wrapper.firstElementChild;
					let next_el = results_wrapper.nextSibling.nextSibling;
					if (next_el.classList.contains("hdq_jPaginate")) {
						next_el.getElementsByClassName("hdq_next_button")[0].click();
					}
				} else {
					// results below
					hdquiz_wrapper.firstElementChild.getElementsByClassName("hdq_next_button")[0].click();
				}
			} catch (e) {}
			let quizzes = document.getElementsByClassName("hdq_quiz");
			for (let i = 0; i < quizzes.length; i++) {
				quizzes[i].style.display = "block";
			}

			const html = `<div class = "hdq_timer"></div>`;
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
				jQuery(".hdq_timer").html(t);
				if (HDQ.VARS.timer.time > 10 && HDQ.VARS.timer.time < 30) {
					jQuery(".hdq_timer").addClass("hdq_timer_warning");
				} else if (HDQ.VARS.timer.time <= 10) {
					jQuery(".hdq_timer").removeClass("hdq_timer_warning");
					jQuery(".hdq_timer").addClass("hdq_timer_danger");
				}
				HDQ.VARS.timer.time = HDQ.VARS.timer.time - 1;
				setTimeout(HDQ.timer.quiz, 1000);
			} else {
				if (HDQ.VARS.timer.active == true) {
					// uh oh! Out of time
					jQuery(".hdq_timer").html("0");
					jQuery(".hdq_timer").removeClass("hdq_timer_danger");
					jQuery(".hdq_finsh_button").click(); // submit quiz for completion
					HDQ.VARS.timer.active = false;
				} else {
					// user finished in time
					jQuery(".hdq_timer").removeClass("hdq_timer_danger");
					jQuery(".hdq_timer").removeClass("hdq_timer_warning");
					jQuery(".hdq_timer").removeClass("hdq_timer_danger");
					jQuery(".hdq_timer").removeClass("hdq_timer_warning");
				}
			}
		},
		question: {
			init: async function () {
				for (let i = 0; i < HDQ.EL.answers.length; i++) {
					HDQ.EL.answers[i].disabled = true;
					HDQ.EL.answers[i].addEventListener("change", HDQ.timer.question.changed);
					let p = await HDQ.getParent(HDQ.EL.answers[i]);
					p.classList.add("hdq_disabled");
				}
				// reenable the first question answers
				let parent = await HDQ.getParent(HDQ.EL.answers[0]);
				parent.classList.add("hdq_active_question");
				let answers = parent.getElementsByClassName("hdq_option");
				p = await HDQ.getParent(answers[0]);
				p.classList.remove("hdq_disabled");

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
				if (el.classList.contains("hdq_question")) {
					let answers = el.getElementsByClassName("hdq_option");
					if (answers.length > 0) {
						let p = await HDQ.getParent(answers[0]);
						p.classList.remove("hdq_disabled");
						for (let i = 0; i < answers.length; i++) {
							answers[i].disabled = false;
						}
						el.classList.add("hdq_active_question");
						return "success";
					} else {
						// probably a question as title
						let next_question = el.nextSibling;
						return await HDQ.timer.question.checkQuestion(next_question);
					}
				} else {
					if (el.classList.contains("hdq_jPaginate")) {
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
				let p = document.getElementsByClassName("hdq_active_question")[0];
				if (!p.classList.contains("hdq_question")) {
					p = HDQ.getParent(p);
				}

				// check question type
				let qt = p.getAttribute("data-type");
				if ((qt == "select_all_apply_text" && sap === false) || (qt == "select_all_apply_image" && sap === false)) {
					// does "next" button already exist?
					let n = p.getElementsByClassName("hdq_button");
					if (typeof n[0] == "undefined") {
						const html = `<div class="hdq_button" role="button" tabindex = "0" onkeyup="HDQ.keyUp(event, this)" onclick = "HDQ.timer.question.changed(this, true)" style = "display: flex; width: fit-content;">${HDQ.VARS.translations.next}</div>`;
						p.insertAdjacentHTML("beforeend", html);
					}
					return;
				} else if (qt == "select_all_apply_text" || qt == "select_all_apply_image") {
					let n = p.getElementsByClassName("hdq_button")[0];
					n.remove();
				}

				// reset timer
				HDQ.VARS.timer.time = HDQ.VARS.timer.max;

				// figure out what the next question is
				let next_question = p.nextSibling;
				let active_question = document.getElementsByClassName("hdq_active_question");
				if (active_question.length > 0) {
					active_question[0].classList.remove("hdq_active_question");
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
					jQuery(".hdq_finsh_button").click(); // submit quiz for completion
				} else {
					jQuery(".hdq_timer").removeClass("hdq_timer_danger");
					jQuery(".hdq_timer").removeClass("hdq_timer_warning");
				}
			},
			question: async function (isFirst = false) {
				if (HDQ.VARS.timer.time > 0 && HDQ.VARS.timer.active == true) {
					let minutes = parseInt(HDQ.VARS.timer.time / 60);
					minutes = minutes < 10 ? "0" + minutes : minutes;
					let seconds = HDQ.VARS.timer.time % 60;
					seconds = seconds < 10 ? "0" + seconds : seconds;
					let t = minutes + ":" + seconds;
					jQuery(".hdq_timer").html(t);
					if (HDQ.VARS.timer.time > 10 && HDQ.VARS.timer.time < 30) {
						jQuery(".hdq_timer").addClass("hdq_timer_warning");
					} else if (HDQ.VARS.timer.time <= 10) {
						jQuery(".hdq_timer").removeClass("hdq_timer_warning");
						jQuery(".hdq_timer").addClass("hdq_timer_danger");
					}
					HDQ.VARS.timer.time = HDQ.VARS.timer.time - 1;
					setTimeout(HDQ.timer.question.question, 1000);
				} else {
					if (HDQ.VARS.timer.active == true) {
						let active_question = document.getElementsByClassName("hdq_active_question");
						if (active_question.length > 0) {
							active_question = active_question[0];
							let answers = active_question.getElementsByClassName("hdq_option");
							for (let i = 0; i < answers.length; i++) {
								answers[i].disabled = true;
							}

							// figure out what the next question is
							let next_question = active_question.nextSibling;
							active_question.classList.remove("hdq_active_question");
							let status = await HDQ.timer.question.checkQuestion(next_question);

							if (status === "complete") {
								// end the quiz
								jQuery(".hdq_finsh_button").click(); // submit quiz for completion
							} else {
								// reset timer
								jQuery(".hdq_timer").html("0");
								HDQ.VARS.timer.time = HDQ.VARS.timer.max;

								jQuery(".hdq_timer").removeClass("hdq_timer_danger");
								jQuery(".hdq_timer").removeClass("hdq_timer_warning");

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
			question = document.getElementById("hdq_question_" + question);
			let answers = question.querySelectorAll(".hdq_option");
			for (let i = 0; i < answers.length; i++) {
				if (answers[i] != el) {
					answers[i].checked = false;
				}
			}
		},
		disable: async function (el) {
			let question = el.getAttribute("data-id");
			question = document.getElementById("hdq_question_" + question);
			let answers = question.querySelectorAll(".hdq_option");
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
				question = document.getElementById("hdq_question_" + question);
				let extra_text = question.querySelector(".hdq_question_after_text");
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
					el.parentNode.classList.add("hdq_correct");
				}
			} else {
				if (HDQ.VARS.show_results == "yes") {
					el.parentNode.classList.add("hdq_wrong");
				}
				if (HDQ.VARS.mark_correct === "yes") {
					if (!el.parentNode.classList.contains("hdq_answered")) {
						let data = " - [" + answers[0] + "]";
						el.value = el.value + data;
						el.parentNode.classList.add("hdq_answered");
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
						row.classList.add("hdq_correct");
					}
				}
			} else {
				if (el.checked == true) {
					if (HDQ.VARS.show_results == "yes") {
						row.classList.add("hdq_wrong");
					}
				}
			}

			let question = el.getAttribute("data-id");
			question = document.getElementById("hdq_question_" + question);
			let answers = question.querySelectorAll(".hdq_option");
			for (let i = 0; i < answers.length; i++) {
				if (HDQ.VARS.mark_correct === "yes") {
					if (answers[i].value == 1) {
						row = answers[i].parentNode.parentNode.parentNode;
						row.classList.add("hdq_correct_not_selected");
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
						row.classList.add("hdq_correct");
					}
				}
			} else {
				if (el.checked == true) {
					if (HDQ.VARS.show_results == "yes") {
						row.classList.add("hdq_wrong");
					}
				}
			}

			let question = el.getAttribute("data-id");
			question = document.getElementById("hdq_question_" + question);
			let answers = question.querySelectorAll(".hdq_option");
			for (let i = 0; i < answers.length; i++) {
				if (HDQ.VARS.mark_correct === "yes") {
					if (answers[i].value == 1) {
						row = answers[i].parentNode.parentNode.parentNode.parentNode;
						row.classList.add("hdq_correct_not_selected");
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
						row.classList.add("hdq_correct");
					}
				}
			} else {
				if (el.checked == true) {
					correct = false;
					if (HDQ.VARS.show_results == "yes") {
						row.classList.add("hdq_wrong");
					}
				}
			}

			let question = el.getAttribute("data-id");
			question = document.getElementById("hdq_question_" + question);
			let answers = question.querySelectorAll(".hdq_option");
			for (let i = 0; i < answers.length; i++) {
				if (HDQ.VARS.mark_correct === "yes" && show_results) {
					if (answers[i].value == 1) {
						row = answers[i].parentNode.parentNode.parentNode;
						row.classList.add("hdq_correct_not_selected");
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
						hdq_show_part_wrong(answers);
					}
					return 0;
					break;
				}
				if (results[i].v == 0 && results[i].checked == true) {
					if (HDQ.VARS.mark_correct != "yes" && HDQ.VARS.show_results == "yes") {
						hdq_show_part_wrong(answers);
					}
					return 0;
					break;
				}
			}
			return 1;

			function hdq_show_part_wrong(answers) {
				// if the user got part of the question right,
				// Visually show that even though the selected answer was correct,
				// the entire answer set is incorrect
				for (let i = 0; i < answers.length; i++) {
					if (answers[i].value == 1 && answers[i].checked == true) {
						answers[i].parentElement.parentElement.parentElement.classList.remove("hdq_correct");
						answers[i].parentElement.parentElement.parentElement.classList.add("hdq_correct_not_selected");
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
						hdq_show_part_wrong(answers);
					}
					return 0;
					break;
				}
				if (results[i].v == 0 && results[i].checked == true) {
					if (HDQ.VARS.mark_correct != "yes" && HDQ.VARS.show_results == "yes") {
						hdq_show_part_wrong(answers);
					}
					return 0;
					break;
				}
			}
			return 1;

			function hdq_show_part_wrong(answers) {
				// if the user got part of the question right,
				// Visually show that even though the selected answer was correct,
				// the entire answer set is incorrect
				for (let i = 0; i < answers.length; i++) {
					if (answers[i].value == 1 && answers[i].checked == true) {
						answers[i].parentElement.parentElement.parentElement.classList.remove("hdq_correct");
						answers[i].parentElement.parentElement.parentElement.classList.add("hdq_correct_not_selected");
					}
				}
			}
		},
	},
	calculateScore: async function () {
		let total_score = 0;
		let total_questions = 0;
		let cs = document.getElementById("hdq_current_score");
		let tq = document.getElementById("hdq_total_questions");
		if (cs != null && tq != null) {
			total_score = parseInt(cs.value);
			total_questions = parseInt(tq.value);
		}
		total_questions += parseInt(HDQ.EL.questions.length);

		for (let i = 0; i < HDQ.EL.questions.length; i++) {
			let t = HDQ.EL.questions[i].getAttribute("data-type");
			let answers = HDQ.EL.questions[i].querySelectorAll(".hdq_option");
			if (answers.length > 0) {
				total_score += await HDQ.getResult[t](answers);
			} else {
				total_questions -= 1;
			}
		}

		HDQ.VARS.hdq_score = [parseInt(total_score), parseInt(total_questions)];
		return HDQ.VARS.hdq_score;
	},
	submit: async function (el) {
		if (el === null || typeof el.getAttribute("id") === "undfined") {
			el = document.getElementsByClassName("hdq_finsh_button")[0];
		}

		if (el.classList.contains("hdq_complete")) {
			return;
		}

		HDQ.VARS.timer.active = false;

		// start visual feedback
		let quiz_ID = el.getAttribute("data-id");
		el.innerHTML = "...";
		el.classList.add("hdq_complete");
		jQuery(el).fadeOut("slow");
		HDQ.EL.loading.classList.add("hdq_animate");

		// hide all buttons
		jQuery(".hdq_jPaginate_button").fadeOut();

		// show all questions in case of jPagination
		if (HDQ.VARS.hide_questions !== "yes") {
			jQuery(".hdq_question").fadeIn();
		}

		// validate all answers
		await HDQ.validate.all();
		// figure out the score
		let score = await HDQ.calculateScore();
		let data = score[0] + " / " + score[1];

		// update results section
		if (jQuery(".hdq_results_inner .hdq_result .hdq_result_percent")[0]) {
			let hdq_results_percent = (parseFloat(HDQ.VARS.hdq_score[0]) / parseFloat(HDQ.VARS.hdq_score[1])) * 100;
			hdq_results_percent = Math.ceil(hdq_results_percent);
			data = '<span class = "hdq_result_fraction">' + data + '</span> - <span class = "hdq_result_percent">' + hdq_results_percent + "%</span>";
		}
		jQuery(".hdq_results_inner .hdq_result").html(data);

		let pass_percent = 0;
		pass_percent = score[0] / score[1];
		pass_percent = pass_percent * 100;
		if (pass_percent >= HDQ.VARS.pass_percent) {
			jQuery(".hdq_result_pass").show();
		} else {
			jQuery(".hdq_result_fail").show();
		}

		if (HDQ.VARS.share_results === "yes") {
			HDQ.share();
		}
		jQuery(".hdq_results_wrapper").fadeIn();

		if (typeof HDQ.VARS.submit_actions != undefined && HDQ.VARS.submit_actions != null) {
			for (let i = 0; i < HDQ.VARS.submit_actions.length; i++) {
				await HDQ.submitAction(HDQ.VARS.submit_actions[i]);
			}
		}

		if (HDQ.VARS.hide_questions === "yes") {
			jQuery(".hdq_question").fadeOut();
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
			data.score = HDQ.VARS.hdq_score;
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
				const el = document.getElementsByClassName("hdq_share_other")[0];
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
						let score = HDQ.VARS.hdq_score[0] + "/" + HDQ.VARS.hdq_score[1];
						text = text.replaceAll("%score%", score);
						text = text.replaceAll("%quiz%", HDQ.VARS.name);

						const data = {
							title: "HD Quiz",
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
				let score = HDQ.VARS.hdq_score[0] + "/" + HDQ.VARS.hdq_score[1];
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
				jQuery(".hdq_twitter").attr("href", shareLink);
			}
		}
		create_social_share();
	},
	jPaginate: function () {
		let hdq_form_id = jQuery(this).attr("data-id");
		jQuery(".hdq_jPaginate .hdq_next_button").removeClass("hdq_next_selected");
		jQuery(this).addClass("hdq_next_selected");

		jQuery("#hdq_" + hdq_form_id + " .hdq_jPaginate:visible")
			.prevAll("#hdq_" + hdq_form_id + " .hdq_question")
			.hide();

		let hdq_class_styles = ["layout_left", "layout_left_full", "layout_right", "layout_right_full"];
		let style = "block";
		let q_style = document.getElementById("hdq_" + hdq_form_id).getElementsByClassName("hdq_quiz")[0].classList;
		let set_style = [];
		for (let i = 0; i < q_style.length; i++) {
			set_style.push(q_style[i]);
		}
		for (let i = 0; i < hdq_class_styles.length; i++) {
			if (set_style.includes(hdq_class_styles[i])) {
				style = "grid";
			}
		}

		jQuery("#hdq_" + hdq_form_id + " .hdq_jPaginate:eq(" + parseInt(HDQ.VARS.jPage) + ")")
			.nextUntil("#hdq_" + hdq_form_id + " .hdq_jPaginate ")
			.show()
			.css("display", style);
		jQuery(".hdq_results_wrapper").hide(); // in case the results are below the quiz
		HDQ.VARS.jPage = parseInt(HDQ.VARS.jPage + 1);

		if (HDQ.VARS.jPage === HDQ.EL.jPaginate.length) {
			jQuery(".hdq_finsh_button").removeClass("hdq_hidden");
		}

		jQuery(this).parent().hide();

		jQuery("#hdq_" + hdq_form_id + " .hdq_jPaginate:eq(" + parseInt(HDQ.VARS.jPage) + ")").show();

		const results_wrapper = jQuery(".hdq_question:visible");
		if (results_wrapper.length == 0) {
			return;
		}
		setTimeout(function () {
			if (!HDQ.VARS.legacy_scroll) {
				// results_wrapper[0].scrollIntoView({ behavior: "smooth", block: "start", inline: "nearest" });
				document.getElementById("hdq_offset_div").scrollIntoView({
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
				let hdq_quiz_container = document.querySelector("#hdq_" + HDQ.VARS.id);
				hdq_quiz_container = jQuery(await HDQ.get_quiz_parent_container(hdq_quiz_container));
				console.log("container:");
				console.log(hdq_quiz_container);

				if (hdq_quiz_container[0].tagName === "DIV") {
					hdq_top = jQuery(hdq_quiz_container).scrollTop() + jQuery(".hdq_results_wrapper").offset().top - jQuery(".hdq_results_wrapper").height() / 2 - 100;
					console.log("hdq_top: " + hdq_top);
					jQuery(hdq_quiz_container).animate(
						{
							scrollTop: hdq_top,
						},
						550
					);
					jQuery("html,body").animate(
						{
							scrollTop: hdq_top,
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
							scrollTop: jQuery(".hdq_results_wrapper").offset().top - 100,
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
				let hdq_quiz_container = document.querySelector("#hdq_" + HDQ.VARS.id);
				hdq_quiz_container = jQuery(await HDQ.get_quiz_parent_container(hdq_quiz_container));

				if (hdq_quiz_container[0].tagName === "DIV") {
					hdq_top = jQuery(hdq_quiz_container).scrollTop() + jQuery(".hdq_question:visible").offset().top - jQuery(".hdq_question:visible").height() / 2 - 100;
					jQuery(hdq_quiz_container).animate(
						{
							scrollTop: hdq_top,
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
							scrollTop: jQuery(".hdq_question:visible").offset().top - 100,
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
				document.getElementById("hdq_offset_div").scrollIntoView({
					behavior: "smooth",
					block: "start",
					inline: "nearest",
				});
			} else {
				const results_wrapper = document.getElementsByClassName("hdq_results_wrapper")[0];
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
		if (p.classList.contains("hdq_question")) {
			return p;
		} else {
			p = HDQ.getParent(p);
		}
		return p;
	},
};

let hdq_locals = {};
if (typeof hdq_local_vars != "undefined") {
	hdq_locals = JSON.parse(hdq_local_vars);
	async function HDQ_INIT() {
		// set init vars
		async function hdqSetInitVars() {
			HDQ.VARS = {
				ajax: hdq_locals.hdq_ajax,
				featured_image: hdq_locals.hdq_featured_image,
				pass_percent: hdq_locals.hdq_pass_percent,
				id: hdq_locals.hdq_quiz_id,
				name: hdq_locals.hdq_quiz_name,
				permalink: hdq_locals.hdq_quiz_permalink,
				mark_correct: hdq_locals.hdq_results_correct,
				hide_questions: hdq_locals.hdq_hide_questions,
				share_results: hdq_locals.hdq_share_results,
				show_extra_text: hdq_locals.hdq_show_extra_text,
				show_results: hdq_locals.hdq_show_results,
				show_results_now: hdq_locals.hdq_show_results_now,
				results_position: hdq_locals.hdq_results_position,
				stop_reselect: hdq_locals.hdq_stop_answer_reselect,
				submit_actions: hdq_locals.hdq_submit,
				init_actions: hdq_locals.hdq_init,
				timer: {
					time: hdq_locals.hdq_timer,
					max: hdq_locals.hdq_timer,
					question: hdq_locals.hdq_timer_question,
					active: false,
				},
				twitter: hdq_locals.hdq_twitter_handle,
				ads: hdq_locals.hdq_use_ads,
				hdq_score: [],
				jPage: 0,
				paginate: false,
				legacy_scroll: hdq_locals.hdq_legacy_scroll,
				translations: hdq_locals.hdq_translations,
				share_text: hdq_locals.hdq_share_text,
			};
		}
		await hdqSetInitVars();
		HDQ.init();
	}
	HDQ_INIT();
}

// TODO: check to see if this integration still works well
/* FB APP - Only used if APP ID was provided */
jQuery("#hdq_fb_sharer").on("click", function () {
	let hdq_score = jQuery(".hdq_result").text();
	let text = HDQ.VARS.share_text;
	text = text.replaceAll("%score%", hdq_score);
	text = text.replaceAll("%quiz%", HDQ.VARS.name);
	FB.ui(
		{
			method: "share",
			href: HDQ.VARS.permalink,
			hashtag: "#hdquiz",
			quote: text, // Note: It looks like Meta depricated sending custom text altogether :(
		},
		function (res) {
			console.log(res);
		}
	);
});
