<html>

<head>
    <title>
        Dicey Game
    </title>
    <link rel="stylesheet" href="css/main.css">
    <script src="js/index.js" defer></script>
</head>

<body>
<span id="message"></span>
<div class="nav">
    <span id="back" class="option">BAck to Game</span>
        <span class="option" id="leaderBoard"> LeaderBoard</span>
<!--        <span class="option"> Game Menu</span>-->
    </div>
</div>
<div class="menu">

    <div class="form" >
        
        <div class="player-name">
            <label for="player1" class="label" id="label1"> Enter Player1 name: </label>
            <input id="player1" placeholder="player1" type="text" name="player1" class="input">
        </div>
        <div class="player-name">
            <label for="player2" class="label" id="label2"> Enter Player2 name: </label>
            <input id="player2" placeholder="player2" type="text" name="player2" class="input">
        </div>
        <button class="button" id="start">Start Game</button>
    </div>
</div>

<div class="game">

    <div class="game-area">
        <div class="dice-area">
            <img id="dice" src="images/dice6.png">
            <span id="player-turn">
                It is Player 1 turn
            </span>
            <button id="roll-dice-btn" class="button" type="button">
                Roll Dice
            </button>
        </div>

        <div id="board"></div>


        </div>
<div class="game-buttons">
    <button class="button" id="reset">
        Reset
    </button>
    <button class="button" id="restart">
        Restart
    </button>
    </div>
</div>

<div class="leader-board">
    <div id="leaderboard">
    <div class="row">
        <div class="col-1"></div>
        <div class="col-2"></div>
        <div class="col-3"></div>
    </div>
       
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>



</body>
</html>