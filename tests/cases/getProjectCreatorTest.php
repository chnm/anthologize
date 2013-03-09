<?php

class getProjectCreatorTest extends AnthologizeTeiApiTest {

    public function testAsStructuredNotAsNode() {
        $creator = $this->api->getProjectCreator(true, false);
        $this->assertTrue(is_array($creator));
    }

    public function testAsNotStructuredAsNode() {
        $creator = $this->api->getProjectCreator(false, true);
        $this->assertTrue(is_a($creator, 'DomNode'));
    }

    public function testAsStructuredAsNode() {
        $creator = $this->api->getProjectCreator(true, true);
        $this->assertTrue($creator->nodeName == 'person');
    }

    public function testAsNotStructuredNotAsNode() {
        $creator = $this->api->getProjectCreator(false, false);
        $this->assertEquals('admin', $creator);
    }

}
