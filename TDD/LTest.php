<?php

/* this test must be run with the file in the root directory */
/**
 * @var array $L
 */
use PHPUnit\Framework\TestCase;

include 'config/config_db.php';
include 'config/config_cst.php';
include 'language/fr/qtf_main.php';
require 'TDD/func_L.php';

$L['level1']['level2']['Value'] = 'Valeur'; // for test

class LTest extends TestCase
{
  protected function setUp()
  {
    parent::setUp();
  }
  protected function tearDown()
  {
    parent::tearDown();
  }

  public function testL_emptyKey_throwException()
  {
    $this->expectException(Exception::class);
    L('');
  }
  public function testL_0Key_returns0()
  {
    //$this->expectException(Exception::class);
    $r = L('0');
    $this->assertEquals( '0', $r );
  }
  public function testL_n_true_returnsKey()
  {
    $this->expectException(Exception::class);
    L('0', true);
    //$this->assertEquals( '0', $r );
  }
  public function testL_n_string_throwException()
  {
    $this->expectException(Exception::class);
    L('0', '0');
  }
  public function testL_n_negative_throwException()
  {
    //$this->expectException(Exception::class);
    $r = L('0', -1);
    $this->assertEquals( '-1 0', $r );
  }
  public function testL_missingKey_returnsKey()
  {
    $r = L('foo');
    $this->assertEquals( 'foo', $r );
  }
  public function testL_validKey_returnsWord()
  {
    $r = L('Y');
    $this->assertEquals( 'Oui', $r );
  }
  public function testL_validLowercaseKey_returnsLowercaseWord()
  {
    $r = L('y');
    $this->assertEquals( 'oui', $r );
  }
  public function testL_CaseMissmachKey_returnsKey()
  {
    $r = L('CanCel');
    $this->assertEquals( 'CanCel', $r );
  }
  public function testL_wrong2Keys_return2Keys()
  {
    $r = L('.ab');
    $this->assertEquals( '.ab', $r );
    $r = L('a.0');
    $this->assertEquals( 'a.0', $r );
  }
  public function testL_valid2Keys_returnWord()
  {
    $r = L('dateSQL.January');
    $this->assertEquals( 'Janvier', $r );
  }
  public function testL_valid2KeysLowerCase_returnLowercaseWord()
  {
    $r = L('dateSQL.january');
    $this->assertEquals( 'janvier', $r );
  }
  public function testL_wrong1String1IntKeys_return2Keys()
  {
    $r = L('dateMMM.0');
    $this->assertEquals( 'dateMMM.0', $r );
  }
  public function testL_valid1String1IntKeys_returnWord()
  {
    $r = L('dateMMM.1');
    $this->assertEquals( 'Janvier', $r );
  }
  public function testL_getArray_returnsArray()
  {
    $r = L('dateD.*');
    $this->assertTrue( is_array($r) );
    $this->assertTrue( count($r)==7 );
  }
  public function testL_getArray3_returnsArray3()
  {
    $r = L('level1.level2.Value');
    $this->assertEquals( 'Valeur', $r );
  }
  public function testL_PluralNegative_returnNegative()
  {
    $r = L('0', -1);
    $this->assertEquals( '-1 0', $r );
  }
  public function testL_Plural01_returnsWord()
  {
    $r = L('Item',0,false,false);
    $this->assertEquals( 'Sujet', $r );
    $r = L('Item',1,false,false);
    $this->assertEquals( 'Sujet', $r );
  }
  public function testL_Plural2_returnsWordPlural()
  {
    $r = L('Item',2,false,false);
    $this->assertEquals( 'Sujets', $r );
  }
  public function testL_PluralLowercase_returnsLowercasePluralWord()
  {
    $r = L('item',2,false,false);
    $this->assertEquals( 'sujets', $r );
  }
  public function testL_ShowNumber_returnsShowNumber()
  {
    $r = L('Item',1);
    $this->assertEquals( '1 Sujet', $r );
    $r = L('Item',2);
    $this->assertEquals( '2 Sujets', $r );
    $r = L('item',2);
    $this->assertEquals( '2 sujets', $r );
    $r = L('a',2);
    $this->assertEquals( '2 a', $r );
  }
  public function testL_ExceptionCode_returnsExceptionDecoded()
  {
    $r = L('Unknown_error');
    $this->assertEquals( 'Unknown error', $r );
    $r = L('E_404');
    $this->assertEquals( 'error: 404', $r );
  }
}
