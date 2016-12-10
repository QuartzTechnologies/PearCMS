<?php

/**
 *
 (C) Copyright 2011-2016 Pear Technology Investments, Ltd.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @copyright	(C) 2011-2012 Pear Technology Investments, Ltd.
 * @license		http://www.apache.org/licenses/LICENSE-2.0	Apache License 2.0
 * @category		PearCMS
 * @package		PearCMS Hebrew Language Pack
 * @author		$Author: Yahav Gindi Bar $
 * @version		$Id: lang_cp_addons.php 41 2012-04-03 01:41:41 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */


return array(
	'addons_manage_page_title' => 'ניהול תוספים',
	'addons_manage_form_title' => 'ניהול תוספים',
	'addons_manage_form_desc' => 'בטופס זה תוכל לנהל את התוספים המותקנים באתרך, לערוך את תפעולם, לחסום/להפעיל אותם ועוד.<br />בכדי להתקין תוסף חדש, עליך להעלות את קבציו לתיקיה <span class="italic">%s</span> ולאחר מכן ללחוץ על הקישור &quot;התקן כעת&quot;.<br />שים לב כי תוכל להסיר כל תוסף ברגע שתרצה דרך טופס זה.',
	'create_new_addon' => 'צור תוסף חדש',
	'addon_name_field' => 'שם התוסף',
	'addon_enabled_field' => 'פעיל',
	'addon_name_with_version_pattern' => '<span class="bold italic">%s</span> <span class="description">(גרסא: <span class="italic">%s</span>)</span>',
	'addon_author_pattern' => 'יוצר: <span class="italic">%s</span>',
	'addon_uuid_pattern' => 'מזהה סידורי: <span class="italic">%s</span>',
	'addon_install_now_button' => '&gt;&gt; התקן כעת &lt; &lt;',
	'addons_pending_form_title' => 'תוספים הממתינים להתקנה',
	'log_toggle_addon_enable_state' => 'הפעיל/חסם את התוסף %s.',
	'edit_addon_page_title' => 'עריכת התוסף %s',
	'edit_addon_form_title' => 'עריכת התוסף <span class="italic underline">%s</span>',
	'create_addon_page_title' =>  'יצירת תוסף חדש',
	'create_addon_form_title' => 'יצירת תוסף חדש',
	'create_addon_submit' => 'צור תוסף חדש',
	'addon_uuid_field' => 'מזהה סידורי (UUID)<br /><span class="description">זהו מזהה ייחודי אותו המערכת יוצרת עבורך בשעת יצירת התוסף.<br /><span class="bold">מזהה זה נועד לצורכי זיהויי התוסף על ידי המערכת ולייצוא התוסף בלבד.</span></span>',
	'addon_key_create_field' => 'מפתח התוסף<br /><span class="description">מפתח התוסף הוא שם ייחודי, הבנויי מאותיות אנגליות, ספרות, קו תחתון (_) ומקף (-) בלבד.<br />מפתח התוסף מייצג את שם קובץ התוסף / תיקית התוסף בתיקית התוספים של המערכת.</span>',
	'addon_key_edit_field' => 'מפתח התוסף<br /><span class="description">מפתח התוסף הוא שם ייחודי, הבנויי מאותיות אנגליות, ספרות, קו תחתון (_) ומקף (-) בלבד.<br />מפתח התוסף מייצג את שם קובץ התוסף / תיקית התוסף בתיקית התוספים של המערכת.</span>',
	'addon_description_field' => 'תאור התוסף',
	'addon_author_key' => 'יוצר התוסף',
	'addon_author_website_key' => 'כתובת אתר יוצר התוסף',
	'addon_version_field' => 'גרסאת התוסף',
	'addon_enabled_field' => 'התוסף מופעל',
	'could_not_locate_addon_boostrap' => 'לא היה ניתן למצוא את קובץ ההגדרות של התוסף %s (מפתח: %s).',
	'addon_already_exists' => 'תיקיה בעלת מפתח זהה קיימת, אנא בחר מפתח אחר התוסף.',
	'could_not_find_addon_class' => 'לא היה ניתן למצוא את מחלקת האם של התוסף (מפתח: %s), על מחלקת האם להיקרא: %s.',	
	'install_addon_key_exists' => 'לא היה ניתן להתקין את התוסף המבוקש מכיוון שמפתח התוסף %s כבר בשימוש על ידי התוסף %s.ש לי על',
	'cannot_install_addon_damaged' => 'לא היה ניתן להתקין את התוסף (מפתח: %s). התוסף פגום.',
	'cannot_uninstall_addon_damaged' => 'לא היה ניתן להסיר את התוסף (מפתח: %s). התוסף פגום.',
	'cannot_install_addon_refused' => 'לא היה ניתן להתקין את התוסף %s - התוסף סרב לבקשה.<br />סיבה: %s',
	'cannot_uninstall_addon_refused' => 'לא היה ניתן להסיר את התוסף %s - התוסף סרב לבקשה.<br />סיבה: %s',
	'addon_author_field' => 'יוצר התוסף',
	'addon_author_website_field' => 'אתר הבית של יוצר התוסף',
	'addon_installed_success' => 'התוסף <span class="bold italic">%s</span> הותקן בהצלחה.',
	'addon_installation_log' => 'דו&quot;ח התקנת תוסף',
	'addon_uninstalled_success' => 'ה <span class="bold italic">%s</span> הוסר בהצלחה.',
	'addon_uninstallation_log' => 'דו&quot;ח הסרת תוסף',
	'addon_name_blank' => 'שם התוסף אינו יכול להישאר ריק.',
	'addon_version_blank' => 'גרסאת התוסף אינה יכולה להישאר ריקה.',
	'log_edited_addon' => 'ערך את התוסף &quot;%s&quot;.',
	'addon_edited_success' => 'התוסף %s נערך בהצלחה.',
	'log_added_addon' => 'הוסיף את התוסף &quot;%s&quot;.',
	'addon_added_success' => "התוסף %s נוסף בהצלחה.\nPearCMS יצרה באופן אוטומטי מפתח UUID ייחודי לתוסף אותו יצרת והוא: %s.\nסביבת העבודה של התוסף נוצרה בהצלחה ונמצאת בדרך: %s.",
	'addon_added_success_no_workspace' => "התוסף %s נוצר בהצלחה.\nPearCMS יצרה באופן אוטומטי מפתח UUID ייחודי לתוסף אותו יצרת והוא: %s.",
	'log_installed_addon' => 'התקין את התוסף &quot;%s&quot;',
	'log_uninstalled_addon' => 'הסיר את התוסף &quot;%s&quot;',
	'create_addon_structure_page_title' => 'יצירת סביבת עבודה לתוסף %s',
	'create_addon_structure_form_title' => 'יצירת סביבת עבודה לתוסף %s',
	'create_addon_structure_form_desc' => 'בכדי להתחיל לפתח את התוסף עליך להשלים לבנות את סביבת העבודה שלו.<br />לפניך פירוט ההוראות לסיום יצירת התוסף, במידה ואינך יודע כיצד לבצע אותן, אנא פנה לצוות התמיכה של PearCMS לקבלת עזרה.',
	'workspace_create_dir_title' => 'יצירת תיקית התוסף',
	'workspace_create_dir_guide' => 'עליך ליצור תיקיה בשם &quot;%s&quot; בדרך &quot;%s&quot; (התיקיה המכילה את כל תוספי המערכת) ולהכיל על התיקיה הרשאות chmod 0755.',
	'workspace_create_bootstrap_title' => 'יצירת קובץ האתחול של התוסף',
	'workspace_create_bootstrap_guide' => 'עליך ליצור קובץ בשם &quot;Bootstrap.php&quot; בתיקיה &quot;%s&quot; ובו לכלול את התוכן הבא: <br /><div style="direction:ltr; text-align: left; overflow: auto; width: 550px;"><pre class="brush: js;" style="direction:ltr; text-align: left;">%s</pre></div>',
	'workspace_create_controllers_title' => '<span class="italic">אופציונלי</span> תיקיות בקרים (controllers)',
	'workspace_create_controllers_guide' => 'בכדי להוסיף פעולות חדשות לתוסף עליך ליצור בתיקית התוסף (<span class="italic">&quot;%s&quot;</span>) את התיקיה &quot;Actions&quot; ובתוכה ליצור את התיקיות &quot;AdminCP&quot; ו-&quot;Site&quot; ולכולן להעניק הרשאות chmod 0755.',
	'workspace_create_themes_title' => '<span class="italic">אופציונלי</span> ערכות נושא וקבצי תצוגה (views)',
	'workspace_create_themes_guide' => 'בכדי להציג תוכן למשתמש, עליך ליצור תחת תיקית התוסף (<span class="italic">&quot;%s&quot;</span>) את התיקיה &quot;Themes&quot; ובתוכה ליצור תיקיה בשם &quot;Classic&quot; (מפתח ערכת ברירת המחדל) ובתוכה את התיקיות &quot;Views&quot;, &quot;Images&quot; ו-&quot;StyleSheets&quot;. עליך להעניק לתיקיות הרשאות chmod 0755.',
	'workspace_create_language_title' => '<span class="italic">אופציונלי</span> לוקאליזציה',
	'workspace_create_language_guide' => 'בכדי לאפשר תמיכה במערכת הלוקאליזציה (המאפשרת מגוון שפות) עליך ליצור בתיקית התוסף (<span class="italic">&quot;%s&quot;</span>) את התיקיה &quot;Languages&quot; ובתוכה את התיקיה &quot;en&quot; (מפתח ערכת ברירת המחדל). עליך להעניק לתיקיות אלו הרשאות chmod 0755.',
	'workspace_create_client_title' => '<span class="italic">אופציונלי</span> צד לקוח',
	'workspace_create_client_guide' => 'בכדי לאפשר לכלול קבצי JavaScript בתוסף, עליך ליצור בתיקית התוסף (<span class="italic">&quot;%s&quot;) את התיקיה &quot;Client&quot; ובתוכה את התיקיה &quot;JScripts&quot;. עליך להעניק לתיקיות הרשאות chmod 0755.',
);