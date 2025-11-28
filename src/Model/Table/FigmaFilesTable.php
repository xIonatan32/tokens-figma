<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class FigmaFilesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('figma_files');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->hasMany('FigmaNodes', [
            'foreignKey' => 'figma_file_id',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('file_key')
            ->maxLength('file_key', 255)
            ->requirePresence('file_key', 'create')
            ->notEmptyString('file_key');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('thumbnail_url')
            ->maxLength('thumbnail_url', 1024)
            ->allowEmptyString('thumbnail_url');

        return $validator;
    }
}
