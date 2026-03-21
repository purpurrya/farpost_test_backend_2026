<?php

namespace App\DTO;

use Symfony\Components\Validator\Constraints as Assert;

class DeleteMachineRequest
{
    #[Assert\NotNull (message: 'Field id is required')]
    #[Assert\Type(type: 'integer', message: 'Field id must be an integer')]
    #[Assert\Positive(message: 'Field id must be a positive number')]
    public int $id;

    public function __construct(int $id)
    {
        $this->id = $id;
    }
}