<?php

use PHPUnit\Framework\TestCase;

include 'bin/init.php';

class CLangTest extends TestCase
{
  private $sLang;
  protected function setUp()
  {
    parent::setUp();
    $this->sLang = new SLang();
  }
  protected function tearDown()
  {
    $this->sLang = null;
    parent::tearDown();
  }
  public function testAdd()
  {
    global $oDB;
    $result = $this->sLang::add('test','en','t1','testname');
    $this->assertTrue( $result );
    $oDB->debug='';
  }
  public function testDelete()
  {
    global $oDB;
    $result = $this->sLang::delete('test','t1');
    $this->assertTrue( $result===1 || $result===true );
    $oDB->debug='';
  }
  public function testGet_AllFr()
  {
    global $oDB;
    $result = $this->sLang::get('domain','fr','*');
    $this->assertTrue( is_array($result) );
    $this->assertTrue( count($result)>0 );
    $oDB->debug='';
  }
  public function testGet_AllLang()
  {
    global $oDB;
    $result = $this->sLang::get('sec','*','s0');
    $this->assertTrue( is_array($result) );
    $this->assertTrue( count($result)==2 );
    $oDB->debug='';
  }
  public function testTrans_NoTranslation()
  {
    $result = $this->sLang::trans('test','t','');
    $this->assertTrue( $result==='(unknown object test)' );
    $result = $this->sLang::trans('domain','d999','');
    $this->assertTrue( $result==='(domain d999)' );
    $result = $this->sLang::trans('domain','d999','Domain 999');
    $this->assertTrue( $result==='Domain 999' );
  }
}