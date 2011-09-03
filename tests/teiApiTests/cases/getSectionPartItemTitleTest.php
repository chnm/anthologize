<?php

require_once('AnthologizeTeiApiTest.php');

class getSectionPartItemTitleTest extends AnthologizeTeiApiTest {

    public function testAsNode() {
        $dedicationTitle = $this->api->getSectionPartItemTitle('front', 0, 0, true);
        $bodyPart0Item0Title = $this->api->getSectionPartItemTitle('body', 0, 0, true);
        $bodyPart0Item1Title = $this->api->getSectionPartItemTitle('body', 0, 1, true);
        $bodyPart1Item1Title = $this->api->getSectionPartItemTitle('body', 1, 1, true);
        $bodyPart1Item0Title = $this->api->getSectionPartItemTitle('body', 1, 0, true);
        $this->assertInstanceOf('DOMNode', $dedicationTitle);
        $this->assertEquals('Dedication', $dedicationTitle->textContent);
        $this->assertEquals('Simplest Page', $bodyPart0Item0Title->textContent);
        $this->assertEquals('Simplest Page', $bodyPart1Item0Title->textContent);
        $this->assertEquals('Categories and Tags', $bodyPart1Item1Title->textContent);
        $this->assertFalse($bodyPart0Item1Title);
    }

}