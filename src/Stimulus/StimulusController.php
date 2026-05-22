<?php

namespace ViewConverter\Stimulus;

class StimulusController
{
    private string $name;

    private ?string $rootSelector;

    /**
     * @var StimulusTarget[]
     */
    private array $targets = [];

    /**
     * @var StimulusAction[]
     */
    private array $actions = [];

    /**
     * @var StimulusValue[]
     */
    private array $values = [];

    /**
     * @var StimulusMethod[]
     */
    private array $methods = [];

    /**
     * @var string[]
     */
    private array $warnings = [];

    public function __construct(string $name, ?string $rootSelector)
    {
        $this->name = $name;
        $this->rootSelector = $rootSelector;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getRootSelector(): ?string
    {
        return $this->rootSelector;
    }

    public function addTarget(StimulusTarget $target): void
    {
        $this->targets[] = $target;
    }

    public function addAction(StimulusAction $action): void
    {
        $this->actions[] = $action;
    }

    public function addValue(StimulusValue $value): void
    {
        $this->values[] = $value;
    }

    public function addMethod(StimulusMethod $method): void
    {
        $this->methods[] = $method;
    }

    public function addWarning(string $warning): void
    {
        $this->warnings[] = $warning;
    }

    /**
     * @return StimulusTarget[]
     */
    public function getTargets(): array
    {
        return $this->targets;
    }

    /**
     * @return StimulusAction[]
     */
    public function getActions(): array
    {
        return $this->actions;
    }

    /**
     * @return StimulusValue[]
     */
    public function getValues(): array
    {
        return $this->values;
    }

    /**
     * @return StimulusMethod[]
     */
    public function getMethods(): array
    {
        return $this->methods;
    }

    /**
     * @return string[]
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    public function hasDocumentListenerWarning(): bool
    {
        foreach ($this->warnings as $warning) {
            if (strpos($warning, 'Document-level') !== false) {
                return true;
            }
        }
        return false;
    }
}
