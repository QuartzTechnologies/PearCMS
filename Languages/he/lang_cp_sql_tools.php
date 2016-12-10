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
 * @version		$Id: lang_cp_sql_tools.php 41 2012-04-03 01:41:41 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */


return array(
	'sql_scheme_manage_page_title' => 'ניהול מסד נתונים',
	'sql_scheme_manage_form_title' => 'ניהול טבלאות מסד הנתונים',
	'sql_scheme_manage_form_desc' => 'בטופס זה תוכל לנהל את טבלאות מסד הנתונים שלך. בכדי לצפות בסכימת נתונים של טבלה מסוייםמת, לחץ על שמה.<br />בסוף רשימת הטבלאות תוכל לראות רשימת פעולות אפשרויות לביצוע כגון הסרה, ייצוא וכו.',
	'table_name_field' => 'שם הטבלה',
	'table_rows_count_field' => 'כמות נתונים (שורות)',
	'table_bytes_field' => 'גודל הטבלה',
	'table_export_field' => 'ייצא',
	'check_all_field' => 'בחר הכל',
	'actions_form_title' => 'פעולות',
	'triggered_sql_error' => 'נמצאה שגיאת SQL בשאילתה המבוקשת.<br />פרטי השגיאה: <span class="bold italic">%s</span>',
	'table_structure' => 'מבנה הטבלה',
	'table_content' => 'תוכן הטבלה',
	'export_table' => 'ייצוא',
	'print_table' => 'הדפס',
	'field_name_field' => 'שם השדה',
	'field_type_field' => 'סוג השדה',
	'field_is_null_field' => 'NULL',
	'run_query_form_title' => "הרצת שאילתה",
	'run_query_form_desc' =>  "כתוב כאן את השאילתה שברצונך להריץ, אנא הקפד על תקינות קוד הSQL",
	'run_query_submit' => 'הרץ שאילתה',
	'export_database_tables' => 'ייצוא טבלאות',

	'database_stucture_title' => 'מבנה',
	'database_indexes_title' => 'אינדקסים',
	'field_default_value_field' => 'ערך ברירת מחדל',	
	'field_extra_field' => 'מידע נוסף',
	'field_indexes_field' => 'אינדקסים',
	'feild_numeration_field' => 'מספור',
	
	'database_table_structure_print_page_title' => 'מבנה טבלה: %s',
	'database_table_structure_print_title' => 'PearCMS - מבנה טבלה: <span class="italic">%s</span>',


	'order_by_field_field' => 'צפה בשורות ביחס לעמודה',
	'manage_table_form_title' => 'ניהול הטבלה <span class="italic underline">%s</span>',
	'manage_table_page_title' => 'ניהול טבלה %s',
	'executed_high_warn_query' => "הריץ שאילתה מרמת סכנה גבוהה מסוג CREATE / DROP / FLUSH",
	'executed_modify_query' => "הריץ שאילתה מסוג INSERT / UPDATE / DELETE / AFTER",
	'query_executed_success' => 'הרצת השאילתה הושלמה בהצלחה.',
	'query_results_form_title' => 'תוצאות שאילתה',
	'query_results_page_title' => 'תוצאות שאילתה',
	'select_all' => 'בחר הכול',
	'deselect_all' => 'הסר בחירה מהכול',
	'select_action' => 'בחר פעולה',
	'backup_selected_tables' => 'גבה טבלאות נבחרות',
	'truncate_selected_tables' => 'רוקן טבלאות נבחרות',
	'drop_selected_tables' => 'הסר טבלאות נבחרות',
	'apply_on_selected' => 'בצע על הנבחרים',
	'execute_query_form_title' => 'הרצת שאילתה',
	'execute_query_form_desc' => 'בשדה זה תוכל לכתוב שאילתת SQL להרצה מול מסד הנתונים.',
	'execute_query_button' => 'הרץ שאילתה',
	'view_query' => 'צפה בשאילתה',
	'edit_query' => 'ערוך שאילתה',
	'generate_php4_execute_code' => 'יצור קוד PHP להרצה',
	'generate_pearcms1_execute_code' => 'ייצר קוד PHP למערכת PearCMS',
	'action_executed_success' => 'הפעולה בוצעה בהצלחה.',
	'sql_export_form_title' => 'ייצוא נתונים',
	'sql_export_page_title' => 'ייצוא נתוני SQL',
	'backup_tables_field' => 'טבלאות לייצוא',
	'backup_as_file_field' => 'ייצוא כקובץ',
	'do_export_button' => 'ייצוא',
	'log_build_sql_backup' => 'ייצא גיבויי SQL.',
	'no_tables_selected' => 'לא נבחרו טבלאות לייצוא.',
	'upload_db_backup_page_title' => 'העלאת גיבויי למסד הנתונים',
	'upload_db_backup_form_title' => 'העלאת גיבויי למסד הנתונים',
	'upload_db_backup_form_desc' => 'בעמוד זה תוכל להעלות גיבויי למסד הנתונים שלך. קובץ מסד הנתונים נדרש להיות קובץ sql.',
	'backup_file_selection_field' => 'בחר קובץ להעלאה<br />
		<span class="description">הקובץ המועלה חייב להיות קובץ SQL</span>',
	'upload_backup_button' => 'העלה גיבויי',
	'no_backup_file_selected' => 'לא נבחר קובץ sql להעלאה.',
	'invalid_backup_file_selected' => 'הקובץ הנבחר לא תקין - הקובץ נדרש להיות מסוג קובץ טקסט בעל סיומת &quot;.sql&quot;',
	'cannot_open_backup_file' => 'לא ניתן לקרוא את הקובץ, אנא צור קשר עם בעל השרת לקבלת מידע נוסף.',
	'log_uploaded_db_backup' => 'העלה גיבויי למסד הנתונים.',
	'upload_backup_success' => 'העלאת הגיבויי הושלמה בהצלחה.',
);