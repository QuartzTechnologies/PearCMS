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
 * @version		$Id: lang_cp_layouts.php 41 2012-03-19 00:23:27 +0200 (Mon, 19 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */


return array(
	'manage_layouts_page_title'		=>	'ניהול תבניות תצוגה',
	'manage_layouts_form_title' => 'ניהול תבניות תצוגה',
	'layout_name_header' => 'שם התבנית',
	'add_new_layout' => 'הוסף תבנית חדשה',
	'manage_layout_create_page_title' => 'יצירת תבנית חדשה',
	'manage_layout_create_form_title' => 'יצירת תבנית חדשה',
	'manage_layout_edit_page_title' => 'עריכת התבנית %s',
	'manage_layout_edit_form_title' => 'עריכת התבנית: <span class="bold italic">%s</span>',
	'manage_layout_create_submit' => 'צור תבנית חדשה',
	'manage_layout_create_and_reload' => 'צור תבנית וטען מחדש',
	'layout_type_selection_type_notes' =>	'שים לב כי במידה ותבחר לשנות את סוג התבנית, כל התוכן הקיים בה כרגע ימחק.<br />שים לב כי שינוי סוג התבנית יכנס לתוקף רק בתנאי שתגיש את טופס עריכת התבנית.',
	'create_layout_type_selection_page_title'	=> 'יצירת תבנית תצוגה חדשה - בחירת סוג תבנית',
	'create_layout_type_selection_form_title' => 'יצירת תבנית תצוגה חדשה - בחירת סוג תבנית',
	'edit_layout_type_page_title' => 'עריכת סוג תבנית התצוגה %s.',
	'edit_layout_type_form_title' => 'עריכת סוג תבנית התצוגה <span class="italic underline">%s</span>.',
	'layout_use_pear_wrapper_field'	=> 'השתמש במעטפת PearCMS?<br /><span class="description">האם להשתמש במעטפת האתר הכללי של PearCMS בעת הצגת עמודים המשתמשים בתבנית זו?</span>',
		
	'layout_type_selection_title__directory' => 'תיקיה',
	'layout_type_selection_desc__directory' => 'צור תבנית תצוגה אשר תשמש להצגת תיקיות אותן תבחר. התבנית יכולה לשמש להצגת רשימת קבצים בתוך תיקיה, הצגת פוסטם בבלוג וכדומה.',
	'layout_type_selection_title__page' => 'עמוד תוכן',
	'layout_type_selection_desc__page' => 'צור תבנית תצוגה אשר תשמש להצגת דפי תוכן בהם תבחר, תבנית התוכן יכולה לשמש כתבנית לפוסט בבלוג, למדריך, לעמוד ריק וכדומה.',
	'layout_content_cache_ttl_field' => 'זמן מטמון (TTL)<br /><span class="description">באפשרותך לכתוב את הזמן (בדקות) בו המערכת תמטמן אוטומטית את הבלוק בכדי לחסוך במשאבים.<br />
		השאר שדה זה ריק בכדי לחסום אפשרות זו, או רשום * בכדי למטמן בלוק זה עד עריכת הדף הבאה.</span>',
	'layout_name_field' => 'שם התבנית<br /><span class="description">בשם זה תשתמש המערכת בכדי לייצג בפניך את תבנית זו בלוח הבקרה למנהלים במקומות המיועדים לכך.</span>',
	'layout_description_field' => 'תאור התבנית',
	'layout_type_field' => 'סוג התבנית',
	'layout_type_field_pattern' => '%s ( <a href="%s">לחץ כאן בכדי לשנות את סוג התבנית</a> )<br /><span class="description">%s</span>',
	'layout_author_field' => 'יוצר התבנית',
	'layout_author_website_field' => 'אתר הבית של יוצר התבנית',
	'layout_version_field' => 'גרסאת התבנית',
	'layout_content_field' => 'תוכן התבנית<br /><span class="description">תוכן התבנית מוצג על ידי המחלקה <code>PearView</code> ולכן יכול לכלול קטעי HTML, CSS, JavaScript וכמובן PHP.<br />הנך יכול להשתמש במשתנה <code>$this</code> בכדי לגשת לכל משתנה ב-<code>PearView</code> וכן למשתנה <code>$itemData</code> הכולל מידע על הפריט שמוצג.</span>',
	'layout_type__page_content_field' => 'תוכן התבנית<br /><span class="description">תוכן התבנית מוצג על ידי המחלקה <code>PearView</code> ולכן יכול לכלול קטעי HTML, CSS, JavaScript וכמובן PHP.<br />הנך יכול להשתמש במשתנה <code>$this</code> בכדי לגשת לכל משתנה ב-<code>PearView</code> וכן למשתנה <code>$pageData</code> המכיל מידע על הדף, התבנית, הסקר, התגובות והדרוג המוצגים.</span>',
	'layout_type__directory_content_field' => 'תוכן התבנית<br /><span class="description">תוכן התבנית מוצג על ידי המחלקה <code>PearView</code> ולכן יכול לכלול קטעי HTML, CSS, JavaScript וכמובן PHP.<br />הנך יכול להשתמש במשתנה <code>$this</code> בכדי לגשת לכל משתנה ב-<code>PearView</code> וכן למשתנה <code>$directoryData</code> המכיל מידע על התיקיה והתבנית המוצגים.</span>',
	'displaying_default_layout_view_content' => 'התוכן המוצג בתיבה זו הינו ברירת המחדל ומהווה נקודת התחלה לסוג התבנית אותה בחרת.',
		'error_no_layout_name' => 'עליך לרשום את שם התוסף',
		'error_no_layout_author' => 'עליך לציין את יוצר התבנית',
		'error_no_layout_version' => 'עליך לציין את גרסאת התבנית',
		'log_created_layout' => 'יצר תבנית תצוגה חדשה - %s.',
		'log_edited_layout' => 'ערך את תבנית התצוגה %s.',
		'created_layout_success' => 'יצירת תבנית התצוגה %s הושלמה בהצלחה.',
		'edited_layout_success' => 'עריכת תבנית התצוגה %s הושלמה בהצלחה.',
		'remove_layout_page_title' => 'הסרת תבנית התצוגה %s',
		'remove_layout_form_title' => 'הסרת תבנית התצוגה <span class="bold italic">%s</span>',
		'remove_layout_submit' => 'הסר תבנית תצוגה זו',
		'remove_layout_form_desc' => 'בעקבות כך שתבנית התצוגה מקושרת לדפים באתרך, עליך לבחור פעולה לבצע על פריטים (דפים, תיקיות כדומה) אלו.<br />ביכולתך לבחור תבנית תצוגה אחרת אשר תחליף את תבנית זו, או להסיר את הפריטים המקושרים לתבנית תצוגה זו.',
		'move_layout_field' => 'העבר את הדפים המקושרים לתבנית זו לתבנית אחרת<br /><span class="description">באפשרותך לבחור תבנית תצוגה אחרת אשר תתקשר לפריטים המתקשרים לתבני תצוגה זו.</span>',
		'do_remove_layout_field' => '<span class="bold underline">או</span> הסר את כל הפריטים המקושרים לתבנית זו<br /><span class="description">במידה ותבחר ב-&quot;כן&quot;, כל הפריטים המקושרים לתבנית תצוגה זו יוסרו.</span>',
		'use_system_default_layout' => '-- ברירת מחדל (תבנית מערכת) --',
		'log_removed_layout' => 'הסיר תבנית תצוגה - %s',
		'layout_removed_success' => 'תבנית התצוגה %s הוסרה בהצלחה.',
);