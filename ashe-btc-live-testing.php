<? php

global $minEntrySpread = 10;
global $minDeltaSpread = 5;


#PART 1 - Data analysis

#fetches the current BID price at MtGox
function Get_Gox_Price(){
    $goxPrice = [insert API call]
    return $goxPrice;
}

#fetches the current BID price at BitStamp
function Get_Stamp_Price(){
$   stampPrice = [insert API call]
    return $stampPrice;
}

#returns the spread b/t the exchanges.
function Calc_Current_Spread(){
    $dS = Get_Gox_Price()- Get_Stamp_Price();
    if ($dS < $minSpread) {
        return;
    }
   return $dS;
}

#compares spreads and will recommend a trade
function Compare_Spreads(){
    while(1){
        if (Calc_Current_Spread() >= $minEntrySpread) {
            echo "Recommended Trade";
        }
    }
}

#PART 2 - Initial order execution

function Open_Trade(){

Place_Gox_Sell();
Place_Stamp_Buy();
Record_Entry_Details();

}


function Place_Gox_Sell(){

    [insert Gox API call to sell]
}

function Place_Stamp_Buy(){

    [insert Stamp API call to buy]
}














?>
