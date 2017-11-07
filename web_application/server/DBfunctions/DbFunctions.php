<?php
    require("../../models/DB/Db.class.php");
    $db = new Db();


    //echo "Db function";

    //for debug
    function d($v,$t)
        {
            echo '<pre>';
            echo '<h1>' . $t. '</h1>';
            var_dump($v);
            echo '</pre>';
        }


    function getBranchList(){
        global $db;
        $branch = $db->query("SELECT * FROM branch");
        return $branch;
    }


    function getUserTypeList(){
        global $db;
        $branch = $db->query("SELECT * FROM user_type");
        return $branch;
    }



    function getProductList(){
        global $db;
        $product = $db->query("SELECT * FROM product");
        return json_encode($product);
    }
    function getProductInfo(){
        global $db;
        $productlist = $db->query("SELECT * FROM product");
        $data_array = array();
        foreach ($productlist as $product ){
            $data_array[$product["P_ID"]]['P_Commission'] = $product["P_Commission"];
            $data_array[$product["P_ID"]]['P_Selling_Price'] = $product["P_Selling_Price"];
            $data_array[$product["P_ID"]]['P_Cost'] = $product["P_Cost"];
            $data_array[$product["P_ID"]]['P_Name'] = $product["P_Name"];
        }
        //return floatval($data_array['6']['Commision']);
        return $data_array;

    }
//------------ get stock of the given branch ------------//
    function getBranchStock($branch){
        global $db;
        $stock_array = array();
        $query = "SELECT * FROM stock WHERE B_ID = '$branch'";
        $productQuantity = $db->query($query);
        foreach ($productQuantity as $pq){
            $stock_array[$pq['P_ID']] = $pq['Quantity'];
        }
        return $stock_array;
    }
//------------ Enter data for the load_sheet table, morning stock of the ref ------------//
    function loadSheetData($refID,$pID,$pQuantity,$date){
        global $db;
        $query = "INSERT INTO load_sheet (U_ID,P_ID,`Timestamp`,Quantity) VALUES('$refID','$pID','$date','$pQuantity') ON DUPLICATE KEY UPDATE Quantity = Quantity + $pQuantity";
        return $db->query($query);
    }

    function vehicleEnter($refID,$date,$vehicle){
        global $db;
        $query = "INSERT IGNORE INTO sale_vehicle (V_Number,`Timestamp`,Ref_ID) VALUES('$vehicle','$date','$refID')";
        return $db->query($query);
    }
//------------ reduce branch stock when morning load ------------//
    function reduceBranchStock($branchID,$pID,$pQuantity,$userID){
        global $db;
        $query1 = "UPDATE stock SET Quantity = Quantity - '$pQuantity' WHERE B_ID='$branchID' AND P_ID = '$pID'";

        //Stock Transaction Log
        $query2 = $db->query("INSERT INTO `stock_transaction` (`P_ID`, `B_ID`, `Quantity`, `Comment`, `User_ID`) VALUES (:P_ID,:B_ID,:Quantity,:Comment,:User_ID)",array("P_ID" => $pID,"B_ID" => $branchID,"Quantity" => -$pQuantity,"Comment" => 'loading',"User_ID" => $userID));


        $db->query($query1);
    }
//------------ add the remaining stock evening ------------//
    function addBranchStock($branchID,$pID,$pQuantity,$userID){
        global $db;
        $query1 = "UPDATE stock SET Quantity = Quantity + '$pQuantity' WHERE B_ID='$branchID' AND P_ID = '$pID'";

        //Stock Transaction Log
        $query2 = $db->query("INSERT INTO `stock_transaction` (`P_ID`, `B_ID`, `Quantity`, `Comment`, `User_ID`) VALUES (:P_ID,:B_ID,:Quantity,:Comment,:User_ID)",array("P_ID" => $pID,"B_ID" => $branchID,"Quantity" => $pQuantity,"Comment" => 'Unloading',"User_ID" => $userID));

        $db->query($query1);
    }
