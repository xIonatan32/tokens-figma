<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

class FigmaNode extends Entity
{
    protected array $_accessible = [
        'figma_file_id' => true,
        'node_id' => true,
        'name' => true,
        'type' => true,
        'raw_data' => true,
        'figma_file' => true,
    ];
}
