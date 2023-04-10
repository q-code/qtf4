<?php

use PHPUnit\Framework\TestCase;
require '../bin/lib_qt_txt.php';

class QTdropaccentTest extends TestCase
{
  protected function setUp()
  {
    parent::setUp();
  }
  protected function tearDown()
  {
    parent::tearDown();
  }

  public function testQTdropaccent_emptytxt_returnsTxt()
  {
    $r = QTdropaccent(' ');
    $this->assertEquals( ' ', $r );
    $r = QTdropaccent('');
    $this->assertEquals( '', $r );
    $r = QTdropaccent('0');
    $this->assertEquals( '0', $r );
  }
  public function testQTdropaccent_0_throwException()
  {
    $this->expectException(Exception::class);
    QTdropaccent(0);
  }
  public function testQTdropaccent_A_returnsA()
  {
    $r = QTdropaccent('Á');
    $this->assertEquals( 'A', $r );
  }
  public function testQTdropaccent_arrayOf2_returnsArrayOf2()
  {
    $r = QTdropaccent(['a','b'],'"');
    $this->assertEquals( ['a','b'], $r );
  }
  public function testQTdropaccent_arrayOfStringInt_StringsInt()
  {
    $r = QTdropaccent(['ä',0],'"');
    $this->assertEquals( ['a',0], $r );
  }
  public function testQTdropaccent_SubArray_SubArray()
  {
    $r = QTdropaccent( ['é',0,['è','ô',true]] );
    $this->assertEquals( ['e',0,['e','o',true]], $r );
  }
}
