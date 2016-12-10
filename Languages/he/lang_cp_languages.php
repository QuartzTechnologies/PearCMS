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
 * @version		$Id: lang_cp_languages.php 41 2012-04-03 01:41:41 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */


return array(
	'lang_manage_form_title'	=>	'ניהול שפות',
	'lang_manage_page_title' => 'ניהול שפות',
	'lang_manage_form_desc' => 'בטופס זה תוכל לנהל את השפות בהם יוצג אתרך, בחר באפשרות אותה תרצה לבצע.<br />שים לב: בכדי לתרגם שפות דרך לוח הבקרה, על תיקית השפות להיות בעלת הרשאות כתיבה וקריאה.',
	'create_new_language_pack' => 'הוסף סט שפה חדש',
	'lang_name_field'	=>	'שם השפה',
	'lang_key_field' => 'מפתח השפה',
	'lang_is_default_field' => 'ברירת מחדל',
	'lang_calendar_week_from_sunday_field' => 'יום ראשון הוא היום הראשון בשבוע<br /><span class="description">במידה ואפשרות זו מסומנת כ-&quot;כן&quot;, יום ראשון יזוהה כיום הראשון בשבוע, אחרת, יום שני יהיה היום הראשון בשבוע.</span>',
	'lang_translate_field' => 'תרגם',
	'edit_lang_page_title' => 'עריכת שפה: %s',
	'edit_lang_form_title' => 'עריכת שפה <span class="italic underline">%s</span>',
	'add_lang_page_title' => 'הוספת שפה',
	'add_lang_form_title' => 'הוספת שפה חדשה',
	'save_new_lang_submit' => 'צור שפה חדשה',
	'lang_uuid_field' => 'מזהה סידורי (UUID)<br /><span class="description">מזהה זה נוצר באופן אוטומטי על ידי PearCMS בשעת יצירת השפה. המערכת משתמשת בזמהה זה בכדי לזהות את ערכת שפה זו.</span>',
	'lang_name_field' => 'שם השפה',
	'lang_author_field' => 'יוצר השפה',
	'lang_author_website_field' => 'אתר האינטרנט של יוצר השפה',
	'lang_enabled_field' => 'אפשר בחירה בשפה',
	'lang_dir_field' => 'תיקיית (מפתח) השפה',
	'lang_is_rtl_field' => 'טקסט השפה מוצג מימין לשמאל<br /><span class="description">במידה ותבחר באפשרות זו, האתר יוצג מימין לשמאל. אפשרות זו מומלצת לשפות כדוגמאת עברית וערבית.</span>',
	'cannot_disable_default_lang' => 'לא ניתן לחסום את שפת ברירת המחדל, אנא  בחר בשפה אחרת כשפת ברירת מחדל וחזור לטופס זה.',
	'language_pack_already_exists' => 'שם ערכת השפה קיימת כבר, אנא בחר שם אחר.',
	'lang_name_blank' => "שדה שם השפה אינו יכול להישאר ריק",
	'language_author_website_invalid' => 'כתובת אתר האינטרנט של יוצר ערכת השפה אינה תקינה.',
	'cannot_find_lang_key' => 'תיקית השפה לא נמצאה, אנא וודא כי תיקית השפה נמצאת בתיקית השפות ונסה שנית.',
	'log_edited_lang_settings' => 'ערך את הגדרות השפה %s.',
	'lang_settings_edited_success' => 'עריכת הגדרות השפה הושלמה בהצלחה.',
	'log_added_lang_settings' => 'הוסיף שפה חדשה בשם %s.',
	'lang_settings_added_success_with_workspace' => 'הוספת השפה הושלמה בהצלחה. המערכת יצרה עבורה את סביבת העבודה ליצירת ערכת השפה, הקיימת כעת בדרך "%s".',
	'lang_settings_added_success_without_workspace' => 'הוספת השפה הושלמה בהצלחה. לא היה ניתן ליצור באופן אוטומטי את סביבת העבודה לערכת השפה אותה יצרת, אנא בצע את הפעולות הבאות בכלי ליצור באופן עצמאי את סביבת העבודה לערכת השפה.',
	'could_not_open_lang_file_no_perms' => 'לא היה ניתן לפתוח את הקובץ %s.<br />אנא וודא כי יש לתיקיה %s הרשאות כתיבה וקריאה (chmod 0777).',
	'select_language_file_field' => 'בחר קובץ שפה',
	'lang_array_key_field' => 'מפתח השפה<br /><span class="description">אותיות אנגליות, מספרים ומקף תחתון בלבד</span>',
	'lang_array_value_field' => 'ערך',
	'translate_lang_page_title' => 'תרגום שפה',
	'translate_lang_form_title' => 'תרגום שפה',
		'cannot_find_requested_language_folder' => 'לא היה ניתן לקרוא את תיקית השפה לערכת השפה המבוקשת.<br />אנא וודא כי התיקיה הבאה קיימת וניתנת לקריאה: <span class="italic">%s</span>',
	'translate_lang_form_desc' => 'בטופס זה תוכל לתרגם את ערכי השפה באתר בכדי להתאים אותה לשפה אותה אתה רוצה לספק לגולשיך.<br />בכדי לתרגם שפה, בחר קובץ מהרשימה הנ&quot;ל, ותרגם את הערכים המופיעים בתיבות הטקסט. בסיום, לחץ על כפתור ה&quot;שמור&quot; ועבור לקובץ הבא.',
	'available_lang_files_field' => 'בחר קובץ שפה לתרגום',
	'requested_file_not_found' => 'הקובץ המבוקש לא נמצא.',
	'translated_lang_file' => 'תרגם קובץ שפה.',
	'file_translate_success' => 'תרגום קובץ השפה נשמרו בהצלחה.',
	'could_not_delete_default_lang' => 'לא היה ניתן למחוק את סט שפה זה מכיוון שאין סט שפה חלופי למערכת, אנא צור סט שפה חדש ולאחר מכן חזור לטופס זה.',
	'delete_language_form_title' => 'מחיקת סט השפה <span class="italic underline">%s</span>',
	'delete_language_page_title' => 'מחיקת סט השפה %s',
	'delete_language_form_desc' => 'בכדי למחוק את שפה זה, עליך לבחור בסט שפה אחר אליו המערכת תנטב את המשתמשים המשתמשים בסט שפה זה.',
	'members_move_field' => 'בחר סט שפה חלופי למשתמשים בסט שפה זה',
	'lang_delete_confirm_field' => 'האם אתה בטוח שברצונך למחוק סט שפה זה?',
	'lang_delete_button' => 'מחק סט שפה',
	'lang_deletion_operation_canceled' => 'לא אישרת את פעולת המחיקה - הפעולה בוטלה.',
	'log_deleted_lang_file' => 'מחק סט שפה.',
	'deleted_lang_file_success' => "מחיקת סט השפה הושלמה בהצלחה",
	'create_lang_file_form_title' => 'יצירת קובץ שפה חדש',
	'create_lang_file_page_title' => 'יצירת קובץ שפה',
	'create_lang_file_form_desc' => 'בטופס זה תוכל ליצור קובץ שפה חדש לשימוש במערכת, רשום בצד שמאל את מפתחות המילים (array keys) ובצד ימין את ערכי המילים (array values).',
	'lang_file_name_field' => 'שם קובץ השפה',
	'lang_file_dir_field' => 'תיקית סט השפה',
	'lang_array_keys_field' => 'מפתחות השפה',
	'lang_array_values_field' => 'ערכי השפה',
	'create_new_lang_file_button' => 'צור קובץ שפה חדש',
	'lang_file_name_blank' => 'שדה שם הקובץ לא יכול להישאר ריק.',
	'lang_keys_blank' => 'שדה מפתחות השפה לא יכול להישאר ריק.',
	'lang_values_blank' => 'שדה ערכי השפה לא יכול להישא ריק.',
	'could_not_create_file' => 'לא היה ניתן ליצור את הקובץ.',
	'log_created_lang_file' => 'יצר קובץ שפה חדש.',
	'created_lang_file_success' => 'יצירת קובץ השפה הושלמה בהצלהח.',	
	'lang_install_form_title' => 'חבילות השפה הממתינות להתקנה',
	'install_new_language_pack_link' => '&gt;&gt; התקן חבילת שפה חדשה &lt;&lt;',
	'installed_lang_pack_success' => 'חבילת השפה הותקנה בהצלחה',
	'cannot_read_languages_directory' => 'לא היה ניתן לקרוא את תיקית ערכות השפה בדרך &quot;%s&quot;.',
	'keys_and_values_mismatch' => 'כמות המפתחות בקובץ צריכה להיות ככמות הערכים בקובץ.',
	'error_new_file_name_in_use' => 'השם שבחרת לקובץ השפה קיים, אנא בחר שם אחר.',
	'create_language_structure_page_title' => 'יצירת סביבת עבודה לערכת הנושא %s',
		'create_language_structure_form_title' => 'יצירת סביבת עבודה לערכת השפה %s',
		'create_language_structure_form_desc' => 'בכדי להתחיל לתרגם את ערכת השפה עליך להשלים לבנות את סביבת העבודה שלה.<br />לפניך פירוט ההוראות לסיום יצירת ערכת הנושא, במידה ואינך יודע כיצד לבצע אותן, אנא פנה לצוות התמיכה של PearCMS לקבלת עזרה.',
		'workspace_create_dir_title' => 'יצירת תיקית ערכת הנושא',
		'workspace_create_dir_guide' => 'עליך ליצור תיקיה בשם &quot;%s&quot; בדרך &quot;%s&quot; (התיקיה המכילה את כל ערכות השפה הזמינות במערכת) ולהכיל על התיקיה הרשאות chmod 0755.',
		'workspace_create_bootstrap_title' => 'יצירת קובץ האתחול של ערכת השפה',
		'workspace_create_bootstrap_guide' => 'עליך ליצור קובץ בשם &quot;Bootstrap.php&quot; בתיקיה &quot;%s&quot; ובו לכלול את התוכן הבא: <br /><div style="direction:ltr; text-align: left; overflow: auto; width: 550px;"><pre class="brush: js;" style="direction:ltr; text-align: left;">%s</pre></div>',
	);