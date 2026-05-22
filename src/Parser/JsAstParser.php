<?php

namespace ViewConverter\Parser;

use RuntimeException;

class JsAstParser
{
    private string $nodeDir;

    private string $parseScript;

    public function __construct()
    {
        $this->nodeDir = dirname(__DIR__, 2) . '/node';
        $this->parseScript = $this->nodeDir . '/parse.mjs';
    }

    /**
     * @return array<string, mixed>
     */
    public function parse(string $jsFilePath): array
    {
        $this->ensureNodeModules();

        $command = sprintf(
            'node %s %s',
            escapeshellarg($this->parseScript),
            escapeshellarg($jsFilePath)
        );

        $output = [];
        $returnCode = 0;
        exec($command . ' 2>&1', $output, $returnCode);

        $json = implode("\n", $output);

        if ($json === '') {
            throw new RuntimeException('Node.js parser produced no output. Is Node.js installed?');
        }

        $data = json_decode($json, true);

        if ($data === null) {
            throw new RuntimeException('Invalid JSON from parser: ' . $json);
        }

        if (isset($data['error'])) {
            throw new RuntimeException('JS parse error: ' . $data['error']);
        }

        return $data;
    }

    private function ensureNodeModules(): void
    {
        if (is_dir($this->nodeDir . '/node_modules')) {
            return;
        }

        $command = sprintf('npm install --prefix %s 2>&1', escapeshellarg($this->nodeDir));
        exec($command, $output, $code);

        if ($code !== 0) {
            throw new RuntimeException(
                'Failed to install Node.js dependencies: ' . implode("\n", $output)
            );
        }
    }
}
