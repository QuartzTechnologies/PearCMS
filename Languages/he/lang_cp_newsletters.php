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
 * @version		$Id: lang_cp_newsletters.php 41 2012-04-03 01:41:41 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */


return array(
	'newsletters_list_page_name' => 'רשימות תפוצה',
	'newsletters_list_form_name' => 'ניהול רשימות תפוצה',
	'newsletters_list_form_desc' => 'באזור זה תוכל לנהל את רשימות התפוצה אליהם יוכלו להירשם המשתמשים באתרך.<br />תוכל לשלוח &quot;עיתון&quot; בעזרת לוח הבקרה לאחת או יותר מרשימות התפוצה אשר תגדיר באזור זה.',
	'newsletter_name' => 'שם רשימת התפוצה',
	'newsletters_current_subscribers' => 'רשומים נוכחיים',
	'newsletter_allow_new_subscribers' => 'אפשר קבלת נרשמים חדשים?',
	'create_new_newsletter' => 'צור רשימת תפוצה חדשה',
	'log_toggle_newsletter_registeration_state' => 'שינה מצב קבלת נרשמים חדשים לעיתון &quot;%s&quot;.',
	'edit_newsletter_page_title' => 'עריכת רשימת התפוצה &quot;%s&quot;',
	'edit_newsletter_form_title' => 'עריכת רשימת התפוצה &quot;<span class="bold underline">%s</span>&quot;',
	'create_newsletter_page_title' => 'יצירת רשימת תפוצה חדשה',
	'create_newsletter_form_title' => 'יצירת רשימת תפוצה חדשה',
	'create_newsletter_submit' => 'צור רשימת תפוצה חדשה',
	'newsletter_name_field' => 'שם העיתון',
	'newsletter_description_field' => 'תאור העיתון<br /><span class="description">תאור העיתון יוצג למשתמשים בעת ההרשמה לעיתון</span>',
	'newsletter_allow_new_subscribers_field' => 'אפשר הרשמה של משתמשים חדשים<br /><span class="description">במידה ותבחר באפשרות זו כ-&quot;לא&quot;, לא יתאפשר למשתמשים חדשים להירשם לעיתון זה.</span>',
	'newsletter_include_groups_field' => 'כלול אוטומטית את קבוצות המשתמשים<br /><span class="description">בחר מהרשימה את קבוצות המשתמשים אשר למשתמשים הנמצאים בתוכם ישלח העיתון באופן אוטומטי ללא הרשמה לעיתון.</span>',
	'newsletter_subscribing_perms_field' => 'אפשר לקבוצות המשתמשים הבאות להירשם לעיתון<span class="description">קבע אילו מקבוצות המשתמשים הבאות יוכלו להירשם לעיתון במידה ושדה ההרשמה לעיתון יהיה מאופשר.</span>',
	'newsletter_mail_template_field' => 'תבנית HTML<br /><span class="description">זו תבנית ה-HTML בה המערכת תשתמש בכדי לשלוח את ההודעה.<br />המערכת משתמשת בתבנית כקובץ view דינאמי, כך שהנך יכול לשלב קטעי php וכן להשתמש בכל המשתנים הנגישים במחלקה <code>PearView</code><br />עליך להשתמש בתג <code>{#newsletter_content#}</code> בכדי לכלול את תוכן ההודעה, בתג <code>{#newsletter_title#}</code> בכדי לכלול את נושא ההודעה וב-<code>{#unsubscribe_link#}</code> בכדי להציג את לינק ההסרה מרשימת התפוצה (<span class="bold underline">הנך מחוייב לפי חוק התקושרת בישראל לכלול קישור להסרה מרשימת התפוצה</span>).<br /><span class="red">במידה ואינך יודע כיצד לערוך קוד זה, השאר את שדה זה ללא שינויי.</span></span>',
	'newsletter_name_empty'				=>	'שדה שם רשימת התפוצה אינו יכול להישאר ריק.',
	'newsletter_mail_template_blank'		=>	'אינך יכול להשאיר את שדה תבנית רשימת התפוצה ריק.',
	'newsletter_mail_template_no_page_content' => 'עליך לציין בשדה תבנית רשימת התפוצה את התג &quot;$message&quot; אשר יוחלף בתוכן העיתון אותו תציין בעתיד.',
	'newsletter_mail_template_invalid_syntax' => 'קיימת שגיאת תחביר בתבנית רשימת התפוצה, אנא תקן אותה ונסה שנית.',	
	'log_edited_newsletter' => 'ערך את רשימת התפוצה &quot;%s&quot;',
	'newsletter_edited_success' => 'רשימת התפוצה <span class="bold underline">%s</span> נערכה בהצלחה.',
	'log_added_newsletter' => 'הוסיף את רשימת התפוצה &quot;%s&quot;',
	'newsletter_added_success' => 'רשימת התפוצה <span class="bold underline">%s</span> נוספה בהצלחה.',
	
	'removed_newsletter_log' => 'הסיר את רשימת התפוצה &quot;%s&quot;.',
	'remove_newsletter_sucsess' => 'רשימת התפוצה %s הוסרה בהצלחה.',
	'send_newsletter_page_title'		=>	'שליחת עיתון לרשימת התפוצה',
	'send_newsletter_form_title'		=>	'שליחת עיתון לרשימת התפוצה',
	'send_newsletter_form_desc'		=>	'בעמוד זה תוכל לשלוח עיתון (דואר אלקטרוני) לרשימות התפוצה אותם תבחר.',
	'newsletter_mail_subject_field'		=>	'נושא (כותרת) העיתון',
	'send_newsletter_mail_submit'	=>	'שלח עיתון',
	'selection_newsletter_with_subscribers_count_pattern' => '%s (%d רשומים)',
	'newsletter_mail_content_field' => 'תוכן העיתון',
	'newsletter_mail_sender_field' => 'מייל שולח ההודעה<br /><span class="description">ציין בשדה זה את כתובת המייל ממנה תשלח ההודעה.</span>',
	'newsletter_mail_related_newsletters_field' => 'שלח את העיתון לרשימות התפוצה<br /><span class="description">בחר את רשימות התפוצה אליהם המערכת תשלח את המייל.</span>',	
	'newsletter_mail_template_no_unsubscribe_link' => 'עליך לציין את תג הסרת ההצטרפות לעיתון (&quot;{#unsubscribe_link#}&quot;) בתבנית העיתון.',
	'newsletter_mail_unsubscribe_link'	=>	'במידה ואינך מעוניין להיות חלק מרשימת תפוצה זו, היכנס <a href="%s">לקישור הבא</a> או השתמש במפתח האישי שלך: %s.',
	'newsletter_mail_unsubscribe_link_mem' => 'במידה ואינך מעוניין להיות חלק מרשימת תפוצה זו, באפשרותך לחסום את מנהל האתר משליחת מיילים אוטומטיים, <a href="%s">בעריכת הפרופיל האישי בלוח הבקרה שלך</a>.',
	'newsletter_mail_subject_empty'		=>	'שדה כותרת העיתון אינו יכול להישאר ריק.',
	'newsletter_mail_sender_empty'		=>	'כתובת שולח העיתון אינה יכולה להישאר ריקה.',
	'newsletter_mail_sender_not_valid'	=>	'כתובת שולח המייל לא תקנית.',
	'newsletter_mail_related_newsletters_empty' => 'לא נבחרו רשימה(ות) תפוצה לשלוח אליהן את עיתון זה.',
	'newsletter_content_empty'			=>	'תוכן ההודעה אינו יכול להישאר ריק.',
	'send_newsletter_mail_log'			=>	'שלח עיתון לרשימות התפוצה - &quot;%s&quot;.',
	'send_newsletter_mail_sucsess'		=>	'העיתון בעל הכותרת &quot;%s&quot; נשלח בהצלחה.',
	'no_newsletter_list_available' => 'לא נמצאו רשימות תפוצה זמינות. <a href="%s">לחץ כאן להוספת רשימת תפוצה חדשה</a>',
);