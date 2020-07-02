<?php

  namespace OCA\ComicMode\Migration;

  use Closure;
  use OCP\DB\ISchemaWrapper;
  use OCP\Migration\SimpleMigrationStep;
  use OCP\Migration\IOutput;

  class Version000001Date20200702224731 extends SimpleMigrationStep{

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options){
          /** @var ISchemaWrapper $schema */
          $schema = $schemaClosure();

          if (!$schema->hasTable('comicreadrecord')) {
              $table = $schema->createTable('comicreadrecord');
              $table->addColumn('id', 'integer', [
                  'autoincrement' => true,
                  'notnull' => true,
              ]);
              $table->addColumn('user_id', 'string', [
                  'notnull' => true,
                  'length' => 200,
              ]);
              $table->addColumn('file_id', 'integer', [
                'notnull' => true,
            ]);
              $table->addColumn('comic_dir', 'string', [
                'notnull' => true,
                'default' => ''
              ]);
              $table->addColumn('comic_name', 'string', [
                  'notnull' => true,
                  'default' => ''
              ]);
              $table->addColumn('chapter_name', 'string', [
                'notnull' => true,
                'default' => ''
              ]);
              $table->addColumn('last_read_time', 'bigint', [
                'notnull' => true,
              ]);

              $table->setPrimaryKey(['id']);
              $table->addIndex(['user_id'], 'comicreadrecord_user_id_index');
              $table->addIndex(['file_id'], 'comicreadrecord_file_id_index');
          }
          return $schema;
    }
  }

