/**
 * Created by haseebriaz on 12/01/16.
 */
//var excelsheets = "";
var openworkbooks = null;
var wbwait = 0;
var wswait = 0;
var bondlistpushed = 0;
var marketviewlistpopulated = 0;
var marketViewBondIds = [];
var gotMarketViewBonds = 0;
var subscribedMarketViewBonds = 0;


function mehmetfunction(data){
	alert("Hello kerim");
}

$("#connecttoserver").click(function(){
	var line = [];
	line[0] = 0;
	line[1] = $("#username").val();
	line[3] = $("#password").val();
	line[2] = "LOGIN";
	processLine(line,0);
});

$("#activeworkbook").change(function(){
	var activeworkbook = $("#activeworkbook").val();
	$(".workbooklist").removeClass("activeworkbook");
	$("#"+activeworkbook).addClass("activeworkbook");
});

$("#currentworkbooks").change(function(){
	//alert("has changed");
	//$("#pagecontent").html("");
	//$("#pagecontent").html($("#currentworkbooks").val());
	var wbs = JSON.parse($("#currentworkbooks").val());
	
	$("#excelopenworkbooks").html("");
	
	for (var ik = 0; ik < wbs.length; ik++){
		$("#excelopenworkbooks").append("<li class='workbooklist' id='"+wbs[ik].name+"'>"+wbs[ik].name+"</li>");
		workbook = fin.desktop.Excel.getWorkbookByName(wbs[ik].name);
		workbook.addEventListener("workbookActivated",function(event){
			$("#activeworkbook").val(event.target.name).change();
		});
		workbook.addEventListener("workbookDeactivated",function(event){
			$("#activeworkbook").val("").change();
		});
	}
	
	if (wbs.length && ($("#activeworkbook").val() == "")){
		$("#activeworkbook").val();
		workbook = fin.desktop.Excel.getWorkbookByName(wbs[0].name);
		workbook.activate();
		$("#activeworkbook").val(wbs[0].name).change();
	}
});

