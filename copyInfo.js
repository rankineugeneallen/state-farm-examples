/**
  *	Changed fields will be highlighted when CopyInfo button is clicked
  * Author: Allen Rankin
  *	Ladies Day
  */
  
$(document).ready(function(){
  var counter = 1;
  var origBackgroundcolor = $("#FNAME1").css("background-color");
  var origVal;
  var interval;
  //stacks
  var stackReg = [];
  var stackBill = [];
  //arrays
  var badArray = [];
  var arrayOfReg = ["#FNAME1", "#LNAME1", "#EMAIL1", "#ADDR1", 
					"#ADDRL2", "#CITY1", "#ST1", "#ZIP1"];
  var arrayOfBill = ["#CHFNAME", "#CHLNAME", "#CONFEMAIL", "#CHADDR1", 
					 "#CHADDR2", "#CHCITY", "#CHST", "#CHZIP"];
  
  /**
    * Hovering on button shows fields with differing info
	* between both Billing and Registration
    */
  $("#copyInfoButton").hover(function(){
    checkFieldValues();
  }).click(function(){
    animateChanged();
  }).mouseleave(function(){
    animateOut(badArray);
  }); //when mouse moves away from copy button
  
  /**
    * Builds both stacks of each field that we will copy
	* @return void
	*/
  function buildStacks(){
    var b = 0;
    for(b = 0; b < arrayOfReg.length; b++){
      stackReg.push(arrayOfReg[b]);
      stackBill.push(arrayOfBill[b]);
    }
  }
  
  /**
	* Compares data between reg and billing, adds differing data to badArray
	* @return void
    */
  function checkFieldValues(){
    //build the stacks
    buildStacks();
    badArray = [];//empties the badArray
    //stack now built, remember it's added backwards
    
    for(b = 0; b < arrayOfReg.length; b++){
      //pop top value and save to reg and bill from each stack
      var reg = stackReg.pop();
      var bill = stackBill.pop();
      if($(reg).val() != $(bill).val()){ //if not empty, and they aren't =, add to bad
        badArray.push(reg);
      }else if ($(reg).val() == ""){
        badArray.push(reg);
      }else if($(reg).val() == $(bill).val()){
        continue;
      }else if (stackReg.length == 0){//if end of stack, exit
        break;
      }
    }
    //animate the variables in the badArray array
    animateIn(badArray);
  }
  
  /**
	* Turns fields in given array orange
	* @params arrayIn - array of fields to highlight
	* @return void
	*/
  function animateIn(arrayIn){
    var v = 0;
    for(v = 0; v < arrayIn.length; v++){
      $(arrayIn[v]).animate({
        backgroundColor: '#ffcc99'
      }, 60).clearQueue();
    }
  }
  
  /**
	* Returns field background color to original color
	* @params arrayOut - array of fields with color to be changed
	*/
  function animateOut(arrayOut){
    var q=0;
    for(q = 0; q < arrayOut.length; q++){
      $(arrayOut[q]).animate({
        backgroundColor: origBackgroundcolor
      },120).clearQueue();
    }
  }
  
  /**
	* Animate fields in badArray (also fields with differing info)
	* @return void
	*/
  function animateChanged(){
    var copiedData = [];
    var c = 0;
    var clear;
    for(c = 0; c < badArray.length; c++){//going through every value in the badArray
      if(badArray[c] == "#FNAME1"){
        copiedData[c] = "#CHFNAME";
      }else if(badArray[c] == "#LNAME1"){
        copiedData[c] = "#CHLNAME";
      }else if(badArray[c] == "#EMAIL1"){
        copiedData[c] = "#CONFEMAIL";
      }else if(badArray[c] == "#ADDR1"){
        copiedData[c] = "#CHADDR1";
      }else if(badArray[c] == "#ADDRL2"){
        copiedData[c] = "#CHADDR2";
      }else if(badArray[c] == "#CITY1"){
        copiedData[c] = "#CHCITY";
      }else if(badArray[c] == "#ST1"){
        copiedData[c] = "#CHST";
      }else if(badArray[c] == "#ZIP1"){
        copiedData[c] = "#CHZIP";
      }
    }
    
    for(c = 0; c < copiedData.length; c++){
      console.log(copiedData[c]);
    }
    
	//Sends to functions to animate
    animateIn(copiedData);
    animateOut(badArray);
    interval = setTimeout(function(){
     animateOut(copiedData);
      clear = stop();
    },700);
    
    function stop(){
      clearTimeout(interval);
    }
    //clear badArray
    badArray = [];
  }
  
});