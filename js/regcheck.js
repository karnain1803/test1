var xmlHttp = false;
/*@cc_on @*/
/*@if (@_jscript_version >= 5)
try {
  xmlHttp = new ActiveXObject("Msxml2.XMLHTTP");
} catch (e) {
  try {
    xmlHttp = new ActiveXObject("Microsoft.XMLHTTP");
  } catch (e2) {
    xmlHttp = false;
  }
}
@end @*/
if (!xmlHttp && typeof XMLHttpRequest != 'undefined') {
  xmlHttp = new XMLHttpRequest();
}	
function iload()
{
img = new Image();
img.src = "captcha/?tt="+Math.random();
document.getElementById("image").src = img.src;
}		
function callServer() {
  var login = document.getElementById("login").value;
  if ((login == null) || (login == "")) return;
  document.reg.check.disabled = true;
  //document.reg.check.value = "Проверка...";
  var url = "regcheck.php?login=" + escape(login)+'&rand='+Math.random();
  xmlHttp.open("GET", url, true);
  xmlHttp.onreadystatechange = updatePage;
  xmlHttp.send(null);
}
function updatePage() {
  if (xmlHttp.readyState == 4) {
    var response = xmlHttp.responseText;
	if (response == 'denied') {
		ans = '<font color=#ff0000>Данный логин уже используется</font>';
	} else 
	if (response == 'free') {
		ans = '<font color=#007f00>Логин <font color=#0000ff><b>'+document.getElementById("login").value+'</b></font> свободен для регистрации</font>';		
	} else 
	if (response == 'bad') {
		ans = '<font color=#ff0000>В логине использованы недопустимые символы</font>';		
	} else
	if (response == 'length') {
		ans = '<font color=#ff0000>Логин должен быть не менее 4 и не более 20 символов</font>';		
	} else
	{
		ans = '<font color=#ff0000>Произошла ошибка при проверке, попробуйте сделать проверку позже</font>';
	}
	
    document.getElementById("answ").innerHTML = ans;
	document.reg.check.disabled = false;
	//document.reg.check.value = "Проверить";
  }
}

function fastsubm(){	
	if ((document.reg.question.value.length < 3)||(document.reg.question.value.length > 30)) { alert ("Секретный вопрос должен быть не менее 3 и не более 30 символов");
	} else 
	if ((document.reg.answer.value.length < 3)||(document.reg.answer.value.length > 25)) { alert ("Ответ на вопрос должен быть не менее 3 и не более 25 символов");
	} else
	if (document.reg.answer.value==document.reg.question.value) { alert ("Вопрос и ответ не должны быть одинаковыми");
	} else
	if (document.reg.email.value.length < 3) { alert ("Введите E-Mail");
	} else
	if (document.reg.kapcha.value.length != 4) { alert ("Введите цифры с картинки с кодом безопасности");
	} else
	if (!document.reg.ok.checked) { alert ("Обязательно прочтите правила и условия пользования, и поставьте галочку, что вы ознакомились и принимаете их.");
	} else {
		document.reg.submit();		
	}
	return false;
}

function subm(){	
	if ((document.reg.login.value.length < login_min_len)||(document.reg.login.value.length > login_max_len)) { alert ("Логин должен быть не менее "+login_min_len+" и не более "+login_max_len+" символов");
	} else 
	if ((document.reg.pass.value.length < passw_min_len)||(document.reg.pass.value.length > passw_max_len)){ alert ("Пароль должен быть не менее "+passw_min_len+" и не более "+passw_max_len+" символов");
	} else 
	if ((document.reg.question.value.length < 3)||(document.reg.question.value.length > 30)) { alert ("Секретный вопрос должен быть не менее 3 и не более 30 символов");
	} else 
	if ((document.reg.answer.value.length < 3)||(document.reg.answer.value.length > 25)) { alert ("Ответ на вопрос должен быть не менее 3 и не более 25 символов");
	} else
	if (document.reg.answer.value==document.reg.question.value) { alert ("Вопрос и ответ не должны быть одинаковыми");
	} else
	if ((document.reg.name.value.length < 3)||(document.reg.name.value.length > 20)) { alert ("Имя должно быть не менее 3 и не более 20 символов");
	} else
	if (document.reg.email.value.length < 3) { alert ("Введите E-Mail");
	} else
	if (document.reg.kapcha.value.length != 4) { alert ("Введите цифры с картинки с кодом безопасности");
	} else
	if (document.reg.pass.value != document.reg.pass_chek.value) { alert ("Проверьте правильность ввода паролей, они должны совпадать");
	} else 
	if (!document.reg.ok.checked) { alert ("Обязательно прочтите правила и условия пользования, и поставьте галочку, что вы ознакомились и принимаете их.");
	} else {
		document.reg.submit();		
	}
	return false;
}