<?php
namespace Tests\Feature;
use App\Http\Controllers\SalesOrderController;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PDO;
use Tests\TestCase;
/** No CRM DB access. 25 synthetic tied-timestamp sales orders, SQLite :memory: only. */
final class CrmSalesOrderPaginationSqliteIsolationTest extends TestCase {
 private const CONN='crm_a7762_temp';
 private ?string $oldConnection=null;
 private mixed $oldDefinition=null;
 private mixed $oldRequest=null;
 protected function setUp():void {
  parent::setUp();self::assertSame('testing',app()->environment());
  if(!in_array('sqlite',PDO::getAvailableDrivers(),true))$this->markTestSkipped('PDO SQLite required');
  $this->oldConnection=DB::getDefaultConnection();$this->oldDefinition=config('database.connections.'.self::CONN);$this->oldRequest=app('request');
  config()->set('database.connections.'.self::CONN,['driver'=>'sqlite','database'=>':memory:','prefix'=>'','foreign_key_constraints'=>false]);
  DB::purge(self::CONN);DB::setDefaultConnection(self::CONN);
  self::assertSame(':memory:',DB::connection()->getDatabaseName());
  $s=Schema::connection(self::CONN);
  $s->create('sales_orders',static function(Blueprint $t):void {$t->string('sales_order_id')->primary();$t->string('quotation_id')->nullable();$t->string('sales_order_number');$t->string('status');$t->timestamp('created_at')->nullable();$t->timestamp('updated_at')->nullable();});
  $s->create('quotations',static function(Blueprint $t):void {$t->string('quotation_id')->primary();$t->string('opportunity_id')->nullable();});
  $s->create('opportunities',static function(Blueprint $t):void {$t->string('opportunity_id')->primary();$t->string('customer_id')->nullable();});
  $s->create('customers',static function(Blueprint $t):void {$t->string('customer_id')->primary();$t->string('customer_name')->nullable();});
  $rows=[];
  for($i=1;$i<=25;$i++){$id=str_pad((string)$i,4,'0',STR_PAD_LEFT);$rows[]=['sales_order_id'=>'so'.$id,'quotation_id'=>null,'sales_order_number'=>'SO-'.$id,'status'=>'confirmed','created_at'=>'2026-11-20 12:00:00','updated_at'=>'2026-11-20 12:00:00'];}
  DB::connection()->table('sales_orders')->insert($rows);
 }
 protected function tearDown():void {
  if($this->oldConnection!==null){app()->instance('request',$this->oldRequest);DB::setDefaultConnection($this->oldConnection);DB::disconnect(self::CONN);DB::purge(self::CONN);config()->set('database.connections.'.self::CONN,$this->oldDefinition);}
  parent::tearDown();
 }
 public function test_25_same_timestamp_rows_paginate_10_10_5_without_duplicates():void {
  $names=[];$db=DB::connection(self::CONN);$db->flushQueryLog();$db->enableQueryLog();
  try {
   foreach([1,2,3] as $page){$request=Request::create('/sales-orders','GET',['page'=>$page]);app()->instance('request',$request);
    $view=app(SalesOrderController::class)->index($request);$p=$view->getData()['orders'];
    self::assertSame(25,$p->total());self::assertSame($page,$p->currentPage());self::assertSame([10,10,5][$page-1],$p->count());
    foreach($p->items() as $row)$names[]=$row->sales_order_number;
   }
   $log=$db->getQueryLog();
  }finally{$db->disableQueryLog();$db->flushQueryLog();}
  self::assertCount(25,$names);self::assertCount(25,array_unique($names));
  self::assertSame('SO-0025',$names[0]);self::assertSame('SO-0015',$names[10]);self::assertSame('SO-0001',$names[24]);
  $selects=0;foreach($log as $item){$sql=(string)($item['query']??'');self::assertDoesNotMatchRegularExpression('/^\s*(insert|update|delete|drop|create|alter|truncate)\b/i',$sql);if(preg_match('/^\s*select\b/i',$sql))$selects++;}
  self::assertLessThanOrEqual(9,$selects);self::assertSame(25,$db->table('sales_orders')->count());
 }
}
