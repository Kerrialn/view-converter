<?php

namespace ViewConverter\Command;

use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;

class VueConverterCommand extends Command
{
    protected function configure(): void
    {
        $this->setName('vue:convert')
            ->setDescription('Converts vanilla JS to a Vue 3 Single File Component skeleton')
            ->addArgument('input', InputArgument::REQUIRED, 'JavaScript file or directory path')
            ->addOption('output', null, InputOption::VALUE_OPTIONAL, 'Output directory (uses source directory if omitted)')
            ->addOption('html', null, InputOption::VALUE_OPTIONAL, 'HTML/Twig template file to extract the <template> structure from')
            ->addOption('from-jquery', null, InputOption::VALUE_NONE, 'Run the jQuery converter first, then produce the Vue SFC')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Preview the conversion without writing files');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $path = $input->getArgument('input');
        $dryRun = (bool) $input->getOption('dry-run');
        $outputDir = $input->getOption('output');
        $fromJquery = (bool) $input->getOption('from-jquery');
        $htmlFile = $input->getOption('html');

        if (! file_exists($path)) {
            $io->error("Path not found: $path");
            return Command::FAILURE;
        }

        if ($htmlFile !== null && ! file_exists($htmlFile)) {
            $io->error("HTML file not found: $htmlFile");
            return Command::FAILURE;
        }

        $files = is_file($path)
            ? [$path]
            : $this->findJsFiles($path);

        if (empty($files)) {
            $io->warning("No .js files found in $path");
            return Command::SUCCESS;
        }

        $transformed = 0;
        $skipped = 0;

        foreach ($files as $filePath) {
            $result = $this->transformFile($filePath, $outputDir, $dryRun, $fromJquery, $htmlFile, $io);
            $result ? $transformed++ : $skipped++;
        }

        if (! $dryRun) {
            $io->success(sprintf(
                'Done: %d file%s converted, %d unchanged.',
                $transformed,
                $transformed === 1 ? '' : 's',
                $skipped
            ));
        }

        return Command::SUCCESS;
    }

    private function transformFile(string $filePath, ?string $outputDir, bool $dryRun, bool $fromJquery, ?string $htmlFile, SymfonyStyle $io): bool
    {
        $transformed = $this->runTransformer($filePath, $fromJquery, $htmlFile, $io);

        if ($transformed === null) {
            return false;
        }

        if ($dryRun) {
            $io->section(basename($filePath));
            $io->writeln($transformed);
            return true;
        }

        $destPath = $this->resolveOutputPath($filePath, $outputDir);
        file_put_contents($destPath, $transformed);
        $io->writeln('<info>Converted:</info> ' . $destPath);

        return true;
    }

    private function runTransformer(string $filePath, bool $fromJquery, ?string $htmlFile, SymfonyStyle $io): ?string
    {
        $nodeDir = dirname(__DIR__, 2) . '/node';
        $jqueryScript = $nodeDir . '/jquery-transform.mjs';
        $vueScript    = $nodeDir . '/vue-transform.mjs';

        $this->ensureNodeModules($nodeDir, $io);

        $quotedFile = escapeshellarg($filePath);
        $quotedHtml = $htmlFile !== null ? ' ' . escapeshellarg($htmlFile) : '';

        if ($fromJquery) {
            $command = sprintf(
                'node %s %s | node %s /dev/stdin%s 2>&1',
                escapeshellarg($jqueryScript),
                $quotedFile,
                escapeshellarg($vueScript),
                $quotedHtml
            );
        } else {
            $command = sprintf('node %s %s%s 2>&1', escapeshellarg($vueScript), $quotedFile, $quotedHtml);
        }

        $output = [];
        $code = 0;
        exec($command, $output, $code);

        if ($code !== 0) {
            $io->warning('Skipped ' . basename($filePath) . ': ' . implode(' ', $output));
            return null;
        }

        return implode("\n", $output);
    }

    private function ensureNodeModules(string $nodeDir, SymfonyStyle $io): void
    {
        if (is_dir($nodeDir . '/node_modules')) {
            return;
        }

        $io->info('Installing Node.js dependencies...');
        $command = sprintf('npm install --prefix %s 2>&1', escapeshellarg($nodeDir));
        exec($command, $out, $code);

        if ($code !== 0) {
            throw new RuntimeException('npm install failed: ' . implode("\n", $out));
        }
    }

    private function resolveOutputPath(string $filePath, ?string $outputDir): string
    {
        $name = pathinfo($filePath, PATHINFO_FILENAME);
        $dir = $outputDir !== null
            ? rtrim($outputDir, '/')
            : dirname($filePath);

        return $dir . '/' . $name . '.vue';
    }

    /**
     * @return string[]
     */
    private function findJsFiles(string $directory): array
    {
        $finder = (new Finder())
            ->in($directory)
            ->name('*.js')
            ->notName('*.vanilla.js')
            ->notPath('node_modules')
            ->files();

        return array_map(fn ($f) => $f->getPathname(), iterator_to_array($finder, false));
    }
}
