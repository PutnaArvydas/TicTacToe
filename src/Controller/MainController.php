<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MainController extends Controller
{
    /**
     * @Route("/", name="home")
     * @param Request $request
     */
    public function index(Request $request = null)
    {        
        $_SESSION['jsonString'] = $request->request->get('fieldData');
        $dataArray = $this->jsonStringToArray($_SESSION['jsonString']);
        
        $dataArray = $this->cpuMove($dataArray);
        $textToDisplay = $this->checkIfWin($dataArray);
        
        return $this->render('home/index.html.twig',
        [
            'data' => $dataArray,
            'text' => $textToDisplay,
        ]);
    }
    
    /**
     * @param string $requestString
     * @return Array dataArray
     */
    private function jsonStringToArray($requestString)
    {
        if(!empty($requestString))
        {
            $jsonString = json_decode($requestString);
            $dataArray = json_decode($jsonString, true);
            return $dataArray['formData'];
        }
        return array(['','',''],['','',''],['','','']);
    }
    
    /**
     * @param Array $dataArray
     * @param string $playerType
     */
    private function checkIfWin($dataArray){
        $cpuWin = $this->checkIfRowFinished($dataArray, 'O');
        $cpuWin = ($cpuWin) ? $cpuWin : $this->checkIfColumnFinished($dataArray, 'O');
        $cpuWin = ($cpuWin) ? $cpuWin : $this->checkIfDiagonalFinished($dataArray, 'O');
        if($cpuWin){
            return 'Lost!!!';
        }
        
        $userWin = $this->checkIfRowFinished($dataArray, 'X');
        $userWin = ($userWin) ? $userWin : $this->checkIfColumnFinished($dataArray, 'X');
        $userWin = ($userWin) ? $userWin : $this->checkIfDiagonalFinished($dataArray, 'X');
        
        $isEmptyFields = $this->checkIfLeftEmptyFields($dataArray);
        
        if($userWin){
            return 'WON!!!';
        }
        if(!$isEmptyFields){
            return 'DRAW!!!';
        }
        else{
            return null;
        }
    }
    
    /**
     * @param Array $dataArray
     * @param string $playerType
     * @return bool
     */
    private function checkIfRowFinished($dataArray, $playerType)
    {
        foreach($dataArray as $row){
            $counter = 0;
            foreach ($row as $element){
                if($element == $playerType){
                    $counter++;
                }
            }
            if($counter == 3){
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param array $dataArray
     * @param string $playerType
     * @return bool
     */
    private function checkIfColumnFinished($dataArray, $playerType)
    {
        for($i = 0; $i < sizeof($dataArray); $i++){
            $counter = 0;
            for($t = 0; $t < sizeof($dataArray); $t++){
                if($dataArray[$t][$i] == $playerType){
                    $counter++;
                }
            }
            if($counter == 3){
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param type $dataArray
     * @param type $playerType
     * @return bool
     */
    private function checkIfDiagonalFinished($dataArray, $playerType)
    {
        $counter = 0;
        for($i = 0; $i < sizeof($dataArray); $i++){
            if($dataArray[$i][$i] == $playerType){
                $counter++;
            }
        }
        if($counter == 3){
            return true;
        }
        else{
           $counter = 0;
           $index = sizeof($dataArray) - 1;
           for($i = 0; $i < sizeof($dataArray); $i++){
                if($dataArray[$i][$index - $i] == $playerType){
                    $counter++;
                }
            }
            if($counter == 3){
                return true;
            }
        }
        return false;
    }
    
    /**
     * @param array $dataArray
     * @return boolean
     */
    public function checkIfLeftEmptyFields($dataArray)
    {
        
        foreach($dataArray as $row){
            foreach ($row as $element){
                if(empty($element)){
                    return true;
                }
            }
        }
        return false;
    }
    
    /**
     * @param array $dataArray
     * @return string
     */
    private function cpuMove($dataArray)
    {
        if($this->checkIfAllFieldsEmpty($dataArray)){
            return $dataArray;
        }
        $emptyCenter = $this->checkIfCenterEmpty($dataArray);
        if($emptyCenter){
            $dataArray[1][1] = 'O';
            return $dataArray;
        }
        $rowsData = $this->checkPrefiledRow($dataArray);
        if($rowsData['addedValue']){
            return $rowsData['data'];
        }
        $columnData = $this->checkPrefiledColumn($dataArray);
        if($columnData['addedValue']){
            return $columnData['data'];
        }
        
        $diagonalData = $this->checkPrefiledFirstDiagonal($dataArray);
        if($diagonalData['addedValue']){
            return $diagonalData['data'];
        }
        $secondDiagonalData = $this->checkPrefiledScondDiagonal($dataArray);
        if($secondDiagonalData['addedValue']){
            return $secondDiagonalData['data'];
        }
        
        return $this->setValueForRndomField($dataArray);
    }
    
    /**
     * @param array $dataArray
     * @return boolean
     */
    private function checkIfCenterEmpty($dataArray){
        if(empty($dataArray[1][1])){
            return true;
        }
        return false;        
    }
    
    /**
     * @param array $dataArray
     * @return array
     */
    private function checkPrefiledRow($dataArray){
        $addedValue = false;
        
        for($i = 0; $i < sizeof($dataArray); $i++){
            $counter['user'] = 0;
            $counter['cpu'] = 0;
            $counter['empty'] = 0;
            
            for ($t = 0; $t < sizeof($dataArray); $t++){
                $counter = $this->countedValuesArray($dataArray[$i][$t], $counter);
            }
            
            if(($counter['user'] == 2 || $counter['cpu'] == 2) && $counter['empty'] == 1){
                $dataArray[$i] = $this->setCpuMoveForRow($dataArray[$i]);
                $addedValue = true;
                return array('data' => $dataArray, 'addedValue' => $addedValue);
            }
        }
        return array('data' => $dataArray, 'addedValue' => $addedValue);
    }
    
    /**
     * @param array $dataArray
     * @return array
     */
     private function checkPrefiledColumn($dataArray){
         $addedValue = false;
         
        for($i = 0; $i < sizeof($dataArray); $i++){
            $counter['user'] = 0;
            $counter['cpu'] = 0;
            $counter['empty'] = 0;
            
            for ($t = 0; $t < sizeof($dataArray); $t++){
                $counter = $this->countedValuesArray($dataArray[$t][$i], $counter);
            }
            if(($counter['user'] == 2 || $counter['cpu'] == 2) && $counter['empty'] == 1){
                $dataArray = $this->setCpuMoveForColumn($dataArray, $i);
                $addedValue = true;
                return array('data' => $dataArray, 'addedValue' => $addedValue);
            }
        }
        return array('data' => $dataArray, 'addedValue' => $addedValue);
    }
    
    /**
     * @param array $dataArray
     * @return array
     */
    private function checkPrefiledFirstDiagonal($dataArray){
        $counter['user'] = 0;
        $counter['cpu'] = 0;
        $counter['empty'] = 0;
        $addedValue = false;
        for($i = 0; $i < sizeof($dataArray); $i++){
            
            $counter = $this->countedValuesArray($dataArray[$i][$i], $counter);
        }
        if(($counter['user'] == 2 || $counter['cpu'] == 2) && $counter['empty'] == 1){
                $dataArray = $this->setCpuMoveForDiagonal($dataArray);
                $addedValue = true;
        }
        return array('data' => $dataArray, 'addedValue' => $addedValue);
    }
    
    /**
     * @param array $dataArray
     * @return array
     */
    private function checkPrefiledScondDiagonal($dataArray){
        $counter['user'] = 0;
        $counter['cpu'] = 0;
        $counter['empty'] = 0;
        $addedValue = false;
        $columnIndex = sizeof($dataArray) - 1;
        for($i = 0; $i < sizeof($dataArray); $i++){
            $counter = $this->countedValuesArray($dataArray[$i][$columnIndex - $i], $counter);
        }
        if(($counter['user'] == 2 || $counter['cpu'] == 2) && $counter['empty'] == 1){
                $dataArray = $this->setCpuMoveForSecondDiagonal($dataArray);
                $addedValue = true;
                return array('data' => $dataArray, 'addedValue' => $addedValue);
        }
        return array('data' => $dataArray, 'addedValue' => $addedValue);
    }

/**
 * @param string $field
 * @param array $counter
 * @return array
 */
    private function countedValuesArray($field, $counter)
    {
        if($field == 'X'){
            $counter['user'] += 1;
        }
        elseif($field == 'O')
        {
            $counter['cpu'] += 1;
        }
        else{
            $counter['empty'] += 1;
        }
        return $counter;
    }
    
    /**
     * @param array $rowArray
     * @return array
     */
    private function setCpuMoveForRow($rowArray)
    {
        for ($t = 0; $t < sizeof($rowArray); $t++){
            if($rowArray[$t] == ''){
                $rowArray[$t] = 'O';
                return $rowArray;
            }
        }
        return $rowArray;
    }
    
    /**
     * @param array $dataArray
     * @param int $columnIndex
     * @return array
     */
    private function setCpuMoveForColumn($dataArray, $columnIndex)
    {
       
        for ($t = 0; $t < sizeof($dataArray[$columnIndex]); $t++){
            if($dataArray[$t][$columnIndex] == ''){
                $dataArray[$t][$columnIndex] = 'O';
                return $dataArray;
            }
        }
        return $dataArray;
    }
    
    /**
     * @param array $dataArray
     * @return array
     */
    private function setCpuMoveForDiagonal($dataArray)
    {
        for ($t = 0; $t < sizeof($dataArray); $t++){
            if($dataArray[$t][$t] == ''){
                $dataArray[$t][$t] = 'O';
                return $dataArray;
            }
        }
        return $dataArray;
    }
    
    /**
     * @param array $dataArray
     * @return array
     */
    private function setCpuMoveForSecondDiagonal($dataArray){
        $columnIndex = sizeof($dataArray) - 1;
        for ($t = 0; $t < sizeof($dataArray); $t++){
            if($dataArray[$t][$columnIndex - $t] == ''){
                $dataArray[$t][$columnIndex - $t] = 'O';
                return $dataArray;
            }
        }
        return $dataArray;
    }
    
    /**
     * @param array $dataArray
     * @return boolean
     */
    private function checkIfAllFieldsEmpty($dataArray)
    {
        foreach($dataArray as $row){
            foreach ($row as $element){
                if(!empty($element)){
                    return false;
                }
            }
        }
        return true;
    }
    
    /**
     * @param array $dataArray
     * @return array
     */
    private function setValueForRndomField($dataArray)
    {
        for($i = 0; $i < sizeof($dataArray); $i++){
            for($t = 0; $t < sizeof($dataArray); $t++){
                if($dataArray[$i][$t] == ''){
                    $dataArray[$i][$t] = 'O';
                    return $dataArray;
                }
            }
        }
        return $dataArray;
    }
}
