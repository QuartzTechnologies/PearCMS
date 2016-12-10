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
 * @author		$Author:  $
 * @version		$Id: lang_cp_menus.php 0   $
 * @link			http://pearcms.com
 */


return array(
	'manage_menu_items_page_title' => 'ניהול תפריט',
	'manage_menu_items_form_title' => 'ניהול פריטי תפריט',
	'menu_item_name' => 'שם הפריט',
	'add_new_menu_item' => 'הוסף פריט חדש',
	'edit_item_type_page_title' => 'עריכת סוג הפריט ל: %s',
	'edit_item_type_form_title' => 'עריכת סוג הפריט לפריט <span class="bold underline">%s</span>',
	'create_item_type_selection_page_title' => 'בחירת סוג הפריט',
	'create_item_type_selection_form_title' => 'בחירת סוג הפריט',
	'item_selection_type_notes' => 'שים לב כי במידה ותבחר לשנות את סוג הפריט, כל המידע הקשור לסוג הפריט (לדוגמא התיקיה הקשורה לפריט) ימחחק.<br />שים לב כי השינויי בסוג הדף יכנס לתוקף רק בתנאי שתגיש את טופס עריכת הפריט.',
		
	'item_type_selection_title__directory' => 'תיקיה',
		'item_type_selection_desc__directory' => 'צור קישור המוביל לצפייה בתיקית תוכן',
	'item_type_selection_title__page' => 'דף תוכן',
		'item_type_selection_desc__page' => 'צור קישור המוביל לצפייה בדף תוכן',
	'item_type_selection_title__link' => 'קישור',
		'item_type_selection_desc__link' => 'צור קישור אבסולוטי אקסטרני המוביל לדף אינטרנט אותו תבחר.',
		'create_item_page_title' => 'צור פריט חדש',
		'create_item_submit' => 'צור פריט',
		'edit_item_page_title' => 'עריכת הפריט %s',
		'item_manage_form_tab_general' => 'כללי',
		'item_manage_form_tab_general_title' => 'הגדרות כלליות',
		'item_manage_form_tab_adv' => 'מתקדם',
		'item_manage_form_tab_adv_title' => 'הגדרות מתקדמות',
		'item_name_field' => 'שם הפריט',
		'item_description_field' => 'תאור הפריט',
		'item_type_field' => 'סוג הפריט',
		'item_type_field_pattern' => '%s ( <a href="%s">לחץ כאן בכדי לשנות את סוג הפריט</a> )<br /><span class="description">%s</span>',
		'item_content_directory_field' => 'בחר את התיקיה אליה יוביל הקישור',
		'item_content_page_field' => 'בחר את הדף אליו יוביל הקישור',
		'item_content_link_field' => 'רשום את כתובת האינטרנט אליו יקשר הקישור<br /><span class="description">באפשרותך לרשום כתובת לדף אינטרנט חיצוני (על הכתובת לכלול את פרוטוקול האינטרנט, לדוגמא <span class="italic">http://pearcms.com</span>), כתובת אימייל או קוד javascript (במידה ותבחר לרשום קוד javascript, עליו להתחיל ב-<code>javascript:</code>)</span>',
		'item_view_perms_field' => 'הרשאות צפייה',
		'item_target_field' => 'דרך פתיחת הקישור',
		'item_target_type_parent' => 'פתיחה בדף הנוכחי',
		'item_target_type_blank' => 'פתיחה בחלון דפדפן חדש',
		'item_robots_field' => 'רובוטים<br /><span class="description">בעזרת אפשרות זו תוכל להגדיר את דרך ההתנהגות של רובוטים לעמוד זה<br /><ul><li><span class="italic">index, follow</span> - רובוטים מורשים להוסיף את דף זה למאגריהם ולעקוב אחרי קישורים שקיימים בו</li><li><span class="italic">index, nofollow</span> - רובוטים מורשים להוסיף דף זה למאגריהם אך אינם מורשים לעקוב אחר קישוריו הפנימיים</li><li><span class="italic">noindex, follow</span> - רובוטים אינם מורשים להוסיף דף זה למאגריהם אך מורשים לעקוב אחר קישוריו</li><li><span class="italic">noindex, nofollow</span> - רובוטים אינם מורשים להוסיף דף זה למאגריהם ולעקוב אחר קישוריו</li></ul><span class="red">במידה ואינך יודע במה לבחור, השאר את אפשרות ברירת המחדל - index, follow</span></span>',
		'item_robots_type_index_follow' => 'index, follow',
		'item_robots_type_index_nofollow' => 'index, nofollow',
		'item_robots_type_noindex_follow' => 'noindex, follow',
		'item_robots_type_noindex_nofollow' => 'noindex, nofollow',
		'item_class_name_field' => 'מחלקת CSS',
		'item_id_attr_field' => 'מאפיין <code>id</code>',
		'item_rel_field' => 'מאפיין <code>rel</code>',
		'item_name_blank' => 'שדה שם הפריט אינו יכול להישאר ריק.',

		'log_edited_item' => 'ערך פריט תפריט: %s',
		'edited_item_success' => 'עריכת הפריט %s הושלמה בהצלחה',
		'log_created_item' => 'יצר פריט תפריט חדש: %s',
		'created_item_success' => 'יצירת הפריט %s הושלמה בהצלחה.',
		'log_removed_item' => 'הסיר את הפריט %s',
		'item_removed_success' => 'הסרת הפריט %s הושלמה בהצלחה.',
);