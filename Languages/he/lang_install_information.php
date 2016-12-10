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
 * @version		$Id: lang_install_information.php 41 2012-04-03 01:41:41 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */

return array(
	'error_not_agree_to_softlicense' => "לא הסכמת לתנאי השימוש",
	'installer_step4_instructions_title' => 'אנו שמחים לבשר כי הנך מוכן להתקנת המערכת!.
		בשלבים הבאים עליך להכניס מידע חיוני שבעזרתו PearCMS תוכל לפעול ולסייע לך לתפעל את אתרך.',
	'installer_step4_url_instructions' => 'בשלב זה הנך נדרש להכניס את כתובת האינטרנט (URL) שבה תקים את אתרך.<br />
		הכתובת צריכה להיות מורכבת מהקידומת &quot;http://&quot;, מהדומיין האתר, ומשם התיקיה (במידה והמערכת לא תשתמש בתיקיה הראשית של השרת) בה המערכת רצה.',
	'installer_step4_email_instructions' => 'השדה השני אותו הנך נדרש למלות הוא כתובת האימייל הראשית, PearCMS תשתמש בה בכדי לשלוח מכתבי אי-מייל.<br />
		לעוד מידע אנא צור קשר עם צוות התמיכה.',
	'installer_site_url' => 'כתובת האתר<br />
				<span class="description">PearCMS זיהתה כי הכתובת הראשית של אתר זה היא:</span>',
	'installer_site_email' => 'כתובת האימייל הראשית<br />
				<span class="description">PearCMS זיהתה כי כתובת האימייל הראשית של שרת זה היא:</span>',
	'installer_upload_path' => 'דרך להעלאת קבצים<br />
		<span class="description">הדרך <span class="underline">הממשית (לא כתובת URL)</span> להעלאת קבצים. PearCMS זיהתה כי הדרך להעלאת קבצים לשרת זה היא:</span>',
	'error_no_site_url' => 'לא כתבת את כתובת האתר.',
	'error_no_email' => 'לא כתבת את כתובת המייל של מנהל האתר.',
	'error_email_not_valid' => 'כתובת האימייל שצויינה לא חוקית.',
	'no_upload_path' => 'לא נבחרה כתובת להעלאת קבצים',
	'errors_exists_title' => "אנא תקן את הפרטים הבאים לפני המשך ההתקנה",
	'installer_step5_instructions' => 'בשלב זה עליך להכניס את הפרטים למסד הנתונים שישמש את PearCMS.<br />
		מומלץ להשתמש במסד נתונים ריק בכדי למנוע התנגשות נתונים, במידה ולא, אנא השתמש בתחילית בכדיל לייחד את טבלאות PearCMS.
		עוד מידע בנוגע למסד הנתונים תוכלו למצוא במדריך ההתקנה.',
	'database_host_field' => 'מארח מסד הנתונים<br />
				<span class="description">כתובת המחשב עליו מתארח מסד הנתונים, בדר&quot;כ מדובר בlocalhost. במידה ואינך בטוח, צור קשר עם בעל השרת עליו אתה מתאחסן.</span>',
	'database_name_field' => 'שם מסד הנתונים<br />
				<span class="description">זהו השם שבחרת למסד הנתונים כשיצרת אותו.</span>',
	'database_user_field' => 'שם המשתמש למסד הנתונים<br />
				<span class="description">זהו שם המשתמש המקושר למסד הנתונים ובעל הרשאות גלובאליות לגביו.</span>',
	'database_pass_field' => 'הסיסמא למסד הנתונים<br />
				<span class="description">הסיסמא, במידה ויש, לשם המשתמש המקושר למסד הנתונים.</span>',
	'database_prefix_field' => 'תחילית לשמות הטבלאות<br />
				<span class="description">בכדי למנוע התנגשויות בין נתונים בעתיד, מומלץ להקצאות בשדה זה תחילית אשר PearCMS תשתמש בה לפני כל שם טבלה.</span>',
	'errfield_dbhost' => 'מארח מסד הנתונים',
	'errfield_dbname' => 'שם מסד הנתונים',
	'errfield_dbuser' => 'משתמש מסד הנתונים',
	'field_xxx_cannot_be_blank' => 'שדה &quot;%s&quot; אינו יכול להישאר ריק.',
	'db_error' => "שגיאה בהתחברות למסד הנתונים.<br />פרטי השגיאה: <br />" ,
	'installer_step6_instructions' => 'בשלב זה עליך להכניס את פרטי משתמש העל של האתר, למשתמש זה יהיו הרשאות גישה ללוח הניהול של האתר ויהיה חסין בברירת מחדל מפני הסרה ועריכה.<br />
		בעקבות הפריווילגיות המיוחדות אותן יש למשתמש זה, אני ממליצים לבחור בסיסמא חזקה במיוחד.',
	'installer_step6_note_title' => 'שים לב',
	'installer_step6_note_desc' => 'חשוב: כל השדות הם שדות חובה.',
	'account_name_field' => 'שם משתמש',
	'account_pass_field' => 'סיסמא',
	'account_mail_field' => 'כתובת אימייל',
	'account_secret_question_field' => 'שאלה סודית',
	'account_secret_answer_field' => 'תשובה סודית',
	'installer_step7_instructions' => 'לפניך מוצגים כל הקלטים אותם הכנסת עד כה. אנא אמת אותם לפני שתעבור לשלב הבא.<br />
		ברגע שתלחץ על &quot;הבא&quot; יסתיים תהליך קליטת הפרטים האישיים, לכן אנו ממליצים בחום לוודאות כי כל המידע אותו הכנסת תקין.<br />',
	'installer_step7_note_title' => 'שים לב',
	'installer_step7_note_desc' => 'בכדי לערוך את אחד הערכים, לחץ עליו.<br />אינך מחוייב לערוך ערכים בטופס זה.',
	'edit_title' => 'עדכון ערך',
	'apply' => 'אשר',
	'cancel' => 'בטל',
	'js_edit_cancel' => 'האם אתה בטוח שברצונך לצאת מאזור העריכה?',
	'js_field_empty' => 'אינך יכול להשאיר שדה זה ריק.',
	'empty_field_label' => '&gt; שדה ריק &lt;',
	'installer_step8_instructions' => 'בשלב זה תוכל לבחור ערכת פתיחה (Starter Kit) בה תשתמש המערכת בכדי לבנות את המבנה ההתחלתי של אתרך.<br />מטרת ערכת הפתיחה היא להתאים את המראה, ההגדרות, ותוכן הדוגמא אותו המערכת יוצרת לסוג האתר אותו אתה מתכנן לבנות.',
	'starter_kit_file_not_found' => 'קובץ ערכת הפתיחה הנבחרה לא נמצא.',
	'starter_kit_xxx_class_not_found' => 'ערכת הפתיחה הקיימת בקובץ %s פגומה.<br />על השם המחלקה שלה להיות: %s.',
	'starter_kit_xxx_bad_class_defination' => 'מחלקת ערכת הפתיחה %s (הממוקמת בקובץ: %s) פגומה ולכן לא ניתן להשתמש בה (חוסר אחד או יותר מהפרטים: UUID / שם ערכת פתיחה / יוצר ערכת פתיחה / גרסאת ערכת פתיחה).<br />אם אינך יוצר ערכה זו, אנא פנה לצוות התמיכה של PearCMS.',
	'starter_kit_xxx_not_extending_abstract_class' => 'מחלקת ערכת הפתיחה %s לא יורשת מהמחלקה PearStarterKit',
	'starter_kit_xxx_bad_uuid' => 'מפתח ה-UUID המצויין לערכת הפתיחה %s (המצוייה בקובץ: %s) פגום.',
	'no_stater_kits_found' => 'לא נמצאו ערכות פתיחה, אנא בדוק את גרסאת PearCMS שהורדת וודא כי כל הקבצים הועלו כהלכה, במידה ואתה נתקל בקשיים, אנא פנה לצוות התמיכה של PearCMS לקבלת מידע נוסף.',
	'missing_built_in_starter_kits' => 'נמצא כי אחת או יותר מערכות הפתיחה ברירת המחדל חסרות או פגומות.<br />אנא בדוק כי העלת את כל הקבצים לשרת או צור קשר עם צוות התמיכה של PearCMS לקבלת מידע נוסף.',
	'starter_kit_title_pattern' => '%s <span class="description">(גרסא: %s)</span>',
	'starter_kit_author_with_website_pattern' => 'יוצר: <a href="%s" target="_blank">%s</a>',
	'starter_kit_author_without_website_pattern' => 'יוצר: %s',
	'starter_kit_search_more_at_pear_market' => 'חפש עוד ערכות פתיחה ב-&quot;PearCMS Market&quot; &gt;',
	'no_starter_kit_selected' => 'עליך לבחור בערכת פתיחה שבה תשתמש המערכת בכדי להתקין עצמה.',
	'step_done_instructions' => 'אנו שמחים לבשר לך כי ההתקנה הושלמה!, כל הרכיבים הותקנו על שרתך ואתרך עלה בהצלחה!.<br />
		בכדי להיכנס לאתרך החדש <a href="%s">לחץ כאן</a> ובכדי להגיע ללוח בקרה למנהלים <a href="%s">לחץ כאן</a>.<br /><br />
		מאחלים לך הרבה הרבה בהצלחה, ומודים לך שבחרת בPearCMS - Pear Technology Investments Ltd.',
	'useful_links_title' => 'קישורים שימושיים',
	'pearti_site' => 'Pear Technology Investments, Ltd. - אתר החברה',
	'pearcms_site' => 'אתר PearCMS',
	'pearcms_support_community' => 'קהילת התמיכה הרשמית ב-PearCMS',
	'general_qanda_mail' => 'שליחת מייל לקבלת תשובות בנושאים כללים',
	'company_management_mail' => 'שליחת מייל להנהלת החברה',
	'general_info_mail' => 'בקשת מידע כללי',
	'accounts_info_mail' => 'שליחת מייל בנוגע למשתמשים ולקוחות',
	'step_done_greeting' => 'מאחלים המשך יום נעים וגלישה נעימה, צוות Pear Technology Investments.',
	'pearcms_install_success' => 'PearCMS הותקנה בהצלחה.',
	'starter_kit_more_at_market_install_instructions' => 'לאחר שתבחר ותוריד את ערכת הפתיחה בה תרצה להשתמש מ-<a href="http://community.pearcms.com/files">Pear Market</a> העלה את תוכן הקבצים אשר הורדת לתיקיה &quot;<span class="italic">%s</span>&quot;.<br />
	לאחר שתעלה את הקבצים לתיקיה, רענן דף זה.',
);