//内容JS
function to_replay(formid,ifuser)
{
	if(ifuser != 1)
	{
		//判断账号和密码
		var name = getform(formid,"username").value;
		if(!name)
		{
			alert("账号不允许为空！");
			return false;
		}
	}
	var subject = getform(formid,"reply_subject").value;
	if(!subject)
	{
		alert("主题不允许为空！");
		return false;
	}
	var content = getform(formid,"reply_content").value;
	if(!content)
	{
		alert("内容不允许为空！");
		return false;
	}
	return true;
}
