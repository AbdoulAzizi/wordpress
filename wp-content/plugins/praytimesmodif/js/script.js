var currentDate = new Date();

// display monthly timetable
function renderPrayTimes(offset=0) {
	var args = JSON.parse(PrayTimeArgs);

	var lat = Number((parseFloat(args.latitude)).toFixed(2));
	var lng = Number((parseFloat(args.longitude)).toFixed(2));
	var timeZone = Number((parseFloat(args.timezone)).toFixed(1));
	var dst = parseInt(args.dst,10);
	var format = args.format;
	var method = args.method;

	prayTimes.setMethod(method);
	currentDate.setMonth(currentDate.getMonth()+ 1* offset);
	var month = currentDate.getMonth();
	var year = currentDate.getFullYear();
	var title = currentDate.toLocaleDateString('en-US', { month: 'long' })+ ' '+ year;
	jQuery('#table-title').html(title);
	makeTable(year, month, lat, lng, timeZone, dst, format);
}

// make monthly timetable
function makeTable(year, month, lat, lng, timeZone, dst, format) {		
	var items = {day_num: 'Date',day: 'Day', fajr: 'Fajr', sunrise: 'Sunrise', dhuhr: 'Dhuhr', asr: 'Asr', maghrib: 'Maghrib', isha: 'Isha'};
	var thead = document.createElement('thead');
	thead.classList.add('thead-default');
	thead.appendChild(makeTableRow(items, items, '','th'));

	var tbody = document.createElement('tbody');

	var date = new Date(year, month, 1);
	var endDate = new Date(year, month+ 1, 1);

	while (date < endDate) {
		var times = prayTimes.getTimes(date, [lat, lng], timeZone, dst, format);
		times.day_num = date.getDate();
		times.day = date.toLocaleDateString('en-US', { weekday: 'short' });
		var today = new Date();
		var isToday = (date.getMonth() == today.getMonth()) && (date.getDate() == today.getDate());
		var klass = isToday ? 'today-row' : '';
		tbody.appendChild(makeTableRow(times, items, klass));
		date.setDate(date.getDate()+ 1);  // next day
	}
	jQuery('#timetable').html('');
	jQuery('#timetable').append(thead);
	jQuery('#timetable').append(tbody);
}

// make a table row
function makeTableRow(data, items, klass, cell_type = 'td') {
	var row = document.createElement('tr');
	for (var i in items) {
		var cell = document.createElement(cell_type);
		if ('day_num'==i && 'Date'!=data[i]) {
			var span = document.createElement('span');
			span.innerHTML = data[i];
			cell.appendChild(span);
		}else{
			cell.innerHTML = data[i];
		}
		cell.classList.add(i);
		row.appendChild(cell);
	}
	if ('' !== klass) {
		row.className = klass;
	}
	return row;		
}