Handlebars.registerHelper('cond', function(v1, operator, v2, options) {
	switch (operator) {
		case '==':
			return (v1 == v2) ? true : false;
		case '===':
			return (v1 === v2) ? true : false;
		case '<':
			return (v1 < v2) ? true : false;
		case '<=':
			return (v1 <= v2) ? true : false;
		case '>':
			return (v1 > v2) ? true : false;
		case '>=':
			return (v1 >= v2) ? true : false;
		case '&&':
			return (v1 && v2) ? true : false;
		case '||':
			return (v1 || v2) ? true : false;
		default:
			return false;
	}
});