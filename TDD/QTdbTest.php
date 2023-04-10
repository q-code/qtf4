<?php

use PHPUnit\Framework\TestCase;
require '../bin/lib_qt_txt.php';
define('QT_CONVERT_AMP',false);

class QTdbTest extends TestCase
{

  protected function setUp()
  {
    parent::setUp();
  }
  protected function tearDown()
  {
    parent::tearDown();
  }

  /**
   * Tests QTdb()
   */
  public function testQTdb_emptyTxt_returnsEmpty()
  {
    $r = QTdb('');
    $this->assertEquals( '', $r );
  }
  public function testQTdb_txt_returnsTxt()
  {
    $r = QTdb('abc');
    $this->assertEquals( 'abc', $r );
  }
  public function testQTdb_txtWithApo_returnsTxtWith39()
  {
    $r = QTdb('ab\'c');
    $this->assertEquals( 'ab&#39;c', $r );
  }
  public function testQTdb_txtWith2Apo_returnsTxtWith39()
  {
    $r = QTdb('ab\'\'c');
    $this->assertEquals( 'ab&#39;&#39;c', $r );
  }
  public function testQTdb_txtWithQuote_returnsTxtWith34()
  {
    $r = QTdb('a&b"c');
    $this->assertEquals( 'a&b&#34;c', $r );
  }
  public function testQTdb_txtWithAmpAndQuote_returnsTxtWith38And34()
  {
    $r = QTdb('ab&euro;"c',true,true);
    $this->assertEquals( 'ab&#38;euro;&#34;c', $r );
  }
  public function testQTdbdecode_emptyTxt_returnsEmpty()
  {
    $r = QTdbdecode('');
    $this->assertEquals( '', $r );
  }
  public function testQTdbdecode_txt_returnsTxt()
  {
    $r = QTdbdecode('abc');
    $this->assertEquals( 'abc', $r );
  }
  public function testQTdbdecode_txtWith39_returnsTxtWithApo()
  {
    $r = QTdbdecode('ab&#39;c');
    $this->assertEquals( 'ab\'c', $r );
  }
  public function testQTdbdecode_txtWith34_returnsTxtWithQuote()
  {
    $r = QTdbdecode('a&b&#34;c');
    $this->assertEquals( 'a&b"c', $r );
  }
  public function testQTdbdecode_txtWith38And34_returnsTxtWithAmpAndQuote()
  {
    $r = QTdbdecode( 'ab&#38;euro;&#34;c',true,true);
    $this->assertEquals( 'ab&euro;"c', $r );
  }
}

