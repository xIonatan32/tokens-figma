<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

class FigmaNodesTable extends Table
{
    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('figma_nodes');
        $this->setDisplayField('name');
        $this->setPrimaryKey('id');

        $this->belongsTo('FigmaFiles', [
            'foreignKey' => 'figma_file_id',
            'joinType' => 'INNER',
        ]);
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->integer('figma_file_id')
            ->requirePresence('figma_file_id', 'create')
            ->notEmptyString('figma_file_id');

        $validator
            ->scalar('node_id')
            ->maxLength('node_id', 255)
            ->requirePresence('node_id', 'create')
            ->notEmptyString('node_id');

        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->allowEmptyString('name');

        $validator
            ->scalar('type')
            ->maxLength('type', 50)
            ->allowEmptyString('type');

        return $validator;
    }
}
