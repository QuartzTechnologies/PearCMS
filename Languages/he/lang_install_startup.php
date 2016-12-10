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
 * @version		$Id: lang_install_startup.php 41 2012-04-03 01:41:41 +0300 (Tue, 03 Apr 2012) yahavgb $
 * @link			http://pearcms.com
 */


return array(
	'installer_step1_instructions' => '<p>
		מערכת ניהול התוכן PearCMS תאפשר לכם לקבל שליטה מלאה בכל הנעשה באתרכם.<br />
		בתוך דקות ספורות יעמוד לרשותכם אתר בעל פאנל ניהול פנומנאלי שיוכל לגרום לחלום &quot;האתר האידיאלי&quot;
		לעלות מדרגה מחלום למציאות.<br />
	</p>
	<p>
		אנא קראו את הוראות ההתקנה בדקדוק, ועקבו אחריהם. לכל בעייה ניתן לפנות לצוות התמיכה של Pear Technology Investments.<br />
		<span class="bold">שימו לב: הוראות ההתקנה נכתבו בלשון זכר אך מיועדות לנשים וגברים כאחד.</span>
	</p>',
	'error_no_config_file' => "PearCMS זיהתה כי קיים על השרת הקובץ Configurations.php.dist והוא נמצא בתיקיה הראשית של המערכת.<br />" .
											"אנא שנה את שם הקובץ &quot;<b>Configurations.php.dist</b>&quot; ל-&quot;<b>Configurations.php</b>&quot;.",
	'installer_step2_instructions' => '		ההליך הראשון בהתקנה הוא המרכיבים של השרת בו אתה מתכוון להריץ את PearCMS.<br />
		PearCMS דורשת מספר רכיבי שרת, במידה ואינם מותקנים על שרתך, לא תוכל להמשיך בתהליך ההתקנה.<br />
		אנו ממליצים במצב זה להתקין את הרכיבים, במידה ואין לך ההרשאות המתאימות או אינך יודע כיצד לעשות זאת, אנא צור קשר עם בעל השרת. במידה והנך בעל השרת, אנא צור קשר עם צוות התמיכה של Pear Technology Investments.',
	
	'error_no_config_file_desc' => "לא נמצא הקובץ Configurations.php בתיקיה הראשית של השרת.",
	'error_config_no_prems' => "הקובץ Configurations.php שנמצא בתיקיה הראשית שלך (במקום בו נמצאים הקבצים index.php ו-admin.php) אינו ניתן לכתיבה.<br />
					בכדי להמשיך בתהליך ההתקנה, אנא הקצה לקובץ זה הרשאות כתיבה. (הרשאות כתיבה מסומנות באמצעות CHMOD 0777)",
	'error_no_writing_prems' => "התיקיה &quot;<b>%s</b>&quot; לא ניתנת לכתיבה. <br />
				אנא שנה את הרשאות ה-chmod של תיקיה זו ל 0777 לפני שתמשיך בתהליך ההתקנה.",
	'system_is_writable_message' => 'PearCMS אישרה כי כל הקבצים הנדרשים ניתנים לכתיבה.<br />
		אתה מורשה להמשיך בתהליך ההתקנה, לחץ הבא בכדי להמשיך.',
	'installer_step3_instructions' => 'כל המשאבים הנדרשים להליך ההתקנה קיימים, ואתה עומד צעד אחד אחרון לפני הגדרת אתרך החדש!. אנא <span class="bold">קרא היטב</span> את תנאי השימוש במערכת.<br />
		במידה והנך רוצה להשתמש במערכת, עליך <span class="bold">לקרוא, להבין ולהסכים</span> לתנאים אלו. <span class="underline">חשוב לציין כי מהרגע בו סימנת כי קראת את התקנון ועברת לשלב הבא, הנך כבול לתנאים אלו ותישא בתוצאות במקרה של הפרתם.</span><br />
		במידה ועלתך שאלה / ברצונך לקבל מידע נוסף או כל מידע אחר בנוגע לתקנון, אנא פנה לצוות התמיכה של Pear Technology Investments.',
	'license_agreement_title' => 'תנאי שימוש',
	'license_agreement_sign' => 'בסימון תיבה זו אני מצהיר כי קראתי את התקנון, עיינתי בו, אני מקבל את תנאיו ואשא באחריות על כל חריגה ממנו',
	
	'requirement_phpver' => 'גרסאת PHP',
	'requirement_gd2' => 'ספריית GD2',
	'requirement_mysql' => 'התקן MySQL',
	'requirement_ok_message' => 'עומד בדרישות',
	'requirement_failed_message' => 'אינו עומד בדרישות',
	'software_license_not_downloaded_title' => 'שים לב',
	'software_license_not_downloaded_desc' => 'לא היה ניתן לטעון את תנאי הרשיון העדכניים ביותר.<br />
					יכולים להיות הבדלים בין תנאים אלו, הנכתבו בתאריך 30 ביוני 2011, לתנאים העדכניים.<br />
					אי קריאה של התנאים העדכניים אינה מסירה מתקפותם. התנאים העדכניים הם התקפים והקובעים.',
	'software_license_local_resource_missing' =>  "לא היה ניתן לטעון את תנאי ההסכם מהקובץ הממוטמן, אנא בדוק כי כל הקבצים הנדרשים קיימים ולאחר מכן נסה שנית.",
	
);