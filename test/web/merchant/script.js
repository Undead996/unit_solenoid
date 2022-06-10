function redirect(json){
	let form = document.createElement('FORM');
	form.action = json.merchant_url;
	form.method = 'POST';
	let keys = Object.keys(json); 
	for (let i = 0; i < keys.length; i++) {
		let input = document.createElement('INPUT');
		input.type = "hidden";
		input.name = keys[i];
		input.value = json[keys[i]];
		form.appendChild(input);
	}
	document.body.append(form);
	form.submit();
}

window.onload = () => {
    redirect(data);
}