//------------ enter data to sales sheet at the evening ------------//
    function salesSheetData($refID,$pID,$pQuantity,$branchID,$date){
        global $db;
        $pvalue = $db->query("SELECT P_Selling_Price FROM stock WHERE P_ID = '$pID' AND B_ID = '$branchID'");

        $pfinalVal = $pvalue[0]['P_Selling_Price'] * $pQuantity;

        $pCommission = $db->query("SELECT P_Commission FROM stock WHERE P_ID = '$pID' AND B_ID = '$branchID'");

        $totalCommission = $pCommission[0]['P_Commission'] * $pQuantity;

        $query = "INSERT INTO sale (Ref_ID,	P_ID,`Timestamp`,Quantity,`Value`,	Total_Commission) VALUES('$refID','$pID','$date','$pQuantity','$pfinalVal','$totalCommission')";

        $olddate = strtotime($date);
        $newDate = date("Y-m-01", $olddate);

        $db->query("INSERT INTO `salary` (U_ID, Timestamp, Commission) VALUES ('$refID', '$newDate', '$totalCommission') ON DUPLICATE KEY UPDATE Commission = Commission + '$totalCommission'");


        //for unregistered alert
         $db->query("INSERT INTO `unregistered_alert` (`Ref_ID`, `P_ID`, `Quantity`) VALUES ('$refID', '$pID','$pQuantity') ON DUPLICATE KEY UPDATE  `Quantity` =  `Quantity` + '$pQuantity' ");



        return $db->query($query);
        //return $pfinalVal;

    }
//------------ add data to missing datable  ------------//
    function missingItem($refID,$pID,$pQuantity,$branchID,$date){
        global $db;
        $pvalue = $db->query("SELECT P_Selling_Price FROM stock WHERE P_ID = '$pID' AND B_ID = '$branchID'");

        $pfinalVal = $pvalue[0]['P_Selling_Price'] * $pQuantity;

        $query = "INSERT INTO missing_item (U_ID,P_ID,`Timestamp`,Quantity,`Value`) VALUES('$refID','$pID','$date','$pQuantity','$pfinalVal')";

        $olddate = strtotime($date);
        $newDate = date("Y-m-01", $olddate);

        $db->query("INSERT INTO `salary` (U_ID, Timestamp, Missing_Item_Deduction) VALUES ('$refID', '$newDate', '$pfinalVal') ON DUPLICATE KEY UPDATE Missing_Item_Deduction = Missing_Item_Deduction + '$pfinalVal'");

        $db->query($query);
    }
//------------ get the loaded stock of the ref ------------//
    function getRefLoadStock($refID,$date){
        global $db;
        $query = "SELECT `load_sheet`.`P_ID`,SUM(`load_sheet`.`Quantity`) AS Quantity FROM load_sheet WHERE U_ID = '$refID'AND DATE_FORMAT(`load_sheet`.`Timestamp`,'%Y-%m-%d') = '$date' GROUP BY P_ID";

        $product = $db->query($query);
        $productList = array();
        $i = 0;
        foreach($product as $p){
            $productList[$p['P_ID']] = $p['Quantity'];
            $i++;
        }

         return $productList;
    }
//------------ add ref in the morning ------------//
    function salesChecking($refID,$date){
        global $db;
        $query = "INSERT IGNORE INTO temp_ref_load (Ref_ID,Load_Date) VALUES('$refID','$date')";
        $db->query($query);
    }
//------------ Confirming sales has added in the evening ------------//
    function salesCheckout($refID,$date){
        global $db;
        $query = "DELETE FROM temp_ref_load WHERE Ref_ID = '$refID' AND Load_Date='$date'";
        $db->query($query);
    }
//------------ check the ref in temp_ref_load table ------------//
    function salesRefCheck($refID,$date){
        global $db;
        $query ="SELECT * FROM temp_ref_load WHERE Ref_ID = '$refID' AND Load_Date='$date'";
        $db->query($query);
        $count = $db->column($query);
        return $count[0];
    }


    function getCustomerID($phoneNo){
        global $db;
        $branch = $db->query("SELECT C_ID FROM customer WHERE C_Telephone = :Pno",array("Pno" =>$phoneNo ));
        return $branch[0]["C_ID"];
    }


    function cardIdCheck($cardId){
        global $db;
        $card = $db->query("SELECT COUNT(`Cd_ID`) FROM `card` WHERE `Cd_ID` = :cid",array("cid" =>$cardId ));
        return $card[0]["COUNT(`Cd_ID`)"];

    }

