<?php

use PHPUnit\Framework\TestCase;

include 'bin/init.php';

/**
 * CHtml test case.
 */
class CHtmlTest extends TestCase
{

  private $cHtml;
  protected function setUp()
  {
    parent::setUp();
    $this->cHtml = new CHtml();
  }

  protected function tearDown()
  {
    $this->cHtml = null;
    parent::tearDown();
  }

  public function testGetBody_NoAttr()
  {
    $this->assertStringStartsWith( '<body>', $this->cHtml->getBody() );
  }

  /**
   * Tests CHtml->pageBox()
   */
  public function testPageBox()
  {
    // TODO Auto-generated CHtmlTest->testPageBox()
    $this->markTestIncomplete("pageBox test not implemented");

    $this->cHtml->pageBox(/* parameters */);
  }

  /**
   * Tests CHtml->pageMsgAdm()
   */
  public function testPageMsgAdm()
  {
    // TODO Auto-generated CHtmlTest->testPageMsgAdm()
    $this->markTestIncomplete("pageMsgAdm test not implemented");

    $this->cHtml->pageMsgAdm(/* parameters */);
  }

  /**
   * Tests CHtml->pageMsg()
   */
  public function testPageMsg()
  {
    // TODO Auto-generated CHtmlTest->testPageMsg()
    $this->markTestIncomplete("pageMsg test not implemented");

    $this->cHtml->pageMsg(/* parameters */);
  }
}

