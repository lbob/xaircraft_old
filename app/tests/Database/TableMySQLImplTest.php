<?php

/**
 * Class TableMySQLImplTest
 *
 * @author lbob created at 2015/1/29 10:25
 */
class TableMySQLImplTest extends DbBaseTest {

    public function testCreate()
    {
        $tableQuery = \Xaircraft\DB::schema()->create("post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->comment("ID");
        });
        $query = $tableQuery->toString();
        $this->assertEquals("CREATE TABLE IF NOT EXISTS `xaircraft`.`x_post` ( `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID' )", $query);

        $tableQuery = \Xaircraft\DB::schema()->create("post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->comment("ID");
            $table->column()->string("title", 100)->comment("标题");
        });
        $query = $tableQuery->toString();
        $this->assertEquals("CREATE TABLE IF NOT EXISTS `xaircraft`.`x_post` ( `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',`title` VARCHAR(100) NOT NULL COMMENT '标题' )", $query);

        $tableQuery = \Xaircraft\DB::schema()->create("post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->comment("ID")->primaryKey();
            $table->column()->string("title", 100)->comment("标题");
        });
        $query = $tableQuery->toString();
        $this->assertEquals("CREATE TABLE IF NOT EXISTS `xaircraft`.`x_post` ( `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',`title` VARCHAR(100) NOT NULL COMMENT '标题',PRIMARY KEY(`id`) )", $query);

        $tableQuery = \Xaircraft\DB::schema()->create("post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->comment("ID")->primaryKey()->unique();
            $table->column()->string("title", 100)->comment("标题");
        });
        $query = $tableQuery->toString();
        $this->assertEquals("CREATE TABLE IF NOT EXISTS `xaircraft`.`x_post` ( `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',`title` VARCHAR(100) NOT NULL COMMENT '标题',PRIMARY KEY(`id`),UNIQUE INDEX `id_UNIQUE` (`id` ASC) )", $query);

        $tableQuery = \Xaircraft\DB::schema()->create("post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->comment("ID")->primaryKey()->unique();
            $table->column()->string("title", 100)->comment("标题");
            $table->column()->string("author", 45)->primaryKey();
        });
        $query = $tableQuery->toString();
        $this->assertEquals("CREATE TABLE IF NOT EXISTS `xaircraft`.`x_post` ( `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',`title` VARCHAR(100) NOT NULL COMMENT '标题',`author` VARCHAR(45) NOT NULL,PRIMARY KEY(`id`,`author`),UNIQUE INDEX `id_UNIQUE` (`id` ASC) )", $query);
    }

    public function testModify()
    {
        $tableQuery = \Xaircraft\DB::schema()->table("post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->comment("ID");
        });
        $query = $tableQuery->toString();
        $this->assertEquals("ALTER TABLE `xaircraft`.`x_post` CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID'", $query);

        $tableQuery = \Xaircraft\DB::schema()->table("post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->comment("ID");
            $table->column()->string('title', 100)->comment("<!--RANGE:(1,50)-->");
        });
        $query = $tableQuery->toString();
        $this->assertEquals("ALTER TABLE `xaircraft`.`x_post` CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',CHANGE COLUMN `title` `title` VARCHAR(100) NOT NULL COMMENT '<!--RANGE:(1,50)-->'", $query);

        $tableQuery = \Xaircraft\DB::schema()->table("post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->comment("ID");
            $table->column()->string('title', 100)->comment("<!--RANGE:(1,50)-->");
            $table->renameColumn("keyword", "keywords");
        });
        $query = $tableQuery->toString();
        $this->assertEquals("ALTER TABLE `xaircraft`.`x_post` CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',CHANGE COLUMN `title` `title` VARCHAR(100) NOT NULL COMMENT '<!--RANGE:(1,50)-->',CHANGE COLUMN `keyword` `keywords` VARCHAR(100) NULL DEFAULT NULL COMMENT ''", $query);

        $tableQuery = \Xaircraft\DB::schema()->table("post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->comment("ID");
            $table->column()->string('title', 100)->comment("<!--RANGE:(1,50)-->");
            $table->renameColumn("keyword", "keywords");
            $table->column()->tinyInteger("view_count")->length(1)->unsigned()->defaultValue(0)->comment("查看次数")->after("update_at");
        });
        $query = $tableQuery->toString();
        $this->assertEquals("ALTER TABLE `xaircraft`.`x_post` CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',CHANGE COLUMN `title` `title` VARCHAR(100) NOT NULL COMMENT '<!--RANGE:(1,50)-->',CHANGE COLUMN `keyword` `keywords` VARCHAR(100) NULL DEFAULT NULL COMMENT '',ADD COLUMN `view_count` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '查看次数' AFTER `update_at`", $query);
    }

    public function testHasTable()
    {
        $result = \Xaircraft\DB::schema()->hasTable("post")->execute();
        $this->assertTrue($result);
    }

    public function testHasColumn()
    {
        $result = \Xaircraft\DB::schema()->hasColumn("post", "id")->execute();
        $this->assertTrue($result);
    }

    public function testDropColumn()
    {
        $tableQuery = \Xaircraft\DB::schema()->table("post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->comment("ID");
            $table->column()->string('title', 100)->comment("<!--RANGE:(1,50)-->");
            $table->renameColumn("keyword", "keywords");
            $table->column()->tinyInteger("view_count")->length(1)->unsigned()->defaultValue(0)->comment("查看次数")->after("update_at");
            $table->dropColumn(array("id", "title"));
        });
        $query = $tableQuery->toString();
        $this->assertEquals("ALTER TABLE `xaircraft`.`x_post` DROP COLUMN `id`,DROP COLUMN `title`,CHANGE COLUMN `id` `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID',CHANGE COLUMN `title` `title` VARCHAR(100) NOT NULL COMMENT '<!--RANGE:(1,50)-->',CHANGE COLUMN `keyword` `keywords` VARCHAR(100) NULL DEFAULT NULL COMMENT '',ADD COLUMN `view_count` TINYINT(1) UNSIGNED NOT NULL DEFAULT 0 COMMENT '查看次数' AFTER `update_at`", $query);
    }

    public function testExecute()
    {
        \Xaircraft\DB::schema()->dropIfExists("test_post")->execute();

        \Xaircraft\DB::schema()->create("test_post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->unique()->primaryKey();
            $table->column()->string("title", 10)->unique();
        })->execute();

        $this->assertTrue(\Xaircraft\DB::table("test_post")->count()->execute() === 0);

        $result = \Xaircraft\DB::schema()->table("test_post", function(\Xaircraft\Database\Table $table) {
            $table->column()->increments("id")->unsigned()->unique()->primaryKey();
            $table->renameColumn("title", "content");
        })->execute();

        $this->assertTrue($result !== false);
        $this->assertTrue(\Xaircraft\DB::table("test_post")->select("titles")->count()->execute() === 0);
    }
}

 