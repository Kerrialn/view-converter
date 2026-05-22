<?php

namespace ViewConverter\Printer\Stimulus;

use ViewConverter\Stimulus\StimulusController;
use ViewConverter\Stimulus\StimulusMethod;

class ControllerPrinter
{
    public function print(StimulusController $controller): string
    {
        $lines = [];

        $lines[] = "import { Controller } from \"@hotwired/stimulus\"";
        $lines[] = "";
        $lines[] = "export default class extends Controller {";

        $targets = $controller->getTargets();
        if ($targets !== []) {
            $names = array_map(fn ($t) => '"' . $t->getName() . '"', $targets);
            $lines[] = "  static targets = [" . implode(', ', $names) . "]";
            $lines[] = "";
        }

        $values = $controller->getValues();
        if ($values !== []) {
            $lines[] = "  static values = {";
            foreach ($values as $value) {
                $lines[] = "    " . $value->getName() . ": " . $value->getStimulusType() . ",";
            }
            $lines[] = "  }";
            $lines[] = "";
        }

        $lines[] = "  connect() {";
        $lines[] = "    // TODO: add any setup that runs when the controller connects to the DOM";
        $lines[] = "  }";

        foreach ($controller->getMethods() as $method) {
            $lines[] = "";
            $lines = array_merge($lines, $this->printMethod($method));
        }

        // Add stubs for any actions that have no corresponding method body
        $methodNames = array_map(fn ($m) => $m->getName(), $controller->getMethods());
        foreach ($controller->getActions() as $action) {
            if (! in_array($action->getMethodName(), $methodNames, true)) {
                $lines[] = "";
                $lines[] = "  " . $action->getMethodName() . "(event) {";
                $lines[] = "    // TODO: migrate handler for '" . $action->getEvent() . "' on " . $action->getTargetName() . "Target";
                $lines[] = "  }";
            }
        }

        if ($controller->hasDocumentListenerWarning()) {
            $lines[] = "";
            $lines[] = "  disconnect() {";
            $lines[] = "    // TODO: remove any document-level listeners that were added in connect()";
            $lines[] = "  }";
        }

        $lines[] = "}";

        return implode("\n", $lines) . "\n";
    }

    /**
     * @return string[]
     */
    private function printMethod(StimulusMethod $method): array
    {
        $params = implode(', ', $method->getParams());
        $lines = [];
        $lines[] = "  " . $method->getName() . "(" . $params . ") {";

        foreach (explode("\n", $method->getBody()) as $bodyLine) {
            $lines[] = "    " . $bodyLine;
        }

        $lines[] = "  }";

        return $lines;
    }
}
