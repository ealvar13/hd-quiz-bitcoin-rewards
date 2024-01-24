<?php
class hdq_settings
{
    public $settings = array();

    function __construct()
    {
        $fields = array();

        /*
            * I just felt it was cleaner
            * to write the arrays as JSON
        */

        // Translations
        $translations = '{
            "hd_qu_next": {
                "name": "hd_qu_next",
                "value": "Next",
                "type": "text"
            },
            "hd_qu_finish": {
                "name": "hd_qu_finish",
                "value": "Finish",
                "type": "text"
            },
            "hd_qu_start": {
                "name": "hd_qu_start",
                "value": "QUIZ START",
                "type": "text"
            },
            "hd_qu_results": {
                "name": "hd_qu_results",
                "value": "Results",
                "type": "text"
            },
            "hd_qu_text_based_answers": {
                "name": "hd_qu_text_based_answers",
                "value": "enter answer here",
                "type": "text"
            },
            "hd_qu_select_all_apply": {
                "name": "hd_qu_select_all_apply",
                "value": "Select all that apply:",
                "type": "text"
            }
        }';
        $translations = json_decode($translations, true);
        foreach ($translations as $k => $data) {
            $fields[$k] = $data;
        }

        // Social Media
        $social_media = '{
            "hd_qu_fb": {
                "name": "hd_qu_fb",
                "value": "",
                "type": "text"
            },
            "hd_qu_tw": {
                "name": "hd_qu_tw",
                "value": "",
                "type": "text"
            },
            "hd_qu_share_text": {
                "name": "hd_qu_share_text",
                "value": "I scored %score% on the %quiz% quiz. Can you beat me?",
                "type": "text"
            }
        }';
        $social_media = json_decode($social_media, true);
        foreach ($social_media as $k => $data) {
            $fields[$k] = $data;
        }

        $others = '{
            "hd_qu_authors": {
                "name": "hd_qu_authors",
                "value": [""],
                "type": "checkbox"
            },
            "hd_qu_percent": {
                "name": "hd_qu_percent",
                "value": [""],
                "type": "checkbox"
            },
            "hd_qu_heart": {
                "name": "hd_qu_heart",
                "value": [""],
                "type": "checkbox"
            },
            "hd_qu_legacy_scroll": {
                "name": "hd_qu_legacy_scroll",
                "value": [""],
                "type": "checkbox"
            },
            "hd_qu_adcode": {
                "name": "hd_qu_adcode",
                "value": "",
                "type": "textarea"
            }
        }';
        $others = json_decode($others, true);

        foreach ($others as $k => $data) {
            $fields[$k] = $data;
        }

        $this->settings = $fields;
    }

    public function get()
    {
        $settings = get_option("hdq_settings");
        if ($settings != "") {
            $settings = hdq_sanitize_fields($settings);
            foreach ($this->settings as $k => $setting) {
                if (isset($settings[$k]) && $settings[$k]["value"] !== "") {
                    $this->settings[$k]["value"] = $settings[$k]["value"];
                }
            }
        }
        return $this->settings;
    }

    public function save()
    {
        if (!current_user_can('edit_others_pages')) {
            return; // permission denied. Only admins and editors should ever have access
        }

        // TODO: Replace old save with this.
    }
}
