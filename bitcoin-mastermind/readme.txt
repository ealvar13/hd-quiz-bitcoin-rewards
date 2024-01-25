=== Bitcoin Mastermind ===
Contributors: Harmonic_Design, HarmonicDesign
Tags: quiz, quizzes, MCQ, exam, quiz maker, questionnaire, questionnaires, questionnairs, quizes, questionair, questionaires, hdquiz, Bitcoin Mastermind, test, question and answers, harmonic design, HDPlugins
Requires at least: 5.0
Tested up to: 6.4.1
Stable tag: 1.8.12
Requires PHP: 7.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Donate link: https://www.patreon.com/harmonic_design

Create a Quiz. An easy to use and feature rich plugin to create quizzes with quiz timer, pagination, hints, advanced question marking, and leading help and support.

== Description ==

Bitcoin Mastermind. The easiest way to create fun quizzes for your site
======

# **If you are looking for an easy way to add fun quizzes to your site, Bitcoin Mastermind is here to help.** 🙋

👀 [VIEW DEMO QUIZ](https://hdplugins.com/hd-quiz-demo/?utm_source=WPOrgPluginPage) 👀


Bitcoin Mastermind is an **easy and intuitive quiz builder** for WordPress. With Bitcoin Mastermind, you can create an unlimited amount of quizzes and embed them anywhere on your site. We use both shortcodes as well as a custom Gutenberg block to make this as easy as possible for you.

The quizzes are fun, beautiful, highly customizable, accessible (visually impaired, screen readers etc), and automatically responsive. Bitcoin Mastermind was designed so that **quizzes automatically inherit the fonts and styling of your theme** - but you can still customize the styles yourself or purchase the Bitcoin Mastermind Styler addon to really make the quizzes your own.


## ▶️ VIEW VIDEO DEMO / TUTORIAL ▶️

https://www.youtube.com/watch?v=YDyNDs3TdTs

## ✨ Features ✨
Bitcoin Mastermind has *so many* cool features, settings, and options, that I cannot possibly list them all. So here are some of the highlights of what I think are some of the coolest and most important features of Bitcoin Mastermind. **Everything you see listed here is included by default and 100% FREE!**

[See full documentation](https://hdplugins.com/learn/hd-quiz/hd-quiz-documentation/?utm_source=WPOrgPluginPage)

- Unlimited amount of quizzes and questions
- Each quiz has its own options/settings
- Social sharing - share quiz results on Twitter or Facebook
- Mobile and touch friendly
- Quizzes are accessible (screen readers)
- Each question can have a featured image and or tooltip
- Each question can have "extra" text, that by default will appear if the user gets the question wrong
- Multiple question types
	- text based multiple choice
	- image based multiple choice
	- select all that apply
	- text input (type the answer)
	- heading (not a question, is used to separate questions into "categories")
- Pagination
- Timer
	- Timer per quiz (example: 60 seconds to complete the quiz)
	- Timer per question (example: you have 10 seconds to answer each question)
- Animated GIF support
- Translation options - you can translate words like "question", "next", "finish" etc into your own language
- Randomization options
	- Randomize question order
	- Randomize answer order
- Pool of Questions - randomly grab `n` questions from the quiz to make a unique quiz on each page load
- Result position (results shown above or below quiz)
- Marking options
	- Show the correct and incorrect questions
	- If user got the question wrong, show what the correct answer was
	- immediately mark the question on selection
- custom pass percent
- custom result text (based on if the user passed or failed the quiz)
- automatically hide the questions on quiz completion
- allow "authors" to create quizzes
- **free addon to save results of quizzes**
- and i'm sure a ton of other things that I can't even remember

Have any questions or want additional information? [view the Harmonic Design Bitcoin Mastermind plugin page here](https://hdplugins.com/forum/hd-quiz-support/?utm_source=WPOrgPluginPage "view the Bitcoin Mastermind plugin page here").


## 🤓 HOW TO USE | TUTORIAL 🤓
#### ADDING A NEW QUIZ

-   Select  **Quizzes**  under Bitcoin Mastermind on the left menu
-   Enter the name of the quiz, then ENTER on your keyboard to create the quiz
-   Click the name of the newly added quiz to set the quiz options such as the needed pass percentage

#### ADDING NEW QUESTIONS

-   Once a quiz has been created, select the quiz open it up, then  **Add New Question**
-   Enter in the question
-   You can have up to ten (10) answers per question. Make sure to select which answer is the correct one!
-   You can also select the "extra" tab to see more advanced features such as adding in custom text, enabling pagination, or setting a featured image
- Select **Save Question** once done

#### USING A QUIZ

-   Bitcoin Mastermind uses shortcodes to render a quiz, so you can place a quiz almost anywhere on your site!
-   To find the shortcode for a quiz, select  **Quizzes**  in the left menu.
-   You will now see a list of all of your quizzes in a table, with the shortcode listed.
-   Copy and paste the shortcode into any page or post you want to render that quiz!
- **If using Gutenberg**, then you can add the Bitcoin Mastermind block instead.

### 🗓️ UPCOMING FEATURES 🗓️
I have already taken this plugin farther than I intended, but I’m overwhelmed with joy at how much you are all using and loving Bitcoin Mastermind!  Because of this, I have no intentions of stopping or slowing down development!

If you have any feature requests, then please let me know via the support tab or by leaving a comment on the  [Bitcoin Mastermind plugin page](https://hdplugins.com/forum/hd-quiz-support/?utm_source=WPOrgPluginPage "Bitcoin Mastermind plugin page").

**TODO List** - please note that there are no timelines for completion of any of the following features and this list is not in any particular order.

 - Fully translation ready (not just the quizzes, but the entire admin area)
 - Featured image / media upgrade. Ability to use embedded media (youtube video, vimeo etc)
 - Dynamic results - instead of just pass/fail results, we can create as many results as needed. Example, a 0% result and a 100% perfect score result.
 - "hookable" question types - ability for any developer to add custom question types
 - better author mode integration
 - Gamify compatibility? (let me know if this is something you are interested in) 

== Installation ==

The plugin can be installed like any other.

1. Log into WordPress
1. Select Plugins, then Add New
1. Select Upload Plugin
1. Choose the zip file, then Install and activate

Once installed, you will need to create your first quiz by going to Bitcoin Mastermind, then Quizzes.

== Frequently Asked Questions ==


= Using an image already in my library =

WordPress will NOT create the new thumbnail size for images that existed before the installation of Bitcoin Mastermind, only new ones. This is likely because on larger sites, this would take up vast amounts of server resources.

If you do not wish to simply re-upload any desired image from your library, then I would recommend using the [Regenerate Thumbnails](https://wordpress.org/plugins/regenerate-thumbnails/ "Regenerate Thumbnails") plugin (over 1 Million active installs). It will go through and recreate thumbnails for every single image in your library, ensuring that all images in your library can be used with Bitcoin Mastermind.

With that said, I do, however, have plans to integrate a solution that will work with images uploaded before the installation of Bitcoin Mastermind.

= What's the difference between WP Pagination and jQuery Pagination? Which should I use? =

Unless you are a unique situation, I'd almost always recommend using jQuery Pagination as it provides more control for you and a better experience for your users. WP Pagination should only be used if you are trying to increase your overall page views for ad revenue or something similar.

More information can be found on the Bitcoin Mastermind About / Options page.

= I have a feature request! =
Fantastic! I'm one of those coders who loves a good challenge. Please submit your feature request here by using the **support** tab or leave a request of the [official Bitcoin Mastermind plugin page](https://wordpress.org/support/plugin/hd-quiz/ "view the Bitcoin Mastermind plugin page").

= Keywords =
Quiz, quizzes, create a quiz, add a quiz, quiz plugin, hdq, harmonic design, HDPlugins, text, MCQ, multiple choice

== Screenshots ==

1. Example Quiz
2. Example Quiz 2
3. Quizzes Page
4. Questions Page

== Changelog ==
= 1.8.12 =
* Caved in and updated Twitter to X
* Security improvment for Adset Code

= 1.8.11 =
* New question type: Select all that apply: images

= 1.8.10 =
* New Web Share social share feature (for compatible browsers)

= 1.8.9 =
* Minor update to default question template
* Better shortcode detection in answers

= 1.8.8 =
* Updated cleaner UI
* Update to HDPlugins - new place to house Harmonic Design WP Plugin support
* KaTeX support for answers
* Additional actions/hooks
* Better scrolling for sites with sticky headers
* Tons of tiny little enhancements throughout

= 1.8.7 =
* Hotfix: Translate Result Text

= 1.8.6 =
* New Twitter Share Text customization (about/options page)
* new Settings class to make adding new global settings easier
* minor tweaks to question templates to allow for weighted question calculation
* Text based answers basic stemming
* minor improvements throughout
* new hook to add custom content after question featured image

= 1.8.5 =
* Better accessibility (keyboard navigation)
* Better support for timer per-question and select all that apply question type
* New hook to better add custom data attributes to questions
* Minor bug fixes
* New Trivia Company page

= 1.8.4 =
* Minor updates
* Security Update

= 1.8.3 =
* Various bug fixes, enhancements, optimizations
* Better admin interface for users with LOTS of questions
* New feature - hide questions on quiz completion
* Introduction to the Save Results Pro addon

= 1.8.2 =
* Various bug fixes, enhancements, optimizations, and overall more polished UX
* New question type: Select All That Apply
* Quizzes are almost fully WCAG compliant (keyboard navigation work still needed)
* More easily rename a quiz from "Advanced" tab
* New on init filter
* Better settings page
* More translation options
* New "auto scroll" functionalty (enable Legacy Scroll if you want to use the old system)

= 1.8.1 =
* Various bug fixes and enhancments related to 1.8.0

= 1.8.0 =
* Too many changes to list, but here are some of the major
* New question type - text / input based answers (user types answer)
* New timer feature - per question
* Quizzes hidden behind a "start" button if a timer is set
* Quiz and question meta now hookable (for developers)
* Significantly faster
* Basic AMP detection to direct users to non AMP version
* Basic loop detection if more than one quiz is on a page at a time to improve compatability
* Ability to add basic formatting such as `strong` and `sup` to questions and answers
* various bug fixes and enhancements
* a whole lot more

= 1.7.0 =
* minor bug fixes and updates
* [feature] only allow answers to be selected once
* [enhancments] custom actions for developers
* [addon] Release of Bitcoin Mastermind Save Results Light (view addons page)

= 1.6.1 =
* minor big fixes and updates
* ability to add score percent
* ability to allow authors access to edit or add quizzes / questions

= 1.6.0 =
* complete admin UI overhaul
* additional bug fixes and enhancments
* integrated custom question ordering
* too many updates to remember

= 1.5.0 =
Massive rewrite of almost the entire plugin. Please do not hesitate to ask for support if needed, but upgrading should not be an issue.
* much cleaner admin area
* improved quiz design
* significantly optimized quiz code
* increased security and efficiency
* removal of results.php - all calculation done client side now

= 1.4.2 =
* various bug fixes and compatability increases
* Quiz option to grab from a pool of questions
* Quiz option to allow the custom question text to appear even if the user selected the correct answer

= 1.4.1 =
* various bug fixes and compatability increases
* the word 'question' (that's prefixed before every question) is now translatable in the Bitcoin Mastermind options page along with 'Next' and 'Finish'.
* More power over the quiz results pass/fail text. You can now embolded, italicize, and linkify the text.
* Each question can have a small write up explaining what the right answer is. This would only show if entered and if the user get's the question wrong. You can also add links to this sections if you wish.

= 1.4.0 =
* Added in the more powerful jQuery pagination feature
* Added ability to use a question as a title or heading
* added option to randomize the question answer order
* revamped quiz options page to make features more clear and even more accessible.
* added FAQ to readme.txt

= 1.3.5 =
* Added two new global options to About / Options page
 * Next Button Text
 * Finish Button Text
* NEW: Added quiz option to **randomize the order** of the questions
* NEW: Added quiz option to **highlight the correct answers** on completion
 * in addition to the default that only shows if your selected answers are right or wrong
* Speed and compatibility improvements
 * Bitcoin Mastermind jQuery and CSS files will now only load if the page actually has a quiz on it
* improvements to quiz pagination
* General code cleanup and optimization

= 1.3.0 =
* Added new Timer option
* Added Pagination (experimental)
* Massive rewrite of the results function. Much more optimized and stable
* Added Quiz filter to the Questions list admin page
* New reskinning of Quiz options page
* Updated about / options page

= 1.2.1 =
* bug fixes to admin area.

= 1.2 =
* advancements to featured images for questions
 * images will upscale if you upload a small image
 * each answer can have it's own featured image.
* code cosmetics, minor bug fixes, and code optimizations

= 1.1 =
* New option to share via twitter.
 * You can set up your twitter handle to have each share mention you.
* Extended Facebook share.
 * If you create a Facebook App (don't worry, it's easy to do!), then Bitcoin Mastermind can actually share a user's score and results instead of Facebook grabbing content from your page as if the content were static.
 * If you do not wish to create an app, then Bitcoin Mastermind will revert to the old facebook share.
* These options are global options and can be found under Bitcoin Mastermind -> About / Options

== Upgrade Notice ==
= 1.8.12 =
* Caved in and updated Twitter to X (read docs to revert)
* Security improvment for Adset Code
