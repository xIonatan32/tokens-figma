<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class FigmaFile extends Entity
{
    protected array $_accessible = [
        'file_key' => true,
        'name' => true,
        'thumbnail_url' => true,
        'created' => true,
        'modified' => true,
        'figma_nodes' => true,
    ];
}
