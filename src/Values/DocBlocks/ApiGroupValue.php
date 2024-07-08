<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks;

use Apiato\Core\Abstracts\Controllers\Controller;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\RouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class ApiGroupValue extends Value
{
    protected string $groupName;

    public function getGroupName(): string
    {
        return $this->groupName;
    }

    public function setGroupName(string $groupName): self
    {
        $this->groupName = $groupName;
        return $this;
    }

}
