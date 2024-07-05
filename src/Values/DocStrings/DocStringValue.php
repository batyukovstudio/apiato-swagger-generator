<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values;

use Apiato\Core\Abstracts\Controllers\Controller;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\RouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class DocStringValue extends Value
{
    protected ?ApiGroupValue $apiGroup;
    protected ?ApiDescriptionValue $apiDescription;

    public function getApiGroup(): ?ApiGroupValue
    {
        return $this->apiGroup;
    }

    public function setApiGroup(?ApiGroupValue $apiGroup): self
    {
        $this->apiGroup = $apiGroup;
        return $this;
    }

    public function getApiDescription(): ?ApiDescriptionValue
    {
        return $this->apiDescription;
    }

    public function setApiDescription(?ApiDescriptionValue $apiDescription): self
    {
        $this->apiDescription = $apiDescription;
        return $this;
    }

}
