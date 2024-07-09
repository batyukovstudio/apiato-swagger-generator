<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values\DocBlocks;

use Apiato\Core\Abstracts\Controllers\Controller;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\RouteValue;
use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;

class ApiSummaryValue extends Value
{
    protected string $text;

    public function getText(): string
    {
        return $this->text;
    }

    public function setText(string $text): self
    {
        $this->text = $text;
        return $this;
    }

}
