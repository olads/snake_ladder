<?php



class Game //implements JsonSerializable
 {


    /**
     * Constants
     */
    const BLACKHOLE_NUMBER = 2;
    const WORMHOLE_NUMBER = 3;

    /**
     * Game variables like player names
     * game progress, position of blackholes and wormholes for
     * each game
     */
    private $id;
    private $player1;
    private $player2;
    private $playerOneLocation;
    private $playerTwoLocation;
    private $turn;
    private $winner;
    private $blackHoles;
    private $wormHoles;



    public function __construct($player1,$player2)
    {
        $this->player1 = $player1;
        $this->player2 = $player2;
        $this->populateBlackHole();
        $this->populateWormHole();
        $this->playerOneLocation = 1;
        $this->playerTwoLocation = 1;
        settype($this->turn,'boolean');
        $this->turn =true;
        //echo "Finished initializing game object";
    }

    /**
     * Getters and settters
     */

    public function getId()
    {
        return $this->id;
    }


    public function setId($id)
    {
        $this->id = $id;
    }


    public function getPlayer1()
    {
        return $this->player1;
    }

    public function setPlayer1($player1)
    {
        $this->player1 = $player1;
    }


    public function getPlayer2()
    {
        return $this->player2;
    }

    public function setPlayer2($player2)
    {
        $this->player2 = $player2;
    }


    public function getPlayerOneLocation()
    {
        return $this->playerOneLocation;
    }




    public function setPlayerOneLocation($playerOneLocation)
    {
        $this->playerOneLocation = $this->parsePosition($playerOneLocation);
    }




    public function getPlayerTwoLocation()
    {
        return $this->playerTwoLocation;
    }




    public function setPlayerTwoLocation($playerTwoLocation)
    {
        $this->playerTwoLocation = $this->parsePosition($playerTwoLocation);
    }

    public function getWinner()
    {
        return $this->winner;
    }

    public function setWinner($winner)
    {
        $this->winner = $winner;
    }


    public function getBlackHoles()
    {
        return $this->blackHoles;
    }

    public function setBlackHoles($blackHoles)
    {
        $this->blackHoles = $this->parseHolesToArray($blackHoles);
    }


    public function getWormHoles()
    {
        return $this->wormHoles;
    }


    public function setWormHoles($wormHoles)
    {
        $this->wormHoles = $this->parseHolesToArray($wormHoles);
    }



    public function getTurn()
    {
        return (bool)$this->turn;
    }

    public function setTurnn()
    {
        // if($turn){
        //     $this->turn = 1;
        // }
        // else{
        //     $this->turn = -1 * 1;
        // }
        $this->turn = (bool)!$this->turn;
    }
    public function setTurn($turn){
        $this->turn =(bool) $turn;
    }

    public function getPlayerTurn(){
        if($this->getTurn() > 1){
            return $this->getPlayer1();
        }
        return $this->getPlayer2();
    }





    /**
     * End of getters and setters
     */


    function restart(){

        $this->populateBlackHole();
        $this->populateWormHole();
        $this->playerOneLocation = 1;
        $this->playerTwoLocation = 1;
        settype($this->turn,'boolean');
        $this->turn =true;

    }
    function parsePosition($pos){
        if($pos <1){
            return 1;
        }
        else if ($pos >36){
            return 36;
        }
        return $pos;
    }
     function parseHolesToArray($holes){
        $resultArray = [];
        $explodedHoles = explode(",",$holes);
        foreach($explodedHoles as $hole){
            $explodedHoles = explode(":",$hole);
            $resultArray += [intval($explodedHoles[0]) =>intval($explodedHoles[1])];
        }
    
        return $resultArray;
    }
    private function isSpecialSpot($arr,$pos){
        $keys = array_keys($arr);
        for($i=0; $i<count($arr)-1; $i++){
            if($arr[$keys[$i]] == $pos){
                return true;
            }
        }

        return false;
    }
    private function populateBlackHole(){
        $previousLocation =1;
        $blackHole = [];
        for ($i = 0; $i< self::BLACKHOLE_NUMBER; $i++){
            $random = rand(8,35);
            if(abs($random - $previousLocation) > 5){
                //array_push($blackHole,$random);
                $newLocation =$random - (rand(3, 8));
                if(!$this->isSpecialSpot($blackHole,$newLocation)) {
                    $blackHole[$random] = $newLocation;
                    $previousLocation = $random;
                    continue;
                }
                $i--;

            }
            else {
                $i--;
            }
        }
        $this->blackHoles = $blackHole;
    }
    private function populateWormHole(){
        $previousLocation =1;
        $wormHoles = [];
        for ($i = 0; $i< self::WORMHOLE_NUMBER; $i++){
            $random = rand(4,35);

            if(abs($random - $previousLocation) > 5) {
                //array_push($wormHoles,$random);
                $newLocation = $random + rand(1, 8);
                if (!$this->isSpecialSpot($wormHoles, $newLocation)) {
                    $wormHoles[$random] = $random + rand(3, 8);
                    $previousLocation = $random;
                    continue;
                }
                $i--;
            }
            else {
                $i--;
            }
        }
        $this->wormHoles = $wormHoles;

    }

    public function checkJump($pos){
        if(array_key_exists($pos,$this->getblackHoles())){
           // echo "Landed on blackhole $pos";
            return array("blackhole"=>$this->getBlackHoles()[$pos],"intermediary_pos"=>$pos);

        }
        else if(array_key_exists($pos,$this->getWormHoles())){
            //echo "Landed on wormhole $pos";
            return array('wormhole'=>$this->getWormHoles()[$pos],"intermediary_pos"=>$pos);
        }
        
    }

    public function checkWin($pos){
        if($pos >= 36){

            $this->setWinner($this->getPlayerTurnName());
           // echo "$player has won!!";
            return true;
        }
        return false;
    }

    public function checkPosition($pos){
        $pos = $this->parsePosition($pos);
        if($this->checkWin($pos)){
            return array("win"=>$pos);
        }
        $checkJump = $this->checkJump($pos);
        if($checkJump != 0){
            return $checkJump;
        }
        return array("normal"=>$pos);
        
    }



public function getPlayerTurnName($opp = false){
    $turn = (bool) $this->getTurn();
   
    if($turn){

        return ($opp) ? $this->getPlayer2():$this->getPlayer1();
    }
    else{
        return ($opp) ? $this->getPlayer1():$this->getPlayer2();;
    }
}

public function setPlayerPosition($pos){
    if($this->turn){
        $this->setPlayerOneLocation($pos);
    }
    else{
        $this->setPlayerTwoLocation($pos);
    }
}

public function getIntTurn(){
    return ($this->getTurn()) ? 1:0;
}
   /* public function jsonSerialize()
    {
        return [
            "player1"=>$this->player1,
            'player2'=> $this->player2,
            'blackholes'=>$this->blackHoles,
            'wormholes'=>$this->wormHoles
        ];
    }*/
}

?>