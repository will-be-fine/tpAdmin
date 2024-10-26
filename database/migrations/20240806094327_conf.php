<?php

use think\migration\Migrator;
use think\migration\db\Column;

class Conf extends Migrator
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        $table = $this->table('conf', [
            'engine'    => 'InnoDB',
            'comment'   => '系统配置',
            'collation' => 'utf8mb4_general_ci',
        ]);

        //删除表
        if ($table->exists()) {
            $table->drop();
        }

        $table
            ->addColumn('group', 'string', ['limit' => 255, 'default' => '', 'comment' => '配置分组'])
            ->addColumn('key', 'string', ['limit' => 255, 'default' => '', 'comment' => '配置key'])
            ->addColumn('value', 'text', ['comment' => '配置值'])
            ->addColumn('form_type', 'string', ['limit' => 255, 'default' => '', 'comment' => '配置表单元素'])
        ;

        $data = [
            '1'  => [
                'group'     => 'system.upload',
                'key'       => 'size',
                'value'     => '200MB',
                'form_type' => 'input',
            ],
            '2'  => [
                'group'     => 'system.upload',
                'key'       => 'mime',
                'value'     => 'png,jpg,gif,jpeg,doc,xls,pdf',
                'form_type' => 'input',
            ],
        ];

        $table->setData($data)->create();
    }

}
