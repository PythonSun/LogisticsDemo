/*在js中调用别的js*/
document.write("<script language=javascript src='/static/js/jquery-1.11.1.min.js'></script>");

/*检测输入字符*/
function detectinputtext(object){

	var value = $(object).val();
	/*替换特殊字符*/
	if(/[^\u4E00-\u9FA5A-Za-z0-9-()（）']+/.test(value)){
        $(object).val(value.replace(/[^\u4E00-\u9FA5A-Za-z0-9-()（）']+/,""));
    }
}

/*检测输入整形数字*/
function detectinputnumber(object){
	var value = $(object).val();
	if(/[^\d]/g.test(value)){
        $(object).val(value.replace(/[^\d]/g,""));
    }
}

/*检测输入浮点型数字*/
function detectinputfloat(object){
	if('' != object.value.replace(/\d{1,}\.{0,1}\d{0,}/,'')){
          object.value = object.value.match(/\d{1,}\.{0,1}\d{0,}/) == null ? '' :object.value.match(/\d{1,}\.{0,1}\d{0,}/);
    }
}

/*检测只能输入数字和字母*/
function detectinputdataletter(object){
	var value = $(object).val();
    $(object).val(value.replace(/[\W|\_]/g,""));
}

/*检测电话*/
function detectinputphone(object){

    var value = $(object).val();
    /*替换特殊字符*/
    if(/[^0-9-]+/.test(value)){
        $(object).val(value.replace(/[^0-9-]+/,""));
    }
}


