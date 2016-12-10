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
 * @version		$Id: lang_cp_settings.php 41 2012-04-03 01:41:41 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */


return array(
	'site_offline_page_title' => 'נעילת האתר',
	'site_offline_form_title' => 'נעילת האתר',
	'site_offline_form_desc' => 'בטופס זה תוכל לנעול את האתר ולאפשר אך ורק למנהלים מורשים לצפות בו.',
	'site_is_offline_field' => 'המערכת מכובה?',
	'turn_on_or_off_site' => 'כיבה / הדליק את המערכת.',
	'site_state_success' => 'השינויי נשמר בהצלחה',
	'cp_settings_tab_title' => 'לוח הבקרה למנהלים',
	'cp_settings_form_title' => 'הגדרות לוח הבקרה למנהלים',
	'cp_settings_form_desc' => 'בטופס זה תוכל לנהל את הגדרות לוח הבקרה למנהלים שלך.',
	'cp_setting_use_passcode_field' => 'השתמש בסיסמאת גישה?<br /><span class="description">סיסמאת הגישה היא סיסמא נוספת ללוח הבקרה שמטרתה להקשות על התחברות משתמשים לא רצויים ללוח הבקרה. שים לב כי הסיסמא זהה לכל המשתמשים.</span>',

	'cp_setting_passcode_field' => 'סיסמאת הגישה לפאנל הניהול (בכדי לא לשנות את סיסמאת הגישה, השאר שדה זה ריק)<br />
		<span class="description">
		במידה ובחרת באפשרות סיסמאת הגישה, ציין בשדה זה מהי סיסמאת הגישה בה תרצה להשתמש.<br />
		שים לב כי במידה והשדה הנ&quot;ל לא נבחר, שדה זה אינו רלוונטי.
		</span>',
	'cp_setting_charset' => 'קידוד לוח הבקרה<br /><span class="description">במידה ואינך בטוח מהו ערכו של שדה זה, רשום utf-8</span>',
	'cp_setting_confirm_lang' => 'בכדי לאשר את השינויים בטופס זה, אנא רשום את סיסמאתך.',
	'cp_setting_err_invalid_pass' => 'סיסמאת המשתמש שציינת שגוייה, אנא נסה שנית.',
	'log_edited_cp_settings' => 'ערך הגדרות ראשיות ללוח הבקרה למנהלים',
	'edit_cp_settings_success' => 'הגדרות לוח הבקרה למנהלים נשמרו בהצלחה.',
	'content_root_directory_page_layout_field' => 'תבנית התצוגה של התיקית התוכן הראשית (/)<br /><span class="description">תיקית התוכן הראשית (root directory) היא התיקיה המכילה את כל קבצי המערכת, תוכל לבחור בהגדרה זו את תבנית התצוגה בה המערכת תשתמש כדי להציג את התיקיה</span>',
	'setting_page_title' => 'הגדרות כלליות',
	'general_setting_tab_title' => 'כללי',
	'advance_setting_tab_title' => 'מתקדם',
	'setting_form_title' => 'ניהול ההגדרות הכלליות של האתר',
	'title_field' => 'שם האתר',
	'slogan_field' => 'סלוגן<br /><span class="description">באפשרותך לכתוב סלוגן קצר המתאר את אתרך.</span>',
	'charset_field' => 'קידוד האתר',
	'meta_keywords_field' => 'מילות מפתח לחיפוש האתר<br /><span class="description">מילות המפתח יתרמו בקידום האתר במנועי החיפוש השונים, על מילות המפתח להיות מופרדות בפסיקים.</span>',
	'meta_description_field' => 'תאור האתר<br /><span class="description">בשדה זה תוכל לציין תאור קצר לאתר, תאור זה ישומש על ידי מנועי החיפוש בכדי לקדם את אתרך.</span>',
	'admin_email_field' => "אימייל מנהל האתר<br /><span class=\"description\">בעזרת כתובת אימייל זו ישלחו הודעות למשתמשים כגון עיתון, הודעות מערכת וכו'.</span>",
	'upload_path_field' => 'דרך פיזית לתיקית הקבצים המועלים<br /><span class="description">רשום כאן את כתובת הדרך הפיזית לקבצים המועלים <span class="bold">שים לב כי זו אינה כתובת URL אלא דרך לתיקיה.<br />לדוגמא: /home/admin/public_html/Client/Uploads</span>',
	'upload_url_field' => 'כתובת ה-URL לקבצים מועלים<br/ ><span class="description">ציין את כתובת ה-URL בה תשתמש המערכת בכדי לפנות לקבצים מועלים.</span>',
	'upload_max_size_field' => "גודל מקסימלי לקבצים מועלים<br /><span class=\"description\">ציין בשדה זה מהו גודל הקבצים המקסימלי לכל קובץ המועלה למערכת.</span>",
	'search_spam_timeout_field' => "זמן מנגנון חיפוש",
	'require_email_vertification_field' => "דרישת אימות הרשמה דרך אימייל",
	'search_anti_spam_filter_enabled_field' => "הפעל את מנגנון בקרת ההצפה בחיפוש האתר?<br /><span class=\"description\">מנגנון ההצפה מונע ממשתמשים לחפש יותר מביטויי אחד במשך מספר שניות המוגדר על ידך.</spn>",
	'require_captcha_field' => "דרוש תמונת אימות בהרשמה",
	'redirection_screen_field' => 'דפי מעבר',
	'redirectionScreen_type_loc_header' => 'ללא דפי מעבר (PHP Header)',
	'redirectionScreen_type_ref_header' => 'הצג דפי מעבר (PHP Header)',
	'redirectionScreen_type_loc_html' => 'ללא דפי מעבר (HTML Meta)',
	'redirectionScreen_type_ref_html' => 'הצג דפי מעבר (HTML Meta)',
	'redirectionScreen_type_loc_js' => 'ללא דפי מעבר (JS Location)',
	'time_offset_field' => 'אזור תצוגת הזמן של האתר<br /><span class="description">במידה ובחרת בזמן הנכון והשעון מאחר בשעה, תקלה זו נגרמת בעקבות שעון ורף/קיץ ומשתמשי האתר יכולים לתקן זאת דרך לוח הבקרה האישי שלהם.</span>',
	'time_adjust_field' => 'הארכת זמן (בדקות)<br /><span class="description">אתה יכול לכוון את שעון הזמן ידנית בעזרת הוספת / הסדרת דקות. במידה ואתה רוצה לחסר דקות, שים לב כי הנך נדרש להוסיף את הסימן &quot;-&quot; (ללא הציטוט) בתחילת הטקסט.<br />הזמן העכשווי <% TIME %>',
	'cookie_id_field' => 'תחילית לעוגיות<br /><span class="description">שימושי במידה ויש לך כמה התקנות של המערכת באותה כתובת מתחם.</span>',
	'cookie_domain_field' => 'כתובת מתחם לעוגיות',
	'cookie_path_field' => 'דרך לעוגיות',
	'site_settings_no_title' => "אינך יכול להשאיר את שדה כותרת האתר ריק.",
	'site_settings_no_charset' => "אינך יכול להשאיר את אזור הקידוד ריק, דבר זה יפגום באתר.",
	'site_settings_no_ademail' => "אינך יכול להשאיר את שדה כתובת מנהל האתר ריק, דבר זה יפגום באתר.",
	'site_settings_no_upload_path' => "אינך יכול להשאיר את שדה כתובת העלאת הקבצים ריק, דבר זה יפגום באתר.",
	'log_edited_site_settings' => "ערך הגדרות אתר כלליות",
	'edited_settings_success' => "העריכה הושלמה בהצלחה",
	'allow_secure_sections_ssl_field' => 'השתמש באזורים &quot;רגישים&quot; בהתקשרות מוצפנת (SSL)<br /><span class="description">במידה ואפשרות זו תסומן כ-&quot;כן&quot;, המערכת תשתמש בהתקשורות מוצפנת (פרוטוקול https://) באזורים &quot;רגישים / חסויים&quot; כגון התחברות משתמשים, לוח בקרה אישי, לוח הבקרה למנהלים וכו\'.</span><br /><span class="description red">שים לב כי בכדי להשתמש באפשרות זו, עליך לקבל &quot;הסמכת SSL&quot;, במידה ואינך בטוח, צור קשר עם צוות PearCMS למידע נוסף.</span',
	'advance_setting_form_title' => 'הגדרות מתקדמות',
	'toggle_module_custom_options' => 'או התאם אישית מודול זה',
	'toggle_module_allow' => 'אפשר',
	'toggle_module_disable' => 'חסום',
		'members_module' => 'משתמשים &amp; קבוצות משתמשים',
	
		'memberlist_module_name' => 'רשימת משתמשים',
		'register_module_name' => 'הרשמה',
		'login_module_name' => 'התחברות',
		'messenger_module_name' => 'מסנג\'ר (הודעות פרטיות)',
		'usercp_module_name' => 'לוח בקרה אישי',
		'profile_module_name' => 'פרופיל משתמשים',
		'content_module' => 'תוכן',
		'newsletters_module_name' => 'רשימת תפוצה',
		'search_module_name' => 'חיפוש תוכן',
	'cp_setting_captcha_field' => 'השתמש בתמונת אבטחה?<br />
		<span class="description">
		במידה ותבחר באפשרות זו, בכל כניסה ללוח הבקרה למנהלים תידרש לכתוב את המלל המופיע בתמונת אבטחה
		שתוגרל על ידי המערכת.<br />
		תמונת האבטחה תגביר את האבטחה באתרך ותעזור להגן כנגד פריצות.
		</span>',
	
	'content_links_type_field' => 'סוג יצירת קישורי עמודים ותיקיות<br /><span class="description">ביצירת דפי התוכן והתיקיות במערכת, עליך לבחור את דרך היצירה של קישורי המערכת.<br />קלאסי: http://example.com/index.php?page_id=1<br />כתובת URI: http://example.com/index.php?/DirectoryName/PageName.html<br />כתובת פיזית: http://example.com/DirectoryName/PageName.html</span><br/ /><span class="red">שים לב כי אם אתה בוחר להשתמש באפשרות השלישית (כתובת פיזית), עליך ליצור קובץ בשם <span dir="ltr">.htaccess</span> בתיקיה הראשית של המערכת, ולהדביק לקובץ את התוכן הבא: </span><pre class="description" dir="ltr">%s</pre>',
	'content_links_type_classic' => 'קלאסי (http://example.com/index.php?page_id=1)',
	'content_links_type_query_string' => 'כתובת URI (http://example.com/index.php?/DirectoryName/PageName.html)',
	'content_links_type_url_rewrite' => 'כתובת פיזית (http://example.com/DirectoryName/PageName.html)',
	'content_error_page_handler_field' => 'דרך ניהול דפי שגיאה<br /><span class="description">בחר את הדרך אשר תרצה לנהל שגיאות בהצגת דפים (כגון דף לא קיים)</span>',
	'content_error_page_handler_frontpage' => 'הצג את עמוד הבית',
	'content_error_page_handler_customerror' => 'הצג עמוד שגיאה מותאם אישית',
	'content_error_page_handler_systemerror' => 'הצג את שגיאת המערכת הקלאסית',
	'content_index_page_file_name_field' => 'שם קובץ לעמוד ברירת מחדל<br /><span class="description">שם הקובץ בו תשתמש המערכת כברירת מחדל בעת פנייה לתיקה ללא ציון קובץ.<br />לדוגמא: http://example.com/<span class="bold">DirectoryName/</span> - במצב זה <span class="bold">לא</span> צויין שם הקובץ ולכן המערכת תקרא לקובץ ברירת המחדל.</span>',
	'frontpage_content_field' => 'עמוד שער (ראשי)',
	'default_error_page_field' => 'עמוד שגיאה מותאם אישית<br /><span class="description">באפשרותך לבחור עמוד שגיאה מותאם אישית להצגה במצבי שגיאה (ראה אפשרות: דרך ניהול דפי שגיאה)</span>',
	'content_settings_tab_title' => 'תוכן',
	'content_settings_page_title' => 'הגדרות תכנים',
	'content_settings_form_title' => 'הגדרות תכנים',
		'log_edited_content_settings' => 'ערך הגדרות תצורת תוכן.',
	'edit_content_settings_success' => 'עריכת הגדרות תצורת התוכן הושלמה בהצלחה.',
	'webservices_setting_form_title' => 'הגדרות גישה חיצונית (שרת API מבוסס SOAP - Web Services)',
	'allow_web_services_access_field' => 'אפשר גישה לאתר דרך תוכנות צד-שלישי<br /><span class="description">במידה ותסמן אפשרות זו כ-&quot;כן&quot;, תוכנות צד שלישי יוכלו לגשת למידע ולבצע פעולות באתרך כגון ניהול מאמרים, קטגוריות, משתמשים וכדומה על ידי שימוש ב-SOAP.<br />שים לב כי תוכנות מחשב / מובייל מסויימות דורשות אפשרות זו על מנת לעבוד.<br /><span class="red">בכדי לגשת לאזור זה, כל משתמש יידרש לאמת עצמו ע&quot;י שם משתמש וסיסמא. אנא וודא כי ההגדרה התואמת בהגדרות קבוצת המשתמשים דולקת.</span></span>',
	'frontpage_type_field' => 'סוג דף השער<br /><span class="description">בחר מהרשימה הנ&quot;ל מהו הסוג של הדף שיוצג למשתמשים כדף השער של האתר.</span>',
	'frontpage_type_static_page' => 'עמוד סטאטי (עמוד מרשימת העמודים אותם יצרת)',
	'fontpage_type_category_list' => 'רשימת עמודים מתיקיה (סידור פוסטים בו מוצגים תיאורי העמודים אחד אחרי השני)',
	'frontpage_type_static_page_plain' => 'עמוד סטאטי',
	'frontpage_type_category_list_plain' => 'רשימת עמודים מתיקיה',
	'frontpage_type_static_page_selection_instructions' => 'בחר את העמוד מרשימת העמודים אותם יצרת אשר יוצג כעמוד שער לאתרך.',
	'frontpage_type_category_list_selection_instructions' => 'בחר את התיקיה בה תשתמש המערכת להצגת העמודים בעמוד השער באתרך.',
	'fontpage_type_category_list_no_selection' => 'במידה ובחרת כי העמוד הראשי יציג רשימת עמודים מתיקיה, עליך לבחור לפחות תיקיה אחת ממנה המערכת תציג את העמודים.',
	'modules_toggle_settings_tab_title' => 'הפעלת / נעילת מודולים',
		
);