<?php

require_once('AnthologizeTeiApiTest.php');
//@TODO: teidom does not split apart multiple authors
//@TODO: is there any utility to keeping the span? Maybe better to have a way to auto-wrap with element and attributes?

class getSectionPartItemAssertedAuthorTest extends AnthologizeTeiApiTest {
    
    public function testNotAsNodeValueOnly() {
        $author = $this->api->getSectionPartItemAssertedAuthor('body', 0, 0, false, true);
        $this->assertEquals('Part 1 Simplest Page Author', $author);
    }
    
    public function testAsNodeValueOnly() {
        //might be a flaw in api design, since this should give same as above
        $author = $this->api->getSectionPartItemAssertedAuthor('body', 0, 0, true, true);
        $this->assertEquals('Part 1 Simplest Page Author', $author);
    }
    
    public function testAsNodeNotValueOnly() {
        $author = $this->api->getSectionPartItemAssertedAuthor('body', 0, 0, true, false);
        $this->assertTrue(is_a($author, 'DOMNode'));
        $this->assertEquals('Part 1 Simplest Page Author', $author->textContent);
        
    }
    public function testNotAsNodeNotValueOnly() {
        $author = $this->api->getSectionPartItemAssertedAuthor('body', 0, 0, false, false);
        $this->assertTrue(is_array($author));
        $this->assertEquals('Part 1 Simplest Page Author', $author['spans'][0]['value']);
    }
}