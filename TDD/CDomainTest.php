<?php

use PHPUnit\Framework\TestCase;

include 'bin/init.php';

/*
 * CDomain test case.
 */
class CDomainTest extends TestCase
{
  private $cDomain;
  protected function setUp()
  {
    parent::setUp();
    $this->cDomain = new CDomain();
  }
  protected function tearDown()
  {
    $this->cDomain = null;
    parent::tearDown();
  }

  public function testConstructor_Null()
  {
    $this->assertTrue( $this->cDomain->id<0 );
  }
  public function testSetFromId_0()
  {
    global $oDB;
    $this->cDomain->setFrom(0);
    $this->assertTrue( $this->cDomain->id===0 );
    $oDB->debug='';
  }
  public function testSetFromArray_Unformatted()
  {
    $this->cDomain->setFrom([]);
    $this->assertTrue( $this->cDomain->id<0 );
    $this->cDomain->setFrom(['a','b','c']);
    $this->assertTrue( $this->cDomain->id<0 );
  }
  public function testSetFromArray_Formatted()
  {
    // perfect format
    $this->cDomain->setFrom(['id'=>1,'title'=>'a title']);
    $this->assertTrue( $this->cDomain->id===1 );
    $this->assertTrue( $this->cDomain->title==='a title' );
    // incomplete format (alternate title is used)
    $this->cDomain->setFrom(['id'=>1,'title'=>'0']);
    $this->assertTrue( $this->cDomain->title==='domain-1' );
    $this->cDomain->setFrom(['id'=>1,'title'=>null]);
    $this->assertTrue( $this->cDomain->title==='domain-1' );
  }
  public function testCreate_TitleNull()
  {
    global $oDB;
    $result = $this->cDomain->create();
    $this->assertTrue( $result>1 );
    // delete domain after created
    $this->cDomain->delete($result);
    $oDB->debug='';
  }
  public function testCreate_TitleEmpty()
  {
    global $oDB;
    $this->expectException(Exception::class);
    $this->cDomain->create('0');
    $oDB->debug='';
  }
  public function testCreate_DuplicateTitle()
  {
    global $oDB;
    $this->expectException(Exception::class);
    $this->cDomain->create('Test');
    $oDB->debug='';
  }
  //   public function testDelete()
  //   {
  //     // not applicatble (delete/update works with not existing id)
  //   }
  public function testRename_DuplicateTitle()
  {
    global $oDB;
    $this->expectException(Exception::class);
    $this->cDomain->rename(0,'Test');
    $oDB->debug='';
  }
  public function testGetTitles()
  {
    global $oDB;
    $arr = $this->cDomain->getTitles();
    $this->assertTrue( is_array($arr) );
    $this->assertTrue( count($arr)>2 );
    $this->assertTrue( $arr[0]==='Staff domain' );
    $oDB->debug='';
  }
  //   public function testGetOwner()
  //   {
  //   not applicable
  //   }
  //   public function testMoveSections()
  //   {
  //     not applicable (update works with wrong id)
  //   }
  public function testGetPropertiesAll()
  {
    global $oDB;
    $arr = $this->cDomain->getPropertiesAll();
    $this->assertTrue( is_array($arr) );
    $this->assertTrue( count($arr)>1 );
    $oDB->debug='';
  }

  public function testGet_pSectionsVisible()
  {
    $arr = $this->cDomain->get_pSectionsVisible(1);
    $this->assertTrue( is_array($arr) );
    $this->assertTrue( count($arr)==3 );
  }
}

