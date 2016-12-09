




	var gsCommands 			= ["LOGIN", "NAMEBUY", "NAMESELL", "ANONBUY", "ANONSELL", 
									"NAMEBUY+", "NAMESELL+", "ANONBUY+", "ANONSELL+", 
									"INDBUY", "INDSELL", "CANCELMYORDERS", "CANCELALLORDERS"];
	var gnOffsetTime 		= 0;	//in seconds
	var gnSequenceNumber 	= 1;
	
	var validationUserList 	= [];
	var userList			= [];
	var lnUserCount			= 0;
	
	var gnMaxTime			= 0;

	var fileInput = document.getElementById('fileInput');
	var envSelect = document.getElementById('env');
	
	var bondList			= [];
	var gData				= [];
	
	var sizePatternPart1 = "^([1-9]\\d{0,11})$|^([1-9]\\d{0,2}([,.]\\d{1,9})?MMM)$|^(0[,.]\\d{1,9}MMM)$|^([1-9]\\d{0,5}([,.]\\d{1,6})?MM)$";
	var sizePatternPart2 = "|^(0[,.]\\d{1,6}MM)$|^([1-9]\\d{0,8}([,.]\\d{1,3})?M)$|^(0[,.]\\d{1,3}M)$";
	var SIZE_PATTERN = new RegExp(sizePatternPart1.concat(sizePatternPart2), "i");
	var PRICE_PATTERN = /^(([1-9]\d*)([.,]\d{1,3})?)$|^([0]?([.,]\d{1,3}))$/i;	
		
	window.onload = function() {

		envSelect.addEventListener('change', function(e) {
			if(envSelect.value.toLowerCase() === 'null') {
				fileInput.disabled = true;				
			} else {
				fileInput.disabled = false;
			}
		});			

		
		fileInput.addEventListener('change', function(e) {

			var file = fileInput.files[0];
			var ext = file.name.split(".").pop().toLowerCase();
			
			if (ext === "csv") {
				writeText('File supported.', true, 1);
				writeText('Begining parsing...', true, 0);
				
				Papa.parse(file, {
					header: false,
					dynamicTyping: true,
					complete: function(results) {
						if (results.errors.length == 0) {
							
							writeText('Parsing successfully finished.', true, 1);
							printData(results.data);
							
							isValid = validateData(results.data);
							if(isValid == 0) {
								writeText('Validation failed.', true, 2);
								return;
							}
							writeText('Validation is successful.', true, 1);

							//processData(results.data);
							gData = results.data;
							processData(0);
							
														
						} else {
							writeText('error occured while parsing.', true, 2);
							return;								
						}
					}
				});
				
			} else {
				writeText('File not supported.', true, 2);
				return;
			}
		});		
	}

	function processData(mode) {	
		
		for (var i = 0; i < gData.length; i++) {
			
			var line = gData[i];
			
			if (line.length <= 1) {
				writeText('bos satir: ' + (i+1), false, 0);
				continue;						
			}
			if (String(line[0]).lastIndexOf('#', 0) === 0) {
				writeText('comment satiri: ' + (i+1), false, 0);
				continue;
			}
			if (isEmpty(String(line[0])) == true || isEmpty(String(line[1])) == true) {
				writeText('bos hucre: ' + (i+1), false, 0);
				continue;
			}

			processLine(line, mode);
		
		}
		
		if(mode != 0) {
			setTimeout(function(){ 
					writeText('All commands have been executed', true, 1); 
				}, (gnOffsetTime + gnMaxTime + 3) * 1000
			);
		}
	}
	
	function processLine(line, mode) {
		
		if(mode == 0) {
			
			if(line[2] === "LOGIN") {
				
				var username = line[1];
				var password = line[3];
				
				writeText(username + ' Opening socket...', true, 0);
				
				writeText('adding user ' + username, false, 0);
				userList.push({
					lnWebSocket : undefined,
					lsUserToken : '',
					lsLogin : username,
					lsPassword : password,
					lnUserId : 0,
					lnCounterpartyId : 0,
					lsRole : ''
				});
				writeText('user added ' + username, false, 0);
			
				openSocket(envSelect.value, username, password);
				
			}
		
		} else {
			
			if(line[2] === "NAMESELL" || line[2] === "NAMEBUY" 
					|| line[2] === "ANONSELL" || line[2] === "ANONBUY") {
				
				if (line.length > 8 && isEmpty(String(line[8])) == false) {
					//alert('sending stuff');
					setTimeout(function(){ sendOrder(line[1], line[2], line[3], line[4], line[5], line[7], line[8]) }, 
						(gnOffsetTime + line[0]) * 1000);
				} else {
					//alert('not sending stuff');
					if (line.length > 7 && isEmpty(String(line[7])) == false) {
						setTimeout(function(){ sendOrder(line[1], line[2], line[3], line[4], line[5], line[7], "") }, 
							(gnOffsetTime + line[0]) * 1000);
					} else {
						setTimeout(function(){ sendOrder(line[1], line[2], line[3], line[4], line[5], "", "") }, 
							(gnOffsetTime + line[0]) * 1000);
					}
				}
				
				
				
			}
			
			if(line[2] === "INDSELL" || line[2] === "INDBUY") {
				setTimeout(function(){ sendIndicativeOrder(line[1], line[2], line[3], line[4], line[5]) }, 
						(gnOffsetTime + line[0]) * 1000);
			}
			
			if(line[2] === "NAMESELL+" || line[2] === "NAMEBUY+" || line[2] === "ANONSELL+" || line[2] === "ANONBUY+") {
				setTimeout(function(){ sendIcebergOrder(line[1], line[2], line[3], line[4], line[5], line[6]) }, 
						(gnOffsetTime + line[0]) * 1000);
			}
			
			if(line[2] === "CANCELMYORDERS" || line[2] === "CANCELALLORDERS") {
				setTimeout(function(){ cancelOrders(line[1], line[2]) }, 
						(gnOffsetTime + line[0]) * 1000);
			}
			
			if (Number(line[0]) > gnMaxTime) {
				gnMaxTime = Number(line[0]);
			}
		}
	}
	
	function counterpartyTraders(user){
		var msg = {
			"action": "counterparty/traders"
		}	
		
		sendMessage(user, msg);
		writeText(user + ' counterpart/traders request is sent.', true, 0);	
	}
	
	function cancelOrders(user, command) {
		
		var l_action = "panic/trader/own";
		if (command == "CANCELALLORDERS") {
			l_action = "panic/trader/all";
		}
		
		var msg = {
			"action":l_action
		}	
		
		sendMessage(user, msg);
		writeText(user + ' ' + command + " request is sent.", true, 0);		
	}
	
	function sendOrder(user, command, isin, price, size, fillCondition, timeInForce) {
		
		var bondId = findBondIdByISIN(isin);
		if (bondId < 0) {
			writeText('bondId couldn\'t be found for ISIN: ' + isin + ' Order is not sent.', true, 2);
			return;
		}
		
		var traderPrice = convertFromTraderPrice(price);
		var traderSize = convertFromTraderSize(size);
		
		var traderSide = "BUY";
		if (command === "NAMESELL" || command === "ANONSELL") {
			traderSide = "SELL";
		} 
		
		var isAnonymous = "false";
		if (command === "ANONBUY" || command === "ANONSELL") {
			isAnonymous = "true";
		}
		
		var traderAllOrNone = "false";
		var traderFillOrKill = "false";
		
		if(isEmpty(String(fillCondition)) == false) {
			if(toEnglishUpperCase(String(fillCondition)) == "ALLORNONE") {
				traderAllOrNone = "true";
				traderFillOrKill = "true";
			} else {
				traderAllOrNone = "false";
				if(isEmpty(String(timeInForce)) == false) {
					if(toEnglishUpperCase(String(timeInForce)) == "GOODTILLCANCEL") {
						traderFillOrKill = "false";
					} else {
						traderFillOrKill = "true";
					}
				} else {
					traderFillOrKill = "true";
				}
			}
		}
		
		
		var msg = {
			"action":"order/add", 
			"details":{
				"bondId":bondId, 
				"live":"true", 
				"hidden":"false", 
				"allOrNone":traderAllOrNone, 
				"anonymous":isAnonymous, 
				"fillOrKill":traderFillOrKill, 
				"limitAction":"CANCEL", 
				"orders":[{"price":traderPrice,"size":traderSize,"side":traderSide}]
			}
		}	
		
		sendMessage(user, msg);
		writeText(user + ' ' + command + " request is sent for bond: " + bondId + ' isin: ' + isin 
						+ " price: " + traderPrice + " size: " + traderSize, true, 0);
		
	}
	
	function sendIndicativeOrder(user, command, isin, price, size) {
		
		var bondId = findBondIdByISIN(isin);
		if (bondId < 0) {
			writeText('bondId couldn\'t be found for ISIN: ' + isin + ' Order is not sent.', true, 2);
			return;
		}
		
		var traderPrice = convertFromTraderPrice(price);
		var traderSize = convertFromTraderSize(size);
		
		var traderSide = "BUY";
		if (command === "INDSELL") {
			traderSide = "SELL";
		} 
		
		var msg = {
			"action":"order/add", 
			"details":{
				"bondId":bondId, 
				"live":"false", 
				"hidden":"false", 
				"allOrNone":"false", 
				"anonymous":"false", 
				"fillOrKill":"false", 
				"limitAction":"CANCEL", 
				"orders":[{"price":traderPrice,"size":traderSize,"side":traderSide}]
			}
		}	
		
		sendMessage(user, msg);
		writeText(user + ' ' + command + " request is sent for bond: " + bondId + ' isin: ' + isin 
						+ " price: " + traderPrice + " size: " + traderSize, true, 0);
		
	}
	
	function sendIcebergOrder(user, command, isin, price, visibleSize, size) {
		
		var bondId = findBondIdByISIN(isin);
		if (bondId < 0) {
			writeText('bondId couldn\'t be found for ISIN: ' + isin + ' Order is not sent.', true, 2);
			return;
		}
		
		var traderPrice = convertFromTraderPrice(price);
		var traderVisibleSize = convertFromTraderSize(visibleSize);
		var traderSize = convertFromTraderSize(size);
		
		
		var traderSide = "BUY";
		if (command === "NAMESELL+" || command === "ANONSELL+") {
			traderSide = "SELL";
		} 
		
		var isAnonymous = "false";
		if (command === "ANONBUY+" || command === "ANONSELL+") {
			isAnonymous = "true";
		}
		
		var msg = {
			"action":"order/add", 
			"details":{
				"bondId":bondId, 
				"price":traderPrice, 
				"live":"true", 
				"hidden":"false", 
				"side":traderSide, 
				"allOrNone":"false", 
				"anonymous":isAnonymous, 
				"fillOrKill":"false", 
				"limitAction":"CANCEL", 
				"visibleSize":traderVisibleSize,
				"size":traderSize
			}
		}	
		
		sendMessage(user, msg);
		writeText(user + ' ' + command + " request is sent for bond: " + bondId + ' isin: ' + isin 
						+ " price: " + traderPrice + " visibleSize: " + traderVisibleSize + " size: " + traderSize, true, 0);
		
	}
	
	function login(username, password){
		
		writeText(username + ' Trying to log in using password: ' + password + '...', true, 0);
			
		var msg = {
			"action" : "authenticate",
			"details" : {
				"login" : username,
				"password" : password
			}
		};
		var messageText = JSON.stringify(msg);
		writeText("OUT: " + messageText, true, 0);
		getUserByUsername(username).lnWebSocket.send(messageText);
	}
	
	function openSocket(strHostname, username, password) {

		var webSocket;
		var webSocketURL = getWebSocketUrl(strHostname);
	
		if(webSocket !== undefined && webSocket.readyState !== WebSocket.CLOSED){
			writeText("WebSocket is already opened to " + webSocketURL + ' for ' + username, false, 1);
			return;
		}

		webSocket = new WebSocket(webSocketURL);
		 
		webSocket.onopen = function(event){       
		
			//if(event.data === undefined)
			//    return;
			writeText(username + " WebSocket successfully opened to " + webSocketURL, true, 1);	
			
			writeText('getUserByUsername as ' + username + '...' + password, false, 0);
			getUserByUsername(username).lnWebSocket = this;
			
			writeText('login as ' + username + '...' + password, false, 0);
			login(username, password);
			
		};

		webSocket.onmessage = function(event){
		
			var msg = null;
			try {
				msg = JSON.parse(event.data);
			} catch (e) {
				msg = null;
				writeText("Message parsing to JSON failed.", false, 0);
				return;
			}
			
			writeText("IN: " + JSON.stringify(msg).replace(/,/g, ", "), false, 0);
			
			/*
			if(!isSet(msg.response)) {
				writeText(msg.request.action + " request failed. Error code: " + msg.errors[0].code.toUpperCase() 
								+ " Error message: " + msg.errors[0].message, true, 0);
				return;
			} 
			*/
			processResponse(msg);

		};

		webSocket.onclose = function(event){
			
			writeText("WebSocket Connection closed. Reason: " + event.reason, true, 1);
			writeText("EMBonds Client Tester has been closed", true, 1);	
			
			//if (event.reason != "logout") {
			//	alert("WebSocket closed!");
			//}

		};
		return webSocket;
	}
	
	function processResponse(msg) {

		console.log(msg);

		if(isSet(msg.request) && isSet(msg.request.action)) {
			switch(msg.request.action) {
				
				case "authenticate":
				
					if(isSet(msg.response)) {
						var user = getUserByUsername(msg.request.details.login);
						
						user.lsUserToken 	= msg.response[0].token;
						user.lsLogin 		= msg.response[0].user.login;
						user.lsPassword 	= msg.request.details.password;
						user.lnUserId 		= msg.response[0].user.id;
						user.lnCounterpartyId = msg.response[0].user.counterparty.id;
						user.lsRole 		= msg.response[0].user.counterparty.role.toUpperCase();
						
						lnUserCount++;
						writeText(user.lsLogin + " logged in as " +  user.lsRole  + " ( " + user.lsUserToken + " )", true, 1);	
						
						if (lnUserCount === 1) {
							getBondList(user.lsLogin);
						}
					} else {
						writeText(msg.request.details.login + " " + msg.request.action + " request failed" 
									+ " Error code: " + msg.errors[0].code.toUpperCase() 
									+ " Error message: " + msg.errors[0].message, true, 2);
					}
					
					break;
				
				case "bond/get":
					
					for(var i=0;i<msg.response.length;i++) {
						bondList.push({
							id : msg.response[i].id,
							isin : msg.response[i].isin
						});
						$("#bondlistul").append('<li>' + msg.response[i].isin + '</li>');
					}
					writeText('bondList is retrieved.', true, 1);
					
					writeText('listing bonds...', false, 0);
					for(var i=0;i<bondList.length;i++){
						writeText(' bondId: ' + bondList[i].id + ' isin: ' + bondList[i].isin, false, 0);
					}
					writeText('bonds listed.', false, 0);
					
					processData(1);
									
					break;
				
				case "order/panicButton":
					
					var user = getUserByUserToken(msg.request.token);
					var command = "";
					if(String(msg.request.details.path) == "traderOwnOrders") {
						command = "CANCELMYORDERS";
					} else {
						command = "CANCELALLORDERS";
					}
					if(isSet(msg.errors)) {
						writeText(user.lsLogin + " " + command + " request is failed."
										+ " Error code: " + msg.errors[0].code.toUpperCase() 
										+ " Error message: " + msg.errors[0].message, true, 2);
					} else {
						writeText(user.lsLogin + " " + command + " request is successful.", true, 1);
					}
					
					break;
				
				case "order/add":
					
					var user = getUserByUserToken(msg.request.token);
					var isin = findISINByBondId(msg.request.details.bondId);
					var command = msg.request.details.orders[0].side;
					
					if (String(msg.request.details.live) == "false") {
						command = "IND" + command;
					} else {
						if (String(msg.request.details.anonymous) == "false") {
							command = "NAME" + command;
						} else {
							command = "ANON" + command;
						}
						if (isSet(msg.request.details.orders[0].visibleSize)) {
							command = command + "+";
						} 
					}
					
					if(isSet(msg.response)) {
						
						if(!isSet(msg.request.details.visibleSize)) {					
							writeText(user.lsLogin + " " + command + " request is successful for " + isin 
										+ " price: " + msg.request.details.orders[0].price
										+ " size: " + msg.request.details.orders[0].size, true, 1);									
						} else {
							writeText(user.lsLogin + " " + command + " request is successful for " + isin 
										+ " price: " + msg.request.details.orders[0].price
										+ " visibleSize: " + msg.request.details.orders[0].visibleSize
										+ " size: " + msg.request.details.orders[0].size, true, 1);
						}	
						
					} else {	
						if(!isSet(msg.request.details.visibleSize)) {		
							writeText(user.lsLogin + " " + command + " request failed for " + isin 
										+ " price: " + msg.request.details.orders[0].price
										+ " size: " + msg.request.details.orders[0].size
										+ " Error code: " + msg.errors[0].code.toUpperCase() 
										+ " Error message: " + msg.errors[0].message, true, 2);
						} else {
							writeText(user.lsLogin + " " + command + " request failed for " + isin 
										+ " price: " + msg.request.details.orders[0].price
										+ " visibleSize: " + msg.request.details.orders[0].visibleSize
										+ " size: " + msg.request.details.orders[0].size
										+ " Error code: " + msg.errors[0].code.toUpperCase() 
										+ " Error message: " + msg.errors[0].message, true, 2);
						}
					}
					break;
			}
		}
		
		if (isSet(msg.messages)) {
			//do nothing
		}
	}


	function getBondList(username){
	
		writeText('Getting the bondList...', true, 0);
	
		var msg = {
			"action":"bond/get", 
			"details":{}
		}
		
		sendMessage(username, msg);
		writeText('bond/get request is sent', false, 0);
		
	}

	function sendMessage(username, msg){
		
		var user = getUserByUsername(username);
		
		msg.token = user.lsUserToken;
		msg.sequenceNumber = gnSequenceNumber;		
		var messageText = JSON.stringify(msg);
		writeText("OUT: " + messageText, false, 0);
		user.lnWebSocket.send(messageText);
	}
	
	var getWebSocketUrl = function(strHostname) {
		
		if (strHostname.toLowerCase() === "rhodes") {
			return "ws://rhodes:8080/EMBonds/socket/1.0";
		}
		if (strHostname.toLowerCase() === "uat") {
			return "wss://uat.embonds.com/EMBonds/socket/1.0";
		}
		if (strHostname.toLowerCase() === "demo") {
			return "wss://demo.embonds.com/EMBonds/socket/1.0";
		}
		if (strHostname.toLowerCase() === "uat2") {
			return "wss://uat2.embonds.com/EMBonds/socket/1.0";
		}
		if (strHostname.toLowerCase() === "iron1") {
			return "wss://iron1.embonds.net/EMBonds/socket/1.0";
		}
		if (strHostname.toLowerCase() === "bronze1") {
			return "wss://bronze1.embonds.com/EMBonds/socket/1.0";
		}
		if (strHostname.toLowerCase() === "iron2") {
			return "wss://iron2.embonds.net/EMBonds/socket/1.0";
		}
		if (strHostname.toLowerCase() === "copper1") {
			return "wss://copper1.embonds.net/EMBonds/socket/1.0";
		}
		if (strHostname.toLowerCase() === "copper2") {
			return "wss://copper2.embonds.net/EMBonds/socket/1.0";
		}
		if (strHostname.toLowerCase() === "gold1") {
			return "wss://gold1.embonds.com/EMBonds/socket/1.0";
		}

		return "wss://rhodes/EMBonds/socket/1.0";
	}
	
	function validateData(data) {		
	
		writeText('Validating the file...', true, 0);
		
		var isExists = 0;
		
		for (var i = 0; i < data.length; i++) {
			
			var line = data[i];
			if (line.length <= 1) {
				writeText('bos satir: ' + (i+1), false, 0);
				continue;						
			}
			if (String(line[0]).lastIndexOf('#', 0) === 0) {
				writeText('comment satiri: ' + (i+1), false, 0);
				continue;
			}
			if (isEmpty(String(line[0])) == true || isEmpty(String(line[1])) == true) {
				writeText('bos hucre: ' + (i+1), false, 0);
				continue;
			}
			
			
			//checking if the commands are valid
			isExists = 0;
			for(var j=0;j<gsCommands.length;j++) {
				if(line[2] === gsCommands[j]) {
					isExists = 1;
				}
			}
			if (isExists == 0) {
				writeText('Row ' + (i+1) + ' has a wrong command: ' + line[2], true, 2);
				writeText('Accepted commands are:', true, 2);
				writeText(String(gsCommands).replace(/,/g, ", "), true, 2);
				return 0;
			}
			
			if (line[2] === "LOGIN") {
				if (isEmpty(String(line[3])) == true) {
					writeText('Row ' + (i+1) + ' ' + line[2] + ' command must have 1 parameter', true, 2);
					writeText('Syntax for ' + line[2] + ' command: time username ' + line[2] + 
									' password {FillCondition TimeInForce}', true, 2);
					return 0;
				}
			}
			
			if (line[2] === "ANONBUY" || line[2] === "ANONSELL" || line[2] === "NAMEBUY" 
						|| line[2] === "NAMESELL" || line[2] === "INDBUY" || line[2] === "INDSELL") {
				if (isEmpty(String(line[3])) == true || isEmpty(String(line[4])) == true || isEmpty(String(line[5])) == true) {
					writeText('Row ' + (i+1) + ' ' + line[2] + ' command must have 3 parameter', true, 2);
					writeText('Syntax for ' + line[2] + ' command: time username ' + line[2] + 
									' isin price size {FillCondition TimeInForce}', true, 2);
					return 0;
				}
			}
			
			//if commands are iceberg orders
			if (line[2] === "ANONBUY+" || line[2] === "ANONSELL+" || line[2] === "NAMEBUY+" || line[2] === "NAMESELL+") {
				
				if (isEmpty(String(line[3])) == true || isEmpty(String(line[4])) == true 
						|| isEmpty(String(line[5])) == true || isEmpty(String(line[6])) == true) {
					writeText('Row ' + (i+1) + ' ' + line[2] + ' command must have 4 parameter', true, 2);
					writeText('Syntax for ' + line[2] + ' command: time username ' + line[2] + 
									' isin price visibleSize size {Partial GoodTillCancel}', true, 2);
					return 0;
				}
				
				if (convertFromTraderSize(line[6]) <= convertFromTraderSize(line[5])) {
					writeText('Row ' + (i+1) + ' has a size less than visibleSize.', true, 2);
					return 0;
				}
			}
						
			if (line[2] === "ANONBUY" || line[2] === "ANONSELL" || line[2] === "NAMEBUY" || line[2] === "NAMESELL") {
				
				if (line.length > 7 && isEmpty(String(line[7])) == false) {
					
					if (toEnglishUpperCase(String(line[7])) != "PARTIAL" && toEnglishUpperCase(String(line[7])) != "ALLORNONE") {
						writeText('Row ' + (i+1) + ' ' + line[2] + 
										' command\'s FillCondition parameters can only be either Partial or AllOrNone', true, 2);
						writeText('Syntax for ' + line[2] + ' command: time username ' + line[2] + 
										' isin price size {Partial|AllOrNone GoodTillCancel|FillOrKill}', true, 2);
						return 0;
					}
					
					if (line.length > 8 && isEmpty(String(line[8])) == false) {
						if (toEnglishUpperCase(String(line[8])) != "GOODTILLCANCEL" && toEnglishUpperCase(String(line[8])) != "FILLORKILL") {
							writeText('Row ' + (i+1) + ' ' + line[2] + 
								' command\'s TimeInForce parameters can only be either GoodTillCancel or FillOrKill', true, 2);
							writeText('Syntax for ' + line[2] + ' command: time username ' + line[2] + 
										' isin price size {Partial|AllOrNone GoodTillCancel|FillOrKill}', true, 2);
							return 0;
						}
					}
					
					if (toEnglishUpperCase(String(line[7])) == "ALLORNONE") {
						if (line.length > 8 && isEmpty(String(line[8])) == false) {
							if (toEnglishUpperCase(String(line[8])) != "FILLORKILL") {
								writeText('Row ' + (i+1) + ' If ' + line[2] + 
									' command\'s FillCondition parameter is AllOrNone, then the TimeInForce can only be FillOrKill', true, 2);
								writeText('Syntax for ' + line[2] + ' command: time username ' + line[2] + 
										' isin price size {Partial|AllOrNone GoodTillCancel|FillOrKill}', true, 2);
								return 0;
							}
						}						
					}
				}
			}

			//checking if a same user has more than one LOGIN command
			if(line[2] === "LOGIN") {
				isExists = 0;
				for(var j=0;j<validationUserList.length;j++) {
					if(line[1] === validationUserList[j].userName) {
						isExists = validationUserList[j].lineNumber + 1;
					}
				}
				if(isExists > 0) {
					writeText('Row ' + (i+1) + ' has a second LOGIN command for the user ' + line[1] + ' in row ' + isExists, true, 2);
					writeText('There must be only one LOGIN command for each user', true, 2);
					return 0;
				}
				
				validationUserList.push({
					userName : line[1],
					lineNumber : i
				});
			}
			
			
		}
		return 1;
	}
	
	function printData(data) {			
		var i, j;
		
		writeText('Row count: ' + data.length, false, 0);
		for (i = 0; i < data.length; i++) {
			var line = data[i];
			writeText('Column count: ' + line.length + ' for row: ' + i, false, 0);
			if (line.length <= 1) {
				return;						
			}
			for(j = 0; j < line.length; j++) {
				var cell = line[j];
				writeText('i: ' + i + " j: " + j + " cell: " + cell, false, 0);
			}
		}
	}	

