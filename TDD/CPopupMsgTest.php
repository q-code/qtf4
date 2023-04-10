<?php

use PHPUnit\Framework\TestCase;

class CPopupMsg extends TestCase
{
  private $sPopupMsg;
  protected function setUp()
  {
    parent::setUp();
    $this->sPopupMsg = new Splash();
  }
  protected function tearDown()
  {
    $this->sPopupMsg = null;
    parent::tearDown();
  }

  public function testGetIconClass()
  {
    $result = $this->sPopupMsg::getIconClass();
    $this->assertTrue( $result==='fa fa-2x fa-check' );
  }
  public function testGetIconStyle()
  {
    $result = $this->sPopupMsg::getIconStyle();
    $this->assertTrue( $result==='color:green' );
  }
  public function testGetText()
  {
    $result = $this->sPopupMsg::getText();
    $this->assertTrue( $result==='' );
  }
}

