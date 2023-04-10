<?php

use PHPUnit\Framework\TestCase;
require '../bin/lib_qt_txt.php';

class QTquotedTest extends TestCase
{
  protected function setUp()
  {
    parent::setUp();
  }
  protected function tearDown()
  {
    parent::tearDown();
  }

  public function testQTquoted_emptytxt_returnsdefaultquotes()
  {
    $r = QTquoted('','"');
    $this->assertEquals( '""', $r );
  }
  public function testQTquoted_emptyquote_throwexception()
  {
    $this->expectException(Exception::class);
    QTquoted('a','');
  }
  public function testQTquoted_text_returnquoted()
  {
    $r = QTquoted('a','"');
    $this->assertEquals( '"a"', $r );
  }
  public function testQTquoted_NotStringOrArrayAndNumericFalse_throwException()
  {
    $this->expectException(Exception::class);
    QTquoted(0,'"');
  }
  public function testQTquoted_arrayOf2_returnsArrayOf2()
  {
    $r = QTquoted(['a','b'],'"');
    $this->assertEquals( ['"a"','"b"'], $r );
  }
  public function testQTquoted_arrayOfStringInt_QuoteOnlyStrings()
  {
    $r = QTquoted(['a',0],'"');
    $this->assertEquals( ['"a"',0], $r );
  }
  public function testQTquoted_OpenClosedQuote_OpenCloseQuotes()
  {
    $r = QTquoted(['a',0],'+','-');
    $this->assertEquals( ['+a-',0], $r );
  }
  public function testQTquoted_OpenNoClose_OpenCloseQuotes()
  {
    $r = QTquoted(['a',0],'+');
    $this->assertEquals( ['+a+',0], $r );
  }
  public function testQTquoted_SubArray_SubStringQuoted()
  {
    $r = QTquoted(['a',0,['a','b',true]],'+');
    $this->assertEquals( ['+a+',0,['+a+','+b+',true]], $r );
  }
  public function testQTquoted_Allow1Number_NumberQuoted()
  {
    $r = QTquoted(1,'+','',true);
    $this->assertEquals( '+1+', $r );
  }
  public function testQTquoted_AllowNumber_SubStringQuoted()
  {
    $r = QTquoted(['a',0,['a','b',3,true,[null,'x','y']]],'+','',true);
    $this->assertEquals( ['+a+','+0+',['+a+','+b+','+3+',true,[null,'+x+','+y+']]], $r );
  }
}
