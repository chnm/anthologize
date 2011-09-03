<?php
require_once('AnthologizeTeiApiTest.php');



class buildQueryStringTest extends AnthologizeTeiApiTest {
    
    public function testId() {
        $id = "body-1-1";
        $params = array('id'=>$id);
        $this->assertEquals("//*[@xml:id = '$id']", $this->api->buildQueryString($params));
    }
    
    public function testSectionPartNumber() {
        $params = array('section'=>'body', 'partNumber' => 1);
        $this->assertEquals("//tei:body/tei:div[@n='1']", $this->api->buildQueryString($params));
    }
    
    public function testBodyPartNumberItemNumber() {
        $params = array('section'=>'body', 'partNumber' => 0, 'itemNumber' => 0 );
        $this->assertEquals("//tei:body/tei:div[@n='0']/tei:div[@n='0']", $this->api->buildQueryString($params));
        
    }

  
    public function testFrontPartNumberItemNumber() {
        $params = array('section'=>'front', 'partNumber' => 0, 'itemNumber' => 0 );
        $this->assertEquals("//tei:front/tei:div[@n='0']", $this->api->buildQueryString($params));
        
    }
    
    public function testPartItemTitle() {
        $params = array('section'=>'body', 'partNumber' => 0, 'itemNumber' => 0, 'subPath'=>"/tei:head/tei:title" );
        $this->assertEquals("//tei:body/tei:div[@n='0']/tei:div[@n='0']/tei:head/tei:title", $this->api->buildQueryString($params));
        $params = array('section'=>'front', 'partNumber' => 0, 'itemNumber' => 0, 'subPath'=>"/tei:head/tei:title" );
        $this->assertEquals("//tei:front/tei:div[@n='0']/tei:head/tei:title", $this->api->buildQueryString($params));
    }
    
    public function testPartItemSubpath() {
        $params = array('section'=>'body', 'partNumber' => 1, 'itemNumber' => 1, 'subPath'=>'/subpath' );
        $this->assertEquals("//tei:body/tei:div[@n='1']/tei:div[@n='1']/subpath", $this->api->buildQueryString($params));
    }
    
    public function testSubPath() {
        $id = "body-1-1";
        $subpath = "/subpath";
        $params = array('id'=>$id, 'subPath'=>$subpath);
        $this->assertEquals("//*[@xml:id = '$id']/subpath", $this->api->buildQueryString($params));
    }
    
    
    
}