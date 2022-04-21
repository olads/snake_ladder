<?php
require_once 'Game.php';
include "DBConnection.php";

session_start();

define("conn",openConnection());

if(isset($_POST['player1']) && isset($_POST['player2'])){
  
    $game = new Game($_POST['player1'],$_POST['player2']);
    $gameId = fetchGame($game,conn);

    if($gameId == 0){
        addGame($game,conn);
       
    }

   else {
    $game = fetchGameWithId($gameId,conn)['game'];
    if($game->getWinner() != ''){
        $game = new Game($_POST['player1'],$_POST['player2']);
        addGame($game,conn);
    }
    
   }
   $_SESSION['game'] = $game;
   $_SESSION['conn'] = conn;
   echo parseResponse(["player1_location"=> $game->getPlayerOneLocation()],
   ["player2_location"=> $game->getPlayerTwoLocation()],["player1_name"=> $game->getPlayer1()],
   ["player2_name"=> $game->getPlayer2()],["turn"=> $game->getPlayerTurnName()]);
  
}
closeCon(conn);
