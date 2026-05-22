<?php

namespace ViewConverter\Stimulus;

class StimulusAction
{
    private string $event;

    private string $targetName;

    private string $methodName;

    public function __construct(string $event, string $targetName, string $methodName)
    {
        $this->event = $event;
        $this->targetName = $targetName;
        $this->methodName = $methodName;
    }

    public function getEvent(): string
    {
        return $this->event;
    }

    public function getTargetName(): string
    {
        return $this->targetName;
    }

    public function getMethodName(): string
    {
        return $this->methodName;
    }
}
