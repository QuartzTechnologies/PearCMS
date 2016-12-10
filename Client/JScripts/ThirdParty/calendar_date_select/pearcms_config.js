(function() {
	
	Date.weekdays = $w("M T W T F S S");
	Date.first_day_of_week = (PearRegistry.Settings.languageWeekFromSunday ? 7 : 1);
})();