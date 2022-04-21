$(document).ready(startGame);

function startGame() {
  const boardDimension = 6;
  let gameStarted = false;

  $(".game").hide();
  $(".menu").show();
  $("#back").hide();
  $(".leader-board").hide();

  $("#leaderBoard").on("click", () => {

    $(".leader-board").show();

    $("#back").show();
    $(".game").hide();
    $(".menu").hide();

    $.ajax("leaderboard.php", {
      type: "POST",
      data: {
        leaderboard: true,
      },
      success: (response) => {

        let res = JSON.parse(response);
        console.log(res.player, res.shortest_win);
        $("#leaderboard").empty();
        console.log(res.id === undefined)
        let row = "";
        
        $("#leaderboard").append(
          $(
            "<tr><th>Player Name</th><th>Number of Wins</th><th>Number of Losses</th><th>Shortest Win</th></tr>" ));

        if (res.id === undefined) {
          row = "<tr>No completed games</tr>";
        }
         else {
        
        row = $(`<tr>  
        <td>${res.player_name}</td>
        <td>${res.win}</td>
        <td>${res.lose}</td>
        <td>${res.shortest_win}</td>
        </tr>`);
        }

        $("#leaderboard").append(row);
      },
    });
  });


  $("#back").on("click", () => {
    $(".leader-board").hide();
    if (gameStarted) {
      $(".game").show();
      $(".menu").hide();
    } else {
      $(".game").hide();
      $(".menu").show();
      $("#back").hide();
    }
  });

  createBoard();

  $("#start").on("click", start);
 async function start() {
    player1 = $("#player1").val();
    player2 = $("#player2").val();
    function checkInput(name1, name2) {
      if (name1 === name2) {
        console.log("they are equal ");
        $("#message").text(
          "Please ensure NOT to use same name for both players"
        );
        return true;
      }
      return false;
    }
    if (!checkInput(player1, player2)) {
      $(".menu").hide();
      $("#message").text("")
      $(".game").show();
     await $.ajax("welcome.php", {
        type: "POST",
        data: {
          player1: player1,
          player2: player2,
        },
        success: (result) => {
          let parsedJson = JSON.parse(result);
          playerOneLocation = parsedJson[0].player1_location;
          playerTwoLocation = parsedJson[1].player2_location;
          player1_name = parsedJson[2].player1_name;
          player2_name = parsedJson[3].player2_name;
          $("#player-turn").text("It is " + parsedJson[4].turn + "'s turn ");
          changeName(player1_name, playerOneLocation);
          changeName(player2_name, playerTwoLocation);
          gameStarted = true;
        },
      });
    }
  }

  function createBoard() {
    $("#board").empty();
    $("#message").text("")
    let pos = 1;
    for (var i = 1; i <= boardDimension; i++) {
      for (var j = 1; j <= boardDimension; j++) {
        var className = pos % 2 == 0 ? "cell" : "alt-cell";
        var cell = $(`<div class='${className}' id='${pos}'>${pos}</div>`);
        cell.addClass("tiny");
        let number = $(`<span ></span>`);
        number.addClass("playerName");
        cell.append(number);
        $("#board").append(cell);
        pos++;
      }
    }
  }

  function changeName(name, at, remove = false) {
    let text = $("#" + at + " span").text();

    let parsedString = "";

    if (remove) {
      parsedString = text.replace(name, "");
    } 
    else {
      if (text.indexOf(name) === -1) parsedString = text + "\n" + name;
    }
    $("#" + at + " span").text(parsedString);
  }

  const animateCellMovement = (from, to, name) => {
    return new Promise((res, rej) => {
      let pos = from;
      let loopIncrement = from < to ? 1 : -1;
      changeName(name, pos, true);
      pos += loopIncrement;
      const id = setInterval(() => {
        changeName(name, pos);
        changeName(name, pos - loopIncrement, true);
        if (pos === to) {
          clearInterval(id);
          res();
        }
        pos += loopIncrement;
      }, 100);
    });
  };

  //This function handles the rolling of dice
  $("#roll-dice-btn").on("click", () => {
    let timeoutId = 0;
    let nums = 0;
    const animate = () => {
      timeoutId = setInterval(() => {
        if (nums === 12) {
          clearInterval(timeoutId);
          updateDice();
        }
        var random = Math.floor(Math.random() * 6) + 1;

        $("#dice").attr("src", "images/dice" + random + ".png");
        nums++;
      }, 50);
    };
    animate();
    const updateDice = () => {
      $.post(
        "backend.php",
        {
          roll: true,
        },
        async function (data) {
          console.log(data);
          var dd = JSON.parse(data);
          var resultType = dd[0].resultType;
         if(resultType === "winner-exists"){
            $("#message").text("There is a winner already")
            console.log("Winner exists")
            return;
          }
          else{
          var from = parseInt(dd[1].from);
          var diceResult = dd[2].dice_result;
          var to = dd[3][resultType];
          var intermed_pos = dd[3]["intermediary_pos"];
          var name = dd[4].name;
          var turn = dd[5].turn;
          console.log(dd, resultType, from, diceResult, to);
          var className = intermed_pos % 2 == 0 ? "cell" : "alt-cell";
         
          if (resultType === "win") 
          {

            $("#dice").attr("src", "images/dice" + diceResult + ".png");
            if(intermed_pos == null){
               await animateCellMovement(from, to, name);
            }
            else{
              await animateCellMovement(from, intermed_pos, name);
            }
           
            $("#player-turn").text("Player " + name + " Won");
            
          }

           else if (resultType === "blackhole")
            {

            await animateCellMovement(from, intermed_pos, name);
              $("#" + intermed_pos).removeClass(className);
            $("#" + intermed_pos).addClass("blackhole");
           // $("#" + intermed_pos).text("Blackhole");
            $("#" + intermed_pos + " span").addClass("blackhole");
            //changeName(name,intermed_pos)

            setTimeout(() => {
              animateCellMovement(intermed_pos, to, name);
            }, 1000);

          } else if (resultType === "wormhole")
           {

            await animateCellMovement(from, intermed_pos, name);
             $("#" + intermed_pos).removeClass(className);
            $("#" + intermed_pos).addClass("wormhole");
           // $("#" + intermed_pos).text("Wormhole");
            $("#" + intermed_pos + " span").addClass("wormhole");
           // changeName(name,intermed_pos)
            setTimeout(() => {
              animateCellMovement(intermed_pos, to, name);
            }, 1000);

          }
          
          else
           {

            $("#dice").attr("src", "images/dice" + diceResult + ".png");
            $("#player-turn").text("Ït is " + turn + "'s turn ");
            animateCellMovement(from, to, name);
            clearInterval(timeoutId);
          }
        }
      }
      );
    };
  });

  $("#reset").on("click", () => {
    $.ajax("backend.php", {
      type: "POST",
      data: {
        reset: true,
      },
      success: () => {
        //createBoard();
        startGame();
      },
    });
  });

  $("#restart").on("click", async () => {
    createBoard();
    $.ajax("backend.php", {
      type: "POST",
      data: {
        restart: true,
      },
      success: (response) => {
        let res = JSON.parse(response);
        console.log(res.player1_name);

        changeName(res.player1_name, 1);
        changeName(res.player2_name, 1);
        $("#player-turn").text("It is " + res.player1_name + "'s turn ");
      },
    });
  });
}
