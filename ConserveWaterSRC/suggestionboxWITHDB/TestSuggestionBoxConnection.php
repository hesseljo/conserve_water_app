<?php
require_once "PHPUnit/Autoload.php";

class SuggestionsDBTest extends PHPUnit_Extensions_Database_TestCase
{
    public function getConnection() {
        $mysqli = new mysqli("oniddb.cws.oregonstate.edu","loy-db","7LOgg6VSYfMcvlBJ","loy-db");
    
    	return $this->createDefaultDBConnection($db, "loy-db");
    }

    public function getDataSet() {
        return $this->createXMLDataSet("testData.xml");
    }

    public function testGetSuggestions() {
    $SuggestionsDBTest = new SuggestionsDBTest();
    $suggestions = $SuggestionsDBTest->getSuggestions(1, false);
    $this->assertEquals(
        array(
            array("id" => 1, "id" => "1"),
            array("id" => 2, "name" => "give me more and more"),
        $suggestions);
    }

    public function getSuggestions($id, $name) {
        $mysqli = new mysqli("oniddb.cws.oregonstate.edu","loy-db","7LOgg6VSYfMcvlBJ","loy-db");

        $result = $db->query("SELECT suggestion.inputSuggestion FROM suggestion");
        $suggestions = array();
        while ($row = $result->fetch(mysqli::FETCH_ASSOC)) {
            $suggestions[] = $row;
        }
        $result->closeCursor();

        return $suggestions;
    }
}

?>