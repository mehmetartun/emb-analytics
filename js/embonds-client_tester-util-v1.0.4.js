
	function toTurkishUpperCase(str){
		var letters = { "i": "İ", "ş": "Ş", "ğ": "Ğ", "ü": "Ü", "ö": "Ö", "ç": "Ç", "ı": "I" };
		str = str.replace(/(([iışğüçö]))/g, function(letter){ return letters[letter]; })
		return str.toUpperCase();
	}

	function toTurkishLowerCase(str){
		var letters = { "İ": "i", "I": "ı", "Ş": "ş", "Ğ": "ğ", "Ü": "ü", "Ö": "ö", "Ç": "ç" };
		str = str.replace(/(([İIŞĞÜÇÖ]))/g, function(letter){ return letters[letter]; })
		return str.toLowerCase();
	}

	function toEnglishLowerCase(str){
		var letters = { "İ": "i", "I": "i", "Ş": "s", "Ğ": "g", "Ü": "u", "Ö": "o", "Ç": "c" };
		str = str.replace(/(([İIŞĞÜÇÖ]))/g, function(letter){ return letters[letter]; })
		return str.toLowerCase();
	}
	
	function toEnglishUpperCase(str){
		var letters = { "i": "I", "ş": "S", "ğ": "G", "ü": "U", "ö": "O", "ç": "C", "ı": "I" };
		str = str.replace(/(([iışğüçö]))/g, function(letter){ return letters[letter]; })
		return str.toUpperCase();
	}

	function getUserByUsername(username) {
		for(var i=0;i<userList.length;i++) {
			if(username === userList[i].lsLogin) {
				return userList[i];
			}
		}
		return {};
	}

	function getUserByUserToken(userToken) {
		for(var i=0;i<userList.length;i++) {
			if(userToken === userList[i].lsUserToken) {
				return userList[i];
			}
		}
		return {};
	}
	
	function findISINByBondId(bondId) {
		for(var i=0;i<bondList.length;i++) {
			if(bondId === bondList[i].id) {
				return bondList[i].isin;
			}
		}
		return 'NULL';
	}
	
	function findBondIdByISIN(isin) {
		for(var i=0;i<bondList.length;i++) {
			if(isin === bondList[i].isin) {
				return bondList[i].id;
			}
		}
		return -1;
	}
	
	function convertFromTraderPrice(input) {
		var tmpString = String(input);
		if (PRICE_PATTERN.test(tmpString)) {
			return parseFloat(tmpString.replace(/,/g, "."));
		} else {
			return 0;
		}
	}

	function convertFromTraderSize(input) {
		var tmpString = String(input);
		if (SIZE_PATTERN.test(tmpString)) {
			tmpString = tmpString.replace(/,/g, ".");
			if (tmpString.length == 0) {
				return 0;
			}
			var factor = 1;
			while ((tmpString.length > 0) && (tmpString.charAt(tmpString.length - 1).toUpperCase() == 'M')) {
				factor *= 1000;
				tmpString = tmpString.substring(0, tmpString.length - 1);
			}
			if (Number(tmpString) != "NaN") {
				return Number(tmpString) * factor;
			} else {
				return 0;
			}
		} else {
			return 0;
		}
	}	

	function isEmpty(str) {
		return (!str || 0 === str.trim().length);
	}
	
	function isSet(obj) {
		return ((typeof (obj) !== "undefined") && (obj != null));
	}
	
	function rightPad(number, size) {
		return String(number + "00000").slice(0,size);;
	}
	
	function leftPad(number, size) {
		return String("00000" + number).slice(-size);
	}
	
	function writeText(text, innerHTMLMode, errorMode){
		var d = new Date();
		var textStr = "[" + leftPad(d.getHours(), 2) + ":" + leftPad(d.getMinutes(), 2) + ":" + leftPad(d.getSeconds(), 2) + "." 
					+ rightPad(d.getMilliseconds(), 3) + "] " + text;
		console.log(textStr);
		if (innerHTMLMode == true) {
			if (errorMode == 2) {
				messages.innerHTML += "<br/><font face='Consolas' size='2' color='red'>" + textStr + "</font>";
			} else {
				if (errorMode == 1) {
					messages.innerHTML += "<br/><font face='Consolas' size='2' color='green'>" + textStr + "</font>";
				} else {
					messages.innerHTML += "<br/><font face='Consolas' size='2' color='000000'>" + textStr + "</font>";
				}
			}
		}
	}		