//------------ get customer cards ------------//
    function customerCard($customerID){
        global $db;
        $query = "SELECT * FROM card WHERE C_ID = '$customerID'";
        $cardlist = $db->query($query);
        return json_encode($cardlist);
    }

// get main stock quntity of given product //
    function getProductCount($productID){
        global $db;
        $product = $db->query("SELECT `Quantity` FROM `stock` WHERE `B_ID` = 'main' AND `P_ID` = :pid",array("pid" =>$productID ));
        if (!empty($product)){
            return $product[0]["Quantity"];
        }else{
            return 0;
        }

    }


// get stock quntity of given product of givien branch //
function getProductCountStock($productID,$branchID){
    global $db;
    $product = $db->query("SELECT `Quantity` FROM `stock` WHERE `B_ID` = :bid AND `P_ID` = :pid",array("bid" => $branchID,"pid" =>$productID ));
    if (!empty($product)){
        return $product[0]["Quantity"];
    }else{
        return 0;
    }

}

// get default selling price and commission  of given product //
function getDefaultInfo($productID){
    global $db;
    $product = $db->query("SELECT `P_Selling_Price`,`P_Commission` FROM `product` WHERE `P_ID` = :pid",array("pid" =>$productID ));
    return $product[0];
}


function getCommissionProductBranch($branchID,$ProductID){
    global $db;
    $pCommission = $db->query("SELECT P_Commission FROM stock WHERE P_ID = :pid AND B_ID = :bid ",array("pid" =>$ProductID ,"bid" =>$branchID  ));
    return $pCommission[0]['P_Commission'];


}

function getCollectorSummary($cID,$date1,$date2){
    global $db;
    $query = "SELECT * FROM collection_info WHERE U_ID = '$cID' AND DATE_FORMAT(`Timestamp`,'%Y-%m-%d') BETWEEN '$date1' AND '$date2'";
    $collection = $db->query($query);
    return $collection;
}

function getCollectorSummarySum($cID,$date1,$date2){
    global $db;
    $query = "SELECT SUM(collection_info.Total_Collection) AS TCollection, SUM(collection_info.No_Of_Card) AS NCards,SUM(collection_info.Commission) AS TCommission FROM collection_info WHERE U_ID = '$cID' AND DATE_FORMAT(`Timestamp`,'%Y-%m-%d') BETWEEN '$date1' AND '$date2'";
    $collection = $db->query($query);
    return $collection;
}

function getCollectorName($cID){
    global $db;
    $name = $db->query("SELECT U_Name FROM user WHERE U_ID = '$cID'");
    return $name;
}

//get sales Sum

function getSalesSum($cID,$date1,$date2){
    global $db;
    $query = "SELECT SUM(sale.Quantity) AS tQuantity,SUM(sale.Total_Commission) AS tCommission FROM sale WHERE Ref_ID = '$cID' AND DATE_FORMAT(`Timestamp`,'%Y-%m-%d') BETWEEN '$date1' AND '$date2'";
    $salesSum = $db->query($query);
    return $salesSum;

}


// function checkHasSale(){
//     global $db;
//     $query =  "SELECT `Sale_ID` FROM `sale` WHERE `Ref_ID` = 'cs' AND `P_ID` = 'G --35' AND `Timestamp` = '2016-03-01 00:00:00'";
//     $result = $db->query($query);
//     return empty($result);
// }



//
//function test(){
//    global $db;
//    $refList = $db->query("SELECT `U_ID` FROM `user` WHERE `Type` = 'Sales Representative'");
//    return $refList;
//}

//d(getSalesSum('S001','2016-02-01','2016-02-23'),"test");

//d(salesSheetData('S001','aplleLAp','2','aa','2016-02-15'),"sales");

    //var_dump(getBranchList());

    //echo getProductList();

   // d(getCustomerID('075213213'),"getCustomerID");

//d(cardIdCheck('S0021601/22332'),"cardIdCheck");



//d(getDefaultInfo('apple'),"getProductCount");

//d(getCommissionProductBranch('aa','aplleLAp'),"getCommissionProductBranch");

//d(test(),"test");
//
//$temp = test();
//
//foreach($temp as $aa){
//    var_dump($aa['U_ID']);
//}


// d(checkHasSale(),"checkHasSale");
