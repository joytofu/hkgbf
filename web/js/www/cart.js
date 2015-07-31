//购物车里的其他相关信息
function cart_minus(id,amount)
{
	if(amount<2)
	{
		alert("产品数量小于2个，不能执行此操作，如果您不需要此产品，请删除！");
		return false;
	}
	var namount = amount - 1;
	var url = base_url + base_func + "=ajax_update&id="+id+"&amount="+namount.toString();
	get_ajax(url);
}

function cart_plus(id,amount)
{
	var namount = amount + 1;
	var url = base_url + base_func + "=ajax_update&id="+id+"&amount="+namount.toString();
	get_ajax(url);
}

function cart_del(id)
{
	var url = base_url + base_func + "=ajax_del&id="+id;
	get_ajax(url);
}

function to_checkout(cart)
{
	if(cart == "0")
	{
		alert("购物车为空，请返回！");
		return false;
	}
	//联系人信息
	var fullname = getid("fullname").value;
	if(!fullname)
	{
		alert("收货人姓名不允许为空");
		return false;
	}
	var tel = getid("tel").value;
	var mobile = getid("mobile").value;
	if(!tel && !mobile)
	{
		alert("电话和手机至少要填写一个，以方便客服与您取得联系");
		return false;
	}
	var addr = getid("address").value;
	if(!addr)
	{
		alert("地址信息不能为空，如果您购买的是虚拟产品，请填写您的邮箱");
		return false;
	}
	var zipcode = getid("zipcode").value;
	if(!zipcode)
	{
		alert("邮编不允许为空，如您购买的是虚拟产品，请填写：123456");
		return false;
	}
	var email = getid("email").value;
	if(!email)
	{
		alert("邮箱不允许为空！");
		return false;
	}
	var str_reg=/^\w+((-\w+)|(\.\w+))*\@{1}\w+\.{1}\w{2,4}(\.{0,1}\w{2}){0,1}/ig;
	if(email.search(str_reg) == -1)
	{
		alert("邮箱格式不正确！");
		return false;
	}
	getid("_phpok_submit").disabled = true;
	return true;
}