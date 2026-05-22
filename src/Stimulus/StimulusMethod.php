<?php

namespace ViewConverter\Stimulus;

class StimulusMethod
{
    private string $name;

    /** @var string[] */
    private array $params;

    private string $body;

    /** @param string[] $params */
    public function __construct(string $name, array $params, string $body)
    {
        $this->name = $name;
        $this->params = $params;
        $this->body = $body;
    }

    public function getName(): string
    {
        return $this->name;
    }

    /** @return string[] */
    public function getParams(): array
    {
        return $this->params;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
