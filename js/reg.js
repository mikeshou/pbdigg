
function ckusername()
{
	var username = $.trim($('#username').val());
	var ck = $('#ckusername');
	var ulen = username.replace(/[^\x00-\xff]/g, "**").length;
	if(ulen < reg_minname || ulen > reg_maxname)
	{
		ck.css('display','block');
		return false;
	}
    CKAjax('username', '&username=' + encodeURIComponent(username));
}
function ckemail()
{
	var email = $.trim($('#email').val());
	var ck = $('#ckemail');
	var reg = /^(([0-9a-zA-Z]+)|([0-9a-zA-Z]+[_.0-9a-zA-Z-]*[0-9a-zA-Z]+))@([a-zA-Z0-9-]+[.])+([a-zA-Z]{2}|net|com|gov|mil|org|edu|info)$/i;
	if(reg.test(email))
	{
		CKAjax('email', '&email=' + email);
	}
	else
	{
		ck.css('display','block');
		return false;
	}
}
function ckpw()
{
	var password = $.trim($('#password').val());
	var ck = $('#ckpw');
	if(password.length < 6 || /[\'\"\\]/.test(password))
	{
		ck.css('display','block');
	}
	else
	{
		ck.css('display', 'none');
	}
}
function ckrpw()
{
	var password = $.trim($('#password').val());
	var rpassword = $.trim($('#rpassword').val());
	var ck = $('#ckrpw');
	if(password != rpassword)
	{
		ck.css('display','block');
	}
	else
	{
		ck.css('display', 'none');
	}
}
function ckcaptcha()
{
	var captcha = $.trim($('#captcha').val());
	var ck = $('#ckcaptcha');
	if(/^[a-z0-9]+?$/.test(captcha))
	{
		CKAjax('captcha', '&captcha=' + captcha);
	}
	else
	{
		ck.css('display', 'block');
	}
}
function ckanswer()
{
	CKAjax('answer', '&answer=' + encodeURIComponent($('#answer').val()));
}
function ckprivacy(obj)
{
	var ck = $('#ckprivacy');
	var submit = $('#submit');
	if(obj.checked)
	{
		ck.css('display', 'none');
		submit.attr('disabled', '');
	}
	else
	{
		submit.attr('disabled', 'disabled');
		ck.css('display', 'block');
	}
}
function CKAjax(action, param)
{
	$('#ck'+action).html(I18N.ajax_loading).css('display','block');
	$.ajax({
		type: 'GET',
		url: pb_url+'ajax.php',
		dataType: 'json',
		data: 'action='+action+'&verify='+verify+param+'&random=' + Math.random(),
		error: function(var1,var2,var3)
		{
			contentlayer(action, I18N.ajax_response_failed, true);
		},
		success: function(data)
		{
			if (data[0] == '1')
			{
	            $('#ck'+action).html('<img src="' + pb_url + 'templates/' + pb_template + '/pic/finish.gif" width="13" height="13" />');
			}
			else
			{
				$('#ck'+action).html(data[1]).css('display','block');
			}
		}
	});
	return;
}
function regsubmit(obj)
{
	$('#submit').css('display', 'none');
	$('#registsending').css('display', '');
	return true;
}
function loginsubmit()
{
	$('#submit').attr('disabled', 'true');
	return true;
}