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
 * @version		$Id: lang_install_steps.php 41 2012-03-24 23:34:39 +0200 (Sat, 24 Mar 2012) yahavgb $
 * @link			http://pearcms.com
 */


return array(
	'step_xx_from_xx' => 'שלב %s מתוך %s.',
	'auto_redirection_button' => '&lt;&lt; לחץ כאן במידה ולא הועברת אוטומטית &gt;&gt;',
	'pearcms_install_title' => 'PearCMS בשלבי התקנה, אנא המתן...',
	'install_fatal_error_title' => 'שגיאה פטלית התרחשה',
	'install_fatal_error_description' => 'שגיאה פטלית התרחשה בזמן הליך ההתקנה, אנא הסתכל פלט ההתקנה של שלב זה  וצור קשר עם צוות PearCMS לקבל סיוע נוסף.',
	'create_admin_syssettings' => "כותב הגדרות לוח בקרה למנהלים...",
	'cannot_create_adcategory' => "לא הייתה אפשרות להוסיף את הקטגוריה - &quot;%s&quot;<br />
						פרטי השגיאה: %s<br /> " ,
	'step_check_md5sums' => 'מאמת תקינות קצבי מערכת (md5 sums check)',
	'checksums_mismatch_hashes' => 'הקובץ &quot;%s&quot; נמצא פגום, אנא הורד את הקובץ מחדש.',
	'could_not_locate_md5_sums' => 'לא היה ניתן לאתר את הקובץ &quot;%s&quot;<br />לא אוכל להמשיך בהליך ההתקנה בעעדיו.',
	'could_not_read_md5_sums' => 'לא היה ניתן לקרוא את הקובץ &quot;%s&quot;',
	'checksums_could_not_locate_file' => 'לא היה ניתן למצוא את הקובץ &quot;%s&quot;',
	'checksums_valid_hashes' => 'מאמת קובץ &quot;%s&quot;...',
	'step_create_sysfiles' => "יוצר קבצי המערכת...",
	'error_open_config' => "שגיאה בפתיחת הקובץ &quot;Configurations.php&quot;",
	'prepare_config_file' => "מכין לעריכה קובץ &quot;Configurations.php&quot;...",
	'step_writing_dbdata' => "כותב פרטי מסד נתונים וכתובות ראשיות...",
	'step_writing_superuser' => "כותב פרטי משתמש ראשיי על קובץ &quot;Configurations.php&quot;...",
	'error_cannot_create_installcache' => "לא הייתה אפשרות ליצור את הקובץ &quot;Install_Catche.inc&quot; בתיקית Catche.",
	'create_installcache' => "יוצר קובץ &quot;Install_Catche.inc&quot;...",
	'step_create_dbtables' => "יוצר טבלאות מערכת...",
	'error_no_sqltables_file' => "הקובץ &quot;Sql_Tables.php&quot; חסר או לא יכול היה להיפתח, אנא בדוק את גרסאת PearCMS שברשותך.",
	'error_sqltables_damaged' => "קובץ &quot;Sql_Tables.php&quot; שברשותך פגום, אנא בדוק אותו ונסה שנית.",
	'error_dbquery' => 'נוצרה שגיאה במהלך הרצת שאילתה: ',
	'create_dbtable' => "מתקין חלק תוכנה - &quot;%s&quot;...",
	'tables_create_success' => 'כל הטבלאות נוצרו בהצלחה.',
	'create_xxx_tables_from_xxx' => 'נוצרו %d טבלאות מתוך %d.',
	'step_create_membersmodule' => "יוצר הגדרות מודל משתמשים...",
	'writing_superuser_settings' => "כותב הגדרות משתמש ראשי",	
	'cannot_add_new_group' => 'לא היה ניתן להוסיף את הקובצת המשתמשים %s.<br />פרטי השגיאה: %s.',
	'create_new_user_group' => "מוסיף קבוצת משתמשים - &quot;%s&quot;",
	'step_create_syssettings' => "יוצר הגדרות מערכת...",
	'cannot_create_syssettings' => "לא הייתה אפשרות להוסיף את ההגדרות הראשיות למערכת.<br />
			סיבת השגיאה: <br />",
	'writing_syssettings' => "כותב הגדרות מערכת...",
	'cannot_create_admin_syssettings' => "לא הייתה אפשרות להוסיף את ההגדרות לוח הבקרה של למערכת.<br />
			סיבת השגיאה: <br />" ,
	'cannot_create_sysversion' =>  "לא הייתה אפשרות להוסיף את גרסאת המערכת.<br />
			סיבת השגיאה: <br />",
	'create_sysversion' => "כותב היסטורית גירסאות...",
	'step_adminmodules' => "כותב אפשרויות מודל מנהלים...",
	'admin_modules_damaged' => "הקובץ &quot;Admin_Data.php&quot; חסר או לא יכול היה להיפתח, אנא בדוק את גרסאת PearCMS שברשותך.",
	'create_adcategory' => "יוצר קטגורית ניהול - &quot;%s&quot;...",
	'writing_pages_in_adcategory' => "כותב דפים בקטגוריה - &quot;%s&quot...",
	'cannot_create_pages_in_adcategory' => "נתקל בשגיאה בעת כתיבת הדפים בקטגוריה &quot;%s&quot;<br />
					פרטי השגיאה:%s<br />",
	'all_categories_added' => 'כל הקטגוריות נוספו בהצלחה.',
	'added_xxx_from_xxx_cats' => 'נוספו %d קטגוריות מתוך %d.',
	'all_pages_added' => "כל הדפים הוספו בהצלחה.",
	'added_xxx_from_xxx_pages' => 'נוספו %d דפים מתוך %d.',
	'step_define_cmsmodules' => "מגדיר תכני אתר ברירת מחדל...",
	'cmsmodules_file_damaged' => "הקובץ &quot;Defualt_CMS_Data.php&quot; חסר או לא יכול היה להיפתח, אנא בדוק את גרסאת PearCMS שברשותך.",
	'cmsmodules_langs_dberror' => "נוצרה שגיאה בעת הכנסת נתוני השפה.<br />
					פרטי השגיאה: %s",
	'writing_lang' => "כותב הגדרות שפה לשפה &quot;%s&quot;...",
	'cmsmodules_themes_dberror' => "נוצרה שגיאה בעת הכנסת נתוני העיצוב.<br />
					פרטי השגיאה: %s",
	'writing_theme' =>  "כותב הגדרות כלליות לעיצוב - &quot;%s&quot;...",
	'secret_questions_dberror' => "נוצרה שגיאה במהלך הכנסת אוסף השאלות הסודיות<br /.
					פרטי השגיאה: %s",
	'writing_secret_questions' => "כותב אוסף שאלות סודיות לבחירה...",
	'error_acp_sections_pages_damaged' => 'קובץ דפי לוח הבקרה פגום, אנא הורד את המערכת ונסה שנית או צור קשר עם צוות התמיכה של PearCMS.',
	'error_acp_sections_damaged' => 'קובץ אזורי לוח הבקרה פגום, אנא הורד את המערכת ונסה שנית או צור קשר עם צוות התמיכה של PearCMS.',
		
	'step_build_cache' => 'ממטמן נתוני מערכת',
	'could_not_recache_packet' => 'נוצרה שגיאה במהלך מטמון החבילה %s',
	'caching_cache_packet' => 'ממטמן נתוני מערכת לחבילה %s...',
		
	'step_install_addons' => 'מתקין תוספים ראשונים...',
	'install_addons_no_requested_addons' => 'לא נמצאו תוספים להתקנה ראשונית, מדלג...',
		'install_addon_not_found' => 'התוסף %s לא נמצא, מדלג...',
	'install_addon_class_not_found' => 'לא ניתן להתקין את התוסף %s - מחלקת האתחול (%s) לא נמצאה, מדלג...',
	'install_addon_class_not_implements_pearaddon' => 'לא ניתן להתקין את התוסף %s - על מחלקת האתחול (%s) לממש את PearAddon, מדלג...',	
	'cannot_install_addon_message' => 'לא ניתן להתקין את התוסף %s.<br />תוצאה: %s',
	'installed_addon_message' => 'מתקין את התוסף %s...',
	
	'step_checking_system' => "בודק תקינות מערכת...",
	'all_demo_data_written' => 'כל נתוני הדוגמא נוצרו בהצלחה.',
	'cannot_create_install_cache' => "לא הייתה אפשרות ליצור את הקובץ &quot;Install_Catche.inc&quot; בתיקית Catche.",
	'locking_installer' => "נועל את אזור ההתקנה...",
	'cannot_create_securitytool' => 'לא היה ניתן ליצור את כלי האבטחה המבוקש.<br />פרטי שגיאה: %s',
	'created_security_tool' => 'יצר כלי אנליזת אבטחה: %s',

);