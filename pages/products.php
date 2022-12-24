<?php
// init PHP
require_once "../lib.php"; ?>
<!DOCTYPE html>
<html lang='en'>

<head>
    <meta charset='UTF-8'>
    <meta http-equiv='X-UA-Compatible' content='IE=edge'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Account</title>
    <link rel='stylesheet' href='/Nova-Auction/css/styles.css'>
    <link rel='stylesheet' href='/Nova-Auction/css/products.css'>
    <link rel='stylesheet' href='https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css'>
</head>

<body>
    <?php printNav(); ?>

    <div class='main'>
        <div class='search-options'>
            <form class='search-form' method='get'>
                <select  name='city' id='city'>
                    <option  value="" disabled selected>City</option>
                    <?php 
                    $res = Database("select concat(upper(substring(city_name,1,1)),lower(substring(city_name,2))) from city",1);
                        foreach($res as $row){
                            print("<option value='$row[0]'>$row[0]</option>");
                        }
                    
                    ?>
                </select>

                <select onchange='getSelected()' name='car_mekes' id='car-mekes'  >
                        <option value="" disabled selected>Car makes</option>
                        <?php 
                        $res = Database("select upper(makes_name) from car_info group by makes_name",1);
                            foreach($res as $row){
                                print("<option value='$row[0]'>$row[0]</option>");
                            }
                        
                        ?>
                </select>
                
                <select name='car-model' id='model' disabled  >
                        <option value="" disabled selected>Model</option>
                </select>
                <div class="group-info">
                <input type="number" min="0" name='price-from' placeholder='Price from' >
                <input type="number" min="0" name='price-to' placeholder='Price to' >
                </div>
                <div class="group-info">
                <input type="number" min="1900" max="2023" step="1" name='year-from' placeholder='Year from' >
                <input type="number" min="1900" max="2023" step="1" name='year-to' placeholder='Year to' >
                </div>
                <div class="group-info">
                <select name='sort' id='sort'>
                            <option value='' disabled selected>Sort by</option>
                            <option value='name asc'>A-Z</option>
                            <option value='name desc'>Z-A</option>
                            <option value='price desc'>High>Low</option>
                            <option value='price asc'>Low>High</option>
                        </select>
                <button class='button' name="search" value="search">Search</button>
                </div>
            </form>
        </div>

        <?php 
        // select name, price, img_path ,id from items 
        // where lower(city_name) LIKE '%%' 
        // and car_id IN 
        //     (select id from cars 
        //     where lower(makes_name) like '%%' 
        //     and lower(model_name) like '%%' 
        //     and year_of_make BETWEEN 0 and 99000099) 
        // and price BETWEEN 0 and 9999999; 
                
                $NOIPP = 6;  /*Number Of Items Per Page*/
                if(!isset($_GET["page"]))
                    $_GET["page"]=1;
                
                (isset($_GET["city"])) ? null : $_GET["city"]="";
                (isset($_GET["car_mekes"])) ? null : $_GET["car_mekes"]="";
                (isset($_GET["car-model"])) ? null : $_GET["car-model"]="";
                (isset($_GET["year-from"]))  ? ($_GET["year-from"]=="")? $_GET["year-from"]=Database("select min(year_of_make) from cars",1)[0][0] :null :$_GET["year-from"]=Database("select min(year_of_make) from cars",1)[0][0];
                (isset($_GET["year-to"])) ? ($_GET["year-to"]=="")? $_GET["year-to"]=Database("select max(year_of_make) from cars",1)[0][0] :null :$_GET["year-to"]=Database("select max(year_of_make) from cars",1)[0][0];
                (isset($_GET["price-from"])) ? ($_GET["price-from"]=="")? $_GET["price-from"]=Database("select min(price) from items",1)[0][0]:null: $_GET["price-from"]=Database("select min(price) from items",1)[0][0];
                (isset($_GET["price-to"])) ? ($_GET["price-to"]=="")?  $_GET["price-to"]=Database("select max(price) from items",1)[0][0] :null :$_GET["price-to"]=Database("select max(price) from items",1)[0][0];
                (isset($_GET["sort"])) ? null:$_GET["sort"]="name asc";
                
                $res = Database("select name, price, img_path ,id from items 
                where lower(city_name) LIKE '%{$_GET["city"]}%'
                and car_id IN
                (select id from cars
                where lower(makes_name) like '%{$_GET["car_mekes"]}%'
                and lower(model_name) like '%{$_GET["car-model"]}%'
                and year_of_make BETWEEN {$_GET["year-from"]} and {$_GET["year-to"]})
                and price BETWEEN {$_GET["price-from"]} and {$_GET["price-to"]} 
                order by {$_GET["sort"]}" , 1);

               if(count($res)>0){
                    if($_GET["page"]*$NOIPP < count($res)){
                        print(
                            "<div class='search-details'>
                                <p>Showing ".(($_GET["page"]*$NOIPP)-($NOIPP)+1)."-".($_GET["page"]*$NOIPP)." of ".count($res)." results</p>   
                            </div>"
                        );
                    }
                    else{
                        print(
                            "<div class='search-details'>
                                <p>Showing ".(($_GET["page"]*$NOIPP)-($NOIPP)+1)."-".count($res)." of ".count($res)." results</p>   
                            </div>"
                        );
                    }
                }elseif(count($res)==0){
                    print(
                        "<div class='search-details'>
                            <p>There is no results</p>   
                        </div>"
                    );
                }


                echo "<div class='cards-grid'>";
                for($i = $_GET["page"]*$NOIPP-$NOIPP; $i < count($res); $i++) {
                    if($i>$_GET["page"]*$NOIPP-1)break;
                    $name = $res[$i][0];
                    $price = $res[$i][1];
                    $img_p = "../".$res[$i][2];
                    $item_id = $res[$i][3];

                    print("
                        <div class='card'>
                            <img src='$img_p' alt=''>
                            <span style='font-size:25px ;'> $name</span>
                            <br>
                            <span>Price: <bold>$price$</bold></span>
                            <br>
                            <a href='/Nova-Auction/pages/item.php?item_id=$item_id' ><button class='button b_card' >View</button></a>
                        </div>
                    ");

                    }
                    
                    
                    
        echo "</div>";
        echo "<div class='page-counter'>";
        for($i =1;$i<count($res)/$NOIPP+1;++$i){
            $_GET["page"]+=1;
            
            print("<a href='/Nova-Auction/pages/products.php?city={$_GET["city"]}&car_mekes={$_GET["car_mekes"]}&car-model={$_GET["car-model"]}&price-from={$_GET["price-from"]}&price-to={$_GET["price-to"]}&year-from={$_GET["year-from"]}&year-to={$_GET["year-to"]}&sort={$_GET["sort"]}&page=$i'><button class='button' >$i</button></a>");
            
        }

        echo "</div>";
        ?>
    </div>
    <footer class='footer'>
        <p>Copyright © 2022 Nova Auction | Design By Humble Ghost Team</p>
    </footer>
</body>
<script>
var CarArr = <?php
echo json_encode(Database("select upper(makes_name) , upper(model_name) from car_info order by model_name asc",1,MYSQLI_NUM));
?>;


function getSelected(){
    var seleted = document.getElementById('car-mekes').value; 
    var model = document.getElementById('model');
    console.log(seleted);
    if(seleted == '0'){
        model.disabled = true;
        return;
    }
    
    
    while (model.lastChild) {
        if(model.lastChild.value == 0)
            break;
        model.removeChild(model.lastChild);
    }

    for(var i = 0; i<CarArr.length;++i){
        if(CarArr[i][0]==seleted)
        {
            var node = document.createElement("option");
            node.value = CarArr[i][1];
            node.innerHTML = CarArr[i][1];
            model.appendChild(node);
        }
    }
   model.disabled = false;
}




</script>
</html>