/*
	HDQ main admin script
*/

console.log("Init: HD Quiz Settings Page");

const HDQ = {
	EL: {
		page: {
			header: {
				title: document.getElementById("heading_title"),
				actions: {
					wrapper: document.getElementById("header_actions"),
					save: document.getElementById("save"),
				},
			},
			addQuiz: document.getElementById("hdq_new_quiz_name"),
			editors: document.getElementsByClassName("editor"),
			quizzes: document.getElementById("hdq_list_quizzes"),
			questions: document.getElementById("hdq_questions_list"),
			main: document.getElementById("main"),
			inputEnter: document.getElementsByClassName("input_enter"),
		},
		tabs: {
			nav: document.getElementsByClassName("tab_nav_item"),
			content: document.getElementsByClassName("tab_content"),
			active: {
				nav: document.getElementsByClassName("tab_nav_item_active"),
				content: document.getElementsByClassName("tab_content_active"),
			},
		},
		fields: document.getElementsByClassName("hderp_input"),
		quizzes: document.getElementsByClassName("hdq_quiz_term"),
		questions: document.getElementsByClassName("hdq_quiz_question "),
		nonce: document.getElementById("hdq_quiz_nonce"),
	},
	vars: {
		loadTimer: 0,
		orderChanged: false,
		mediaFrame: {
			frame: null,
			title: null,
			button: null,
			multiple: false,
		},
		showingEnterNotifiction: [],
	},
	init: {
		settings: function () {
			HDQ.EL.page.header.actions.save = document.getElementById("hdq_save_settings"); // since it's probably null right now
			if (HDQ.EL.page.header.actions.save !== null) {
				// save
				HDQ.EL.page.header.actions.save.addEventListener("click", async function () {
					let payload = await HDQ.validate.validateSettings();
					if (payload) {
						console.log(payload);
						save(payload);
					}
				});

				//HDQ.createEditors(); // initialize various content editors
				setTabEventListeners(); // allow tab switching
				setCustomEventListeners();

				function setCustomEventListeners() {
					// not sure if needed yet
				}

				// allow tab switching
				function setTabEventListeners() {
					for (let i = 0; i < HDQ.EL.tabs.nav.length; i++) {
						HDQ.EL.tabs.nav[i].addEventListener("click", loadTabContent);
					}

					function loadTabContent() {
						HDQ.EL.tabs.active.nav[0].classList.remove("tab_nav_item_active");
						this.classList.add("tab_nav_item_active");

						let content = "tab_" + this.getAttribute("data-id");
						HDQ.EL.tabs.active.content[0].classList.remove("tab_content_active");
						document.getElementById(content).classList.add("tab_content_active");
					}
				}

				function save(payload) {
					console.log(document.getElementById("hdq_about_options_nonce").value);
					HDQ.EL.page.header.actions.save.classList.add("saving");
					HDQ.EL.page.header.actions.save.innerHTML = "saving...";
					console.log(payload);
					jQuery.ajax({
						type: "POST",
						data: {
							action: "hdq_save_settings",
							payload: payload,
							nonce: document.getElementById("hdq_about_options_nonce").value,
						},
						url: ajaxurl,
						success: function (data) {
							console.log(data);
						},
						complete: function () {
							HDQ.EL.page.header.actions.save.classList.remove("saving");
							HDQ.EL.page.header.actions.save.innerHTML = "SAVE";
						},
					});
				}
			}
		},
		quizzes: function () {
			HDQ.EL.page.addQuiz.addEventListener("keyup", HDQ.quizzes.add);

			setQuizSelectListeners();
			function setQuizSelectListeners() {
				for (let i = 0; i < HDQ.EL.quizzes.length; i++) {
					HDQ.EL.quizzes[i].addEventListener("click", HDQ.quizzes.load);
				}
			}

			for (let i = 0; i < HDQ.EL.page.inputEnter.length; i++) {
				HDQ.vars.showingEnterNotifiction.push(false);
				HDQ.EL.page.inputEnter[i].addEventListener("keyup", function (e) {
					setEnterNotification(i, e, this);
				});
			}

			const data_upgrade_notice = document.getElementById("hdq_update_data_notice");
			if (data_upgrade_notice != null) {
				data_upgrade_notice.addEventListener("click", HDQ.removeDataUpgradeNotice);
			}

			function setEnterNotification(i, e, el) {
				if (e.keyCode === 13) {
					let next = el.nextSibling;
					if (next.nodeName == "P" && next.classList.contains("enter_notification")) {
						next.remove();
					}
					HDQ.vars.showingEnterNotifiction[i] = false;
				} else {
					if (el.value.length > 0 && HDQ.vars.showingEnterNotifiction[i] == false) {
						let data = `<p class = "enter_notification">press enter to add</p>`;
						el.insertAdjacentHTML("afterend", data);
						HDQ.vars.showingEnterNotifiction[i] = true;
						setTimeout(function () {
							let next = el.nextSibling;
							if (next.nodeName == "P" && next.classList.contains("enter_notification")) {
								next.classList.add("enter_notification_visible");
							}
						}, 1000);
					} else if (el.value.length == 0 && HDQ.vars.showingEnterNotifiction[i] == true) {
						HDQ.vars.showingEnterNotifiction[i] = false;
						let next = el.nextSibling;
						if (next.nodeName == "P" && next.classList.contains("enter_notification")) {
							next.remove();
						}
					}
				}
			}
		},
		quiz: function () {
			HDQ.EL.page.header.actions.save = document.getElementById("save"); // since it's probably null right now
			if (HDQ.EL.page.header.actions.save !== null) {
				// save
				HDQ.EL.page.header.actions.save.addEventListener("click", async function () {
					let payload = await HDQ.validate.validateSettings();
					if (payload) {
						HDQ.quizzes.save(payload);
					}
				});

				// add new question
				document.getElementById("hdq_add_question").addEventListener("click", async function () {
					let quizID = parseInt(this.getAttribute("data-id"));
					HDQ.questions.load(quizID);
				});

				// others
				HDQ.createEditors(); // initialize various content editors
				setTabEventListeners(); // allow tab switching
				HDQ.quizzes.setSortable();
				setQuestionEventListeners();
				setCustomEventListeners();
			}

			function setCustomEventListeners() {
				const showCorrectResults = document.getElementById("variation_field_show_results_correctyes");
				const showResults = document.getElementById("variation_field_show_resultsyes");
				const immediate = document.getElementById("variation_field_show_results_nowyes");
				const noChange = document.getElementById("variation_field_stop_answer_reselectyes");

				if (showCorrectResults != null) {
					showCorrectResults.addEventListener("change", function () {
						if (this.checked === true) {
							showResults.checked = true;
						}
					});
				}
				if (showResults != null) {
					showResults.addEventListener("change", function () {
						if (this.checked != true) {
							showCorrectResults.checked = false;
						}
					});
				}
				if (immediate != null) {
					immediate.addEventListener("change", function () {
						if (this.checked === true) {
							noChange.checked = true;
						}
					});
				}
			}

			function setQuestionEventListeners() {
				for (let i = 0; i < HDQ.EL.questions.length; i++) {
					HDQ.EL.questions[i].addEventListener("click", HDQ.questions.load);
				}
			}

			// question pagination
			function setQuestionPagination() {
				const next = document.getElementById("hdq_next_questions");
				const prev = document.getElementById("hdq_prev_questions");
				if (next != null) {
					next.addEventListener("click", HDQpaginate);
				}
				if (prev != null) {
					prev.addEventListener("click", HDQpaginate);
				}

				function HDQpaginate() {
					let page = parseInt(this.getAttribute("page-id"));
					let quiz = parseInt(this.getAttribute("quiz-id"));
					HDQ.quizzes.load(quiz, null, page);
				}
			}
			setQuestionPagination();

			// allow tab switching
			function setTabEventListeners() {
				function main() {
					const tabs = document.getElementsByClassName("hdq_quiz_tab");
					for (let i = 0; i < tabs.length; i++) {
						tabs[i].addEventListener("click", loadTabContent);
					}

					function loadTabContent() {
						let active = document.getElementsByClassName("hdq_quiz_tab_active")[0];
						active.classList.remove("hdq_quiz_tab_active");
						this.classList.add("hdq_quiz_tab_active");

						document.getElementById("hdq_questions_list").style.display = "none";
						document.getElementById("hdq_settings_page").style.display = "none";

						let el = this.getAttribute("data-id");
						document.getElementById(el).style.display = "block";
					}
				}

				function settings() {
					for (let i = 0; i < HDQ.EL.tabs.nav.length; i++) {
						HDQ.EL.tabs.nav[i].addEventListener("click", loadTabContent);
					}

					function loadTabContent() {
						HDQ.EL.tabs.active.nav[0].classList.remove("tab_nav_item_active");
						this.classList.add("tab_nav_item_active");

						let content = "tab_" + this.getAttribute("data-id");
						HDQ.EL.tabs.active.content[0].classList.remove("tab_content_active");
						document.getElementById(content).classList.add("tab_content_active");
					}
				}

				// if there was a prev active question, autoscroll to it
				let last_active_q = document.getElementsByClassName("hdq_question_last_active");
				if (last_active_q.length > 0) {
					last_active_q[0].scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
				}

				main();
				settings();
			}

			// copy shortcode to clipboard
			let shortcode = document.getElementsByClassName("hdq_shortcode_copy");
			for (let i = 0; i < shortcode.length; i++) {
				shortcode[i].addEventListener("click", function () {
					let text = this.innerHTML;
					navigator.clipboard.writeText(text);
					this.nextSibling.innerHTML = "you can now paste the shortcode onto any page or post";
				});
			}
		},
		question: function () {
			const ELSAVE = document.getElementById("hdq_save_question");
			if (ELSAVE !== null) {
				let questionID = document.getElementById("question_id").value;
				if (questionID == 0) {
					// since new question, auto focus the title
					document.getElementById("title").focus();
				}
				HDQ.EL.page.header.actions.save = ELSAVE;
				HDQ.createEditors(); // initialize various content editors
				HDQ.setImageListeners(); // allow setting and removing of image based inputs
				setTabEventListeners(); // allow tab switching
				setNavEventListeners(); // alow for back to quiz, add new question, delete etc

				// Question type changed
				const question_type = document.getElementById("question_type");
				question_type.addEventListener("change", function () {
					HDQ.questions.type.change(this);
				});

				// save
				ELSAVE.addEventListener("click", async function () {
					let payload = await HDQ.validate.validateSettings();
					if (payload) {
						console.log(payload);
						HDQ.questions.save(payload);
					}
				});

				// add new question
				document.getElementById("hdq_add_question").addEventListener("click", async function () {
					let quizID = parseInt(this.getAttribute("data-id"));
					HDQ.questions.load(quizID);
				});

				// delete this
				document.getElementById("hdq_delete_question").addEventListener("click", async function () {
					let quizID = parseInt(this.getAttribute("data-quiz"));
					let questionID = parseInt(this.getAttribute("data-question"));
					HDQ.questions.delete(quizID, questionID);
				});
			}

			function setNavEventListeners() {
				const ELBACK = document.getElementById("hdq_back_to_quiz");
				const ELNEW = document.getElementById("hdq_add_question");
				const ELDELETE = document.getElementById("hdq_delete_question");

				ELBACK.addEventListener("click", function () {
					let quizID = this.getAttribute("data-id");
					let questionID = this.getAttribute("data-question-id");
					HDQ.quizzes.load(quizID, questionID);
				});

				// TODO: new q and delete and save
			}

			function setTabEventListeners() {
				for (let i = 0; i < HDQ.EL.tabs.nav.length; i++) {
					HDQ.EL.tabs.nav[i].addEventListener("click", loadTabContent);
				}

				function loadTabContent() {
					HDQ.EL.tabs.active.nav[0].classList.remove("tab_nav_item_active");
					this.classList.add("tab_nav_item_active");

					let content = "tab_" + this.getAttribute("data-id");
					HDQ.EL.tabs.active.content[0].classList.remove("tab_content_active");
					document.getElementById(content).classList.add("tab_content_active");
				}
			}
		},
	},
	quizzes: {
		load: async function (quizID = null, questionID = null, pageID = 1) {
			if (typeof quizID == "object") {
				if (typeof quizID.target != "undefined") {
					if (quizID.target.nodeName === "CODE") {
						return;
					}
				}
				quizID = parseInt(this.getAttribute("data-id"));
			} else {
				quizID = parseInt(quizID);
			}
			questionID = parseInt(questionID);

			let nonce = HDQ.EL.nonce.value;
			HDQ.EL.page.header.title.innerHTML = "loading...";
			HDQ.EL.page.quizzes.classList.add("hdq_quiz_loading");
			jQuery("#hdq_message").fadeOut();
			jQuery("#hdq_loading ").fadeIn();

			HDQ.vars.loadTimer = 0;
			let loadTime = setInterval(function () {
				HDQ.vars.loadTimer += 10;
			}, 10);
			jQuery.ajax({
				type: "POST",
				data: {
					action: "hdq_load_quiz",
					quiz: quizID,
					questionID: questionID,
					hdq_paged: pageID,
					nonce: nonce,
				},
				url: ajaxurl,
				success: function (data) {
					// force a more smooth loading integration
					if (HDQ.vars.loadTimer > 1000) {
						HDQ.EL.page.main.innerHTML = data;
						HDQ.init.quiz();
						jQuery("#hdq_loading ").fadeOut();
					} else {
						setTimeout(function () {
							HDQ.EL.page.main.innerHTML = data;
							HDQ.init.quiz();
							jQuery("#hdq_loading ").fadeOut();
						}, 500);
					}
				},
				complete: function () {
					clearInterval(loadTime);
					HDQ.EL.page.header.actions.save = document.getElementById("save");
				},
				error: function () {
					jQuery("#hdq_loading ").fadeOut();
					alert("there was an error retrieving your quiz");
					HDQ.EL.page.quizzes.classList.remove("hdq_quiz_loading");
				},
			});
		},
		save: async function (payload) {
			HDQ.EL.page.header.actions.save.classList.add("saving");
			HDQ.EL.page.header.actions.save.innerHTML = "saving...";
			console.log(payload);
			jQuery.ajax({
				type: "POST",
				data: {
					action: "hdq_save_quiz",
					payload: payload,
					nonce: document.getElementById("hdq_quiz_nonce").value,
				},
				url: ajaxurl,
				success: function (data) {
					console.log(data);
					HDQ.scroll();
				},
				complete: function () {
					HDQ.EL.page.header.actions.save.classList.remove("saving");
					HDQ.EL.page.header.actions.save.innerHTML = "SAVE";
				},
			});
		},
		add: function (e) {
			if (e.which === 13) {
				let v = e.target.value;
				if (v.length > 0) {
					e.target.value = "";
					e.target.disabled = true;

					let nonce = HDQ.EL.nonce.value;
					HDQ.EL.page.header.title.innerHTML = "loading...";
					HDQ.EL.page.quizzes.classList.add("hdq_quiz_loading");
					jQuery("#hdq_message").fadeOut();
					jQuery("#hdq_loading ").fadeIn();

					HDQ.vars.loadTimer = 0;
					let loadTime = setInterval(function () {
						HDQ.vars.loadTimer += 10;
					}, 10);
					jQuery.ajax({
						type: "POST",
						data: {
							action: "hdq_add_quiz",
							quiz: v,
							nonce: nonce,
						},
						url: ajaxurl,
						success: function (data) {
							try {
								data = JSON.parse(data);
								console.log(data);
							} catch (e) {
								console.warn(e);
								console.log("There was an error parsing JSON data");
								return;
							}

							data.quiz = decodeURIComponent(data.quiz);
							let html = `<div role="button" class="hdq_quiz_item hdq_quiz_term" data-name="${data.quiz}" data-id="${data.id}">${data.quiz}<code>[HDquiz quiz = "${data.id}"]</code></div>`;
							// force a more smooth loading integration
							if (HDQ.vars.loadTimer > 1000) {
								HDQ.EL.page.quizzes.insertAdjacentHTML("beforeend", html);
								jQuery("#hdq_loading ").fadeOut();
								HDQ.init.quizzes();
								HDQ.scroll();
							} else {
								setTimeout(function () {
									HDQ.EL.page.quizzes.insertAdjacentHTML("beforeend", html);
									jQuery("#hdq_loading ").fadeOut();
									HDQ.init.quizzes();
									HDQ.scroll();
								}, 500);
							}
						},
						complete: function () {
							clearInterval(loadTime);
							HDQ.EL.page.addQuiz.disabled = false;
							HDQ.EL.page.quizzes.classList.remove("hdq_quiz_loading");
							HDQ.EL.page.header.title.innerHTML = "HD Quiz - Quizzes";
						},
						error: function () {
							jQuery("#hdq_loading ").fadeOut();
							alert("there was an error retrieving your quiz");
						},
					});
				}
			}
		},
		setSortable: function () {
			jQuery("#hdq_questions_list").sortable({
				placeholder: "sorting_placeholder",
				items: ".hdq_quiz_question",
				handle: ".hdq_quiz_item_drag",
				distance: 15, // sets the drag tolerance to something more acceptable
				update: function (event, ui) {
					HDQ.vars.orderChanged = true;
				},
			});
		},
	},
	questions: {
		load: async function (el) {
			if (HDQ.EL.page.questions === null) {
				HDQ.EL.page.questions = document.getElementById("hdq_questions_list");
			}

			let questionID = 0;
			let quizID = 0;
			if (typeof el === "number") {
				quizID = el;
			} else {
				questionID = parseInt(this.getAttribute("data-id"));
				quizID = parseInt(this.getAttribute("data-quiz-id"));
			}
			let nonce = HDQ.EL.nonce.value;
			jQuery("#hdq_message").fadeOut();
			jQuery("#hdq_loading").fadeIn();

			HDQ.vars.loadTimer = 0;
			let loadTime = setInterval(function () {
				HDQ.vars.loadTimer += 10;
			}, 10);
			jQuery.ajax({
				type: "POST",
				data: {
					action: "hdq_load_question",
					question: questionID,
					quiz: quizID,
					nonce: nonce,
				},
				url: ajaxurl,
				success: function (data) {
					// force a more smooth loading integration
					if (HDQ.vars.loadTimer > 1000) {
						HDQ.EL.page.main.innerHTML = data;
						HDQ.init.question();
						jQuery("#hdq_loading ").fadeOut();
						HDQ.scroll();
					} else {
						setTimeout(function () {
							HDQ.EL.page.main.innerHTML = data;
							HDQ.init.question();
							jQuery("#hdq_loading ").fadeOut();
							HDQ.scroll();
						}, 500);
					}
				},
				complete: function () {
					clearInterval(loadTime);
				},
				error: function () {
					jQuery("#hdq_loading ").fadeOut();
					alert("there was an error retrieving your quiz");
					HDQ.EL.page.questions.classList.remove("hdq_quiz_loading");
				},
			});
		},
		delete: async function (quizID, questionID) {
			if (questionID == 0 || questionID == "") {
				console.log("No need to delete: This question has not been saved yet");
				return;
			}
			let nonce = HDQ.EL.nonce.value;
			document.getElementById("content_tabs").classList.add("hdq_quiz_loading");
			jQuery("#hdq_loading ").fadeIn();
			HDQ.vars.loadTimer = 0;
			let loadTime = setInterval(function () {
				HDQ.vars.loadTimer += 10;
			}, 10);
			jQuery.ajax({
				type: "POST",
				data: {
					action: "hdq_delete_question",
					question: questionID,
					quiz: quizID,
					nonce: nonce,
				},
				url: ajaxurl,
				success: function (data) {
					console.log(data);
					// force a more smooth loading integration
					if (HDQ.vars.loadTimer > 1000) {
						HDQ.quizzes.load(quizID);
					} else {
						setTimeout(function () {
							HDQ.quizzes.load(quizID);
						}, 500);
					}
				},
				complete: function () {
					clearInterval(loadTime);
				},
				error: function () {
					jQuery("#hdq_loading ").fadeOut();
					alert("there was an error deleting this question");
					document.getElementById("content_tabs").remove("hdq_quiz_loading");
				},
			});
		},
		save: async function (payload) {
			HDQ.EL.page.header.actions.save.classList.add("saving");
			HDQ.EL.page.header.actions.save.innerHTML = "saving...";

			jQuery.ajax({
				type: "POST",
				data: {
					action: "hdq_save_question",
					payload: payload,
					nonce: document.getElementById("hdq_quiz_nonce").value,
				},
				url: ajaxurl,
				success: function (data) {
					let json = JSON.parse(data);
					console.log(json);
					document.getElementById("hdq_save_question").setAttribute("data-id", parseInt(json.id));
					document.getElementById("question_id").value = parseInt(json.id);
				},
				complete: function () {
					HDQ.EL.page.header.actions.save.classList.remove("saving");
					HDQ.EL.page.header.actions.save.innerHTML = "SAVE";
					HDQ.scroll();
				},
			});
		},
		type: {
			change: async function (el) {
				if (el.value != "") {
					let action = el.value;
					await HDQ.questions.type.reset();
					HDQ.questions.type[action]();
				}
			},
			reset: async function () {
				let question_type_tip = document.getElementById("question_type_tip");
				question_type_tip.innerHTML = "";

				let answers = document.getElementById("answers");
				answers.style.display = "table";

				let featured_images_wrap = document.getElementsByClassName("hdq_answer_as_image");
				for (let i = 0; i < featured_images_wrap.length; i++) {
					featured_images_wrap[i].classList.add("hdq_hide");
				}

				let answer_selected = document.getElementsByClassName("hdq_answer_selected");
				for (let i = 0; i < answer_selected.length; i++) {
					answer_selected[i].classList.remove("hdq_hide");
				}
				return;
			},
			multiple_choice_text: function () {
				// default
			},
			select_all_apply_image: function () {
				let question_type_tip = document.getElementById("question_type_tip");
				let data = `<p>With this question type, the user will need to correctly select ALL of the correct answers in order to be awarded a point for this question</p>`;
				question_type_tip.innerHTML = data;
				
				let featured_images_wrap = document.getElementsByClassName("hdq_answer_as_image");
				for (let i = 0; i < featured_images_wrap.length; i++) {
					featured_images_wrap[i].classList.remove("hdq_hide");
				}
				
			},			
			select_all_apply_text: function () {
				let question_type_tip = document.getElementById("question_type_tip");
				let data = `<p>With this question type, the user will need to correctly select ALL of the correct answers in order to be awarded a point for this question</p>`;
				question_type_tip.innerHTML = data;
			},
			multiple_choice_image: function () {
				let question_type_tip = document.getElementById("question_type_tip");
				let data = `<p>Image Based Answers: quiz takers will be able to select the corresponding image and text. Please note that you still need to include a text based answer so that the quiz is accessible to those with screen readers (plus it's better for SEO).</p>`;
				question_type_tip.innerHTML = data;

				let featured_images_wrap = document.getElementsByClassName("hdq_answer_as_image");
				for (let i = 0; i < featured_images_wrap.length; i++) {
					featured_images_wrap[i].classList.remove("hdq_hide");
				}
			},
			title: function () {
				let question_type_tip = document.getElementById("question_type_tip");
				let data = `<p>Instead of showing a question, show a title/heading. Useful for grouping similar questions together into categories.</p>`;
				question_type_tip.innerHTML = data;
				let answers = document.getElementById("answers");
				answers.style.display = "none";
			},
			text_based: function () {
				let question_type_tip = document.getElementById("question_type_tip");
				let data = `<p>Instead of multiple choice answers, the quiz taker will have to type their answers. The below answers are NOT cAsE sEnSiTive. Each answer will correspond with an accepted correct answer, so it's best to include common spelling mistakes.</p><p>NEW: You can add an asterisks <code>*</code> to the end of a word to allow all extentions of that word. Example: <code>hop*</code> would allow "hop, hope, hopping" etc to be accepted.</p>`;
				question_type_tip.innerHTML = data;

				let answer_selected = document.getElementsByClassName("hdq_answer_selected");
				for (let i = 0; i < answer_selected.length; i++) {
					answer_selected[i].classList.add("hdq_hide");
				}
			},
		},
	},
	setImageListeners: function () {
		let image_fields = document.getElementsByClassName("input_image");

		let images_remove = document.getElementsByClassName("remove_image");
		for (let i = 0; i < images_remove.length; i++) {
			images_remove[i].addEventListener("click", removeImage);
		}

		function removeImage() {
			let id = this.getAttribute("data-id");
			el = document.getElementById(id);
			el.setAttribute("data-value", "");
			el.innerHTML = "set image";
			this.parentNode.remove();
		}

		for (let i = 0; i < image_fields.length; i++) {
			image_fields[i].addEventListener("click", HDQ.mediaUploader);
		}
	},
	mediaUploader: function () {
		let el = this;
		let multiple = false;
		let options = this.getAttribute("data-options");
		options = decodeURIComponent(options);
		options = JSON.parse(options);
		if (options.multiple) {
			HDQ.vars.mediaFrame.multiple = options.multiple;
		}
		if (options.title) {
			HDQ.vars.mediaFrame.title = options.title;
		}
		if (options.button) {
			HDQ.vars.mediaFrame.button = options.button;
		}

		var type = this.getAttribute("data-type");

		if (type == "gallery") {
			multiple = true;
		}
		HDQ.vars.mediaFrame.multiple = multiple;

		// Create the media frame.
		HDQ.vars.mediaFrame.frame = wp.media.frames.file_frame = wp.media({
			title: HDQ.vars.mediaFrame.title,
			button: {
				text: HDQ.vars.mediaFrame.button,
			},
			multiple: HDQ.vars.mediaFrame.multiple,
		});

		// When an image is selected, run a callback.
		HDQ.vars.mediaFrame.frame.on("select", function () {
			let attachment = HDQ.vars.mediaFrame.frame.state().get("selection");
			if (type == "image") {
				setImage(attachment, el);
			} else if (type == "gallery") {
				setGallery(attachment, el);
			}
		});

		// Finally, open the modal
		HDQ.vars.mediaFrame.frame.open();

		async function setImage(attachment, el) {
			attachment = attachment.first().toJSON();
			if (attachment.type != "image") {
				alert("this is not an image");
				return;
			}
			let url = await getImageThumb(attachment.sizes);
			let id = attachment.id;
			let title = attachment.title;
			let data = `<img class = "image_field_image" src = "${url}" alt = "${title}" />`;
			el.innerHTML = data;
			el.setAttribute("data-value", id);
			if (el.nextElementSibling == null || !el.nextElementSibling.classList.contains("remove_image_wrapper")) {
				id = el.getAttribute("id");
				data = `<p class = "remove_image_wrapper" style="text-align:center"><span class="remove_image" data-id="${id}">remove image</span></p>`;
				el.insertAdjacentHTML("afterend", data);
			}
			HDQ.setImageListeners();
		}

		async function setGallery(attachments, el) {
			attachments = attachments.toJSON();
			let container = document.getElementById(el.getAttribute("id") + "_container");
			let arr = [];
			let gallery = el.getAttribute("data-value");
			if (gallery == "0") {
				gallery = [];
			} else {
				gallery = gallery.split(",");
			}

			for (let i = 0; i < attachments.length; i++) {
				if (attachments[i].type == "image") {
					let url = await getImageThumb(attachments[i].sizes);
					if (!url) {
						console.log("could not get URL for image. Is this a real image?");
						return;
					}
					let id = attachments[i].id;
					let title = attachments[i].title;
					gallery.push(id);
					let data = `<img data-id = "${id}" data-parent = "${el.getAttribute("id")}" class = "gallery_field_image" title = "click to remove, or drag to reorder" src = "${url}" alt = "${title}" />`;
					container.insertAdjacentHTML("beforeend", data);
				}
			}

			gallery = gallery.join(",");
			el.setAttribute("data-value", gallery);
		}

		async function getImageThumb(sizes) {
			if (sizes.large) {
				return sizes.large.url;
			} else if (sizes.full) {
				return sizes.full.url;
			} else {
				return false;
			}
		}
	},
	createEditors: function () {
		let editors = document.getElementsByClassName("hderp_editor");
		for (let i = 0; i < editors.length; i++) {
			let eID = editors[i].getAttribute("id");
			tinyMCE.execCommand("mceRemoveEditor", false, eID); // destroy old
			let parent = editors[i].parentNode.parentNode.parentNode;
			let tab = parent.getAttribute("data-tab");
			if (parent.getAttribute("data-required") == "required") {
				editors[i].setAttribute("data-required", "required");
			}
			editors[i].classList.add("hderp_input");
			editors[i].setAttribute("data-type", "editor");
			editors[i].setAttribute("data-tab", tab);
		}

		// this is stupid. there has to be a better way right?
		setTimeout(initTINYMCE, 500);
		function initTINYMCE() {
			tinyMCE.init({
				mode: "textareas",
				theme: "modern",
				skin: "lightgray",
				formats: {
					alignleft: [
						{
							selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
							styles: {
								textAlign: "left",
							},
						},
						{
							selector: "img,table,dl.wp-caption",
							classes: "alignleft",
						},
					],
					aligncenter: [
						{
							selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
							styles: {
								textAlign: "center",
							},
						},
						{
							selector: "img,table,dl.wp-caption",
							classes: "aligncenter",
						},
					],
					alignright: [
						{
							selector: "p,h1,h2,h3,h4,h5,h6,td,th,div,ul,ol,li",
							styles: {
								textAlign: "right",
							},
						},
						{
							selector: "img,table,dl.wp-caption",
							classes: "alignright",
						},
					],
					strikethrough: {
						inline: "del",
					},
				},
				relative_urls: false,
				remove_script_host: false,
				convert_urls: false,
				browser_spellcheck: true,
				entity_encoding: "raw",
				keep_styles: false,
				resize: true,
				menubar: false,
				branding: false,
				preview_styles: "font-family font-size font-weight font-style text-decoration text-transform",
				wpeditimage_html5_captions: true,
				wp_shortcut_labels: {
					"Heading 1": "access1",
					"Heading 2": "access2",
					"Heading 3": "access3",
					"Heading 4": "access4",
					"Heading 5": "access5",
					"Heading 6": "access6",
					Paragraph: "access7",
					Blockquote: "accessQ",
					Underline: "metaU",
					Strikethrough: "accessD",
					Bold: "metaB",
					Italic: "metaI",
					Code: "accessX",
					"Align center": "accessC",
					"Align right": "accessR",
					"Align left": "accessL",
					Justify: "accessJ",
					Cut: "metaX",
					Copy: "metaC",
					Paste: "metaV",
					"Select all": "metaA",
					Undo: "metaZ",
					Redo: "metaY",
					"Bullet list": "accessU",
					"Numbered list": "accessO",
					"Insert/edit image": "accessM",
					"Remove link": "accessS",
					"Toolbar Toggle": "accessZ",
					"Insert Read More tag": "accessT",
					"Insert Page Break tag": "accessP",
					"Distraction-free writing mode": "accessW",
					"Keyboard Shortcuts": "accessH",
				},
				plugins: "charmap,colorpicker,hr,lists,media,paste,tabfocus,textcolor,wordpress,wpautoresize,wpeditimage,wpemoji,wpgallery,wplink,wpdialogs,wptextpattern,wpview",
				wpautop: true,
				indent: false,
				toolbar1: "formatselect,bold,italic,bullist,numlist,blockquote,alignleft,aligncenter,alignright,link,dfw,wp_adv",
				toolbar2: "strikethrough,hr,forecolor,pastetext,removeformat,charmap,outdent,indent,undo,redo,wp_help",
				toolbar3: "",
				toolbar4: "",
				tabfocus_elements: "content-html,save-post",
				wp_autoresize_on: true,
				add_unload_trigger: false,
				block_formats: "Paragraph=p;Heading 2=h2;Heading 3=h3;Heading 4=h4;Code=code",
			});
		}
	},
	radioFieldSelect: function (el) {
		// only one radio can be active at a time
		let v = el.value;
		let checked = false;
		let radios = el.parentNode.parentNode.parentNode.querySelectorAll(".hdq_radio_input");
		for (let i = 0; i < radios.length; i++) {
			if (radios[i] != el) {
				radios[i].checked = false;
			}
			if (radios[i].checked == true) {
				checked = true;
			}
		}
		if (!checked) {
			v = "";
		}
		let id = el.getAttribute("data-id");
		document.getElementById(id).value = v;
	},
	checkboxFieldSelect: function (el) {},
	validate: {
		questionOrder: async function () {
			let questions = [];
			for (let i = 0; i < HDQ.EL.questions.length; i++) {
				let ID = HDQ.EL.questions[i].getAttribute("data-id");
				let question = [parseInt(ID), i];
				questions.push(question);
			}
			return questions;
		},
		validateSettings: async function () {
			if (HDQ.EL.page.header.actions.save.classList.contains("saving")) {
				// we are currently saving
				return;
			}

			let r = [];

			for (let i = 0; i < HDQ.EL.fields.length; i++) {
				const input = HDQ.EL.fields[i];
				let type = input.getAttribute("data-type");
				let data = {
					name: input.getAttribute("id"),
					type: type,
					required: input.getAttribute("data-required") || null,
					value: await HDQ.validate.getValue(input, type),
					tab: input.getAttribute("data-tab"),
				};
				r.push(data);
			}

			let required = await HDQ.validate.checkRequiredFields(r);

			if (!required) {
				return false;
			} else {
				let required = document.getElementsByClassName("hdq_input_required");
				while (required.length > 0) {
					required[0].classList.remove("hdq_input_required");
				}

				// turn into associative
				let payload = {};
				for (let i = 0; i < r.length; i++) {
					payload[r[i].name] = r[i];
				}

				if (HDQ.vars.orderChanged === true) {
					let question_order = await HDQ.validate.questionOrder();
					payload["question_order"] = {};
					payload["question_order"].name = "question_order";
					payload["question_order"].type = "question_order";
					payload["question_order"].value = question_order;
					HDQ.vars.orderChanged = false;
				}
				return payload;
			}
		},
		getValue: async function (input, type) {
			let o = JSON.parse(decodeURIComponent(input.getAttribute("data-options"))) || {};
			let data = await HDQ.getValueByType[type](input, o);
			return data;
		},
		checkRequiredFields: async function (r) {
			let valid = true;
			for (let i = 0; i < r.length; i++) {
				value = r[i].value;

				if (typeof value == "object") {
					value = r[i].value.text; // for editor
				}

				if (r[i].required === "required" && (value.length <= 0 || value == "\n")) {
					if (r[i].type == "editor") {
						document.getElementById("wp-" + r[i].name + "-editor-container").classList.add("hdq_input_required");
					} else {
						document.getElementById(r[i].name).classList.add("hdq_input_required");
					}
					try {
						document.querySelector(`#tab_nav .tab_nav_item[data-id="${r[i].tab}"]`).classList.add("hdq_input_required");
					} catch (e) {}

					valid = false;
				} else {
					if (r[i].type == "editor") {
						document.getElementById("wp-" + r[i].name + "-editor-container").classList.remove("hdq_input_required");
					} else {
						document.getElementById(r[i].name).classList.remove("hdq_input_required");
					}
				}
			}

			let tabs = document.querySelectorAll("#tab_nav .tab_nav_item.input_required");
			for (let i = 0; i < tabs.length; i++) {
				let tab = tabs[i].getAttribute("data-id");
				let required = document.getElementById("tab_" + tab).querySelectorAll(".input_required");
				if (required.length === 0) {
					tabs[i].classList.remove("hdq_input_required");
				}
			}

			return valid;
		},
	},
	getValueByType: {
		title: async function (input, options) {
			return input.value;
		},
		text: async function (input, options) {
			return input.value;
		},
		quiz_name: async function (input, options) {
			return input.value;
		},
		email: async function (input, options) {
			function isValidEmail(email) {
				var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
				return re.test(String(email).toLowerCase());
			}

			let emails = input.value;
			emails = emails.split(",");

			if (emails.every(isValidEmail)) {
				return input.value;
			} else {
				return "";
			}
		},
		encode: async function (input, options) {
			return input.value;
		},
		image_select: async function (input, options) {
			return input.getAttribute("data-value");
		},
		checkbox: async function (input, options) {
			let items = input.getElementsByClassName("hdq_checkbox_input");
			let value = [];
			for (let i = 0; i < items.length; i++) {
				if (items[i].checked === true) {
					value.push(items[i].value);
				}
			}
			return value;
		},
		radio: async function (input, options) {
			return input.value;
		},
		integer: async function (input, options) {
			let d = input.value || null;
			if (d != null) {
				if (d > options.max) {
					d = options.max;
				}
				if (d < options.min) {
					d = options.min;
				}
				input.value = parseInt(d);
				return parseInt(d);
			} else {
				return "";
			}
		},
		float: async function (input, options) {
			let d = input.value || null;
			if (d != null) {
				if (d > options.max) {
					d = options.max;
				}
				if (d < options.min) {
					d = options.min;
				}
				input.value = parseFloat(d);
				return parseFloat(d);
			} else {
				return "";
			}
		},
		editor: async function (input, options) {
			await tinyMCE.triggerSave(); // trigger tinyMCE so we can get the value
			return input.value;
		},
		select: async function (input, options) {
			return input.options[input.selectedIndex].value;
		},
		image: async function (input, options) {
			return input.getAttribute("data-value");
		},
		categories: async function (input, options) {
			let categories = [];
			let quizzes = input.querySelectorAll(".hdq_category_input");

			for (let i = 0; i < quizzes.length; i++) {
				let cat = quizzes[i];
				if (cat.checked) {
					categories.push(cat.getAttribute("data-id"));
				}
			}
			return categories;
		},
		answers: async function (input, options) {
			let answers = document.querySelectorAll("#answers .hdq_input_answer");
			let v = [];
			for (let i = 0; i < answers.length; i++) {
				v[i] = {
					answer: "",
					image: "",
				};
				let answer = "";
				if (answers[i].value != "") {
					answer = answers[i].value;
					v[i].answer = answer;
				}
			}
			answers = document.querySelectorAll("#answers .input_image");
			for (let i = 0; i < answers.length; i++) {
				let iv = parseInt(answers[i].getAttribute("data-value"));
				if (iv > 0) {
					v[i].image = iv;
				}
			}
			return v;
		},
		correct: async function (input, options) {
			let selected = document.querySelectorAll("#answers .hdq_radio_input");
			let v = [];
			for (let i = 0; i < selected.length; i++) {
				if (selected[i].checked == true) {
					v.push(i + 1);
				}
			}
			return v;
		},
	},
	scroll: function () {
		function scroll() {
			jQuery("html").animate(
				{
					scrollTop: 0,
				},
				550
			);
		}
		setTimeout(scroll, 100);
	},
	removeDataUpgradeNotice: function () {
		this.parentNode.parentNode.remove();

		jQuery.ajax({
			type: "POST",
			data: {
				action: "hdq_remove_data_upgrade_notice",
			},
			url: ajaxurl,
		});
	},
};

if (jQuery("body").hasClass("toplevel_page_hdq_quizzes")) {
	HDQ.init.quizzes();
}

if (jQuery("body").hasClass("hd-quiz_page_hdq_options")) {
	HDQ.init.settings();
}

// Accordion
// ______________________________________________
jQuery(".hdq_accordion h3").on("click", function () {
	jQuery(this).next("div").toggle(600);
});
