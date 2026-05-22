<?php

namespace ViewConverter\Stimulus;

class StimulusValue
{
    private string $name;

    private string $stimulusType;

    public function __construct(string $name, string $stimulusType)
    {
        $this->name = $name;
        $this->stimulusType = $stimulusType;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStimulusType(): string
    {
        return $this->stimulusType;
    }
}
