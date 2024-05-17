<?php

namespace Batyukovstudio\ApiatoSwaggerGenerator\Values;

use Batyukovstudio\ApiatoSwaggerGenerator\Values\Abstract\Value;
use Illuminate\Support\Collection;

class OpenAPIValue extends Value
{
    protected string $openapi;
    protected OpenAPIInfoValue $info;
    protected Collection $servers;
    protected Collection $paths;
    protected Collection $tags;

    public function getPaths(): Collection
    {
        return $this->paths;
    }

    public function setPaths(Collection $paths): self
    {
        $this->paths = $paths;
        return $this;
    }

    public function getOpenapi(): string
    {
        return $this->openapi;
    }

    public function setOpenapi(string $openapi): self
    {
        $this->openapi = $openapi;
        return $this;
    }

    public function getInfo(): OpenAPIInfoValue
    {
        return $this->info;
    }

    public function setInfo(OpenAPIInfoValue $info): self
    {
        $this->info = $info;
        return $this;
    }

    public function getServers(): Collection
    {
        return $this->servers;
    }

    public function setServers(Collection $servers): self
    {
        $this->servers = $servers;
        return $this;
    }

    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function setTags(Collection $tags): self
    {
        $this->tags = $tags;
        return $this;
    }

}