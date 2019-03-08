/**
  * Changes elements when window size is smaller than standard desktop 
  * Author: Allen Rankin
  * Ladies Day
  */

$(document).ready(function(){
  //Sticks the Billing Information if the page is a relatively good height and width to do so
  var onTop = $("#billingInfo").offset().top;
  var mobile = false;
  var noSnap = false;
  
  //Checks the height and width on page load
  $(document).ready(function(){
    buttonValue($(window).width());
    if($(window).width() < 640){
      mobile = true;
    }else {
      mobile = false;
    }
    if($(window).height() < 740){
      noSnap = true;
    } else {
      noSnap = false;
    }
    if(mobile=== false && noSnap === false){
      stickyBilling();
    }
  });
  
  /** 
    * Determines if device is mobile or is high enough for billing to stick by window size, dynamically
	*/
  $(window).resize(function(){
    buttonValue($(window).width());//change copy button value depending on the size of the window
    if($(window).width() < 640){
      mobile = true;
    }
    if($(window).height() < 740){
      noSnap = true;
    }
    else if($(window).width() > 640 && $(window).height() > 740){
      mobile = false;
      noSnap = false;
    }
   
    stickyBilling();
  }).scroll(function(){ //when the page scrolls, calls stickyBilling()
    stickyBilling();
  });
  
  /**
	* Billing info section will stick when the page needs to scroll
	* @return void
	*/
  function stickyBilling(){
    var currentScroll = $(window).scrollTop();
    if(currentScroll >= onTop && mobile === false && noSnap === false){
      $("#billingInfo").css({"position":"fixed", "top":"0", "left":"50%", "width":"auto", "max-width":"40%"});
    } else {
      $("#billingInfo").css({"position":"static", "width":"", "max-width":""});
    }
  }  
  
  /**
	* Changes copy button text when page is resized
	* @params width - width of page
	* @return void
	*/
  function buttonValue(width){
    if(width < 850){
      //change the button text from Copy Billing Info from Registrant to Copy from Billing
      $("#copyInfoButton").attr("value","Copy to Billing");
    }
    if (width >= 850){
      $("#copyInfoButton").attr("value","Copy Billing Info from Registrant");
    }
  }
});