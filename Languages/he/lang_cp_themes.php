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
 * @version		$Id: lang_cp_themes.php 41 2012-04-03 01:41:41 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */


return array(
	'manage_themes_page_title' => 'ניהול ערכות נושא',
	'manage_themes_form_title' => 'ניהול ערכות נושא',
	'manage_themes_form_desc' => 'בעמוד זה תוכל לנהל את ערכות הנושא של אתרך, להתקין ערכות נושא חדשות וליצור ערכות נושא משלך.',
	'create_new_theme' => 'צור ערכת נושא חדשה',
	'theme_name_field' => 'שם ערכת הנושא',
	'theme_author_field' => 'יוצר ערכת הנושא',
	'theme_is_default_field' => 'ערכת ברירת המחדל',
	'theme_preview_field' => 'תצוגה מקדימה',
	'theme_name_pattern' => '<span class="bold italic">%s</span> <span class="description">(גרסא <span class="italic">%s</span>)</span>',
	'theme_author_pattern' => 'יוצר: <span class="italic">%s</span>',
	'theme_uuid_pattern' => 'מזהה סידורי: <span class="italic">%s</span>',
	'manage_uninstalled_themes_form_title' => 'ערכות נושא הממתינות להתקנה',
	'manage_uninstalled_themes_form_desc' => 'באזור זה תוכל לנהל את ערכות הנושא הממתינות להתקנה.<br />בכדי להתקין ערכת נושא עליך להעלות את קבצי ערכת הנושא לתיקיה <span class="italic">%s</span> בשרתך, ולאחר מכן ללחוץ על קישור ה<span class="italic">&quot;התקנה&quot;</span> בעמוד זה.<br />שים לב שבכל שלב תוכל להסיר את ערכת הנושא דרך טופס זה במידה ותרצה.',
	'theme_damaged_error' => 'ערכת הנושא <span class="italic">%s</span> פגומה.<br />על מחלקת ערכת הנושא להיקרא <span class="italic">%s</span>.',
	'theme_damaged_not_extending_peartheme_error' => 'ערכת הנושא <span class="italic">%s</span> פגומה.<br />על מחלקת ערכת הנושא (%s) לרשת מהמחלקה האבסטרקטית PearTheme.',
	'install_new_theme_link' => '&gt;&gt; התקן ערכת נושא &lt;&lt;',
	
	'theme_uuid_field' => 'מזהה סידורי (UUID)<br /><span class="description">זהו מזהה סידורי אותו המערכת מייצרת באופן אוטומטית בשעת יצירת הערכה, ומשתמשת בו לצורך זיהויי במערכת.</span>',
	'edit_theme_page_title'			=>	'עריכת ערכת הנושא %s',
	'edit_theme_form_title'			=>	'עריכת ערכת הנושא <span class="italic underline">%s</span>',
	'create_new_theme_page_title'	=>	'יצירת ערכת נושא חדשה',
	'create_new_theme_form_title'	=>	'יצירת ערכת נושא חדשה',
	'theme_description_field'		=>	'תאור ערכת הנושא',
	'theme_author_website_field'		=>	'דף הבית של יוצר ערכת הנושא',
	'theme_version_field'			=>	'גרסאת ערכת הנושא',
	'theme_enabled_field'			=>	'ערכת הנושא ניתנת לבחירה',
	'theme_key_field'				=>	'מפתח ערכת הנושא<br /><span class="description">מפתח ערכת הנושא הוא שם ייחודי המכיל אותיות אנגליות, מספרים, קו תחתון (_) ומקף (-).<br />המערכת משתמשת בשם זה כשם תיקית ערכת הנושא הנמצאת בתוך התקיה הראשית %s.',
	'theme_default_css_files_field'	=>	'קבצי CSS ברירת מחדל<br /><span class="description">בשדה זה תוכל לרשום את שמות הקובץ/קבצי ה-CSS שיטענו כברירת מחדל בערכת הנושא.<br /> שים לב כי אינך נדרש לציין את PearRtl.css אשר נטען אוטומטית בשעת הצורך.<br />רשום כל קובץ CSS בהפרדת פסיק, כל קבצי העיצוב מופנים לתיקיה StyleSheets/ שבתוך תיקית ערכת הנושא.<br />(לדוגמא: Default.css בערכה Classic יפנה ל- %s/StyleSheets/Default.css)',	
	'theme_default_js_files_field' => 'קבצי JS ברירת מחדל<br /><span class="description">בשדה זה תוכל לרשום את שמות קבצי ה-JavaScript שיטענו כברירת מחדל בערכת הנושא.<br />קבצי ה-JavaScript יטענו מהתיקיה &quot;Client/JScripts&quot; שבתוך תיקית התוסף.<br />שים לב שעליך לרשום רק את שם הקובץ ללא הדרך אליו ולהפריד כל קובץ עם פסיק, לדוגמא לטעינת הקובץ &quot;%s/Client/JScripts/foo.js&quot; עליך לכתוב &quot;foo.js&quot;',
	'theme_key_taken' => 'מפתח ערכת הנושא אותו כתבת תפוס, אנא בחר מפתח אחר.',
	'theme_name_empty' => 'שדה שם ערכת הנושא אינו יכול להישאר ריק.',
	'theme_version_empty' => 'שדה גרסאת ערכת הנושא אינו יכול להישאר ריק.',
	'theme_author_website_invalid' => 'כתובת יוצר ערכת הנושא שגוייה. על כתובת האינטרנט להיות בפרוטוקול http או https.',
	'invalid_css_files'	=> 'קבצי ה-css שצויינו לא חוקיים, על קבצי ה-css להכיל את הסיומת &quot;.css&quot;',
	'invalid_js_files'	=> 'קבצי ה-js שצויינו לא חוקיים, על קבצי ה-js להכיל את הסיומת &quot;.js&quot;',
	'log_edited_theme' => 'ערך את ערכת הנושא %s.',
	'theme_edited_success' => 'ערכת הנושא נערכה בהצלחה. שים לב שבכדי שתוכל לייצא את ערכת הנושא עם הפרטים המעודכנים, עליך לערוך את קובץ האתחול של ערכת הנושא (Bootstrap.php)',
	'log_created_theme' => 'יצר את ערכת הנושא %s.',
	'theme_create_success_with_workspace' => 'יצירת ערכת הנושא הושלמה בהצלחה. סביבת העבודה לערכת הנושא נוצרה בהצלחה וזמינה בדרך "%s".',
	'theme_create_success_without_workspace' => 'יצירת ערכת הנושא הושלמה בהצלחה. המערכת לא הצליחה ליצור את סביבת העבודה לערכת הנושא באופן אוטומטי.',
	'log_removed_theme' => 'הסיר את ערכת הנושא %s.',
	'remove_theme_success' => 'ערכת הנושא הוסרה בהצלחה.',
	'theme_install_refused' => 'לא היה ניתן להתקין את ערכת הנושא %s כיוון שסרבה לבקשה.<br />שגיאות: %s',
	'theme_uninstall_refused' => 'לא היה ניתן להסיר את ערכת הנושא %s כיוון שסרבה לבקשה.<br />שגיאות: %s',
	'log_installed_theme' => 'התקין את ערכת הנושא %s.',
	'theme_installed_success' => 'ערכת הנושא הותקנה בהצלחה.',
		
		'create_theme_structure_page_title' => 'יצירת סביבת עבודה לערכת הנושא %s',
		'create_theme_structure_form_title' => 'יצירת סביבת עבודה לערכת הנושא %s',
		'create_theme_structure_form_desc' => 'בכדי להתחיל לעצב את ערכת הנושא עליך להשלים לבנות את סביבת העבודה שלה.<br />לפניך פירוט ההוראות לסיום יצירת ערכת הנושא, במידה ואינך יודע כיצד לבצע אותן, אנא פנה לצוות התמיכה של PearCMS לקבלת עזרה.',
		'workspace_create_dir_title' => 'יצירת תיקית ערכת הנושא',
		'workspace_create_dir_guide' => 'עליך ליצור תיקיה בשם &quot;%s&quot; בדרך &quot;%s&quot; (התיקיה המכילה את כל ערכות הנושא הזמינות במערכת) ולהכיל על התיקיה הרשאות chmod 0755.',
		'workspace_create_bootstrap_title' => 'יצירת קובץ האתחול של ערכת הנושא',
		'workspace_create_bootstrap_guide' => 'עליך ליצור קובץ בשם &quot;Bootstrap.php&quot; בתיקיה &quot;%s&quot; ובו לכלול את התוכן הבא: <br /><div style="direction:ltr; text-align: left; overflow: auto; width: 550px;"><pre class="brush: js;" style="direction:ltr; text-align: left;">%s</pre></div>',
		'workspace_create_images_title' => '<span class="italic">אופציונלי</span> תיקית תמונות',
		'workspace_create_images_guide' => 'בכדי להוסיף תמונות עליך ליצור בתיקית ערכת הנושא (<span class="italic">&quot;%s&quot;</span>) את התיקיה &quot;Images&quot; ולהעניק לה הרשאות chmod 0755.',
		'workspace_create_stylesheets_title' => '<span class="italic">אופציונלי</span> תיקית קבצי CSS',
		'workspace_create_stylesheets_guide' => 'בכדי להוסיף קבצי CSS עליך ליצור בתיקית ערכת הנושא (<span class="italic">&quot;%s&quot;</span>) את התיקיה &quot;StyleSheets&quot; ולהעניק לה הרשאות chmod 0755.',
		'workspace_create_views_title' => '<span class="italic">אופציונלי</span> תיקית קבצי תבנית (Views או Templates)',
		'workspace_create_views_guide' => 'בכדי להוסיף קבצי תבניות עליך ליצור בתיקית ערכת הנושא (<span class="italic">&quot;%s&quot;</span>) את התיקיה &quot;Views&quot; ולהעניק לה הרשאות chmod 0755.',
		'workspace_create_jscripts_title' => '<span class="italic">אופציונלי</span> תיקית קבצי JavaScript',
		'workspace_create_jscripts_guide' => 'בכדי להוסיף קבצי JavaScript עליך ליצור בתיקית ערכת הנושא (<span class="italic">&quot;%s&quot;</span>) את התיקיה &quot;Client&quot; ובתוכה ליצור את התיקיה &quot;JScripts&quot; ולהעניק להן הרשאות chmod 0755.',
		
);