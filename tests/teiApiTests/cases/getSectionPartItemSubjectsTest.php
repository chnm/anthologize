<?php

require_once('AnthologizeTeiApiTest.php');
//@TODO: is there any utility to keeping the span? Maybe better to have a way to auto-wrap with element and attributes?

//@TODO: tags index appears to be fail in teidom

class getSectionPartItemSubjectsTest extends AnthologizeTeiApiTest {
    
    public function testAsNode() {
        $subjects = $this->api->getSectionPartItemSubjects('body', 1, 1, true);
        $this->assertTrue(is_a($subjects, 'DOMNodeList'));
    }
    
    public function testNotAsNode() {
        $subjects = $this->api->getSectionPartItemSubjects('body', 1, 1, false);
        $this->assertTrue(is_array($subjects));
    }
    
    public function testValues() {
        $subjects = $this->api->getSectionPartItemSubjects('body', 1, 1, false);
        $this->assertEquals('tag1', $subjects[0]['spans'][0]['value']);
        $this->assertEquals('post_tag', $subjects[0]['atts']['type']);
        $this->assertEquals('post_tag-tag1', $subjects[0]['atts']['ref'] );
    }
}