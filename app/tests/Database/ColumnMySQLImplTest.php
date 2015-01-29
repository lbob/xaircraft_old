<?php

/**
 * Class ColumnMySQLImplTest
 *
 * @author lbob created at 2015/1/28 11:45
 */
class ColumnMySQLImplTest extends BaseTest {

    public function testToString()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->increments('id');
        $this->assertEquals("`id` INT(10) NOT NULL AUTO_INCREMENT", $column->toString());
    }

    public function testUnsigned()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->increments('id')->unsigned();
        $this->assertEquals("`id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT", $column->toString());
    }

    public function testBigIncrements()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->bigIncrements('id')->unsigned();
        $this->assertEquals("`id` BIGINT(10) UNSIGNED NOT NULL AUTO_INCREMENT", $column->toString());
    }

    public function testBigInteger()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->bigInteger('id')->unsigned();
        $this->assertEquals("`id` BIGINT(10) UNSIGNED NOT NULL", $column->toString());
    }

    public function testBinary()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->binary('id', 10)->nullable();
        $this->assertEquals("`id` BINARY(10) NULL DEFAULT NULL", $column->toString());
    }

    public function testBoolean()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->boolean('id')->nullable();
        $this->assertEquals("`id` TINYINT(1) NULL DEFAULT NULL", $column->toString());
    }

    public function testChar()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->char('id')->nullable();
        $this->assertEquals("`id` CHAR NULL DEFAULT NULL", $column->toString());
        $column->length(10);
        $this->assertEquals("`id` CHAR(10) NULL DEFAULT NULL", $column->toString());
    }

    public function testDate()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->date('id')->nullable();
        $this->assertEquals("`id` DATE NULL DEFAULT NULL", $column->toString());
    }

    public function testDateTime()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->dateTime('id')->nullable();
        $this->assertEquals("`id` DATETIME NULL DEFAULT NULL", $column->toString());
    }

    public function testDecimal()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->decimal('id', 10, 2)->nullable();
        $this->assertEquals("`id` DECIMAL(10,2) NULL DEFAULT NULL", $column->toString());
    }

    public function testDouble()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->double('id', 10, 2)->nullable();
        $this->assertEquals("`id` DOUBLE(10,2) NULL DEFAULT NULL", $column->toString());
    }

    public function testEnum()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->enum('id', array("test", "test2"))->nullable();
        $this->assertEquals("`id` ENUM('test','test2') NULL DEFAULT NULL", $column->toString());
    }

    public function testFloat()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->float('id', 10, 2)->nullable();
        $this->assertEquals("`id` FLOAT(10,2) NULL DEFAULT NULL", $column->toString());
    }

    public function testInteger()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->integer('id')->nullable();
        $this->assertEquals("`id` INT(11) NULL DEFAULT NULL", $column->toString());
    }

    public function testLongText()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->longText('id')->nullable();
        $this->assertEquals("`id` LONGTEXT NULL DEFAULT NULL", $column->toString());
    }

    public function testMediumInteger()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->mediumInteger('id')->nullable();
        $this->assertEquals("`id` MEDIUMINT(9) NULL DEFAULT NULL", $column->toString());
    }

    public function testSmallInteger()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->smallInteger('id')->nullable();
        $this->assertEquals("`id` SMALLINT(6) NULL DEFAULT NULL", $column->toString());
    }

    public function testTinyInteger()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->tinyInteger('id')->nullable();
        $this->assertEquals("`id` TINYINT(1) NULL DEFAULT NULL", $column->toString());
    }

    public function testSoftDeletes()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->softDeletes()->nullable();
        $this->assertEquals("`delete_at` INT(10) NULL DEFAULT 0", $column->toString());
    }

    public function testString()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->string('id', 45)->nullable();
        $this->assertEquals("`id` VARCHAR(45) NULL DEFAULT NULL", $column->toString());
    }

    public function testText()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->text('id')->nullable();
        $this->assertEquals("`id` TEXT NULL DEFAULT NULL", $column->toString());
    }

    public function testTime()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->time('id')->nullable();
        $this->assertEquals("`id` TIME NULL DEFAULT NULL", $column->toString());
    }

    public function testAfter()
    {
        $column = new \Xaircraft\Database\ColumnMySQLImpl();
        $column->time('id')->nullable()->after("name");
        $this->assertEquals("`id` TIME NULL DEFAULT NULL AFTER `name`", $column->toString());
    }
}

 