//找到url中匹配的字符串
function findInUrl(str){
	url = location.href;
	return url.indexOf(str) == -1 ? false : true;
}
//获取url参数
function queryString(key){
    return (document.location.search.match(new RegExp("(?:^\\?|&)"+key+"=(.*?)(?=&|$)"))||['',null])[1];
}

//产生指定范围的随机数
function randomNumb(minNumb,maxNumb){
	var rn=Math.round(Math.random()*(maxNumb-minNumb)+minNumb);
	return rn;
	}
	
var wHeight;
$(document).ready(function(){
	wHeight=$(window).height();
	if(wHeight<832){
		wHeight=832;
		}
	$('.pageMain').height(wHeight);
	
	//test
	$('.quBlock').css('top',(wHeight-566)/2+'px');
	
	//res
	$('.resPage1').css('top',(wHeight-566)/2+'px');
	
	//info
	$('.infoPage').css('top',(wHeight-610)/2+'px');
	
	
	//end
	$('.endPage1').css('top',(wHeight-661)/2+'px');
	$('.endPage2').css('top',(wHeight-661)/2+'px');
	$('.endPage3').css('top',(wHeight-661)/2+'px');
	});
	
function showPop(obj){
	$('.popBg').show();
	$('.pop').hide();
	$('.pop'+obj).show();
	}
	
function closePop(){
	$('.popBg').hide();
	$('.pop').hide();
	}
	