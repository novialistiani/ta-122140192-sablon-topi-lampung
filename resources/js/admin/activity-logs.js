window.filterByAction = function filterByAction(action) {
	const url = new URL(window.location.href);
	if (action) {
		url.searchParams.set('action', action);
	} else {
		url.searchParams.delete('action');
	}
	window.location.href = url.toString();
};