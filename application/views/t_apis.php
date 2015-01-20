<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN"
    "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd"> 
<html>
<head>
<title>API 테스트</title>
<meta name="generator" content="Namo WebEditor v3.0">
<STYLE>
	html, body, div, span, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, address, cite, code, del, dfn, em, img, ins, q, small, strong, sub, sup, dl, dt, dd, ol, ul, li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td {
	    border: 0px none;
	    margin: 0px;
	    padding: 0px;
		color: #FFFFFF;
	}
	BODY {
		background: #222222;
		text-align: center;
	}
	#mainboard {
		 width: 80%;
		 text-align: center;
		 background: #222222;
		 margin-top: 50px;
	}
	TABLE { width:100%; border: solid red 0px; margin-left: 50px}
	TD { padding: 5px; text-align: left;}
	TH { background: #23ADCC; width: 150px}
	INPUT[type="text"] { width: 300px; height:20px }
	INPUT .paramtext { width: 150px; }
	INPUT[type="button"] { width: 100px; }
	TEXTAREA { width: 600px; }
</STYLE>
<script type='text/javascript' src="<?php echo $baseDir ?>/www/js/jquery-2.1.0.js"></script>
<!--<script type='text/javascript' src='http://ajax.googleapis.com/ajax/libs/jquery/1.6/jquery.min.js?ver=1.6'></script>-->
</head>

<body bgcolor="white" text="black" link="blue" vlink="purple" alink="red" style="text-align: center;">
<div align="center">
	<div id="mainboard">
		<p><h3>API테스트</h3></p>
		<hr/>
		<p>
		<table border="1">
			<tr><th>써버IP</th><td><input type="text" id="server" value="localhost" placeholder="써버IP" readonly/></td></tr>
			<tr><th>프로쎄스명</th><td><input type="text" id="process" value="netproc" placeholder="프로쎄스명"/></td></tr>
			<tr><th>호출방식</th><td>
				<input type="radio" id="apimethod" name="apimethod" value="POST" onclick="setMethod(this.value)">POST<br/>
				<input type="radio" id="apimethod" name="apimethod" value="GET" onclick="setMethod(this.value)">GET<br/><br/>
				선택한 호출방식은 <input type="text" id="apimethodresult" value="" placeholder="호출방식" readonly style="width: 100px">
				</td></tr>
			<tr><th>API명</th><td><input type="text" id="apiname" value="" placeholder="API이름(ex: login_user)"></td></tr>
			<tr><th>PARAMS</th><td>
				<div id="apiparams">
					파라메터를 추가하세요
				</div>
			</td></tr>
			<tr><th>호출URL</th><td><input type="text" id="callurl" value="" placeholder="호출되는 완전URL" readonly style="width: 600px"></td></tr>
			<tr><th>응답</th><td><textarea id="response" rows="10"></textarea></td></tr>
		</table>
		</p>
		<hr/>
		<p>
			<input type="button" value="파라메터추가" onclick="javascript:addParam();">
			<input type="button" value="결과보기" onclick="javascript:onVerify();">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="button" value="초기화" onclick="javascript:clearParam();">
		</p>
	</div>
</div>
<script type='text/javascript'>
	var pcount = 0;
	
	function clearParam()
	{
		pcount = 0;
		var div_params = document.getElementById("apiparams");
		div_params.innerHTML="파라메터를 추가하세요";
		$("#apiname").val("");
		$("#response").val("");
		
	}
	function addParam()
	{
		pcount = pcount + 1;
		
		var div_params = document.getElementById("apiparams");
		var div_element = document.createElement("div");
		
		var elCaption = document.createTextNode("[PARAM"+pcount+"]------");

		var elParamName = document.createElement("input");
		elParamName.setAttribute("type", "text");
		elParamName.setAttribute("id", "paramname"+pcount);
		elParamName.setAttribute("value", "");
		elParamName.setAttribute("style", "width:150px");
		elParamName.setAttribute("placeholder", "파라메터명");
		
		var elNode = document.createTextNode("===>");
		
		var elParamValue = document.createElement("input");
		elParamValue.setAttribute("type", "text");
		elParamValue.setAttribute("id", "paramvalue"+pcount);
		elParamValue.setAttribute("value", "");
		elParamValue.setAttribute("style", "width:150px");
		elParamValue.setAttribute("placeholder", "파라메터값");

		div_element.appendChild(elCaption);
		div_element.appendChild(elParamName);
		div_element.appendChild(elNode);
		div_element.appendChild(elParamValue);
		div_params.appendChild(div_element);
		
		return false;
	}
	function setMethod(method)
	{
		$("#apimethodresult").val(method);
	}
	function onVerify() 
	{
		if ($("#apimethodresult").val() == "")
		{
			alert("호출방식을 설정하세요");
			return;
		}
		var apiname = $("#apiname").val();
		//var baseURL = "http://"+$("#server").attr("value") + "<?php echo $baseDir; ?>/netproc/" + apiname;
		var baseURL = "<?php echo $baseDir; ?>/"+$("#process").val()+"/" + apiname;
		$("#callurl").val(baseURL);
		
		var i;
		var params={};
		for (i=1; i<=pcount; i++)
		{
			params[$("#paramname"+i).val()] = $("#paramvalue"+i).val();
		}
		
		$("#response").val("응답대기중입니다...");
		$.ajax({
            type: $("#apimethodresult").val(),
            url: baseURL,
            data: params
        })
		.done(function(msg){
			$("#response").val(msg);
        })
		.fail(function(){
			$("#response").val("처리가 실패하였습니다.");
        });
	}
</script>
</body>

</html>
