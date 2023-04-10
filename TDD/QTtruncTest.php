<?php

use PHPUnit\Framework\TestCase;
require '../bin/lib_qt_txt.php';

class QTtruncTest extends TestCase
{
  protected function setUp()
  {
    parent::setUp();
  }
  protected function tearDown()
  {
    parent::tearDown();
  }

  public function testQTtrunc_emptytxt_returnsTxt()
  {
    $r = QTtrunc(' ');
    $this->assertEquals( ' ', $r );
    $r = QTtrunc('');
    $this->assertEquals( '', $r );
    $r = QTtrunc('0');
    $this->assertEquals( '0', $r );
  }
  public function testQTtrunc_0_throwException()
  {
    $this->expectException(Exception::class);
    QTtrunc(0);
  }
  public function testQTtrunc_longText_returns255char()
  {
    $input = str_pad('ABC',260);
    $r = QTtrunc( $input );
    $this->assertStringStartsWith( 'ABC', $r );
    $this->assertTrue( strlen($r)===255 );
    $this->assertStringEndsWith( '...', $r );
  }
  public function testQTtrunc_shortText_returnsEnds()
  {
    $r = QTtrunc('ABC',3,'...');
    $this->assertEquals( '...', $r );
  }
  public function testQTtrunc_arrayOf2_returnsArrayOf2()
  {
    $r = QTtrunc(['a','b']);
    $this->assertEquals( ['a','b'], $r );
  }
  public function testQTtrunc_arrayTruncToShort_returnsArrayOfEnds()
  {
    $input1 = 'ABCDEF';
    $input2 = 'ABCDEF';
    $r = QTtrunc([$input1,$input2], 4, '...');
    $this->assertEquals( ['A...','A...'], $r );
    $r = QTtrunc([$input1,$input2], 3, '...');
    $this->assertEquals( ['...','...'], $r );
  }
  public function testQTtrunc_arrayOfArray_returnsArrayOfArray()
  {
    $r = QTtrunc(['A',0,'AAAAA',['B',0,'BBBBB']], 4, '...');
    $this->assertEquals( ['A',0,'A...',['B',0,'B...']], $r );
  }
  public function testQTtrunc_arrayOfInt_returnsArrayOfInt()
  {
    $r = QTtrunc([1,2,3,[1,2,3]], 4, '...');
    $this->assertEquals( [1,2,3,[1,2,3]], $r );
  }
}
