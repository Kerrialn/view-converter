<?php

namespace ViewConverter\Converter;

use ViewConverter\Stimulus\StimulusAction;
use ViewConverter\Stimulus\StimulusController;
use ViewConverter\Stimulus\StimulusMethod;
use ViewConverter\Stimulus\StimulusTarget;
use ViewConverter\Stimulus\StimulusValue;

class JsToStimulusConverter
{
    /**
     * @param array<string, mixed> $parsed
     */
    public function convert(array $parsed): StimulusController
    {
        $controller = new StimulusController(
            $parsed['controllerName'] ?? 'my-controller',
            $parsed['rootSelector'] ?? null
        );

        foreach ($parsed['targets'] ?? [] as $target) {
            $controller->addTarget(new StimulusTarget(
                $target['name'],
                $target['selector'] ?? null
            ));
        }

        foreach ($parsed['values'] ?? [] as $value) {
            $controller->addValue(new StimulusValue(
                $value['name'],
                $value['stimulusType'] ?? 'String'
            ));
        }

        foreach ($parsed['actions'] ?? [] as $action) {
            $controller->addAction(new StimulusAction(
                $action['event'],
                $action['targetName'],
                $action['methodName']
            ));
        }

        foreach ($parsed['methods'] ?? [] as $method) {
            $controller->addMethod(new StimulusMethod(
                $method['name'],
                $method['params'] ?? ['event'],
                $method['body'] ?? ''
            ));
        }

        foreach ($parsed['warnings'] ?? [] as $warning) {
            $controller->addWarning($warning);
        }

        return $controller;
    }
}
