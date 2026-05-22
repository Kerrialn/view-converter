<?php

namespace ViewConverter\Stimulus;

class StimulusTarget
{
    private string $name;

    private ?string $selector;

    public function __construct(string $name, ?string $selector)
    {
        $this->name = $name;
        $this->selector = $selector;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getSelector(): ?string
    {
        return $this->selector;
    }
}
