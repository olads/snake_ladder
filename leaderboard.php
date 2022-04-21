<?php
include 'DBConnection.php';

if($_POST['leaderboard']){
    echo json_encode(fectchLeaderBoard(openConnection()));
}


?>
