<?php
require_once "Game.php";

define("CREATE_GAME_TABLE","create table games(id int primary key auto_increment not null,
player1_name varchar(100),
player2_name varchar(100),
player1_pos int,
player2_pos int,
turn tinyint,
blackholes varchar(55),
wormholes varchar(55),
winner varchar(100),
number_of_moves int default 0)");

define("CREATE_LEADERBOARD_TABLE","create table leaderboard(id int primary key auto_increment,
player_name varchar(100),
win int default 0,
lose int default 0,
shortest_win int)");

define("ADD_GAME","insert into games(player1_name,player2_name,player1_pos,player2_pos,turn,blackholes,wormholes,winner) 
values('%s','%s',%d,%d,%d,'%s','%s','%s')");


define("UPDATE_GAME_DATA","update games set %s='%s' where id=%d");

define("UPDATE_TURN","update games set turn=%d where id=%d");

define("INCREMENT_MOVE_NUMBER",'update games set number_of_moves=number_of_moves+1 where id=%d');

define("RESET_MOVE_NUMBER", "update games set number_of_moves=0 where id=%d");

define("UPDATE_PLAYER_POS","update games set player%d_pos=%d where id=%d");

define("FETCH_GAME","SELECT id from games where player1_name='%s'AND player2_name='%s' and winner=''");

define("FETCH_GAME_DATA","select * from games where id = %d");

define('SELECT_DATA_FROM_GAME','select %s from games where id=%d');

define("ADD_PLAYER","insert into leaderboard(player_name) values('%s')");

define("INCREMENT_PLAYER_WIN","update leaderboard set win=win+1 where player_name='%s'");

define("FETCH_LEADERBOARD","SELECT * FROM leaderboard ORDER BY win>lose DESC, shortest_win asc");

define("INCREMENT_PLAYER_LOSS","update leaderboard set lose=lose+1 where player_name='%s'");

define("UPDATE_SHORTEST_WIN","update leaderboard set shortest_win=%d where player_name='%s'");

define("FETCH_SHORTEST_WIN","select leaderboard.player_name,min(games.number_of_moves) as moves from leaderboard 
left JOIN games on games.winner=leaderboard.player_name where leaderboard.player_name='%s'");

//Database connectivity
 function openConnection(){
     $dbhost = "localhost";
     $dbuser = "root";
     $dbpass = "";
    $db = "snake_ladder";
    
     $conn = new mysqli($dbhost, $dbuser, $dbpass,$db) or die("Connect failed: %s\n". $conn -> error);
    if($conn){
       try
       {
           $conn->query(CREATE_GAME_TABLE);
           $conn->query(CREATE_LEADERBOARD_TABLE);
           //echo "Game table created successfully";
       }
       catch(exception $err){
          
       }
       
        return $conn;
    }
    else{
        echo "Failed to connect to the database";
    }
     return $conn;
 }

function CloseCon($conn)
{
    $conn -> close();
}


function parseHolesToString($arr){
     $str = "";
     $keys = array_keys($arr);
     $length=count($keys);
     for ($i=0; $i< $length; $i++){
         $str = $str.$keys[$i].":".$arr[$keys[$i]];
         if($i<$length-1)
             $str = $str.",";
         else
             break;
     }
     //echo $str;
     return $str;
}
function addGame($game,$conn){
     $player1 = mysqli_real_escape_string($conn,$game->getPlayer1());
     $player2 = mysqli_real_escape_string($conn,$game->getPlayer2());
     $player1Pos = mysqli_real_escape_string($conn,$game->getPlayerOneLocation());
     $player2Pos = mysqli_real_escape_string($conn,$game->getPlayerTwoLocation());
    $turn = $game->getTurn();
    $blackholes = mysqli_real_escape_string($conn,parseHolesToString($game->getBlackHoles()));
     $wormholes = mysqli_real_escape_string($conn,parseHolesToString($game->getWormHoles()));
     $winner = mysqli_real_escape_string($conn,$game->getWinner());

    $query = sprintf(ADD_GAME,$player1,$player2,$player1Pos,$player2Pos,$turn,$blackholes,$wormholes,$winner);

     if ($conn->query($query) === TRUE) {
      //  echo "New record created successfully";
        $game->setId(fetchGame($game,$conn));
    } else {
        echo "Error: " . $query . "<br>" . $conn->error;
    }



   // $conn->close();
}
function updateGame($what,$to,$where,$conn){

    $query = sprintf(UPDATE_GAME_DATA,$what,$to,$where);
    $conn->query($query);
}

function updatePlayerPosition($player,$to,$at,$conn){
     $query = sprintf(UPDATE_PLAYER_POS,$player,$to,$at);
     $conn->query($query);
}

function updateTurn($to, $at,$conn){
    $query = sprintf(UPDATE_TURN,$to,$at,$at);
    $conn->query($query);
    updateNumberOfMoves($at,$conn);
}

function updateNumberOfMoves($at,$conn,$reset=false){
    $operation = ($reset) ? RESET_MOVE_NUMBER:INCREMENT_MOVE_NUMBER;
    $query = sprintf($operation,$at);
    $conn->query($query);
}

function fetchGameWithId($id,$conn){
     $query = sprintf(FETCH_GAME_DATA,$id);
     $result = $conn->query($query);
     $game = null;
    if($result->num_rows >0){
        while ($row = $result->fetch_assoc()){
            $id = $row['id'];
            $game = new Game($row['player1_name'],$row['player2_name']);
            $game->setId($row['id']);
            $game->setBlackHoles($row['blackholes']);
            $game->setWormHoles($row['wormholes']);
            $game->setWinner($row['winner']);
            $game->setTurn($row['turn']);
            $game->setPlayerOneLocation($row['player1_pos']);
            $game->setPlayerTwoLocation($row['player2_pos']);
          // print_r($game);
           return array('game'=>$game,'id'=>$id);
          
        }
    }
}

function fetchGame($game,$conn){
     $p1 = $game->getPlayer1();
     $p2 = $game->getPlayer2();
    $query = sprintf(FETCH_GAME,$p1,$p2);
    $result = $conn->query($query);
    if($result->num_rows >0){
        while ($row = $result->fetch_assoc()){
            return $row['id'];
        }
    }
     return 0;
}

function fetchData($fetchWhat,$gameId,$conn){
    $query = sprintf(SELECT_DATA_FROM_GAME,$fetchWhat,$gameId);
    $result = $conn->query($query);
    if($result->num_rows >0){
        while ($row = $result->fetch_assoc()){
            return $row[$fetchWhat];
        }
    }
     return 0;
}


function updateData($changeWhat,$to,$gameId,$conn){

    $query = sprintf(SELECT_DATA_FROM_GAME,$changeWhat,$to,$gameId);
    $result = $conn->query($query);
   
}



function parseResponse(...$args){
    $arr = [];
    $arr += $args;
    return json_encode($arr);
}

function addPlayer($name,$winner,$conn){
    $query = sprintf("select id from leaderboard where player_name='%s'",$name);
   $result = $conn->query($query);
   if($result->num_rows <1){
     $query = sprintf(ADD_PLAYER,$name);
    $conn->query($query);  
   }
   if($winner){
    incrementPlayerWin($name,$conn);
    }
    else{
    incrementPlayerLost($name,$conn);
    }
   UpdateShortestWin($name,$conn);
   

}

function incrementPlayerWin($name,$conn){
    $query = sprintf(INCREMENT_PLAYER_WIN,$name);
    $conn->query($query);
}

function incrementPlayerLost($name,$conn){
    $query = sprintf(INCREMENT_PLAYER_LOSS,$name);
    $conn->query($query);
}

function UpdateShortestWin($name,$conn){
    $getShortesWinQuery = sprintf(FETCH_SHORTEST_WIN,$name);
    $shortestWins = $conn->query($getShortesWinQuery);
    if($shortestWins->num_rows >0){
        while($row = $shortestWins->fetch_assoc()){
            $updateQuery = sprintf(UPDATE_SHORTEST_WIN,$row['moves'],$name);
            $conn->query($updateQuery);
            //return $row['moves'];
        }
    }
}

function fectchLeaderBoard($conn){
    $fectchLeaderBoardQuery = sprintf(FETCH_LEADERBOARD);
    $leaderBoard = $conn->query($fectchLeaderBoardQuery);
    $leaderBoardArr=[];
    if($leaderBoard->num_rows > 0){
        while($player = $leaderBoard->fetch_assoc()){
            $leaderBoardArr += $player;
        }
    }
    return $leaderBoardArr;
}


//$conn = openConnection();
//updateShortestWin("MiGiA",$conn);
//$game = fetchGameWithId(1,$conn)['game'];

// print_r($game->getPlayerOneLocation());
//$game = new Game("MiGiA","Steve");
// print_r($game);
//addGame($game,$conn);
//  $game->setTurnn();
//  updateTurn($game->getTurn(),$game->getId(),$conn);
// $game->setTurnn();

?>