
$(document).ready(function(){
	
	$("#loginbutton").click(function(){
		var user_name = $("#username").val();
		var pass_word = $("#password").val();
		userList.push({
			lnWebSocket : undefined,
			lsUserToken : '',
			lsLogin : user_name,
			lsPassword : pass_word,
			lnUserId : 0,
			lnCounterpartyId : 0,
			lsRole : ''
		});
		openSocket(envSelect.value, user_name, pass_word);
	});	
	$("#tradersbutton").click(function(){
		var user_name = $("#username").val();
		
		var msg = {
			"action" : "user/loginstatus/subscribe",
			details: {
				"ids": null,
				"path": null,
				"type": "subscribe",
				"overwrite":true,
				"notifyOnAdd":true,
			}
		}
		
		sendMessage(user_name, msg);
		//counterpartyTraders(user_name);
	});

	
});
