<?php

namespace ViewConverter\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use ViewConverter\Converter\JsToStimulusConverter;
use ViewConverter\Parser\HtmlScanner;
use ViewConverter\Parser\HtmlUpdater;
use ViewConverter\Parser\JsAstParser;
use ViewConverter\Printer\Stimulus\ControllerPrinter;

class StimulusConverterCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('stimulus:convert')
            ->setDescription('Converts a legacy JavaScript file to a Stimulus controller')
            ->addArgument('input', InputArgument::REQUIRED, 'Path to the JavaScript file')
            ->addOption('html-dir', null, InputOption::VALUE_OPTIONAL, 'Directory to scan for HTML/Twig/Blade templates')
            ->addOption('name', null, InputOption::VALUE_OPTIONAL, 'Override the generated controller name')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Output directory for the generated controller file')
            ->addOption('update-html', null, InputOption::VALUE_NONE, 'Write data-attributes directly into matched template files')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Preview the generated controller without writing a file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $filePath = $input->getArgument('input');

        if (!file_exists($filePath)) {
            $io->error("File not found: $filePath");
            return Command::FAILURE;
        }

        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'js') {
            $io->error("Expected a .js file, got: $filePath");
            return Command::FAILURE;
        }

        $io->info('Parsing JavaScript with Node.js/acorn...');

        try {
            $parsed = (new JsAstParser())->parse($filePath);
        } catch (RuntimeException $e) {
            $io->error($e->getMessage());
            return Command::FAILURE;
        }

        if ($input->getOption('name') !== null) {
            $parsed['controllerName'] = $input->getOption('name');
        }

        $controller = (new JsToStimulusConverter())->convert($parsed);

        // Show parse summary
        $io->definitionList(
            ['Controller name' => $controller->getName()],
            ['Root selector' => $controller->getRootSelector() ?? '(none)'],
            ['Targets' => implode(', ', array_map(fn ($t) => $t->getName(), $controller->getTargets()))],
            ['Actions' => implode(', ', array_map(fn ($a) => $a->getMethodName(), $controller->getActions()))],
            ['Values' => implode(', ', array_map(fn ($v) => $v->getName(), $controller->getValues()))],
        );

        foreach ($controller->getWarnings() as $warning) {
            $io->warning($warning);
        }

        // HTML scan
        $htmlDir = $input->getOption('html-dir');
        if ($htmlDir !== null) {
            $io->section('Template scan: ' . $htmlDir);

            if (!is_dir($htmlDir)) {
                $io->warning("html-dir not found: $htmlDir");
            } else {
                $suggestions = (new HtmlScanner())->scan($htmlDir, $controller);

                if ($suggestions === []) {
                    $io->note('No matching elements found in templates.');
                } else {
                    $lastFile = null;
                    foreach ($suggestions as $s) {
                        if ($s['file'] !== $lastFile) {
                            $io->writeln('<comment>' . $s['file'] . '</comment>');
                            $lastFile = $s['file'];
                        }
                        $io->writeln(sprintf(
                            '  Line %d  <info>%s</info>',
                            $s['line'],
                            $s['suggestion']
                        ));
                        $io->writeln('  <fg=gray>' . $s['original'] . '</>');
                        $io->writeln('');
                    }

                    if ($input->getOption('update-html') && !$input->getOption('dry-run')) {
                        $modified = (new HtmlUpdater())->update($suggestions);
                        foreach ($modified as $path) {
                            $io->writeln('<info>Updated:</info> ' . $path);
                        }
                    }
                }
            }
        }

        // Generate controller JS
        $controllerJs = (new ControllerPrinter())->print($controller);

        if ($input->getOption('dry-run')) {
            $io->section('Generated controller (dry run)');
            $io->writeln($controllerJs);
            return Command::SUCCESS;
        }

        $outputDir = $input->getOption('output') ?? dirname($filePath);
        $outputPath = rtrim($outputDir, '/') . '/' . $controller->getName() . '-controller.js';

        file_put_contents($outputPath, $controllerJs);
        $io->success('Generated: ' . $outputPath);

        return Command::SUCCESS;
    }
}
