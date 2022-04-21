<?php

session_start();
require_once 'Game.php';
include 'DBConnection.php';


define("conn",openConnection());


if (!isset($_SESSION['game'])) {
echo "Game object is not set";
$_SESSION["game"] = new Game("player1", "player2",true);
$_SESSION['game']->setId($_SESSION['gameId']);
}
else{
   // echo "Game object is present";
}


$_SESSION['game'] = unserialize(serialize($_SESSION['game']));




//returns a game object that contains where the player moves to
//also it check whether it a special position
if(isset($_POST['roll'])){
  
    $game = $_SESSION['game'];
    if($game->getWinner() == "")
    {
          $pos = rand(1,6);  
    $position = ["dice_result" => $pos];
    $from = [];
    if($game->getTurn()){
        $from = ["from"=>$game->getPlayerOneLocation()];
    }
    else{
        $from = ["from"=>$game->getPlayerTwoLocation()];
    }
   
   $newLocation =0;
    //$player2Loc = [$game->getPlayerTwoLocation()];
    $result = $game->checkPosition($from['from'] + $pos);
   


    reset($result);
    $resultType = ["resultType" => key($result)];
     
    $newLocation =$result[$resultType['resultType']];
    $game->setPlayerPosition($newLocation);
    $playerName = ['name'=> $game->getPlayerTurnName()];
    if($game->checkWin($newLocation)){
        $resultType['resultType'] = 'win';
        updateGame("winner", $game->getPlayerTurnName(), $game->getId(),conn);
        addPlayer($game->getPlayerTurnName(),true,conn);
        addPlayer($game->getPlayerTurnName(true),false,conn);
    }
    echo parseResponse($resultType,$from,$position,$result,$playerName,['turn'=> $game->getPlayerTurnName(true)]); 
    updatePlayerPosition(2-$game->getIntTurn(),$newLocation,$game->getId(),conn);
    
    $game->setTurnn();
   updateTurn($game->getTurn(),$game->getId(),conn);
   closeCon(conn);
    exit;
    }

    else{
        echo json_encode([array("resultType" => "winner-exists")]);
    }
    
  
}
if (isset($_POST['reset'])) {
    //echo "Reset button clicked";
    unset($_SESSION['game']);
    unset($_SESSION['gameId']);
    session_destroy();

    exit();
}

function updateGameData($game){
    updatePlayerPosition(1,$game->getPlayerOneLocation(),$game->getId(),conn);
    updatePlayerPosition(2,$game->getPlayerTwoLocation(),$game->getId(),conn);
    updateTurn($game->getTurn(),$game->getId(),conn);
    updateNumberOfMoves($game->getId(),conn,true);


}

if (isset($_POST['restart'])) {
   // echo "Restart button clicked";
    $_SESSION['game']->restart();
    updateGameData($_SESSION['game']);
    updateGame("winner","",$_SESSION['game']->getId(),conn);
    echo json_encode(array('player1_name'=> $_SESSION['game']->getPlayer1(),'player2_name'=> $_SESSION['game']->getPlayer2()));

        exit();
}




closeCon(conn);