fin.desktop.main(function(){
   
	var embondsWorkbookName = null;
	var embondsWorksheetName = null;
	var allWorkbooks = null;
	var allWorksheets = null;
	var activeWorkbook = null;
	var activeWorksheet = null;
	
    var Excel = fin.desktop.Excel;
	

    Excel.init();
	//console.log(Excel);
	//alert("excel initialized");
    Excel.getConnectionStatus(isExcelConnected);
    Excel.addEventListener("connected", onExcelConnected);
	Excel.addEventListener("workbookClosed", function(event){
		refreshWorkbookList();
	});
	Excel.addEventListener("workbookAdded", function(event){
		refreshWorkbookList();
		$("#activeworkbook").val(event.target.name).change();
	});

	Excel.addEventListener("afterCalculation", function(event){
		
	})

    function isExcelConnected(isConnected){
        if(!isConnected){
			$("#excelconnectionstatus").html("Excel Not Connected");
            Excel.addEventListener("connected", onExcelConnected);
        } else {
            onExcelConnected();
        }
    }

	function refreshWorkbookList(){
		Excel.getWorkbooks(function(workbooks){
			openworkbooks = workbooks;
			$("#currentworkbooks").val(JSON.stringify(workbooks)).change();
		});
	}

    function onExcelConnected(){
		$("#excelconnectionstatus").html("Connection Established");
		//excelheartbeat();
		runheartbeat();
		refreshWorkbookList();
    }

	function runheartbeat(){
		setInterval(function(){excelheartbeat();
								//console.log("heartbeating");
							},5000);
	}
	


	function excelheartbeat(){
		
			
		wbwait = 1;
		wswait = 0;
		
		var now = moment.utc();
		var numworkbooks = 0;
		//$("#excelsheets").html("");
		$("#lasthearbeat").html("");
		Excel.getCalculationMode(function(data){
			//console.log(data);
			//$("#excelconnectionstatus").append("<br>Calc "+data.calculationMode);
			if (data.calculationMode == "automatic") {
				$("#calculationMode").html("<a href='#'><span class='label success'>Auto</span></a>");
			} else {
				$("#calculationMode").html("<a href='#'><span class='label alert'>Man</span></a>");
			}
			
		});
		var rightnow = Number(moment(now).format('x'));
		//$("#lasthearbeat").append("<br>"+moment(now).format("x"));
		
		var tmpwb = null;
		excelsheets = "";
		Excel.getWorkbooks(function(workbooks){
			numworkbooks = workbooks.length;
			wbwait = 0;
			wswait = workbooks.length;
			for (var iwb = 0; iwb < numworkbooks; iwb++){
				var numworksheets = 0;
				workbooks[iwb].addEventListener("sheetAdded",function(event){
					excelheartbeat();
				});
				workbooks[iwb].addEventListener("sheetRemoved",function(event){
					excelheartbeat();
				});
				
				workbooks[iwb].getWorksheets(function(worksheets){
					wswait = wswait -1;
					numworksheets = worksheets.length;
					for (var iws = 0; iws < numworksheets; iws++){
						if (worksheets[iws].name === "EMBONDS"){
							worksheets[iws].setCellName(excelRefConvert(3,3),"Mehmet");
							worksheets[iws].getCellByName("Mehmet",function(data){
								$("#embondscell").html(data.value);
							});
							$("#embondspublishingworksheet").val(1);
							excelsheets = excelsheets + "<li>"+worksheets[iws].workbook.name+" <strong>"+worksheets[iws].name+"</strong></li>";
							$("#embondsworkbook").val(worksheets[iws].workbook.name);
							//console.log("EMbonds workbook is: " + $("#embondsworkbook").val());
						} else {
							excelsheets = excelsheets + "<li>"+worksheets[iws].workbook.name+" "+worksheets[iws].name+"</li>";
						}
					}
					if (wswait == 0){
						$("#excelsheets").html(excelsheets);
					}
				});
			}
			
			if (bondlistpushed == 0){
				if (bondList.length > 0) {
					var embondsworkbook = fin.desktop.Excel.getWorkbookByName($("#embondsworkbook").val());
					//console.log("embonds workbook object");
					//console.log(embondsworkbook);
					if (embondsworkbook) {
						var embondsworksheet = embondsworkbook.getWorksheetByName("EMBONDS");
						if (embondsworksheet){
							//console.log("embonds worksheet object");
							//console.log(embondsworksheet);
							for (var bl = 0; bl < bondList.length; bl++){

								var cellref = "BA"+(1+bl);
								//console.log("Setting cells *"+cellref+"*");
								embondsworksheet.setCells([[bondList[bl].bondname,bondList[bl].isin,bondList[bl].bondname]], cellref);
							}
							bondlistpushed = 1;
							embondsworksheet.formatRange("BA1",3,bondList.length,{ font: {color: "0,0,0,1", size: 12, name: "Courier New"},
							columnWidth: 3});
							embondsworksheet.setCellName("$BA:$BB","EmbondsBondListNameToIsin");
							embondsworksheet.setCellName("$BB:$BC","EmbondsBondListIsinToName");
							embondsworksheet.setCells([["ISIN","BondName","BidSize","BidPx","AskPx","AskSize","QuoteType","Pub"]],"A1");
							embondsworksheet.formatRange("A1",7,0,{interior: {color: "200,200,200,1"}, font: {bold: true}});
							for (var il = 2; il < 100; il++){
								//embondsworksheet.setCells([["=if(A"+il+"<>\"\",vlookup(A"+il+",EmbondsBondListIsinToName,2,false),\"\"),\"\")"]],"B"+il);
								//console.log("=if(A"+il+"<>\"\",vlookup(A"+il+",EmbondsBondListIsinToName,2,false),\"\"),\"\")");
								//embondsworksheet.setCells([["2"]],"C"+il);
								//embondsworksheet.setCells([["=E"+il+"+F"+il]],"D"+il);
								//embondsworksheet.setCells([["=if(isblank(G"+il+"),\"XX\",\"yy\")"]],"D"+il );
								
								embondsworksheet.setCells([['=IF(A'+il+'<>"",VLOOKUP(A'+il+',EmbondsBondListIsinToName,2,FALSE),"")']],"B"+il);
								
								//console.log("C"+il);
								//console.log("=E"+il+"+F"+il);
							}
							
						}
					}
				}
			}
			
			if (bondlistpushed == 1 && marketviewlistpopulated == 0){
				if (activeMarketViewId){
					var pgg = null;
					var bondid = null;
					pgg = "<table class='embx-table'><thead><tr><th>Name</th><th>ISIN</th><th>Bid Sz</th><th>Bid Px</th><th>Ask Px</th><th>Ask Sz</th></tr></thead><tbody>";
					for (var i = 0; i < marketViews[activeMarketViewId].bonds.length; i++){
						bondid = marketViews[activeMarketViewId].bonds[i].id;
						marketViewBondIds[i] = bondid;
						pgg +="<tr id='bondrow"+bondid+"'><td id='bondname"+bondid+"'>"+findBond(bondid)[0]+"</td><td  id='isin"+bondid+"'>"+findBond(bondid)[1]+"</td><td style='text-align: right;'  id='bidsize"+bondid+"'></td><td  style='text-align: right;' id='bidprice"+bondid+"'></td><td  style='text-align: right;'  id='askprice"+bondid+"'></td><td  style='text-align: right;'  id='asksize"+bondid+"'></td></tr>";
					}
					marketviewlistpopulated = 1;
					pgg+="</tbody></table>";
					$("#pagecontent").append(pgg);
				}
			}
			
			if (marketviewlistpopulated && !gotMarketViewBonds){
				getMarketViewBonds(activeUser.lsLogin);
				subscribeMarketViewBonds(activeUser.lsLogin);
				gotMarketViewBonds = 1;
				subscribedMarketViewBonds = 1;
				//getMarketViewList(marketViewBondIds);
				//subscribeMarketViewList(activeUser.lsLogin,marketViewBondIds);
				
			}
			
			$("#heartbeatsign").animate({opacity: 1},500).delay(300).animate({opacity: 0.5},500);
		});
	}   // function excelheartbeat
	
});  // fin.desktop.excel

var excelCols = []
excelCols[1] = "A";
excelCols[2] = "B";
excelCols[3] = "C";
excelCols[4] = "D";
excelCols[5] = "E";
excelCols[6] = "F";
excelCols[7] = "G";
excelCols[8] = "H";
excelCols[9] = "I";
excelCols[10] = "J";
excelCols[11] = "K";
excelCols[12] = "L";
excelCols[13] = "M";
excelCols[14] = "N";
excelCols[15] = "O";
excelCols[16] = "P";
excelCols[17] = "Q";
excelCols[18] = "R";
excelCols[19] = "S";
excelCols[20] = "T";
excelCols[21] = "U";
excelCols[22] = "V";
excelCols[23] = "W";
excelCols[24] = "X";
excelCols[25] = "Y";
excelCols[26] = "Z";


function excelRefConvert(row,column){
	var colmult = Math.floor(column/26);
	if (colmult == 0) { 
		return excelCols[column]+row;
	} else {
		return excelCols[colmult]+excelCols[column - colmult*26]+row;
	}
}